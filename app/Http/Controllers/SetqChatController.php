<?php

namespace App\Http\Controllers;

use App\Agents\AgentInterface;
use App\Agents\BaseSetqAgent;
use App\Agents\SetqAssistantAgent;
use App\Agents\SetqGrowthAgent;
use App\Agents\SetqInsightsAgent;
use App\Agents\SetqOperationsAgent;
use App\Models\UsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public demo chat for the 4 SETQ.AI agents.
 *
 * Phase 1: anonymous sessions only — no auth, no persistence beyond the
 * 15-minute Cache TTL keyed on a session cookie. Each /demo/{agent} GET
 * issues (or refreshes) the cookie + sandbox slot. POST /demo/{agent}/stream
 * pulls the conversation history from the slot, calls Anthropic, streams
 * SSE back to the browser, then writes the updated history.
 *
 * Rate guarding is left to nginx + Laravel's `throttle` middleware (later).
 */
class SetqChatController extends Controller
{
    /** Map URL slug → concrete agent. */
    protected const AGENTS = [
        'assistant'  => SetqAssistantAgent::class,
        'operations' => SetqOperationsAgent::class,
        'growth'     => SetqGrowthAgent::class,
        'insights'   => SetqInsightsAgent::class,
    ];

    /** Hard session window — expires_at does NOT slide forward. */
    public const SESSION_MINUTES = 15;

    /**
     * GET /demo/{agent} — render the chat page + ensure session cookie.
     *
     * Initialises the session record with a fixed expires_at so the
     * 15-min window is enforced server-side and doesn't reset on each
     * chat turn (was the bug — user could chat indefinitely).
     */
    public function show(Request $request, string $agent)
    {
        $sessionId = $this->ensureSession($request);
        $cacheKey  = "setq:demo:{$agent}:{$sessionId}";

        // First visit OR cookie reused but cache evicted → seed new window
        if (!Cache::has($cacheKey)) {
            $expiresAt = now()->addMinutes(self::SESSION_MINUTES);
            Cache::put($cacheKey, [
                'expires_at' => $expiresAt->timestamp,
                'history'    => [],
            ], $expiresAt);
        }

        $session   = Cache::get($cacheKey);
        $expiresAt = (int) ($session['expires_at'] ?? now()->addMinutes(self::SESSION_MINUTES)->timestamp);

        return response()
            ->view('setq.chat', [
                'agent'        => $agent,
                'sessionId'    => $sessionId,
                'meta'         => $this->agentMeta($agent),
                'expiresAtSec' => $expiresAt,                              // unix ts
                'remainingSec' => max(0, $expiresAt - time()),             // for UI countdown
            ])
            ->cookie('setq_demo', $sessionId, self::SESSION_MINUTES, '/', null, true, true, false, 'lax');
    }

