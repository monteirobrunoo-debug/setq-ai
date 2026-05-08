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

<script>
const AGENT       = @json($agent);
const STREAM_URL  = "{{ route('demo.stream', $agent) }}";
const CSRF        = "{{ csrf_token() }}";
const chatEl      = document.getElementById('chat');
const emptyEl     = document.getElementById('empty');
const messageEl   = document.getElementById('message');
const sendBtn     = document.getElementById('send');
const timerEl     = document.getElementById('timer');

// 15-min countdown — visual only; server enforces via Cache TTL
let secondsLeft = 15 * 60;
setInterval(() => {
  secondsLeft = Math.max(0, secondsLeft - 1);
  const m = String(Math.floor(secondsLeft / 60)).padStart(2, '0');
  const s = String(secondsLeft % 60).padStart(2, '0');
  timerEl.textContent = `${m}:${s}`;
  if (secondsLeft === 0) {
    sendBtn.disabled = true;
    timerEl.style.color = '#ff4444';
    addMsg('ai', 'Demo expired. Refresh the page to start a new 15-minute sandbox.', true);
  }
}, 1000);

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
