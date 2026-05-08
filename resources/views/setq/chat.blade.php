<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $meta['name'] }} — SETQ.AI demo</title>
<style>
  :root {
    --bg: #0a0a14;
    --bg2: #11111c;
    --bg3: #181828;
    --border: #1f1f30;
    --text: #e8e8ee;
    --muted: #8a8aa0;
    --accent: {{ $meta['accent'] }};
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: var(--bg); color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif;
    height: 100vh; display: flex; flex-direction: column;
    -webkit-font-smoothing: antialiased;
  }
  header {
    background: rgba(10, 10, 20, 0.85); backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
    padding: 14px 24px; display: flex; align-items: center; gap: 16px; flex-shrink: 0;
  }
  .back { color: var(--muted); text-decoration: none; font-size: 20px; }
  .back:hover { color: var(--text); }
  .h-info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
  .h-name { font-weight: 800; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
  .h-desc { color: var(--muted); font-size: 12px; }
  .h-timer {
    background: var(--bg3); border: 1px solid var(--border);
    color: var(--accent); padding: 5px 12px; border-radius: 999px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
    font-family: 'JetBrains Mono', monospace;
  }
  #chat {
    flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 16px;
  }
  .empty {
    margin: auto; text-align: center; max-width: 520px; padding: 32px 24px;
  }
  .empty h1 {
    font-size: 28px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.5px;
  }
  .empty h1 span { color: var(--accent); }
  .empty p { color: var(--muted); font-size: 14px; line-height: 1.6; margin-bottom: 22px; }
  .tags { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-bottom: 24px; }
  .tag {
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
    padding: 6px 11px; border-radius: 999px;
    background: rgba(255,255,255,.03); border: 1px solid var(--border); color: var(--muted);
  }
  .starters { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; max-width: 640px; margin: 0 auto; }
  @media (max-width: 600px) { .starters { grid-template-columns: 1fr; } }
  .starter {
    text-align: left; background: var(--bg2); border: 1px solid var(--border);
    color: var(--text); padding: 12px 14px; border-radius: 10px;
    font-size: 13px; line-height: 1.45; cursor: pointer; transition: border-color .15s, background .15s;
  }
  .starter:hover { border-color: var(--accent); background: var(--bg3); }
  .msg { display: flex; gap: 10px; max-width: 760px; width: 100%; margin: 0 auto; }
  .msg.user { justify-content: flex-end; }
  .bubble {
    padding: 12px 16px; border-radius: 14px; max-width: 80%;
    font-size: 14px; line-height: 1.55;
    white-space: pre-wrap; word-wrap: break-word;
  }
  .msg.user .bubble {
    background: var(--accent); color: #000; font-weight: 500;
    border-bottom-right-radius: 4px;
  }
  .msg.ai .bubble {
    background: var(--bg2); border: 1px solid var(--border);
    border-bottom-left-radius: 4px;
  }
  .msg.ai .bubble.error { border-color: #ff4444; color: #ff8888; }
  #input-area {
    border-top: 1px solid var(--border); padding: 14px 24px;
    display: flex; gap: 10px; align-items: flex-end; flex-shrink: 0;
    background: var(--bg2);
  }
  #message {
    flex: 1; background: var(--bg3); color: var(--text);
    border: 1px solid var(--border); border-radius: 12px;
    padding: 12px 14px; font-size: 14px; resize: none; min-height: 44px; max-height: 160px;
    font-family: inherit;
    outline: none;
  }
  #message:focus { border-color: var(--accent); }
  #send {
    background: var(--accent); color: #000; border: none;
    padding: 12px 20px; border-radius: 12px; font-weight: 700;
    font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase; cursor: pointer;
    transition: filter .12s;
  }
  #send:hover { filter: brightness(1.1); }
  #send:disabled { opacity: .4; cursor: not-allowed; }
  .demo-banner {
    background: rgba(255, 200, 80, 0.1); border-bottom: 1px solid rgba(255, 200, 80, 0.25);
    color: #ffc850; padding: 8px 24px; font-size: 11px; text-align: center;
    letter-spacing: 0.5px; flex-shrink: 0;
  }
