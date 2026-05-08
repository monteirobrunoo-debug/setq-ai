<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Captures buyers who liked a demo and want to be contacted.
 *
 * Triggered from the chat page slider (after ≥3 messages or ≥5 min).
 * Stores in `leads` table + sends email to LEAD_NOTIFY_EMAIL.
 */
class LeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email', 'max:200'],
            'company'  => ['nullable', 'string', 'max:200'],
            'use_case' => ['nullable', 'string', 'max:2000'],
            'agent'    => ['required', 'in:assistant,operations,growth,insights'],
        ]);

        $sessionId = (string) $request->cookie('setq_demo', '');
        $cacheKey  = "setq:demo:{$data['agent']}:{$sessionId}";
        $session   = Cache::get($cacheKey);
        // Cache value can be:
        //   • new format: ['expires_at' => ts, 'history' => [...]]
        //   • legacy: just an array of messages (pre-2026-05-08 expiry fix)
        if (is_array($session) && isset($session['history'])) {
            $history = $session['history'];
        } elseif (is_array($session)) {
            $history = $session;
        } else {
            $history = [];
        }
        $transcript = array_slice($history, -10);  // last 10 turns max

        $lead = Lead::create([
            'email'      => $data['email'],
            'company'    => $data['company'] ?? null,
            'use_case'   => $data['use_case'] ?? null,
            'agent'      => $data['agent'],
            'session_id' => $sessionId,
            'transcript' => $transcript,
            'ip'         => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'referrer'   => mb_substr((string) $request->header('referer', ''), 0, 500),
            'status'     => Lead::STATUS_NEW,
        ]);

        // Best-effort notification — log failure but don't fail the request
        try {
            $this->notifyTeam($lead);
            $lead->update(['notified' => true, 'notified_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Lead notify failed: ' . $e->getMessage(), ['lead_id' => $lead->id]);
        }

        return response()->json([
            'ok'      => true,
            'message' => "Thanks — we'll be in touch shortly.",
        ]);
    }

    protected function notifyTeam(Lead $lead): void
    {
        $to = config('mail.lead_notify_to');
        if (!$to) {
            Log::info('Lead captured but LEAD_NOTIFY_EMAIL not set — skipping email', ['lead_id' => $lead->id]);
            return;
        }

        $subject = "🎯 New SETQ.AI demo lead — {$lead->email} ({$lead->agent})";

        $bodyLines = [
            "New lead captured from the {$lead->agent} demo:",
            '',
            "Email: {$lead->email}",
            "Company: " . ($lead->company ?: '(not provided)'),
            "Use case: " . ($lead->use_case ?: '(not provided)'),
            "Agent: {$lead->agent}",
            "Captured at: " . $lead->created_at->toIso8601String(),
            "IP: {$lead->ip}",
            '',
            '── Transcript (last 10 turns) ──',
            '',
        ];

        foreach (($lead->transcript ?? []) as $msg) {
            $role = strtoupper($msg['role'] ?? '?');
            $text = mb_substr((string) ($msg['content'] ?? ''), 0, 800);
            $bodyLines[] = "[{$role}] {$text}";
            $bodyLines[] = '';
        }

        $bodyLines[] = '— SETQ.AI demo capture';

        Mail::raw(implode("\n", $bodyLines), function ($m) use ($to, $subject) {
            $m->to($to)->subject($subject);
        });
    }
}
