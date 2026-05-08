<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Analytics · SETQ.AI Admin</title>
<style>
  :root {
    --bg: #0a0a14; --bg2: #11111c; --bg3: #181828;
    --border: #1f1f30; --text: #e8e8ee; --muted: #8a8aa0;
    --cyan: #4dd4ff; --violet: #b15cff; --green: #54e0a3; --blue: #5b8cff;
    --red: #ff6666;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: var(--bg); color: var(--text); font-family: -apple-system, BlinkMacSystemFont, sans-serif; -webkit-font-smoothing: antialiased; }
  header { background: var(--bg2); border-bottom: 1px solid var(--border); padding: 16px 24px; display: flex; align-items: center; gap: 16px; }
  .logo { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 16px; background: linear-gradient(90deg, var(--cyan), var(--violet)); -webkit-background-clip: text; background-clip: text; color: transparent; }
  .badge { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); padding: 4px 10px; border: 1px solid var(--border); border-radius: 999px; }
  main { max-width: 1280px; margin: 0 auto; padding: 32px 24px; }
  h1, h2, h3 { letter-spacing: -0.5px; }
  h1 { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
  .sub { color: var(--muted); font-size: 13px; margin-bottom: 28px; }
  .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px; }
  @media (max-width: 800px) { .grid { grid-template-columns: repeat(2, 1fr); } }
  .kpi { background: var(--bg2); border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; }
  .kpi .label { color: var(--muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
  .kpi .value { font-size: 24px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
  .kpi .delta { color: var(--muted); font-size: 11px; margin-top: 4px; }
  .panel { background: var(--bg2); border: 1px solid var(--border); border-radius: 14px; padding: 22px 24px; margin-bottom: 22px; }
  .panel h2 { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; color: var(--muted); margin-bottom: 16px; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); }
  th { color: var(--muted); font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 700; }
  td.num { font-family: 'JetBrains Mono', monospace; text-align: right; }
  .bar { background: var(--bg3); border-radius: 4px; height: 8px; overflow: hidden; }
  .bar-fill { height: 100%; background: var(--cyan); transition: width .3s; }
  a { color: var(--cyan); text-decoration: none; }
  a:hover { text-decoration: underline; }
  .funnel { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: var(--border); border-radius: 10px; overflow: hidden; }
  .funnel-step { background: var(--bg3); padding: 18px; text-align: center; }
  .funnel-step .v { font-size: 22px; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: var(--cyan); }
  .funnel-step .l { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 1.2px; margin-top: 4px; font-weight: 700; }
  .funnel-step .r { font-size: 10px; color: var(--green); margin-top: 2px; font-family: 'JetBrains Mono', monospace; }
  .agent-pill { font-size: 10px; padding: 3px 8px; border-radius: 999px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }
  .agent-assistant  { background: rgba(77, 212, 255, .12); color: var(--cyan); }
  .agent-operations { background: rgba(177, 92, 255, .12); color: var(--violet); }
  .agent-growth     { background: rgba(84, 224, 163, .12); color: var(--green); }
  .agent-insights   { background: rgba(91, 140, 255, .12); color: var(--blue); }
  .trend-chart { display: flex; gap: 4px; align-items: flex-end; height: 100px; padding-top: 8px; }
  .trend-bar { flex: 1; background: var(--cyan); border-radius: 2px 2px 0 0; min-height: 2px; opacity: .7; transition: opacity .15s; position: relative; }
  .trend-bar:hover { opacity: 1; }
  .trend-bar .tip { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); white-space: nowrap; background: var(--bg3); border: 1px solid var(--border); padding: 4px 8px; border-radius: 4px; font-size: 10px; font-family: 'JetBrains Mono', monospace; opacity: 0; pointer-events: none; transition: opacity .15s; margin-bottom: 4px; }
  .trend-bar:hover .tip { opacity: 1; }
  .trend-axis { display: flex; gap: 4px; margin-top: 4px; font-size: 9px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }
  .trend-axis div { flex: 1; text-align: center; }
</style>
</head>
<body>

<header>
  <div class="logo">SETQ.AI</div>
  <span class="badge">Admin · Analytics</span>
  <a href="/" style="margin-left:auto;font-size:12px;color:var(--muted);">← back to site</a>
</header>

<main>
  <h1>Analytics</h1>
  <p class="sub">Demo usage, costs, lead capture funnel.</p>

  <!-- ── KPIs ── -->
  <div class="grid">
    <div class="kpi">
      <div class="label">Sessions today</div>
      <div class="value">{{ $kpi['sessions_today'] }}</div>
      <div class="delta">{{ $kpi['sessions_week'] }} last 7d</div>
    </div>
    <div class="kpi">
      <div class="label">Messages today</div>
      <div class="value">{{ $kpi['msgs_today'] }}</div>
      <div class="delta">{{ $kpi['msgs_week'] }} last 7d</div>
    </div>
    <div class="kpi">
      <div class="label">Cost today (USD)</div>
      <div class="value">${{ number_format($kpi['cost_today'], 2) }}</div>
      <div class="delta">${{ number_format($kpi['cost_week'], 2) }} last 7d · ${{ number_format($kpi['cost_month'], 2) }} last 30d</div>
    </div>
    <div class="kpi">
      <div class="label">Leads</div>
      <div class="value">{{ $kpi['leads_week'] }}</div>
      <div class="delta">{{ $kpi['leads_total'] }} all-time</div>
    </div>
  </div>

  <!-- ── Funnel ── -->
  <div class="panel">
    <h2>Funnel · last 7 days</h2>
    <div class="funnel">
      <div class="funnel-step">
        <div class="v">{{ $funnel['visited'] }}</div>
        <div class="l">Sessions</div>
      </div>
      <div class="funnel-step">
        <div class="v">{{ $funnel['sent_1plus'] }}</div>
        <div class="l">Sent ≥1 msg</div>
        <div class="r">{{ $funnel['visited'] > 0 ? round($funnel['sent_1plus'] / $funnel['visited'] * 100) : 0 }}%</div>
      </div>
      <div class="funnel-step">
        <div class="v">{{ $funnel['sent_3plus'] }}</div>
        <div class="l">Engaged (≥3 msg)</div>
        <div class="r">{{ $funnel['visited'] > 0 ? round($funnel['sent_3plus'] / $funnel['visited'] * 100) : 0 }}%</div>
      </div>
      <div class="funnel-step">
        <div class="v">{{ $funnel['captured'] }}</div>
        <div class="l">Captured (lead)</div>
        <div class="r">{{ $funnel['visited'] > 0 ? round($funnel['captured'] / $funnel['visited'] * 100) : 0 }}%</div>
      </div>
    </div>
  </div>

  <!-- ── Per-agent breakdown ── -->
  <div class="panel">
    <h2>Per agent · last 7 days</h2>
    <table>
      <thead>
        <tr><th>Agent</th><th>Sessions</th><th>Messages</th><th>Cost (USD)</th><th>Avg latency</th></tr>
      </thead>
      <tbody>
        @forelse(['assistant', 'operations', 'growth', 'insights'] as $a)
          @php $row = $perAgent[$a] ?? null; @endphp
          <tr>
            <td><span class="agent-pill agent-{{ $a }}">{{ ucfirst($a) }}</span></td>
            <td class="num">{{ $row?->sessions ?? 0 }}</td>
            <td class="num">{{ $row?->msgs ?? 0 }}</td>
            <td class="num">${{ number_format((float) ($row?->cost ?? 0), 4) }}</td>
            <td class="num">{{ round((float) ($row?->avg_latency ?? 0)) }} ms</td>
          </tr>
        @empty
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- ── Trend ── -->
  <div class="panel">
    <h2>Daily trend · last 14 days</h2>
    @php
      $maxMsgs = max($trend->max('msgs') ?? 1, 1);
      $axisDates = $trend->pluck('d')->map(fn($d) => substr((string) $d, 5))->all();  // MM-DD
    @endphp
    <div class="trend-chart">
      @forelse($trend as $row)
        <div class="trend-bar" style="height: {{ round(($row->msgs / $maxMsgs) * 100) }}%;">
          <div class="tip">{{ $row->msgs }} msgs · ${{ number_format((float) $row->cost, 4) }}</div>
        </div>
      @empty
        <div style="color:var(--muted);font-size:13px;">No usage yet — buyers haven't started chatting.</div>
      @endforelse
    </div>
    <div class="trend-axis">
      @foreach($axisDates as $d)
        <div>{{ $d }}</div>
      @endforeach
    </div>
  </div>

  <!-- ── Recent leads ── -->
  <div class="panel">
    <h2>Recent leads · last 50</h2>
    <table>
      <thead>
        <tr><th>When</th><th>Email</th><th>Company</th><th>Agent</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($leads as $l)
          <tr>
            <td style="color:var(--muted);font-size:11px;">{{ $l->created_at->diffForHumans() }}</td>
            <td><strong>{{ $l->email }}</strong></td>
            <td>{{ $l->company ?: '—' }}</td>
            <td><span class="agent-pill agent-{{ $l->agent }}">{{ ucfirst($l->agent) }}</span></td>
            <td><span style="color:var(--muted);font-size:11px;">{{ $l->status }}</span></td>
            <td><a href="/admin/leads/{{ $l->id }}">view →</a></td>
          </tr>
        @empty
          <tr><td colspan="6" style="color:var(--muted);text-align:center;padding:24px;">No leads yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