</style>
</head>
<body>

<header>
  <a href="/" class="back" title="Back to SETQ.AI">←</a>
  <div class="h-info">
    <div class="h-name">{{ $meta['name'] }}</div>
    <div class="h-desc">{{ $meta['desc'] }}</div>
  </div>
  <div class="h-timer" id="timer">15:00</div>
</header>

<div class="demo-banner">⚠ Demo mode — sample data only · sandbox expires in 15 min</div>

<div id="chat">
  <div class="empty" id="empty">
    <h1>Ask <span>{{ $meta['name'] }}</span> anything</h1>
    <p>This is a 15-minute demo running on sample data. Try one of these to start:</p>
    <div class="tags">
      @foreach($meta['tags'] as $t)
        <span class="tag">{{ $t }}</span>
      @endforeach
    </div>
    <div class="starters" id="starters">
      @if($agent === 'assistant')
        <button class="starter" data-prompt="Triage my inbox for today and tell me what to act on first.">Triage my inbox for today</button>
        <button class="starter" data-prompt="Draft a reply to a client asking for a 2-week extension.">Draft a polite delay-request reply</button>
        <button class="starter" data-prompt="Prep a 1-page brief for tomorrow's board meeting.">Prep a board-meeting brief</button>
        <button class="starter" data-prompt="Summarise this thread and propose a meeting agenda.">Summarise + propose agenda</button>
      @elseif($agent === 'operations')
        <button class="starter" data-prompt="An invoice for €12,450 doesn't match the PO — walk me through the reconciliation.">Reconcile a mismatched invoice</button>
        <button class="starter" data-prompt="Design a workflow that escalates supplier non-response after 3 days.">Supplier escalation playbook</button>
        <button class="starter" data-prompt="Project Alpha is 2 weeks late. Build a recovery plan.">Recovery plan for slipping project</button>
        <button class="starter" data-prompt="Compare 3 quotes I received for steel sheet sourcing.">Compare 3 supplier quotes</button>
      @elseif($agent === 'growth')
        <button class="starter" data-prompt="Write a cold email to the COO of a 200-person logistics company about reducing supplier reply lag.">Cold email — logistics COO</button>
        <button class="starter" data-prompt="Score this lead: VP Eng at a Series B SaaS, opened 3 emails, hasn't replied.">Score a stale lead</button>
        <button class="starter" data-prompt="Plan a 3-post LinkedIn series about why most ops AI fails in week 2.">3-post LinkedIn series</button>
        <button class="starter" data-prompt="Find 5 keyword gaps competitors aren't ranking for in 'AI agents for SMBs'.">SEO keyword gap analysis</button>
      @elseif($agent === 'insights')
        <button class="starter" data-prompt="Show last week's revenue breakdown with WoW deltas and the 3 actions I should take.">Last week revenue + actions</button>
        <button class="starter" data-prompt="What's driving the 18% drop in conversion rate for product-tour signups?">Diagnose conversion drop</button>
        <button class="starter" data-prompt="Build a CFO dashboard for monthly close — what KPIs and cadence?">Design a CFO dashboard</button>
        <button class="starter" data-prompt="Forecast next quarter MRR — show assumptions and stress-test.">Forecast MRR with assumptions</button>
      @endif
    </div>
  </div>
</div>

<div id="input-area">
  <textarea id="message" placeholder="Type your message…" rows="1"></textarea>
  <button id="send">Send</button>
</div>

