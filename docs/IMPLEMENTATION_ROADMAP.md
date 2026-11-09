# Implementation Roadmap

Sequence follows spec Phase 17. Run milestones one at a time; a milestone is complete only when its acceptance criteria pass and `PROGRESS.md` has been updated.

Rule: backend milestones (M3 to M10) do not depend on the visual design and may run in parallel with M1 and M2 if the PM chooses. Frontend milestones (M12 to M15) must not start until M1 is reviewed.

## Business decisions needed

| ID | Decision | Options | Recommendation | Blocks |
|---|---|---|---|---|
| BD1 | Merchant entity for Stripe. Stripe's availability page lists UAE but not Egypt or Saudi Arabia. | (a) Entity in a supported country such as UAE, (b) launch with PayPal and Paymob only | (a) if international card volume is a goal; otherwise (b) to start | Stripe at go-live, not development |
| BD2 | Direct game top-up. Reloadly cannot do player-ID top-up. | (a) Add a specialist supplier, (b) manual fulfilment, (c) defer those products | (b) at launch for selected products; research (a) | Product catalog scope |
| BD3 | Wallet scope | (a) Credits and refunds only, (b) customer top-up | (a), since stored value raises licensing and AML questions | M11 |
| BD4 | Refund policy for digital goods | (a) Undelivered only, (b) unrevealed codes too, (c) discretionary | (a) by default, with (c) by admin permission | Terms, M10 |
| BD5 | Launch languages | `en`; `en` and `ar` (RTL) | `en` and `ar`, given the Egypt market; decide before M1 as RTL affects design | M1 |
| BD6 | Base currency | USD; other | USD, matches suppliers and FX provider | M6 |
| BD7 | Tax. Spec is silent; digital goods can trigger VAT (Egypt, EU). | Tax-inclusive pricing with accountant advice; or build a tax engine | Tax-inclusive at launch with professional advice; columns are ready (ADR-022) | Go-live |
| BD8 | Gift card resale rights and brand use per platform | Obtain from suppliers and brands | Confirm before using logos or listing each brand | M2, go-live |
| BD9 | Alert channel, backup storage, GeoIP key, LLM key | Choose providers | Slack or Telegram; S3-compatible storage; MaxMind free tier; Anthropic | M17, M20 |

## Definition of done (every milestone)

Migrations reversible where practical. Pint clean. Pest passing for the milestone's scope on MySQL `backend_testing`. No N+1 on new listings (`Model::preventLazyLoading()` in tests). No TODOs. Docs updated. `PROGRESS.md` section added. External integrations labelled honestly.

## Milestones

### M0: Discovery and architecture

Done by this document set. Accept: all docs in `docs/` exist.

### M1: Figma design (Step 2)

Depends: M0, BD5. Scope: spec Phase 2.
Accept: design system with variables, components and variants; storefront, checkout, account, auth and admin screens at desktop, tablet and mobile; consistency reviewed; checkout shows display price, payment amount and payment currency; supplier capability matrix, order timeline, reveal flow designed; file link recorded.

### M2: Canva assets (Step 3)

Depends: M1, BD8. Scope: spec Phase 3.
Accept: assets exported as WebP or AVIF in `backend/resources/assets/` or media seed folder, named per placement; missing assets listed; no invented official logos; licensing note for each.

### M3: Foundation (Step 4)

Depends: M0, DB login fixed.
Scope: install packages from architecture section 4 (Sanctum, Permission, DataTables, Stripe SDK, Image). `backend_testing` database and `phpunit.xml`. Domain skeleton, `Money` VO, JSON exception envelope, `Http::preventStrayRequests`, audit writer, settings service, redaction processor, roles and permissions seed, auth (register, login, verify, reset, 2FA for staff), route files.
Accept:
- Tests: Money arithmetic and rounding; envelope shape for 401, 403, 404, 422, 429, 500; auth flows; staff without 2FA denied; each role only reaches its permissions; canary redaction in logs.
- Arch tests: no floats in Money DTOs; controllers do not use Eloquent directly; domain repository isolation.

### M4: Full schema (Step 4)

Depends: M3. Scope: all migrations in `DATABASE_DESIGN.md` order, models with casts and relations, factories, seeders.
Accept: `migrate:fresh --seed` succeeds on MySQL; `migrate:rollback` succeeds; test proves `uq_st_active_unit` rejects a second active attempt and allows one after `not_accepted_verified`; test proves `uq_o_primary_payment` and `uq_pwh`; every table in section 17 exists.

### M5: Catalog and CMS services (Step 4)

Depends: M4. Scope: services, repositories, admin JSON endpoints for catalog, CMS, media, search index.
Accept: CRUD with permissions; listing filter query under defined N queries; search by game, platform, product, SKU; delivery field validation; slug reserved words; tests pass.

### M6: Currency and pricing (Step 4)

Depends: M4, BD6. Scope: FX provider contract and OXR adapter, history, overrides, spike guard, staleness, `PricingEngine`, margin guard, recalculation job.
Accept: tests for each precedence level in architecture 11.5; explicit price beats markup; margin policy `floor`, `block`, `allow`; override needs permission and audit; spike held; stale rate blocks checkout in that currency; no rate literals in code (grep test); OXR mocked.

### M7: Orders and checkout core (Step 4)

Depends: M5, M6. Scope: cart, checkout validation, place-order, snapshots, state machine, aggregator, events, idempotency, risk engine.
Accept: test covers every row of the transition table and asserts illegal ones throw; snapshot fields all populated; a later rate change does not alter an order; duplicate `Idempotency-Key` handled in all three cases; duplicate placement prevented; expiry job works.