    /**
     * POST /demo/{agent}/stream — SSE chat. Returns 410 Gone if session expired.
     */
    public function stream(Request $request, string $agent)
    {
        $sessionId = $request->cookie('setq_demo') ?: '';
        $message   = trim((string) $request->input('message', ''));

        if ($message === '') {
            abort(422, 'Empty message');
        }
        if (!$sessionId) {
            return response()->json(['error' => 'session_missing'], 410);
        }

        $cacheKey = "setq:demo:{$agent}:{$sessionId}";
        $session  = Cache::get($cacheKey);

        // Server-side hard expiry: if cache evicted or window passed, lock out
        if (!$session || (int) ($session['expires_at'] ?? 0) <= time()) {
            return response()->json([
                'error'   => 'session_expired',
                'message' => 'Your 15-minute demo has expired. Start a fresh sandbox to continue.',
            ], 410);
        }

        $expiresAt = (int) $session['expires_at'];
        $history   = (array) ($session['history'] ?? []);

        // Cap history to last 10 turns (memory + token budget)
        if (count($history) > 20) $history = array_slice($history, -20);

        $agentClass = self::AGENTS[$agent] ?? null;
        if (!$agentClass) abort(404);

        /** @var BaseSetqAgent $instance */
        $instance = new $agentClass();
        $full     = '';
        $started  = microtime(true);
        $errMsg   = null;

        $ip        = $request->ip();
        $userAgent = mb_substr((string) $request->userAgent(), 0, 500);

        return response()->stream(function () use ($instance, $message, $history, $cacheKey, $sessionId, $agent, $ip, $userAgent, $started, $expiresAt, &$full, &$errMsg) {
            // Disable PHP output buffering for true streaming
            while (ob_get_level() > 0) ob_end_flush();

            try {
                $full = $instance->stream($message, $history, function (string $chunk) {
                    echo 'data: ' . json_encode(['chunk' => $chunk], JSON_UNESCAPED_UNICODE) . "\n\n";
                    @flush();
                });
            } catch (\Throwable $e) {
                $errMsg = $e->getMessage();
                echo 'data: ' . json_encode(['error' => $errMsg]) . "\n\n";
                @flush();
            }

            // Persist new turn — TTL is FIXED at the original expires_at,
            // so the 15-min window stays hard (does not slide on activity).
            if ($full !== '') {
                $newHistory = array_merge($history, [
                    ['role' => 'user',      'content' => $message],
                    ['role' => 'assistant', 'content' => $full],
                ]);
                $remainingSec = max(60, $expiresAt - time());
                Cache::put($cacheKey, [
                    'expires_at' => $expiresAt,
                    'history'    => $newHistory,
                ], $remainingSec);
            }

            // Best-effort usage log — failure here doesn't break the response
            try {
                $u = $instance->lastUsage;
                UsageLog::create([
                    'session_id'        => $sessionId,
                    'agent'             => $agent,
                    'ip'                => $ip,
                    'user_agent'        => $userAgent,
                    'model'             => $u['model'] ?: config('services.anthropic.model'),
                    'input_tokens'      => (int) $u['input_tokens'],
                    'output_tokens'     => (int) $u['output_tokens'],
                    'cache_read_tokens' => (int) $u['cache_read_tokens'],
                    'cost_usd'          => UsageLog::computeCost($u['model'] ?: 'sonnet', $u['input_tokens'], $u['output_tokens'], $u['cache_read_tokens']),
                    'latency_ms'        => (int) ((microtime(true) - $started) * 1000),
                    'errored'           => $errMsg !== null,
                    'error_msg'         => $errMsg ? mb_substr($errMsg, 0, 250) : null,
                ]);
            } catch (\Throwable $logErr) {
                Log::warning('UsageLog failed: ' . $logErr->getMessage());
            }

            echo "data: [DONE]\n\n";
            @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',  // tell nginx to not buffer
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * POST /demo/{agent}/reset — wipe sandbox + cookie, redirect to fresh demo.
     * Triggered from the "Start fresh demo" button shown after a 410 expiry.
     */
    public function reset(Request $request, string $agent)
    {
        $sessionId = $request->cookie('setq_demo');
        if ($sessionId) {
            Cache::forget("setq:demo:{$agent}:{$sessionId}");
        }
        return response()->json([
            'ok'       => true,
            'redirect' => route('demo.chat', $agent),
        ])->cookie('setq_demo', '', -2628000, '/');  // expire cookie immediately
    }

    /** Issue a 15-min anonymous sandbox session id. */
    protected function ensureSession(Request $request): string
    {
        $existing = $request->cookie('setq_demo');
        if ($existing && preg_match('/^[a-f0-9]{32}$/', $existing)) {
            return $existing;
        }
        return bin2hex(random_bytes(16));
    }

    /** Display metadata per agent — used by the Blade view. */
    protected function agentMeta(string $agent): array
    {
        return match ($agent) {
            'assistant' => [
                'name'  => 'SETQ Assistant',
                'desc'  => 'Inbox · Calendar · Drafts · CRM',
                'accent' => '#4dd4ff',
                'tags'  => ['Calendar', 'Email Drafts', 'Meeting Briefs', 'CRM Updates'],
            ],
            'operations' => [
                'name'  => 'SETQ Operations',
                'desc'  => 'Invoicing · Projects · Suppliers · Automation',
                'accent' => '#b15cff',
                'tags'  => ['Invoicing', 'Project Tracking', 'Supplier Comms', 'Automation'],
            ],
            'growth' => [
                'name'  => 'SETQ Growth',
                'desc'  => 'Content · Outreach · Lead Scoring · SEO',
                'accent' => '#54e0a3',
                'tags'  => ['Content', 'Email Outreach', 'Lead Scoring', 'SEO Research'],
            ],
            'insights' => [
                'name'  => 'SETQ Insights',
                'desc'  => 'KPIs · Reports · Trends · Dashboards',
                'accent' => '#5b8cff',
                'tags'  => ['KPI Monitoring', 'Auto Reports', 'Trend Analysis', 'Dashboards'],
            ],
        };
    }
}