<!-- ── Lead capture slider — appears after ≥3 msgs or ≥5 min ── -->
<style>
  #lead-capture {
    position: fixed; right: 24px; bottom: 24px; width: min(360px, calc(100vw - 48px));
    background: var(--bg2); border: 1px solid var(--border); border-radius: 14px;
    padding: 18px 20px; z-index: 50;
    box-shadow: 0 18px 48px rgba(0,0,0,.6);
    transform: translateY(120%); transition: transform .3s ease;
  }
  #lead-capture.open { transform: translateY(0); }
  #lead-capture .lc-close {
    position: absolute; top: 10px; right: 12px;
    background: none; border: none; color: var(--muted); font-size: 18px; cursor: pointer; line-height: 1;
  }
  #lead-capture .lc-close:hover { color: var(--text); }
  #lead-capture h3 {
    font-size: 14px; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.2px;
  }
  #lead-capture h3 span { color: var(--accent); }
  #lead-capture p {
    color: var(--muted); font-size: 12px; line-height: 1.5; margin-bottom: 12px;
  }
  #lead-capture input, #lead-capture textarea {
    width: 100%; box-sizing: border-box; background: var(--bg3); color: var(--text);
    border: 1px solid var(--border); border-radius: 8px; padding: 9px 11px;
    font-size: 13px; font-family: inherit; margin-bottom: 8px; outline: none;
  }
  #lead-capture input:focus, #lead-capture textarea:focus { border-color: var(--accent); }
  #lead-capture textarea { resize: vertical; min-height: 56px; }
  #lead-capture .lc-disclosure {
    color: var(--muted); font-size: 10px; line-height: 1.45;
    background: rgba(255, 200, 80, 0.06); border: 1px solid rgba(255, 200, 80, 0.2);
    padding: 7px 10px; border-radius: 6px; margin-bottom: 10px;
  }
  #lead-capture button.lc-submit {
    width: 100%; background: var(--accent); color: #000; border: none;
    padding: 10px; border-radius: 8px; font-weight: 700; font-size: 12px;
    text-transform: uppercase; letter-spacing: 0.8px; cursor: pointer;
  }
  #lead-capture button.lc-submit:disabled { opacity: .4; cursor: not-allowed; }
  #lead-capture .lc-success {
    text-align: center; padding: 16px 0; color: var(--accent); font-weight: 600; font-size: 14px;
  }
</style>
<div id="lead-capture">
  <button class="lc-close" onclick="document.getElementById('lead-capture').classList.remove('open')" aria-label="Close">×</button>
  <div id="lc-form-wrap">
    <h3>Liked the <span>{{ $meta['name'] }}</span> demo?</h3>
    <p>Leave your email — we'll set up a 30-min call with the SETQ.AI team to scope a real deployment.</p>
    <form id="lead-form" onsubmit="return submitLead(event)">
      <input type="email"   name="email"    placeholder="you@company.com" required>
      <input type="text"    name="company"  placeholder="Company (optional)">
      <textarea            name="use_case" placeholder="What would you automate first? (optional)"></textarea>
      <div class="lc-disclosure">
        ⚠ The chat we just had will be shared with our team to prep the call.
      </div>
      <button type="submit" class="lc-submit" id="lc-submit-btn">Talk to a real person →</button>
    </form>
  </div>
  <div id="lc-success-wrap" style="display:none;">
    <div class="lc-success">✓ Thanks — we'll be in touch within 1 working day.</div>
  </div>
</div>

<script>
const AGENT       = @json($agent);
const STREAM_URL  = "{{ route('demo.stream', $agent) }}";
const RESET_URL   = "{{ route('demo.reset',  $agent) }}";
const LEAD_URL    = "{{ route('leads.store') }}";
const CSRF        = "{{ csrf_token() }}";
// Server-driven absolute expiry — used for both the visible countdown
// AND for forcing the expired UI when 0 is reached. No more "refresh
// to actually log out" shenanigans (was a bug — client-only timer).
const EXPIRES_AT  = {{ $expiresAtSec }};         // unix seconds, server time
const SERVER_NOW  = {{ time() }};                 // unix seconds, server time at render
const CLIENT_OFFSET = Date.now() / 1000 - SERVER_NOW;  // for clock skew
const chatEl      = document.getElementById('chat');
const emptyEl     = document.getElementById('empty');
const messageEl   = document.getElementById('message');
const sendBtn     = document.getElementById('send');
const timerEl     = document.getElementById('timer');

