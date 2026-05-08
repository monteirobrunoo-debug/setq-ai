<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SETQ.AI — AI agents that get the work done</title>
<meta name="description" content="Four enterprise AI agents — Assistant, Operations, Growth, Insights — handling inbox, invoices, outreach and dashboards on autopilot.">
<style>
  :root {
    --bg: #0a0a14;
    --bg2: #11111c;
    --bg3: #181828;
    --border: #1f1f30;
    --text: #e8e8ee;
    --muted: #8a8aa0;
    --cyan: #4dd4ff;
    --violet: #b15cff;
    --green: #54e0a3;
    --blue: #5b8cff;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: linear-gradient(180deg, #0a0a14 0%, #0d0d1a 100%);
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
    min-height: 100vh;
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
  }
  /* ── HEADER ── */
  header {
    position: sticky; top: 0; z-index: 50;
    background: rgba(10, 10, 20, 0.7);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
    padding: 14px 32px;
    display: flex; align-items: center; gap: 28px;
  }
  .logo {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-weight: 800;
    font-size: 18px;
    letter-spacing: -0.5px;
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .nav {
    display: flex; gap: 22px; align-items: center;
    border-left: 1px solid var(--border);
    padding-left: 28px;
  }
  .nav a {
    color: var(--muted);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: color .15s;
  }
  .nav a:hover { color: var(--text); }
  .nav .has-caret::after { content: ' ▾'; opacity: .6; }
  .cta-btn {
    margin-left: auto;
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    color: #000;
    font-weight: 700;
    padding: 10px 22px;
    border-radius: 999px;
    text-decoration: none;
    font-size: 13px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: transform .12s;
  }
  .cta-btn:hover { transform: scale(1.04); }

  /* ── HERO ── */
  .hero {
    text-align: center;
    padding: 96px 32px 64px;
    max-width: 920px;
    margin: 0 auto;
  }
  .hero h1 {
    font-size: clamp(36px, 5vw, 60px);
    line-height: 1.1;
    font-weight: 800;
    letter-spacing: -1.5px;
    margin-bottom: 22px;
  }
  .hero h1 .accent {
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .hero p {
    color: var(--muted);
    font-size: 18px;
    max-width: 640px;
    margin: 0 auto 40px;
  }

  /* ── AGENT GRID ── */
  .grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    max-width: 1280px;
    margin: 0 auto;
    padding: 24px 32px 80px;
  }
  @media (max-width: 1024px) { .grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 600px)  { .grid { grid-template-columns: 1fr; padding: 24px 16px 48px; } }

  .card {
    background: linear-gradient(180deg, var(--bg2) 0%, var(--bg) 100%);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 22px;
    display: flex; flex-direction: column;
    transition: transform .18s, border-color .18s, box-shadow .18s;
    position: relative;
    overflow: hidden;
  }
  .card::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at top right, var(--accent, transparent) 0%, transparent 50%);
    opacity: .12;
    pointer-events: none;
  }
  .card:hover {
    transform: translateY(-4px);
    border-color: var(--accent);
    box-shadow: 0 16px 48px -12px rgba(0,0,0,.6), 0 0 0 1px var(--accent);
  }

  /* per-card accent */
  .card[data-agent="assistant"]  { --accent: var(--cyan); }
  .card[data-agent="operations"] { --accent: var(--violet); }
  .card[data-agent="growth"]     { --accent: var(--green); }
  .card[data-agent="insights"]   { --accent: var(--blue); }

  .card .icon-tile {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--accent);
    margin-bottom: 16px;
  }
  .card .icon-tile svg { width: 22px; height: 22px; }
  .card .portrait {
    width: 100%;
    aspect-ratio: 1.05;
    border-radius: 14px;
    margin-bottom: 18px;
    background: linear-gradient(135deg, var(--accent) 0%, transparent 100%);
    border: 1px solid var(--border);
    position: relative;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 56px;
    color: rgba(0,0,0,.35);
    font-weight: 800;
  }
  .card .portrait::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 50%, rgba(10,10,20,.85) 100%);
  }
  .card h3 {
    font-size: 18px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
  }
  .card .desc {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.55;
    flex: 1;
    margin-bottom: 18px;
  }
  .tags {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin-bottom: 18px;
  }
  .tag {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 6px 11px;
    border-radius: 999px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border);
    color: var(--muted);
  }
  .test-btn {
    background: var(--accent);
    color: #000;
    border: none;
    padding: 11px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: filter .12s;
  }
  .test-btn:hover { filter: brightness(1.1); }
  .explore {
    color: var(--muted);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    text-decoration: none;
    margin-top: 12px;
    text-align: left;
  }
  .explore:hover { color: var(--text); }

  /* ── FOOTER ── */
  footer {
    border-top: 1px solid var(--border);
    padding: 24px 32px;
    text-align: center;
    color: var(--muted);
    font-size: 12px;
  }
