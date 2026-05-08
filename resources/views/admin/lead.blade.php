<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lead {{ $lead->id }} · SETQ.AI Admin</title>
<style>
  body { background: #0a0a14; color: #e8e8ee; font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; }
  header { background: #11111c; border-bottom: 1px solid #1f1f30; padding: 16px 24px; display: flex; gap: 16px; align-items: center; }
  a { color: #4dd4ff; text-decoration: none; }
  main { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
  h1 { font-size: 22px; letter-spacing: -0.5px; margin-bottom: 8px; }
  .meta { color: #8a8aa0; font-size: 13px; margin-bottom: 24px; }
  .panel { background: #11111c; border: 1px solid #1f1f30; border-radius: 12px; padding: 22px; margin-bottom: 18px; }
  .panel h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 1.2px; color: #8a8aa0; font-weight: 800; margin-bottom: 12px; }
  .row { display: grid; grid-template-columns: 140px 1fr; gap: 12px; margin-bottom: 8px; font-size: 14px; }
  .row .k { color: #8a8aa0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px; padding-top: 2px; }
  .msg { border-left: 3px solid #1f1f30; padding: 8px 14px; margin-bottom: 10px; }
  .msg.user { border-color: #4dd4ff; }
  .msg.assistant { border-color: #b15cff; }
  .msg .role { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #8a8aa0; margin-bottom: 4px; font-weight: 700; }
  .msg .body { font-size: 14px; line-height: 1.55; white-space: pre-wrap; }
</style>
</head>
<body>

<header>
  <a href="/admin/analytics">← Analytics</a>
  <span style="color:#8a8aa0;font-size:13px;">/ Lead #{{ $lead->id }}</span>
</header>

<main>
  <h1>{{ $lead->email }}</h1>
  <div class="meta">{{ $lead->created_at->format('Y-m-d H:i') }} UTC · captured from <strong>{{ ucfirst($lead->agent) }}</strong> demo</div>

  <div class="panel">
    <h2>Contact</h2>
    <div class="row"><div class="k">Email</div><div>{{ $lead->email }}</div></div>
    <div class="row"><div class="k">Company</div><div>{{ $lead->company ?: '—' }}</div></div>
    <div class="row"><div class="k">Use case</div><div style="white-space:pre-wrap;">{{ $lead->use_case ?: '—' }}</div></div>
  </div>

  <div class="panel">
    <h2>Tracking</h2>
    <div class="row"><div class="k">IP</div><div>{{ $lead->ip ?? '—' }}</div></div>
    <div class="row"><div class="k">User agent</div><div style="font-size:12px;color:#8a8aa0;">{{ $lead->user_agent ?? '—' }}</div></div>
    <div class="row"><div class="k">Referrer</div><div style="font-size:12px;">{{ $lead->referrer ?: '—' }}</div></div>
    <div class="row"><div class="k">Notified</div><div>{{ $lead->notified ? '✓ ' . optional($lead->notified_at)->diffForHumans() : '—' }}</div></div>
    <div class="row"><div class="k">Status</div><div>{{ $lead->status }}</div></div>
  </div>

  <div class="panel">
    <h2>Transcript ({{ count($lead->transcript ?? []) }} turns)</h2>
    @forelse($lead->transcript ?? [] as $msg)
      <div class="msg {{ $msg['role'] ?? 'assistant' }}">
        <div class="role">{{ $msg['role'] ?? 'assistant' }}</div>
        <div class="body">{{ $msg['content'] ?? '' }}</div>
      </div>
    @empty
      <div style="color:#8a8aa0;font-size:13px;">No transcript captured.</div>
    @endforelse
  </div>
</main>

</body>
</html>