// ── Lead capture trigger (≥3 user msgs OR ≥5 min) ─────────────
let userMsgCount   = 0;
const sessionStart = Date.now();
const LEAD_OPENED_KEY = 'setq_lead_opened_' + AGENT;

function maybeShowLeadCapture() {
  if (sessionStorage.getItem(LEAD_OPENED_KEY)) return;
  const minutes = (Date.now() - sessionStart) / 60_000;
  if (userMsgCount >= 3 || minutes >= 5) {
    document.getElementById('lead-capture').classList.add('open');
    sessionStorage.setItem(LEAD_OPENED_KEY, '1');
  }
}
setInterval(maybeShowLeadCapture, 30_000);

async function submitLead(e) {
  e.preventDefault();
  const btn  = document.getElementById('lc-submit-btn');
  btn.disabled = true; btn.textContent = 'Sending…';

  const form = e.target;
  const data = new FormData(form);
  data.append('agent', AGENT);

  try {
    const res = await fetch(LEAD_URL, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: data,
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    document.getElementById('lc-form-wrap').style.display = 'none';
    document.getElementById('lc-success-wrap').style.display = 'block';
  } catch (err) {
    btn.disabled = false; btn.textContent = 'Try again';
    alert('Could not send: ' + err.message);
  }
  return false;
}

// Server-driven countdown — uses absolute EXPIRES_AT from server.
// When it hits zero we LOCK the UI (input disabled, expired panel shown)
// AND show a "Start fresh demo" button that calls /demo/{agent}/reset
// to wipe the cookie + sandbox + reload the page. No manual refresh needed.
let expired = false;
function tickTimer() {
  const nowServerSec = (Date.now() / 1000) - CLIENT_OFFSET;
  const secondsLeft  = Math.max(0, Math.floor(EXPIRES_AT - nowServerSec));
  const m = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
  const s = String(secondsLeft % 60).padStart(2, '0');
  timerEl.textContent = `${m}:${s}`;
  if (secondsLeft <= 60) timerEl.style.color = '#ffc850';
  if (secondsLeft === 0 && !expired) {
    expired = true;
    timerEl.style.color = '#ff4444';
    showExpiredOverlay();
  }
}
tickTimer();
setInterval(tickTimer, 1000);

function showExpiredOverlay() {
  // Disable input controls + show full-screen expired panel
  sendBtn.disabled = true;
  messageEl.disabled = true;
  messageEl.placeholder = 'Demo expired';
  document.querySelectorAll('.starter').forEach(b => b.disabled = true);

  // Inject expired overlay if not already there
  if (document.getElementById('expired-overlay')) return;
  const overlay = document.createElement('div');
  overlay.id = 'expired-overlay';
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(10,10,20,0.92);backdrop-filter:blur(6px);z-index:200;display:flex;align-items:center;justify-content:center;animation:fade .25s ease;';
  overlay.innerHTML = `
    <div style="background:#11111c;border:1px solid #1f1f30;border-radius:16px;padding:36px 32px;max-width:420px;text-align:center;">
      <div style="font-size:38px;margin-bottom:14px;">⏱️</div>
      <h2 style="font-size:20px;font-weight:800;letter-spacing:-0.4px;margin-bottom:10px;">Your 15-min demo expired</h2>
      <p style="color:#8a8aa0;font-size:13px;line-height:1.55;margin-bottom:22px;">Liked it? Reach out at <a href="mailto:sales@partyard.eu" style="color:var(--accent);">sales@partyard.eu</a> or start a fresh sandbox with no history.</p>
      <button onclick="resetDemo(this)" style="background:var(--accent);color:#000;border:none;padding:12px 26px;border-radius:10px;font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:0.8px;cursor:pointer;width:100%;">Start fresh demo →</button>
      <a href="/" style="display:block;margin-top:14px;color:#8a8aa0;font-size:12px;text-decoration:none;">← back to all agents</a>
    </div>`;
  document.body.appendChild(overlay);
}

async function resetDemo(btn) {
  if (btn) { btn.disabled = true; btn.textContent = 'Loading…'; }
  try {
    const res = await fetch(RESET_URL, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });
    const data = await res.json().catch(() => ({}));
    location.href = data.redirect || location.pathname;
  } catch (_) {
    location.reload();  // fallback — fresh page = fresh session if cookie was nuked
  }
}