</style>
</head>
<body>

<header>
  <div class="logo">SETQ.AI</div>
  <nav class="nav">
    <a href="/">Home</a>
    <a href="/about">About Us</a>
    <a href="/platforms" class="has-caret">Platforms</a>
    <a href="/news">News</a>
    <a href="/pricing">Pricing</a>
  </nav>
  <a href="/get-started" class="cta-btn">Get Started</a>
</header>

<section class="hero">
  <h1>AI agents that <span class="accent">get the work done</span>.</h1>
  <p>Four specialists, fluent in your stack. They draft, decide, follow up — so nothing slips through.</p>
</section>

<section class="grid">
  <!-- Assistant -->
  <div class="card" data-agent="assistant">
    <div class="icon-tile">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="portrait">A</div>
    <h3>SETQ Assistant</h3>
    <p class="desc">Inbox management, calendar scheduling, document drafting &amp; meeting coordination — on autopilot.</p>
    <div class="tags">
      <span class="tag">Calendar</span>
      <span class="tag">Email Drafts</span>
      <span class="tag">Meeting Briefs</span>
      <span class="tag">CRM Updates</span>
    </div>
    <a href="{{ route('demo.chat', 'assistant') }}" class="test-btn">Test 15 min</a>
  </div>

  <!-- Operations -->
  <div class="card" data-agent="operations">
    <div class="icon-tile">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7h-3V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM9 5h6v2H9z"/></svg>
    </div>
    <div class="portrait">O</div>
    <h3>SETQ Operations</h3>
    <p class="desc">Invoice processing, project tracking, supplier coordination &amp; full workflow automation.</p>
    <div class="tags">
      <span class="tag">Invoicing</span>
      <span class="tag">Project Tracking</span>
      <span class="tag">Supplier Comms</span>
      <span class="tag">Automation</span>
    </div>
    <a href="{{ route('demo.chat', 'operations') }}" class="test-btn">Test 15 min</a>
  </div>

  <!-- Growth -->
  <div class="card" data-agent="growth">
    <div class="icon-tile">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
    </div>
    <div class="portrait">G</div>
    <h3>SETQ Growth</h3>
    <p class="desc">Content creation, personalised outreach, lead qualification &amp; campaign performance reporting.</p>
    <div class="tags">
      <span class="tag">Content</span>
      <span class="tag">Email Outreach</span>
      <span class="tag">Lead Scoring</span>
      <span class="tag">SEO Research</span>
    </div>
    <a href="{{ route('demo.chat', 'growth') }}" class="test-btn">Test 15 min</a>
  </div>

  <!-- Insights -->
  <div class="card" data-agent="insights">
    <div class="icon-tile">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    </div>
    <div class="portrait">I</div>
    <h3>SETQ Insights</h3>
    <p class="desc">Real-time KPI monitoring, automated reports, trend analysis &amp; live custom dashboards.</p>
    <div class="tags">
      <span class="tag">KPI Monitoring</span>
      <span class="tag">Auto Reports</span>
      <span class="tag">Trend Analysis</span>
      <span class="tag">Dashboards</span>
    </div>
    <a href="{{ route('demo.chat', 'insights') }}" class="test-btn">Test 15 min</a>
  </div>
</section>

<footer>
  &copy; 2026 SETQ.AI · All rights reserved
</footer>

</body>
</html>
