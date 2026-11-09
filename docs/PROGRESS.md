# Progress Tracker

Milestones are defined in `IMPLEMENTATION_ROADMAP.md`. Add a section per milestone using the template below, newest at the bottom.

## Status board

| Milestone | Name | Status |
|---|---|---|
| M0 | Discovery and architecture | Complete |
| M1 | Figma design | Not started |
| M2 | Canva assets | Not started |
| M3 | Foundation | Not started |
| M4 | Full schema | Not started |
| M5 | Catalog and CMS services | Not started |
| M6 | Currency and pricing | Not started |
| M7 | Orders and checkout core | Not started |
| M8 | Supplier engine and Reloadly | Not started |
| M9 | Fulfilment and digital codes | Not started |
| M10 | Payments | Not started |
| M11 | Wallet, promotions, affiliates, reviews, support | Not started |
| M12 | Storefront | Not started |
| M13 | Customer account | Not started |
| M14 | Admin core | Not started |
| M15 | Admin remaining | Not started |
| M16 | MCP server | Not started |
| M17 | n8n workflows | Not started |
| M18 | SEO and analytics | Not started |
| M19 | Full test pass | Not started |
| M20 | Production readiness | Not started |

Status values: Not started, In progress, Blocked (state why), Complete.

## Integration status

| Integration | Adapter | Mock tests | Sandbox verified | Production |
|---|---|---|---|---|
| Reloadly Gift Cards | Not built | No | Awaiting credentials | No |
| Stripe | Not built | No | Awaiting credentials | No |
| PayPal | Not built | No | Awaiting credentials | No |
| Paymob | Not built | No | Awaiting credentials | No |
| Open Exchange Rates | Not built | No | Awaiting credentials | No |
| GA4 Measurement Protocol | Not built | No | Awaiting credentials | No |
| Google Search Console | Not built | No | Awaiting credentials | No |
| n8n | Not built | No | Not installed on this machine | No |

## Business decisions

Tracked as BD1 to BD9 in `IMPLEMENTATION_ROADMAP.md`. Record each outcome and date here.

| ID | Decision | Outcome | Date |
|---|---|---|---|
| BD1 | Stripe merchant entity | Open | |
| BD2 | Direct game top-up route | Open | |
| BD3 | Wallet scope | Open | |
| BD4 | Refund policy | Open | |
| BD5 | Launch languages | Open | |
| BD6 | Base currency | Open | |
| BD7 | Tax | Open | |
| BD8 | Resale rights and brand use | Open | |
| BD9 | Alert channel, backups, GeoIP, LLM | Open | |

---

## Section template (copy per milestone)

```markdown
## M<n>: <name>

Date completed:
Completed by:

### Summary of completed work

### Files created

### Migrations

### Features implemented

### Tests and results
- Command:
- Result (passed, failed, skipped):
- Not tested, and why:

### Outstanding requirements

### Next milestone
```

---

## M0: Discovery and architecture

Date completed: 2026-11-10
Completed by: Architect / Business Analyst

### Summary of completed work

Reviewed the Laravel 13 skeleton and environment. Researched official provider documentation. Produced the architecture, schema, API, integration, security, SEO, n8n, MCP, deployment and roadmap documents. No application code, packages, migrations or commits.

### Files created

`docs/PROJECT_ARCHITECTURE.md`, `docs/DATABASE_DESIGN.md`, `docs/API_DOCUMENTATION.md`, `docs/INTEGRATIONS.md`, `docs/SECURITY.md`, `docs/SEO_STRATEGY.md`, `docs/N8N_AUTOMATIONS.md`, `docs/MCP_SERVER.md`, `docs/DEPLOYMENT.md`, `docs/IMPLEMENTATION_ROADMAP.md`, `docs/PROGRESS.md`

### Migrations

None.

### Features implemented

None (documentation milestone).

### Tests and results

Not applicable. No code was written.

### Outstanding requirements

- App MySQL login (being fixed by the user).
- Business decisions BD1 to BD9.
- All provider credentials.
- Items marked `UNVERIFIED` in `INTEGRATIONS.md`, to be resolved at M20 or earlier when sandbox access exists.

### Next milestone

M1: Figma design. M3 can run in parallel once the database login works.
