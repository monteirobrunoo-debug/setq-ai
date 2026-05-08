# SETQ.AI — Demo Platform

4 horizontal AI agents for enterprise buyers to test on the public website.

## Phase 1 scope (this scaffold)

| Asset | Purpose |
|---|---|
| `prompts/01-setq-assistant.md` | Inbox / calendar / docs persona |
| `prompts/02-setq-operations.md` | Invoicing / project tracking persona |
| `prompts/03-setq-growth.md` | Content / outreach / lead-gen persona |
| `prompts/04-setq-insights.md` | KPI / dashboards / trend analysis persona |
| `app/Agents/Setq*Agent.php` | PHP agent classes (Anthropic-backed, demo mode) |
| `resources/views/setq/landing.blade.php` | Marketing landing replicating the screenshot |
| `public/index.html` | Standalone HTML preview (no Laravel needed for design review) |

## Architecture decisions (to confirm with Bruno)

- **Stack**: Laravel 12 + Anthropic Sonnet (same wiring as ClawYard)
- **Demo mode**: every agent runs against fake data fixtures — no real CRM/SAP.
- **Anon sessions**: 15-min sandbox via cookie + Redis TTL (not implemented yet).
- **Branding**: SETQ.AI corporate, NO references to PartYard / HP-Group / ClawYard.
- **Droplet**: `159.223.4.200` (DigitalOcean), domain `setq.ai`.
- **Repo**: separate from clawyard (to be created on user's GitHub org).

## Phase 1 todo (not done yet)

- [ ] SSH key on `159.223.4.200`
- [ ] Bootstrap Laravel 12 on droplet
- [ ] Forge site `setq.ai` + Postgres DB `setq_ai`
- [ ] Wire 4 agent classes to existing `/api/chat/stream` pattern
- [ ] Anonymous session manager + 15-min Redis TTL
- [ ] Rate limiter (per IP, CAPTCHA-gated)
- [ ] DNS `setq.ai` → droplet IP
- [ ] Let's Encrypt SSL
- [ ] Landing page deployed
- [ ] Demo fixtures (fake invoices, fake leads, fake KPIs)

## Out of scope (Phase 2+)

- Real tenant onboarding + billing
- Production CRM integration
- Hugging Face POC (separate project)