### M8: Supplier engine and Reloadly (Step 5)

Depends: M7. Scope: contract, capabilities, registry, Reloadly adapter, selector, gate, sweeper, verifier, reconciliation, catalog and price sync, health, balances, internal stock and manual adapters.
Accept (mocked, labelled "awaiting sandbox verification"):
- Every row of the classification table in `INTEGRATIONS.md` 1.5.
- Read timeout produces `pending_verification` and **no** second POST.
- Crash after submit (simulated) leads the sweeper to `pending_verification`.
- Redelivered job is a no-op.
- Concurrent submit (two real DB connections) produces one attempt.
- Fallback fires only on `Rejected`, and honours the margin guard.
- Not found until deadline leads to `manual_review` with active lock retained.
- `cancelOrder` and `refundOrder` raise `CapabilityNotSupported`.
- Deliverable fetch failure retries the GET only.
- `cardNumber` with 20 digits stays intact.
- Request body has no `recipientEmail`.

### M9: Fulfilment and digital codes (Step 5)

Depends: M8. Scope: delivery strategies, vault, keyring, reveal flows, guest access, manual tasks, stock import.
Accept: canary test across all sinks in `SECURITY.md` section 7; reveal denied for non-owner, unpaid, undelivered; each attempt audited; ciphertext differs by key version and rotation works; duplicate code rejected; guest needs signed link and OTP; `SKIP LOCKED` allocation test; email contains no code.

### M10: Payments (Step 6)

Depends: M7. Implement independently in order: Stripe, PayPal, Paymob, plus eligibility, resolver, refunds, disputes, reconciliation.
Accept (mocked, labelled):
- Each gateway: create, verify, webhook good and bad signature, duplicate, out-of-order, refund, failed payment, timeout.
- Browser return alone never marks paid.
- Amount or currency mismatch goes to `manual_review`.
- Late payment on a cancelled order goes to `manual_review`.
- Double payment yields `duplicate_capture` and a refund.
- PayPal `COMPLETED` order with `PENDING` capture is not paid.
- Paymob HMAC: fixture with boolean formatting and exact 20-field order.
- Eligibility: Paymob offered only for EG + EGP with passing rules; currency never charged if unsupported; PayPal never receives EGP, SAR or AED; default deny.
- GA4 purchase dispatched once.

### M11: Wallet, promotions, affiliates, reviews, support (Step 4)

Depends: M7, M10, BD3. Accept: ledger balance equals sum, concurrent debit test, idempotent credit; coupon limits under concurrency; verified-purchase reviews; affiliate commission reversal on refund; ticket flows and internal notes hidden from customers.

### M12: Storefront (Step 7)

Depends: M1, M2, M5 to M10. Scope: all pages in spec Phase 4, Vite modules, Blade components. No inline scripts or styles beyond CSP-nonced data.
Accept: every page responsive at 375, 768 and 1280; feature tests per page; no console errors; matches Figma; checkout shows display and payment amounts; guest checkout end to end with mocks.

### M13: Customer account (Step 7)

Depends: M12. Accept: all spec section 9 modules; object-level authorization tests; code reveal UX with re-auth.

### M14: Admin core (Step 8)

Depends: M1, M5 to M10. Scope: layout, dashboard, DataTables base (search, filter, sort, pagination, bulk, export, column visibility, saved filters), form and error modules, orders, suppliers, payments.
Accept: validation errors displayed beside fields using the envelope; each table server-side; order timeline, retry and reconcile with permissions; supplier capability matrix rendered; permission-based navigation and route denial tested.

### M15: Admin remaining (Step 8)

Depends: M14. Scope: catalog, customers, pricing and currency, marketing, content, support, users, all settings, AI review, MCP action approval.
Accept: every module in spec Phase 9 exists and is tested; credentials are write-only.

### M16: MCP server (Step 9)

Depends: M14. Scope: `MCP_SERVER.md`.
Accept: tests in its section 8; all tools listed; read-only default proven in both layers; setup docs with Desktop and Code config.

### M17: n8n workflows (Step 10)

Depends: M14, BD9. Scope: all 58 workflows, docs, automation API, outbox.
Accept: endpoint auth tests (token, HMAC, replay, stale timestamp, duplicate execution); every JSON parses and has an error workflow and no credentials embedded; one doc per workflow with all required headings; labelled not executed.

### M18: SEO and analytics (Step 11)

Depends: M12. Scope: `SEO_STRATEGY.md`.
Accept: structured data validated on samples; sitemap index; hreflang; redirects, 404 and 410; canonical rules tested; Lighthouse run recorded, not claimed; GA4 and GTM settings; Search Console OAuth and reporting (mocked until credentials); browser never sends `purchase`.

### M19: Full test pass (Step 12)

Depends: all above. Accept: entire Pest suite green on MySQL; coverage for each spec Phase 15 item mapped in a table; manual test checklist completed for storefront and admin.

### M20: Production readiness (Step 13)

Depends: M19, credentials, server. Scope: security, performance and integration audit; sandbox verification per provider, resolving each `UNVERIFIED` item in `INTEGRATIONS.md`; deploy.
Accept: `SECURITY.md` section 15 complete; Lighthouse results recorded; each integration status updated to verified or remains labelled; backup restore tested.

## Dependency summary

```
M0 -> M1 -> M2
M0 -> M3 -> M4 -> M5 -> M7 -> M8 -> M9
           M4 -> M6 -> M7 -> M10 -> M11
M1,M2,M5..M10 -> M12 -> M13, M18
M1,M5..M10 -> M14 -> M15, M16, M17
all -> M19 -> M20
```
