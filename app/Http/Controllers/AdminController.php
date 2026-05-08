<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\UsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * GET /admin/analytics — full demo + cost dashboard.
     */
    public function analytics()
    {
        $today = Carbon::today();
        $weekAgo  = Carbon::today()->subDays(7);
        $monthAgo = Carbon::today()->subDays(30);

        // ── Headline KPIs ─────────────────────────────────────────
        $kpi = [
            'sessions_today'  => UsageLog::whereDate('created_at', $today)->distinct('session_id')->count('session_id'),
            'sessions_week'   => UsageLog::where('created_at', '>=', $weekAgo)->distinct('session_id')->count('session_id'),
            'msgs_today'      => UsageLog::whereDate('created_at', $today)->count(),
            'msgs_week'       => UsageLog::where('created_at', '>=', $weekAgo)->count(),
            'cost_today'      => round((float) UsageLog::whereDate('created_at', $today)->sum('cost_usd'), 4),
            'cost_week'       => round((float) UsageLog::where('created_at', '>=', $weekAgo)->sum('cost_usd'), 4),
            'cost_month'      => round((float) UsageLog::where('created_at', '>=', $monthAgo)->sum('cost_usd'), 4),
            'tokens_in_week'  => (int) UsageLog::where('created_at', '>=', $weekAgo)->sum('input_tokens'),
            'tokens_out_week' => (int) UsageLog::where('created_at', '>=', $weekAgo)->sum('output_tokens'),
            'errors_week'     => UsageLog::where('created_at', '>=', $weekAgo)->where('errored', true)->count(),
            'leads_total'     => Lead::count(),
            'leads_week'      => Lead::where('created_at', '>=', $weekAgo)->count(),
        ];

        // ── Per-agent breakdown ───────────────────────────────────
        $perAgent = UsageLog::selectRaw('agent, COUNT(*) as msgs, COUNT(DISTINCT session_id) as sessions, SUM(cost_usd) as cost, AVG(latency_ms) as avg_latency')
            ->where('created_at', '>=', $weekAgo)
            ->groupBy('agent')
            ->get()
            ->keyBy('agent');

        // ── Funnel (engaged sessions = ≥3 messages, captured = lead form submitted) ─
        $sessionMsgCounts = UsageLog::selectRaw('session_id, agent, COUNT(*) as msgs')
            ->where('created_at', '>=', $weekAgo)
            ->groupBy('session_id', 'agent')
            ->get();

        $funnel = [
            'visited'     => $sessionMsgCounts->count(),
            'sent_1plus'  => $sessionMsgCounts->where('msgs', '>=', 1)->count(),
            'sent_3plus'  => $sessionMsgCounts->where('msgs', '>=', 3)->count(),
            'captured'    => Lead::where('created_at', '>=', $weekAgo)->distinct('session_id')->count('session_id'),
        ];

        // ── Daily trend (last 14 days) ────────────────────────────
        $trend = UsageLog::selectRaw('DATE(created_at) as d, COUNT(*) as msgs, COUNT(DISTINCT session_id) as sess, SUM(cost_usd) as cost')
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        // ── Recent leads ──────────────────────────────────────────
        $leads = Lead::orderByDesc('created_at')->limit(50)->get();

        return view('admin.analytics', compact('kpi', 'perAgent', 'funnel', 'trend', 'leads'));
    }

    /**
     * GET /admin/leads/{id} — view single lead with full transcript.
     */
    public function showLead(int $id)
    {
        $lead = Lead::findOrFail($id);
        return view('admin.lead', compact('lead'));
    }
}
