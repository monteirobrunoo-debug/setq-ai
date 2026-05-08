<?php

namespace App\Http\Controllers;

use App\Agents\AgentInterface;
use App\Agents\SetqAssistantAgent;
use App\Agents\SetqGrowthAgent;
use App\Agents\SetqInsightsAgent;
use App\Agents\SetqOperationsAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    /**
     * GET /demo/{agent} — render the chat page + ensure session cookie.
     */
    public function show(Request $request, string $agent)
    {
        $sessionId = $this->ensureSession($request);

        return response()
            ->view('setq.chat', [
                'agent'     => $agent,
                'sessionId' => $sessionId,
                'meta'      => $this->agentMeta($agent),
            ])
            ->cookie('setq_demo', $sessionId, 15, '/', null, true, true, false, 'lax');
    }

    /**
     * POST /demo/{agent}/stream — SSE chat.
     */
    public function stream(Request $request, string $agent): StreamedResponse
    {
        $sessionId = $request->cookie('setq_demo') ?: $this->ensureSession($request);
        $message   = trim((string) $request->input('message', ''));

        if ($message === '') {
            abort(422, 'Empty message');
        }

        $cacheKey = "setq:demo:{$agent}:{$sessionId}";
        $history  = Cache::get($cacheKey, []);

        // Cap history to last 10 turns (memory + token budget)
        if (count($history) > 20) $history = array_slice($history, -20);

        $agentClass = self::AGENTS[$agent] ?? null;
        if (!$agentClass) abort(404);

        /** @var AgentInterface $instance */
        $instance = new $agentClass();
        $full     = '';

        return response()->stream(function () use ($instance, $message, $history, $cacheKey, &$full) {
            // Disable PHP output buffering for true streaming
            while (ob_get_level() > 0) ob_end_flush();

            try {
                $full = $instance->stream($message, $history, function (string $chunk) {
                    echo 'data: ' . json_encode(['chunk' => $chunk], JSON_UNESCAPED_UNICODE) . "\n\n";
                    @flush();
                });
            } catch (\Throwable $e) {
                echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
                @flush();
                return;
            }

            // Persist the turn (15-min TTL, slides forward on each interaction)
            $newHistory = array_merge($history, [
                ['role' => 'user',      'content' => $message],
                ['role' => 'assistant', 'content' => $full],
            ]);
            Cache::put($cacheKey, $newHistory, now()->addMinutes(15));

            echo "data: [DONE]\n\n";
            @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',  // tell nginx to not buffer
            'Connection'        => 'keep-alive',
        ]);
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