function addMsg(role, text, isError = false) {
  if (emptyEl) emptyEl.style.display = 'none';
  const wrap = document.createElement('div');
  wrap.className = 'msg ' + role;
  const bubble = document.createElement('div');
  bubble.className = 'bubble' + (isError ? ' error' : '');
  bubble.textContent = text;
  wrap.appendChild(bubble);
  chatEl.appendChild(wrap);
  chatEl.scrollTop = chatEl.scrollHeight;
  return bubble;
}

async function send() {
  const text = messageEl.value.trim();
  if (!text || sendBtn.disabled) return;

  addMsg('user', text);
  userMsgCount++;
  maybeShowLeadCapture();
  messageEl.value = '';
  messageEl.style.height = 'auto';
  sendBtn.disabled = true;

  const aiBubble = addMsg('ai', '');
  let streamed = '';

  try {
    const res = await fetch(STREAM_URL, {
      method: 'POST',
      headers: {
        'Content-Type'    : 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN'    : CSRF,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: 'message=' + encodeURIComponent(text),
    });

    // 410 Gone = sandbox expired server-side. Show expired overlay
    // immediately (catches edge case where client clock drifted past 0).
    if (res.status === 410) {
      expired = true;
      showExpiredOverlay();
      aiBubble.remove();
      sendBtn.disabled = true;
      return;
    }
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const reader = res.body.getReader();
    const decoder = new TextDecoder();
    let buf = '';

    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      buf += decoder.decode(value, { stream: true });

      let nl;
      while ((nl = buf.indexOf('\n\n')) !== -1) {
        const block = buf.slice(0, nl);
        buf = buf.slice(nl + 2);
        if (!block.startsWith('data: ')) continue;
        const json = block.slice(6).trim();
        if (json === '[DONE]') { sendBtn.disabled = false; messageEl.focus(); return; }
        try {
          const evt = JSON.parse(json);
          if (evt.error) {
            aiBubble.textContent = '❌ ' + evt.error;
            aiBubble.classList.add('error');
            sendBtn.disabled = false;
            return;
          }
          if (evt.chunk !== undefined) {
            streamed += evt.chunk;
            aiBubble.textContent = streamed;
            chatEl.scrollTop = chatEl.scrollHeight;
          }
        } catch (_) { /* ignore non-json lines */ }
      }
    }
    sendBtn.disabled = false;
    messageEl.focus();
  } catch (e) {
    aiBubble.textContent = '❌ ' + e.message;
    aiBubble.classList.add('error');
    sendBtn.disabled = false;
  }
}

sendBtn.addEventListener('click', send);
messageEl.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
});
messageEl.addEventListener('input', () => {
  messageEl.style.height = 'auto';
  messageEl.style.height = Math.min(160, messageEl.scrollHeight) + 'px';
});
document.querySelectorAll('.starter').forEach(b => {
  b.addEventListener('click', () => {
    messageEl.value = b.dataset.prompt;
    send();
  });
});
messageEl.focus();
</script>
</body>
</html>
