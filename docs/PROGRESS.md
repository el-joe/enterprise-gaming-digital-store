# Progress Tracker

Milestones are defined in `IMPLEMENTATION_ROADMAP.md`. Add a section per milestone using the template below, newest at the bottom.

## Status board

| Milestone | Name | Status |
|---|---|---|
| M0 | Discovery and architecture | Complete |
| M1 | Figma design | In progress (delivered, awaiting review) |
| M2 | Canva assets | Partial (hero banners only; blocked by Canva credit quota) |
| M3 | Foundation | Complete |
| M4 | Full schema | Complete |
| M5 | Catalog and CMS services | Complete |
| M6 | Currency and pricing | Complete |
| M7 | Orders and checkout core | Complete (coupons bound at M11; payment currency bound at M10) |
| M8 | Supplier engine and Reloadly | Complete (Reloadly mocked only, awaiting sandbox verification) |
| M9 | Fulfilment and digital codes | Complete (reveal pages and account order list are M13) |
| M10 | Payments | Complete (all three adapters mocked only, awaiting sandbox verification; Stripe also awaits BD1) |
| M11 | Wallet, promotions, affiliates, reviews, support | Complete (wallet is internal, no provider; affiliate payouts, guest tickets and admin screens not built) |
| M12 | Storefront | Not started |
| M13 | Customer account | Not started |
| M14 | Admin core | Not started |
| M15 | Admin remaining | Not started |
| M16 | MCP server | Complete (run before M14 by PM decision; admin screens for tokens and approvals are M15; stdio only; not yet run against a live Laravel instance) |
| M17 | n8n workflows | Complete (run before M14 and BD9 by PM decision; Laravel automation API, outbox and heartbeats built and tested; 58 workflows authored and validated, **not executed**: n8n is not installed here; admin screen is M15) |
| M18 | SEO and analytics | Not started |
| M19 | Full test pass | Not started |
| M20 | Production readiness | Not started |

Status values: Not started, In progress, Blocked (state why), Complete.

## Integration status

| Integration | Adapter | Mock tests | Sandbox verified | Production |
|---|---|---|---|---|
| Reloadly Gift Cards | Built (`ReloadlyGiftCardAdapter`) | Yes (`Http::fake` only) | **Awaiting sandbox verification**; no credentials; never called live | No |
| Stripe | Built (`StripePaymentGateway`, official SDK on Laravel HTTP) | Yes (`Http::fake` only) | **Awaiting sandbox verification**; no credentials; never called live; merchant entity (BD1) open | No |
| PayPal | Built (`PayPalPaymentGateway`) | Yes (`Http::fake` only) | **Awaiting sandbox verification**; no credentials; never called live | No |
| Paymob | Built (`PaymobPaymentGateway`) | Yes (`Http::fake` only) | **Awaiting sandbox verification**; no credentials; never called live | No |
| Open Exchange Rates | Built | Yes (`Http::fake` only) | Awaiting credentials; OXR has no sandbox | No |
| GA4 Measurement Protocol | Not built (M10 queues the `purchase` row once; sending is M18) | No | Awaiting credentials | No |
| Google Search Console | Not built | No | Awaiting credentials | No |
| n8n | Automation API, outbox and 58 workflows built (M17) | Laravel side: yes (`tests/Feature/Automation`); workflows: static validation only | **Not executed**: n8n is not installed on this machine; SMS provider, alert channel and LLM key not chosen (BD9) | No |
| GeoIP | Contract built; `null` (default) and trusted-header drivers. GeoLite2 not built: needs `geoip2/geoip2` and a MaxMind database (BD9) | Yes (header parsing) | Not applicable | No |

## Business decisions

Tracked as BD1 to BD9 in `IMPLEMENTATION_ROADMAP.md`. Record each outcome and date here.

| ID | Decision | Outcome | Date |
|---|---|---|---|
| BD1 | Stripe merchant entity | Build the Stripe adapter now; settle the merchant entity before go-live | 2026-11-10 |
| BD2 | Direct game top-up route | Direct game top-ups use manual fulfilment at launch | 2026-11-10 |
| BD3 | Wallet scope | Wallet is credits and refunds only | 2026-11-10 |
| BD4 | Refund policy | Refunds for undelivered orders by default; otherwise at admin discretion | 2026-11-10 |
| BD5 | Launch languages | English and Arabic with full RTL | 2026-11-10 |
| BD6 | Base currency | USD | 2026-11-10 |
| BD7 | Tax | Tax-inclusive prices, with an accountant's advice | 2026-11-10 |
| BD8 | Resale rights and brand use | Official brand logos not used until rights are confirmed per brand | 2026-11-10 |
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

## M1: Figma design

Date completed: 2026-11-10 (delivered for review)
Completed by: Senior Product Designer

### Summary of completed work

Complete design system and screens in Figma for the working brand PLAYVAULT, dark-first with an original wordmark and a key-cut card silhouette. English and Arabic with RTL. Official brand logos are not used (BD8): platforms are plain-text tiles with an empty logo slot.

Figma file: https://www.figma.com/design/JkUUSR3WGBYC9ewy2qRFQO

| Page | Frames | Contents |
|---|---|---|
| Foundations | 1 | Tokens, typography, spacing, motion, RTL mirroring, implementation notes |
| Components | 37 sets, 349 variants + 63 icons | Buttons, inputs, tables, modals, navigation, cards, status |
| Storefront | 28 | Home, platforms, platform detail, category, product (gift card and top-up), search, cart, auth, 404 |
| Checkout | 7 | Guest (desktop, tablet, mobile), registered, confirmation |
| Account | 16 | Dashboard, orders, order details, wallet, wishlist, tickets, security, reveal flow |
| Admin | 24 | Dashboard (incl. Admin Light), catalog list and edit, suppliers and capability matrix, order details with pending verification, payments, pricing rules, users and permissions, settings, DataTables states; two mobile examples |
| RTL | 7 | Home, product, checkout (desktop, mobile), admin order |

### Files created

`docs/DESIGN_TOKENS.md`

### Migrations

None.

### Features implemented

None (design milestone).

### Tests and results

- Command: not applicable. Visual review through Figma screenshots of main frames and an overflow audit of all frames.
- Result: defects found were fixed (tablet overflow, spacers, truncation, small-tile labels, light-mode charts).
- Not tested: real device rendering; Arabic copy has not been reviewed by a native editor.

### Outstanding requirements

- Not produced: Figma prototype links; tablet frames for screens other than those specified (home, category, product, guest checkout, all admin screens except DataTables states); RTL frames beyond home, product, checkout and admin order; admin Customers, Marketing, Content and Support modules; account Profile, Digital code history page, Reviews and Notifications.
- Code-level art (Canva) and final logos remain for M2 and BD8.
- Light mode is defined for admin only; storefront remains dark.
- Chart series palette: lightness band warning accepted, documented in `DESIGN_TOKENS.md`.

### Next milestone

M2: Canva assets, after M1 review. M3 can run in parallel once the database login works.

---

## M2: Canva assets

Date completed: Not complete (partial delivery 2026-11-10)
Completed by: Brand / Visual Asset Designer agent

### Summary of completed work

Priority 1 only: three textless homepage hero concepts at 2400x1000 with 1080x1350 mobile crops, exported from Canva and converted to WebP. Canva returned `quota_exceeded` during the hero batch, so work stopped there as instructed. Canva folder: https://www.canva.com/folder/FAHad74IjHc

### Files created

- `backend/resources/assets/images/hero/` (6 WebP files)
- `docs/ASSETS.md`

### Migrations

None.

### Features implemented

None (asset milestone).

### Tests and results

- Command: not applicable. Each export was checked as a real image, converted to WebP, and viewed before acceptance.
- Result: 6 files accepted, all 52 to 89 KB. One Canva resize rejected. Concept 2 retouched to remove generation artefacts.
- Not tested: rendering in the storefront, which does not exist yet.

### Outstanding requirements

- Canva credits are needed to produce category banners, platform banners, promotional banners, empty states, marketing and social assets (English and Arabic), and the Open Graph image.
- Favicon and logo exports were not produced.
- Export of portrait designs `DAHad4QmPfA` and `DAHad_yXE2Q` was refused by Canva; mobile heroes are crops of the desktop art.
- Mobile crops have no calm area for copy, so M12 needs a scrim or art-on-top layout.
- Commercial licence for Canva AI output must be confirmed for this account before launch.
- Full list: `docs/ASSETS.md`, Missing Assets.

### Next milestone

Finish M2 once Canva credits are available. M12 (Storefront) depends on M2.

---

## M3: Foundation

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Packages, MySQL-only test setup, domain skeleton under `app/Domains`, `Money` and friends, JSON error envelope, request IDs, audit writer, security events, settings service, log redaction, roles and permissions, authentication with staff TOTP, and route files.

Packages added: `laravel/sanctum` 4.3.3, `spatie/laravel-permission` 8.5.0, `yajra/laravel-datatables-oracle` 13.3.1, `stripe/stripe-php` 22.1.0, `intervention/image` 4.4.1, `pragmarx/google2fa` 9.1.0 (Staff TOTP, listed in architecture section 4). `geoip2/geoip2` is not installed yet because nothing uses it until M10. GD reports WebP and AVIF support.

### Files created

- `app/Domains/Shared/`: `ValueObjects/{Money,Percentage,CountryCode}`, `Exceptions/{ApplicationException,CurrencyMismatch,AppendOnlyViolation,JsonExceptionRenderer}`, `Services/{SensitiveDataRedactor,RedactSensitiveLogData,ApplyLogRedaction}`, `Models/MorphMap`, `Models/Concerns/AppendOnly`
- `app/Domains/Audit/`: `DTOs/AuditEntry`, `Services/{AuditLogger,SecurityEventRecorder}`
- `app/Domains/Settings/Services/SettingsService`
- `app/Domains/Identity/`: `Enums/{PermissionName,RoleName}`, `DTOs/*`, `Services/{AuthenticationService,RegistrationService,PasswordResetService,PasswordPolicy,TwoFactorService}`, `Models/User` (moved from `app/Models`)
- `app/Http/Controllers/{Storefront/Auth,Account,Admin}`, `app/Http/Requests/{Storefront/Auth,Account}`, `app/Http/Resources/Account/UserResource`, `app/Http/Middleware/{AssignRequestId,Admin/*,Account/*}`
- `routes/{auth,account,admin,api,webhooks}.php`, `config/identity.php`, `config/permission.php`, `config/sanctum.php`
- Changed: `bootstrap/app.php`, `phpunit.xml`, `.env.example`, `config/{auth,database,logging,session}.php`, `tests/{Pest,TestCase}.php`

### Migrations

Delivered with M4.

### Features implemented

- `Money`: bcmath, HALF_UP, DECIMAL(20,6), runtime rejection of floats, currency guard.
- Error envelope for 401, 403, 404, 409, 419, 422, 429, 500 and 503; no stack traces even with `APP_DEBUG=true`. `X-Request-Id` accepted (ULID only), generated, echoed and logged.
- Auth: register, login, logout, email verification, password reset (30-minute token, identical response), customer and staff password policies, throttling with `security_events`, staff login audit.
- Staff TOTP: `EnsureTwoFactor` needs confirmed enrolment and a session that passed the challenge. Replay protection, recovery codes, 30-minute admin idle timeout.
- 59 permissions and 8 roles from `SECURITY.md`. No `Gate::before`; Super Admin does not hold the five (E) permissions.
- Log redaction on all channels, through a Monolog tap. Audit snapshots use the same redactor. Append-only rows refuse model updates and deletes.
- Tests refuse to boot unless the connection is MySQL `gaming-store_testing`; `Http::preventStrayRequests()` is global.

### Tests and results

- Command: `php artisan test --compact` (shared with M4; see below).
- Not tested, and why: real SMTP delivery and the live HIBP password API (faked in tests); `SESSION_DRIVER=redis` (tests use the array driver).
- The domain repository isolation arch test currently passes vacuously: no repositories exist until M5.

### Outstanding requirements

- `POST /login` is the route named `login`, and email and reset links land on JSON responses until the M12 Blade pages exist. A browser redirect to login gets 405 until then.
- CSP and security headers (`SECURITY.md` section 2) and customer session revocation are not part of M3 scope.
- Models and migrations for service accounts exist; token issue arrives with M15 to M17.

### Next milestone

M4: Full schema.

---

## M4: Full schema

Date completed: 2026-11-10
Completed by: Senior Backend Engineer and Database Designer

### Summary of completed work

Every table in `DATABASE_DESIGN.md`, in its section 15 order, with models, enums, factories and seeders. All 164 index and constraint names in the design were confirmed present in `information_schema`.

Migrations, models, enums and factories were emitted by a one-off script reading `DATABASE_DESIGN.md` directly, because per-table timestamps must follow section 15. They were sampled by hand, formatted with Pint, and exercised by tests (every factory creates a row and every relation loads). Controllers, requests, middleware, resources, seeders, services and tests started from `make:*`; DTOs and small support classes were written directly.

### Files created

- `database/migrations/`: 125 new files (`2026_11_10_GGNNNN_*`, where GG is the design group), plus the 3 skeleton migrations.
- `app/Domains/*/Models`: 114 models plus `User`; `app/Domains/*/Enums`: 93 generated enums.
- `database/factories/<Domain>/`: one factory per model.
- `database/seeders/`: the 13 seeders in section 16, and `data/{countries,currencies,automation_workflows}.php`.
- `tests/Feature/Schema/{SchemaAcceptanceTest,ModelFactoryTest,SeederTest}.php`

### Migrations

128 files, 134 application tables (119 design tables, 15 framework and package tables), plus `migrations`. `orders.primary_payment_id` and `orders.affiliate_referral_id` get their foreign keys in G13 and G15.

### Features implemented

- `uq_st_active_unit` via a stored generated column; `uq_o_primary_payment`; `uq_pwh`.
- Relations (including cross-domain reads, pivots and morphs), casts (`decimal:N` strings for money, never float), secrets hidden and never mass-assignable status or financial totals.
- Seeders: reference data, roles, base currency settings, inactive gateways with eligibility rules, suppliers, and 58 disabled workflows. No exchange rates, users, customers or orders.
- `SuperAdminSeeder` and `DemoCatalogSeeder` run only with `--class`. The first fails naming the missing variables; the second refuses production.

### Tests and results

Run against MySQL `gaming-store_testing` for tests and `gaming-store` for the acceptance commands:

- `php artisan test --compact`: **446 passed, 0 failed, 0 skipped (2465 assertions), 47.48 s**
- `php artisan migrate:fresh --seed --force`: succeeded on `gaming-store`, which was empty beforehand (0 tables). Row counts: roles 8, permissions 59, currencies 152, countries 241, regions 5, settings 7, payment_gateways 4, payment_eligibility_rules 7, suppliers 3, automation_workflows 58, exchange_rates 0, users 0, orders 0, products 0.
- `php artisan migrate:rollback --force`: all 128 reverted. A second rollback reported "Nothing to rollback".
- `php artisan migrate --force`: all 128 applied again; 135 tables including `migrations`. `db:seed` was rerun afterwards, so the app database is seeded.
- `php artisan db:seed --class=SuperAdminSeeder` with no variables: `RuntimeException: SuperAdminSeeder requires environment variables: SUPER_ADMIN_EMAIL, SUPER_ADMIN_PASSWORD.`
- `composer show | grep -i livewire`: empty. Inertia also absent.
- Acceptance tests: `uq_st_active_unit` rejects each of the four active statuses and allows a new attempt after `not_accepted_verified` and `rejected`; `uq_o_primary_payment`; `uq_pwh`; every section 17 table; the exact table list of the design.
- Not tested, and why: concurrent writes over two real connections (M8 acceptance), and query plans against production-sized data.

### Design changes made

- `DATABASE_DESIGN.md`: enum values added for `security_events.severity`, `export_jobs.status` and `scheduled_task_runs.status`, which the design left undefined. Section 16 now says which seeders are explicit and that admin-editable values are written once.
- `PROJECT_ARCHITECTURE.md` and `IMPLEMENTATION_ROADMAP.md`: `backend_testing` corrected to `gaming-store_testing`; package status and the contents of `Shared` updated.
- `SECURITY.md`: Admin also excludes `roles.manage`, since a role editor could grant itself `users.manage`.

### Outstanding requirements

- The default collation in `config/database.php` is now `utf8mb4_0900_ai_ci`, per the design. `DB_COLLATION` is unset in `.env`, so this applied to both databases.
- Non-base currencies are seeded inactive until a rate exists (M6).
- A Super Admin account does not exist yet; set the two variables and run the seeder when the first admin is needed.
- No services beyond M3 scope are built, by design.

### Next milestone

M5: Catalog and CMS services (Step 4), and M6 can start in parallel.

---

## M5: Catalog and CMS services

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Services, repositories, Form Requests, API Resources and admin JSON endpoints for catalog, CMS, media and redirects; storefront listing and search read endpoints; the search index. Backend only: no Blade, frontend JS or Yajra classes.

### Files created

- `app/Domains/Shared/`: `Services/{CrudService,ListQueryApplier,HtmlSanitizer}`, `DTOs/ListQuery`, `ValueObjects/Rate`, `Rules/{DecimalString,SafeUrl,Slug,MorphTargetExists}`
- `app/Domains/Catalog/`: `Services/{GamingPlatform,Category,Region,AttributeDefinition,DeliveryFieldDefinition,Product,ProductVariant,ProductImage,Search,StorefrontCatalog}Service`, `Services/{ProductPublicationService,SearchIndexer,DeliveryFieldValidator,ReservedSlugs}`, `Repositories/{ProductRepository,SearchRepository}`, `Rules/`, `DTOs/`, `Jobs/ReindexCatalogScope`
- `app/Domains/Cms/Services/` (Page, BlogCategory, BlogPost, FaqGroup, Faq, Menu, MenuItem, HomepageSection, Banner, Media), `app/Domains/Seo/Services/RedirectService`
- `app/Http/{Controllers,Requests,Resources}/Admin/{Catalog,Cms,Seo}`, `app/Http/{Controllers,Requests,Resources}/Storefront`, `app/Http/Requests/Admin/{AdminListRequest,AdminSaveRequest,ReorderRequest}`
- `app/Console/Commands/Catalog/RebuildSearchIndexCommand`, `config/catalog.php`, `routes/admin.php` and `routes/api.php` filled in
- `tests/Feature/Catalog/*`, `tests/Feature/Cms/CmsTest.php`
- Changed: `Controller` (paginated envelope), `AuditLogger` (string-keyed subjects such as currencies are stored with a null `subject_id`), `SensitiveDataRedactor` (`app_id`), `AppServiceProvider` (limiters), `tests/Pest.php` (helpers)

### Migrations

None. The M4 schema was sufficient.

### Features implemented

- CRUD with permissions per `SECURITY.md` and audit entries for every write. Delete of a referenced record returns 409.
- Product publishing owned by `ProductPublicationService`: `catalog.publish`, active variant required, required delivery field for top-ups.
- Allow-list HTML sanitizer, built on `DOMDocument` because no sanitizer package is installed.
- Media upload re-encoded to WebP (and AVIF where GD supports it) under random names. SVG is refused for everyone, which is stricter than `SECURITY.md`.
- Storefront listing, variant offers, search suggest and popular.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| CRUD with permissions | Met | `AdminCrudPermissionsTest`: 15 resources for 401, 403 without permission, view-only, full CRUD with audit. Nested, media and publish routes in their own tests. |
| Listing filter query under defined N queries | Met. N = 10 with a cold cache | `StorefrontListingTest`: same count at 2 and 32 products, all filters on |
| Search by game, platform, product, SKU | Met, with a limit on "game" | `SearchTest`. There is no games table, so a game is found through the product name or an attribute value. Fulltext tests commit their data, as InnoDB fulltext ignores uncommitted rows. |
| Delivery field validation | Met at service level | `SlugAndDeliveryFieldTest`. `DeliveryFieldValidator` has no HTTP caller until the M7 cart. Admin definitions are validated over HTTP. |
| Slug reserved words | Met | Config list plus the first segment of every registered route |
| Tests pass | Met | See below |

### Tests and results

- Command: `php artisan test --compact`
- Result: **795 passed, 0 failed, 0 skipped (4809 assertions), 54.8 s**, up from 446. Of the 349 new tests, 331 are in `tests/Feature/{Catalog,Cms,Currency,Pricing}` (M5 and M6 together); 18 are arch test datasets that grew with the new domains.
- Not tested, and why: query plans against production-sized data; rendering of stored images (no storefront); AVIF output beyond the GD capability check.

### Outstanding requirements

- `content_translations` are read by the search index but there is no admin endpoint to write them, so `ar` search rows hold English titles until M15 or a translation import.
- Reorder exists only where a table has `sort_order`: regions, attributes and delivery fields have none, so the planned `POST reorder` for them was not built.
- Redirect runtime lookup is M18; only the admin CRUD exists.
- Stored paths are returned, not public URLs, until M12 decides URL generation.

### Next milestone

M6, then M7.

---

## M6: Currency and pricing

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Exchange rate provider contract and Open Exchange Rates adapter, history, manual overrides, spike guard, staleness, rate cache, `PricingEngine` for all of section 11.5, margin guard, and the recalculation job. Adapter status: built, tested with `Http::fake` only, awaiting credentials.

### Files created

- `app/Domains/Currency/`: `Contracts/ExchangeRateProvider`, `Adapters/OpenExchangeRatesProvider`, `Services/{ExchangeRateRegistry,ExchangeRateUpdater,ExchangeRateWriter,ExchangeRateAdminService,CurrencyAdminService,CurrencyConverter,CurrencyRounder}`, `DTOs/`, `Exceptions/`, `Rules/RoundingRule`
- `app/Domains/Pricing/`: `Services/{PricingEngine,PricingDataLoader,CostResolver,ExplicitPriceResolver,PricingRuleResolver,DiscountCalculator,MarginGuard,MarginAuthorizer,PricingRuleService,VariantPriceService,VariantPriceRecalculator,PricingDirtyMarker,PriceSimulator}`, `Repositories/PricingRuleRepository`, `Jobs/RecalculateDirtyPrices`, `DTOs/`, `Enums/PriceSource`, `Exceptions/MarginGuardRefused`
- `app/Http/{Controllers,Requests,Resources}/Admin/{Currency,Pricing}`
- `app/Console/Commands/Currency/FetchRatesCommand`, `app/Console/Commands/Pricing/RecalculateDirtyCommand`, both scheduled in `routes/console.php`
- `tests/Feature/Currency/*`, `tests/Feature/Pricing/*`
- Changed: `config/services.php`, `.env.example`, `SettingsSeeder` (`pricing.*` defaults)

### Migrations

None.

### Features implemented

- Rates parsed from the raw response as decimal text. No float enters a money path; the arch test covers the new DTOs and value objects.
- Non-base currencies cannot be activated without a current rate. A fetch never activates one.
- Display tolerates a stale rate with a flag. Checkout refuses it: `PricingContext(forCheckout: true)`, `CurrencyConverter::toCheckout` and `resolveCheckoutCurrency`, which falls back to base.
- Recalculation uses `FOR UPDATE SKIP LOCKED`, and each dirty mark strictly advances `pricing_dirty_at`, so a variant re-marked mid-run stays dirty.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Each precedence level in 11.5 | Met for steps 1 to 8 | `PricingEngineTest`, one `describe` per step. Coupon input is a `CouponTerms` DTO; coupon validity and limits are M11. |
| Explicit price beats markup | Met | `step 2` tests, including all three tiers |
| Margin policy `floor`, `block`, `allow` | Met | `step 7` tests, including fees, conversion cost and exact floor |
| Override needs permission and audit | Met | Rate overrides, margin overrides and the `allow` policy, with `pricing_overrides_log` |
| Spike held | Met | `spike guard` tests, including approve, reject and duplicate approval |
| Stale rate blocks checkout in that currency | Met at service level | No checkout exists until M7, which must use `forCheckout: true` |
| No rate literals in code (grep test) | Met | Scans `app`, `database/seeders`, `config`; the patterns are themselves tested against bad samples |
| OXR mocked | Met | `OpenExchangeRatesProviderTest`: `Http::fake`, including timeout and credential leak checks |
| Historical orders never recomputed | Met | Order and item rows byte-identical after a rate change and recalculation |

### Tests and results

- Command: `php artisan test --compact`
- Result: **795 passed, 0 failed, 0 skipped (4809 assertions), 54.8 s**
- Concurrency: the `SKIP LOCKED` test uses a real second MySQL connection holding the row lock. Duplicate and re-marked recalculation are covered.
- Not tested, and why: the live OXR API (no credentials; EGP, SAR and AED availability stay `UNVERIFIED`); two workers actually racing, beyond the held-lock test.

### Outstanding requirements

- `is_custom_amount` variants return `custom_amount_unsupported`: the documents do not define their cost. M7 and M8 must decide before range products can be sold.
- Cost comes from a mapping override, the latest `supplier_product_prices` row, or `manual_cost_base`. `cost_map`, supplier fees and discounts are not applied, so M8's price sync must write the effective cost to that table.
- `PaymentCurrencyResolver` is M10. M6 supplies the conversions it needs.
- Settings changes to `pricing.*` and fee rule changes do not mark variants dirty; there is no settings API yet. Listing prices catch up on the next change, and checkout always re-evaluates.
- `settings.automation.task_drivers` is not consulted by the two new schedule entries (M17).
- New listings show no price until the next `pricing:recalculate-dirty`, up to 5 minutes.

### Design changes made

- `PROJECT_ARCHITECTURE.md` 11.4 and 11.5: held rates, override staleness, and how discounts interact with the margin floor.
- `DATABASE_DESIGN.md`: `pending_since` meaning, `pricing.*` settings in section 16.
- `INTEGRATIONS.md` section 5: adapter status. `API_DOCUMENTATION.md`: sections 2a and 5a.

### Next milestone

M7: Orders and checkout core.

---

## M7: Orders and checkout core

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Cart, checkout validation, place-order, order snapshots, the order state machine, the unit-status aggregator, order events, Idempotency-Key handling, duplicate-placement protection, the unpaid-order expiry job, the risk engine and guest email verification. Backend only: JSON endpoints, services and tests. No new packages.

Coupon validity and limits (M11) and payment currency with eligibility (M10) are consumed through two contracts, `Promotions\Contracts\CheckoutCoupons` and `Payments\Contracts\PaymentCurrencyResolver`. Both are bound to fail-closed implementations: **every coupon code is refused and every `place-order` returns `gateway_not_eligible`, creating nothing, until M11 and M10 bind real implementations** (ADR-026). The full placement path is tested with test doubles under `tests/Support`.

### Files created

- `app/Domains/Orders/`: `Actions/PlaceOrderAction`; `Services/{OrderStateMachine,OrderTransitionTable,OrderStatusAggregator,CartService,CartLineValidator,CartPricingService,CheckoutService,CheckoutGuard,OrderItemWriter,IdempotencyStore,PlacementHasher,OrderExpiryService,RiskEngine,OrderRiskGate,GuestOrderAccessService,OrderStatusReader}`; `Repositories/OrderRepository`; `DTOs/{CartOwner,CartQuote,CartLineQuote,UnitPrice,OrderActor,PlaceOrderInput,PlaceOrderResult,IdempotencyClaim}`; `Events/OrderStatusChanged`; `Exceptions/*`; `Notifications/GuestOrderAccessCode`
- `app/Domains/Pricing/`: `Services/CartDiscountAllocator`, `DTOs/DiscountSlot`
- `app/Domains/Promotions/`: `Contracts/CheckoutCoupons`, `DTOs/{CouponLookup,ResolvedCoupon}`, `Exceptions/CouponInvalid`, `Services/UnavailableCheckoutCoupons`
- `app/Domains/Payments/`: `Contracts/PaymentCurrencyResolver`, `DTOs/{PaymentCurrencyQuery,ResolvedPaymentCurrency}`, `Exceptions/PaymentGatewayNotEligible`, `Services/UnavailablePaymentCurrencyResolver`
- `app/Http/Controllers/Storefront/{CartController,CheckoutController,GuestOrderController,Concerns/InteractsWithCart}`, seven Form Requests, `Resources/Storefront/CartResource`
- `app/Console/Commands/Orders/{ExpireUnpaidOrdersCommand,PruneIdempotencyKeysCommand}`, `routes/checkout.php`, `config/orders.php`
- Tests: `tests/Feature/Orders/*` (7 files), `tests/Unit/Pricing/CartDiscountAllocatorTest`, `tests/Support/{FakePaymentCurrencyResolver,FakeCheckoutCoupons,Concurrency/place_order_worker.php}`
- Changed: `OrderStatus` (`isClosed`, `requiresVerifiedPayment`), `Order` (refuses status saves outside the machine), `PricingEngine::displayAmountFor`, `MarginGuard::discountRoom`, `DeliveryFieldValidator` (uses loaded relations), `AppServiceProvider` (bindings, `checkout` limiter), `bootstrap/app.php`, `routes/console.php`, `SettingsSeeder`, `tests/Pest.php`

### Migrations

`2026_11_10_190001_add_order_risk_velocity_indexes`: `idx_o_ip` on orders and `idx_pay_fingerprint` on payments, for the risk engine. Rolled back and re-applied on `gaming-store_testing`. **Not yet applied to the app database `gaming-store`** (`migrate:status` shows it pending); run `php artisan migrate`.

### Features implemented

- State machine: all 41 rows of architecture 6.2, actor types and staff permissions enforced, rule 9 (no paid or fulfilment status without `primary_payment_id`), one `order_events` row per transition, `OrderStatusChanged` after commit, alert log on illegal moves, model-level refusal of direct status writes.
- Placement: idempotency claim committed first and fenced (ADR-027); cart row lock; converted-cart and placement-hash checks over two 10-minute buckets; unique `uq_o_placement` as the last barrier; pricing with `forCheckout: true`; confirmed `currency` and `expected_total`; snapshot taken once; items, units (with pre-generated public ids), encrypted delivery values; coupon reservation inside the transaction; no HTTP inside the transaction.
- Cart-level coupon allocation over units within margin room, in whole minor units, deterministic remainders (ADR-025).
- Guest checkout: guest email captured at placement; OTP verification sets `guest_email_verified_at` (needed by M9 reveal); status visible only to the placing or verifying session.
- Risk engine: 11 weighted signals, thresholds in settings, assessment stored once; `OrderRiskGate` moves `paid` to `processing` or `manual_review`.
- `orders:expire-unpaid` every 5 minutes with `SKIP LOCKED`; `orders:prune-idempotency-keys` daily.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Every row of the transition table, illegal ones throw | Met | `OrderStateMachineTest`: 41 allowed rows performed with event and dispatch; all 155 other pairs throw and change nothing; an unnamed actor refused on every row; staff without permission refused; rule 9 on every paid or fulfilment target |
| Snapshot fields all populated | Met | `CheckoutTest` snapshots: all 15 columns of architecture 11.3 non-null for an EGP order, values checked, including the history id. For an all-base-currency order `rate_fetched_at` and `exchange_rate_history_id` are null by design (identity rate) |
| A later rate change does not alter an order | Met | Order, item and unit rows identical after a rate change, a new markup rule, a cost change and `pricing:recalculate-dirty` |
| Duplicate `Idempotency-Key`, all three cases | Met | In process: replay (`Idempotency-Replayed`), mismatch 409, key-order independence, owner scoping. Separate processes: in-flight 409 while the first holds the cart lock, mismatch 409, then replay of the stored 201 |
| Duplicate placement prevented | Met | Same cart under a new key (`cart_empty`), same contents from a new cart (200, `duplicate`), two guest carts with one email, allowed again after the first order closes; two processes racing on one cart with different keys (3 rounds) and with the same key; two guest carts racing into the unique index |
| Expiry job works | Met | `OrderLifecycleTest` and `CheckoutTest`: expired orders cancelled with event and coupon release; unexpired and `payment_processing` untouched; second run is a no-op; an order locked by another connection is skipped, then cancelled |
| Inherited 1: `forCheckout: true`, proven over HTTP | Met | Stale EGP: cart shows `price_may_vary`, `checkout_ready: false`; validate and place-order return `rate_stale` and create nothing; base currency then succeeds; a stale payment-currency rate also refuses |
| Inherited 2: delivery-field validator on the HTTP path | Met | Add to cart (missing, wrong type, pattern, unknown field), revalidation at validate and place-order after the definition changes, encrypted copy to `order_item_delivery_values` |
| Inherited 3: cart-level coupon allocation | Met | ADR-025; allocator unit tests (exact sums, remainders, caps, forfeiture); over HTTP: 1.00 over three units is 0.34/0.33/0.33, a tight unit stops at its floor and the rest moves to the roomy unit, `allow_below_margin` crosses the floor |
| Inherited 4: coupons through a contract M11 implements | Met as a contract; **no coupon can be used until M11** | `CheckoutCoupons` with `resolve`, `reserve`, `release`; default refuses every code (tested) |
| Inherited 5: `PaymentCurrencyResolver` contract, snapshot once | Met as a contract; **no order can be placed until M10** | Contract returns gateway and currency only; order code converts once; default refuses (tested) |
| No N+1 | Met | `GET /cart` runs the same number of queries for 2 and 6 lines; lazy loading throws in tests |

### Tests and results

- Command: `php artisan test --compact`
- Result: **1179 passed, 0 failed, 0 skipped (6520 assertions), 70.4 s**, up from 795.
- Concurrency with separate processes and connections (`CheckoutConcurrencyTest`, each request a separate PHP process via `tests/Support/Concurrency/place_order_worker.php` on committed data): in-flight, mismatch and replay with the cart lock held by a second connection; two simultaneous submits with different keys; two with the same key; two guest carts into the unique index; a process killed with SIGKILL after writing units leaves no partial order, its key returns `idempotency_in_progress` until stale, then a retry places exactly one order. Expiry `SKIP LOCKED` uses a real second connection.
- Simulated, not real: the late failure inside one in-process transaction (a coupon double throws during `reserve`) and the abandoned-claim takeover in `IdempotencyStoreTest` (time travel).
- Not tested, and why: real payment gateways and coupon rules (M10, M11); a real mail transport (Notification fake); race timing is real but not forced, so which loser outcome occurs (200 duplicate or 422 `cart_empty`) varies by run and both are accepted.

### Outstanding requirements

- M10 must bind `PaymentCurrencyResolver` (with eligibility) and create payment attempts; `next` in the place-order response is null until then. GeoIP is not resolved, so `orders.ip_country` is null and the country-mismatch risk signal cannot fire yet.
- M11 must bind `CheckoutCoupons` with limits under concurrency; a fixed coupon's value must be given to the engine in the base currency.
- Nothing calls `OrderRiskGate` yet: M10's paid transition or M8's `FulfillOrder` must. The `block` decision holds for review; automatic refund and customer flagging are M10 and M11.
- The margin floor is evaluated for the display currency. When M10 charges a different currency, the conversion cost of that currency is not in the floor.
- `OrderStatusChanged` has no listeners; the automation outbox is M17.
- Custom-amount variants still cannot be bought (no cost model).
- A signed-in customer who already has a cart does not merge a guest cart; the guest cart is kept separately.
- No customer cancel endpoint; the state machine supports `pending_payment` to `cancelled` by a customer.
- In local development with `MAIL_MAILER=log` the guest one-time code appears in the mail log. It is not a digital code, but the log should not be shared.

### Design changes made

- `PROJECT_ARCHITECTURE.md`: 6.2 enforcement notes; lock order adds `carts`; 11.3 how the snapshot is taken; 11.4 checkout fallback; 11.5 coupon allocation; 13 what is built; ADR-025, ADR-026, ADR-027.
- `DATABASE_DESIGN.md`: `idempotency_keys` behaviour, placement hash definition, `pricing_breakdown.units`, two new indexes, new settings in section 16.
- `API_DOCUMENTATION.md`: section 3a, new error codes, `checkout` limiter.

### Next milestone

M8: Supplier engine and Reloadly. M10 can start in parallel and should begin with `PaymentCurrencyResolver`.


---

## M8: Supplier engine and Reloadly

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Supplier adapter contract and capability declaration, registry, Reloadly Gift Cards adapter, internal stock and manual adapters, supplier selection, the exactly-once submission gate, outcome application with safe fallback, stale-submission sweeper, verification by reference, deliverable retrieval, daily reconciliation, catalog and price sync, balances, health with a circuit breaker, admin JSON for suppliers, mappings and fulfilment actions, and the scheduler entries. Backend only; no new packages. **Reloadly is tested with `Http::fake` only and is awaiting sandbox verification: it has never called the live API and no credentials exist.**

### Files created

- `app/Domains/Suppliers/`: `Contracts/SupplierAdapter`; `Adapters/{AbstractSupplierAdapter,InternalStockAdapter,ManualFulfillmentAdapter}`, `Adapters/Reloadly/{ReloadlyGiftCardAdapter,ReloadlyClient,ReloadlyOutcomeClassifier,ReloadlyProductParser}`; `Enums/{SupplierCapability,CapabilityLevel,SupplierOutcomeKind}`; 21 DTOs; `Exceptions/{CapabilityNotSupported,SupplierRequestFailed,SupplierNotConfigured,DeliverableFetchFailed}`; `Services/{SupplierAdapterRegistry,SupplierSelector,SupplierCatalogSync,SupplierPriceSync,SupplierPriceWriter,SupplierCostCalculator,SupplierMonitor,SupplierReconciler,SupplierAdminService,SupplierMappingService,SupplierApiLogger,HttpFailureClassifier,ExactJson}`; `Jobs/SyncSupplierCatalog`
- `app/Domains/Fulfillment/`: `Services/{FulfilmentPlanner,FulfilmentGuard,SupplierSubmissionGate,SupplierOutcomeApplier,AttemptLocks,SupplierVerificationService,StaleSubmissionSweeper,DeliverableRetriever,FulfilmentAdminService}`, `Strategies/{CodeDeliveryStrategy,DirectTopupStrategy,ManualTaskStrategy}`, `Contracts/DeliveryStrategy`, `Jobs/{FulfilOrder,SubmitUnitToSupplier,VerifySupplierTransaction,RetrieveDeliverables}`, `Listeners/StartFulfilmentWhenPaid`
- `app/Domains/Orders/`: `Services/{OrderProgress,UnitStatusRecorder}`, `Listeners/SendFulfilmentNotifications`, `Notifications/{OrderDelivered,OrderFulfilmentFailed}`, `Exceptions/PaymentCurrencyBelowMargin`
- `app/Console/Commands/Suppliers/*` (7 commands), `config/suppliers.php`
- `app/Http/{Controllers,Requests,Resources}/Admin/{Suppliers,Fulfillment}`
- Tests: `tests/Feature/Suppliers/{ReloadlyAdapterTest,SupplierManagementTest}`, `tests/Feature/Fulfillment/{SupplierFulfilmentTest,SupplierConcurrencyTest}`, `tests/Support/{FulfilmentFixtures.php,Concurrency/submit_unit_worker.php}`
- Changed: `PricingContext`, `PricingEngine` (payment-currency conversion in the floor, `floorAtCost`), `CartPricingService`, `PlaceOrderAction`, `AppServiceProvider` (registry singleton, listeners, `code-reveal` limiter), `routes/{admin,console}.php`, `tests/Pest.php`, `ArchitectureTest`

### Migrations

None. The M4 schema was sufficient.

### Features implemented

- Contract per architecture 7.1 plus `getTransactions()`. Unsupported operations throw `CapabilityNotSupported` and are declared `none`; only `full` capabilities are relied on.
- Default-deny classification (INTEGRATIONS 1.5). Pre-send transport failures are recognised from the cURL error (DNS, refused, TLS, connect-phase timeout); everything else, including read timeouts, resets, 5xx, 429, malformed bodies and unknown codes, is ambiguous.
- Gate (architecture 8.2): the attempt is committed as `submitting` before the HTTP call; `uq_st_active_unit`, the unit row lock, the `queued` check and a unique-until-processing job key all apply; `tries = 1`. No path leads from an ambiguous outcome to `createOrder`.
- Fallback only on `Rejected`, with `fallback_enabled`, the attempt limit and a fresh margin check; same-supplier retry only for pre-send failures under `retry_policy`.
- Verification with the documented backoff until the deadline; not found at the deadline holds the unit in `manual_review` with the active lock kept. Staff can confirm "verified not accepted" with reason and evidence, retry reusing the client reference, or force a lookup.
- Deliverable fetch is a separate GET, retried by the sweeper; never a new order.
- Inherited 1: catalog and pricing sync write effective cost to `supplier_product_prices`; tested end to end through the pricing engine.
- Inherited 2: `OrderRiskGate` runs from `FulfilOrder` on entering `paid`; `review` and `block` go to `manual_review`, tested with no supplier call.
- Inherited 3: `OrderStatusChanged` listeners dispatch fulfilment (on `paid` and `processing`) and send delivery and failure notifications, which carry the order id and a link only.
- Inherited 4: closed in the pricing engine (see Design changes). The per-gateway display in `payment-options` is M10.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Every row of the classification table | Met (mocked) | `ReloadlyAdapterTest`: 21 response rows, 9 refusal codes, 9 uncertain codes |
| Read timeout gives `pending_verification` and no second POST | Met | Adapter test and `SupplierFulfilmentTest` (one POST, then lookups only) |
| Crash after submit leads the sweeper to `pending_verification` | Met | In-process (claim + send, no step 3) and **separate process killed with SIGKILL** after claim and after send |
| Redelivered job is a no-op | Met | Job dispatched and handled again: one attempt, one POST |
| Concurrent submit (two real connections) produces one attempt | Met | `SupplierConcurrencyTest`: two PHP processes started together, 3 rounds; one attempt and one logged POST each time. Also a second connection's insert rejected by `uq_st_active_unit`, and a worker blocked by another connection's unit lock |
| Fallback only on `Rejected`, honours margin guard | Met | Refusal falls back to internal stock; 5 ambiguous cases do not; next candidate below margin holds for review |
| Not found until deadline: `manual_review`, lock retained | Met | `active_unit_key` still set, polling stopped, one POST |
| `cancelOrder` and `refundOrder` raise `CapabilityNotSupported` | Met | Adapter test, nothing sent |
| Deliverable fetch failure retries the GET only | Met | First GET 500, sweeper retries, completed; one POST |
| `cardNumber` with 20 digits stays intact | Met | `ExactJson` keeps every number as text |
| Request body has no `recipientEmail` | Met | Adapter test on the recorded POST |
| Also required by the brief: auth expiry/refresh, duplicate response, partial multi-unit, USD 100 cap, balance shortfall | Met | Adapter auth tests; duplicate transaction id goes to verification; partially fulfilled order; cap and balance excluded in selection |

### Tests and results

- Command: `php artisan test --compact` (M8 and M9 together)
- Result: **1344 passed, 0 failed, 0 skipped (7422 assertions), 74.1 s**, up from 1179.
- Separate processes or connections: concurrent submit (2 processes x 3 rounds), worker killed after claim, worker killed after send, unit row lock held by a second connection, `uq_st_active_unit` from a second connection, and `SKIP LOCKED` stock allocation (M9).
- In-process only: redelivered job, fallback, deadline, deliverable retry, duplicate transaction id, reconciliation, and the "died between send and record" simulation.
- Not tested, and why: the live Reloadly API (no credentials); real queue workers and Redis unique locks (tests use the sync queue and array cache; the database gate does not depend on them).

### Outstanding requirements

- Sandbox verification of every `UNVERIFIED` item in `INTEGRATIONS.md` 1.10a, including the Rejected list, the cost formula and response shapes. Raising the USD 100 cap needs 2FA and a static egress IP.
- Reloadly webhooks are not routed (signature unknown); polling is the only path.
- Supplier management screens are M14; `supplier-mappings/suggestions` and `bulk` are not built.
- Scheduled commands do not yet write `scheduled_task_runs` heartbeats (none of the existing commands do; M17).
- `order_item_financials` is not written at completion (profit reporting).

### Design changes made

- `PROJECT_ARCHITECTURE.md` 6.2, 7.1, 7.3, 7.4, 11.5 and 10: manual fulfilment as a strategy, `OrderProgress` hold rules, selection and fallback details, payment-currency conversion in the margin floor.
- `INTEGRATIONS.md` 1.10a adapter status and assumptions. `DATABASE_DESIGN.md`: `cost_map` for range products, effective cost in `supplier_product_prices`. `API_DOCUMENTATION.md` 5b.

### Next milestone

M9 (below), then M10.

---

## M9: Fulfilment and digital codes

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Delivery strategies, the digital code vault and keyring, key rotation, internal stock import and `SKIP LOCKED` allocation, manual fulfilment tasks with admin actions (BD2), and the audited reveal flows for customers, guests and staff.

### Files created

- `app/Domains/Fulfillment/Services/{DigitalCodeKeyring,DigitalCodeVault,DigitalCodeKeyRotator,DigitalCodeImporter,StockAllocator,CodeRevealService,ManualTaskService}`, `DTOs/{RevealedCode,RevealContext}`, `Exceptions/{DigitalCodeKeyMissing,DuplicateDigitalCode,CodeRevealDenied}`
- `app/Console/Commands/Fulfillment/RotateDigitalCodeKeysCommand`, `config/fulfillment.php`
- `app/Http/Controllers/Account/{ConfirmPasswordController,OrderCodeController}`, `Storefront/GuestOrderCodeController`, `Admin/Fulfillment/{ManualTaskController,DigitalCodeController}`
- Tests: `tests/Feature/Fulfillment/{DigitalCodeTest,CodeCanaryTest,ManualFulfilmentTest}`
- Changed: `GuestOrderController` (marks the session OTP-verified for the order), `routes/{account,checkout}.php`, `phpunit.xml` (test keyring values), `.env.example` (`DIGITAL_CODE_KEYS`, `DIGITAL_CODE_ACTIVE_KID`, `DIGITAL_CODE_BLIND_INDEX_KEY`, empty)

### Migrations

None.

### Features implemented

- AES-256-GCM per key id, separate from `APP_KEY`; code, PIN and URL encrypted separately; HMAC blind index ignoring case, spaces and hyphens; mask of the last four. No key, no storage.
- Code material types refuse serialization and hide values from dumps; jobs carry ids (arch test).
- Reveal: owner with verified email and a password confirmed within 15 minutes; guest with the signed 72-hour link and the email OTP in the same session; staff with the explicit `digital_codes.reveal` grant and a reason. Paid order and delivered unit required. 10 per hour. `no-store`. Every attempt audited in `audit_logs` and, where a code exists, `digital_code_accesses`.
- Manual tasks: list, view with the customer's delivery values, claim, complete, reject, all audited.
- **Production-relevant fix found by the tests:** MySQL chose an index-merge plan for stock allocation under which `SKIP LOCKED` waited on a locked row (lock wait timeout). The allocator now forces `idx_dc_stock`.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Canary across all sinks in `SECURITY.md` 7 | Met | `CodeCanaryTest`: log events and log file, `supplier_api_logs`, `order_events`, `audit_logs`, `automation_outbox`, `analytics_event_dispatches`, job payloads, mail, admin resources and the reveal error response, after a failed then successful fetch |
| Reveal denied for non-owner, unpaid, undelivered; each attempt audited | Met | Dataset in `DigitalCodeTest`, plus missing recent auth (423) and unverified email |
| Ciphertext differs by key version; rotation works | Met | Rotation to kid 2 re-encrypts, decrypts, and a second run does nothing |
| Duplicate code rejected | Met | Vault and import (by position, never echoed) |
| Guest needs signed link and OTP | Met | Each alone denied; tampered and expired links 403; OTP verification marks the session |
| `SKIP LOCKED` allocation | Met | Second real connection holds the first code; allocation takes the next |
| Email contains no code | Met | Rendered delivery mail and the canary mail sink |

### Tests and results

See M8 (shared run): **1344 passed, 0 failed (7422 assertions)**.

### Outstanding requirements

- `DIGITAL_CODE_KEYS`, `DIGITAL_CODE_ACTIVE_KID` and `DIGITAL_CODE_BLIND_INDEX_KEY` must be generated and set in `backend/.env` before any code can be stored; without them fulfilment of code products fails closed (`DigitalCodeKeyMissing`). They must be backed up separately from the database.
- Customer pages (order list, masked code history, reveal UI) are M13; the delivery email links to `/account/orders/{public_id}`, which does not exist until then.
- Staff entry of a code when completing a manual task is not supported; manual completion records that the work was done.

### Next milestone

M10: Payments. It must bind `PaymentCurrencyResolver`, mark orders paid only through verified webhooks or re-reads (which now starts fulfilment automatically), and pass the payment currency into pricing for `payment-options`.

---

## M10: Payments

Date completed: 2026-11-10
Completed by: Senior Payments Engineer

### Summary of completed work

Gateway contract, registry and three adapters (Stripe, PayPal, Paymob), payment eligibility and the real `PaymentCurrencyResolver`, payment options with the payment currency in pricing, payment attempts, webhook intake and processing, server-side verification and PayPal capture, the payment state applier (the only path to `paid`), refunds with `refund_pending` handling (BD4), disputes, reconciliation, `order_item_financials`, the GA4 purchase queue row, and the GeoIP contract. Backend only; no new packages. **Every adapter is tested with mocked HTTP only, has never called a live API, and is awaiting sandbox verification.**

### Files created

- `app/Domains/Payments/`: `Contracts/{PaymentGateway,GeoIpResolver}`; `Adapters/AbstractPaymentGateway`, `Adapters/Stripe/{StripePaymentGateway,LaravelStripeHttpClient}`, `Adapters/PayPal/PayPalPaymentGateway`, `Adapters/Paymob/{PaymobPaymentGateway,PaymobHmac}`; 12 DTOs (`GatewayCapabilities`, `CreatePaymentRequest`, `CreatePaymentResult`, `VerifiedPaymentState`, `WebhookRequestData`, `VerifiedWebhook`, `GatewayEvent`, `DisputeData`, `RefundResult`, `EligibilityContext`, `GatewayEligibility`, `PaymentSession`); `Enums/{ProviderPaymentStatus,GatewayEventKind}`; `Exceptions/{InvalidWebhookSignature,GatewayRequestFailed,GatewayNotConfigured,GatewayCapabilityNotSupported,PaymentAttemptRefused,RefundRefused}`; `Services/{PaymentGatewayRegistry,PaymentEligibilityService,EligibilityContextFactory,EligiblePaymentCurrencyResolver,PaymentOptionsService,PaymentAttemptService,CheckoutPaymentService,PaymentVerifier,PaymentStateApplier,GatewayFeeEstimator,WebhookIntake,WebhookProcessor,DisputeRecorder,RefundService,RefundExecutor,RefundStateApplier,PaymentReconciler,NullGeoIpResolver,HeaderGeoIpResolver}`; `Jobs/{ProcessPaymentWebhook,ExecuteRefund}`; `Listeners/RefundUndeliveredUnits`
- `app/Domains/Orders/Services/OrderFinancialsWriter`, `Orders/Listeners/RecognizeOrderFinancials`, `Analytics/Listeners/RecordPurchaseEvent`
- `app/Http/Controllers/Storefront/PaymentController`, `Webhooks/PaymentWebhookController`, `Admin/Payments/RefundController`; `Requests/Storefront/{PaymentOptionsRequest,RetryPaymentRequest}`, `Requests/Admin/Payments/CreateRefundRequest`; `Resources/Admin/Payments/RefundResource`
- `app/Console/Commands/Payments/{ReconcileOpenPaymentsCommand,ReconcileDailyPaymentsCommand}`, `config/payments.php`
- Tests: `tests/Feature/Payments/{StripePaymentTest,PayPalPaymentTest,PaymobPaymentTest,RefundTest,EligibilityAndCheckoutTest,PaymentConcurrencyTest}`, `tests/Support/{PaymentFixtures.php,Concurrency/payment_race_worker.php}`
- Changed: `AppServiceProvider` (resolver binding, registry, GeoIP binding, three listeners, `webhooks` limiter), `CheckoutController` (GeoIP, first attempt after placement), `PlaceOrderInput` and `PlaceOrderAction` (`ip_country`), `OrderStatusReader` (`viewableOrder`, `describe`), `routes/{webhooks,checkout,admin,console}.php`, `PaymentGatewaySeeder` (Paymob `charge_display_currency_only`), `.env.example`, `tests/Pest.php`
- Tests changed: `CheckoutTest` (the fail-closed test now binds `UnavailablePaymentCurrencyResolver` explicitly, since the default binding is now real); `SupplierFulfilmentTest` (a partially fulfilled order now moves on to `refund_pending`, per architecture 6.2 and BD4); `SupplierConcurrencyTest` (cleanup list gains the tables M10 listeners write)

### Migrations

`2026_11_10_200001_add_payment_provider_order_index`: `idx_pay_provider_order` (payment_gateway_id, provider_order_id) on `payments`, for Paymob callbacks. Rolled back and re-applied on `gaming-store_testing`. **Not applied to the app database `gaming-store`**; run `php artisan migrate`. The seeder change applies on the next `db:seed --class=PaymentGatewaySeeder`.

### Features implemented

- Contract per architecture 9.2. Stripe through the official SDK on a Laravel HTTP transport (ADR-029); PayPal and Paymob on Laravel HTTP. Credentials decrypted only inside adapters, never logged or returned; Stripe keys must match the gateway mode (`sk_test_`/`sk_live_`).
- Eligibility (architecture 12, default deny) bound as `PaymentCurrencyResolver`; payment currency = display currency when the provider and merchant both support it, else the gateway default. PayPal's adapter refuses EGP, SAR and AED outright. Paymob is EGP only and display-currency only.
- Paid only through `PaymentStateApplier`, fed by a server-side read: amount and currency must equal the attempt's snapshot; mismatches, late payments on closed orders and success on unexpected statuses go to `manual_review` or an alert; a second success is `duplicate_capture` with an automatic full refund. Entering `paid` starts fulfilment (M8 listener) and queues the GA4 `purchase` row once.
- Idempotency keys: `pay_create:`, `pay_capture:`, `refund:` (Stripe, PayPal). Paymob has none: a lost create expires the attempt; a lost refund is resolved by re-read and never re-sent.
- Refunds (BD4): failed units refunded automatically when the order enters `refund_pending` (and `partially_fulfilled` moves there); staff refund chosen units, delivered too, with `refunds.create`. One refund per unit (`uq_ru_unit`), never beyond the remaining amount, Paymob one in flight. `order_item_financials` written once when the order settles and corrected on refund; supplier cost of a unit whose attempt did not deliver (including `FAILED`, `cost_reversal_pending`) is not booked.
- `payments:reconcile-open` (every 5 minutes: open attempts, refunds, failed webhooks) and `payments:reconcile-daily` (48-hour comparison into `reconciliation_items`).

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Each gateway: create, verify, webhook good and bad signature, duplicate, out-of-order, refund, failed payment, timeout | Met (mocked) | Stripe, PayPal, Paymob test files: each has create, re-read on webhook, valid/invalid/missing signature, duplicate delivery, failure after success, refund, declined payment, and a provider timeout (Stripe read, PayPal read and verification API, Paymob read and refund) |
| Browser return alone never marks paid | Met | `verify-payment` with the provider saying `requires_action` leaves the order unpaid; with no webhook at all it is paid only because the server read says succeeded; another session gets 404 |
| Amount or currency mismatch goes to `manual_review` | Met | Stripe amount and currency mismatch; Paymob amount mismatch; no fulfilment dispatched |
| Late payment on a cancelled order goes to `manual_review` | Met | Stripe test |
| Double payment yields `duplicate_capture` and a refund | Met | Second attempt refunded in full with `refund:{id}` key; primary slot unchanged |
| PayPal `COMPLETED` order with `PENDING` capture is not paid | Met | PayPal test: `payment_processing`, `paid_at` null |
| Paymob HMAC: boolean formatting and exact 20-field order | Met | Literal concatenation fixture; `1` for `true` rejected; tampered field rejected; body or query `hmac` |
| Eligibility: Paymob only EG + EGP; currency never charged if unsupported; PayPal never EGP, SAR, AED; default deny | Met | `EligibilityAndCheckoutTest`: seeded rules, five Paymob refusals (declared US, IP US, IP unknown, Tor, USD display), place-order refusal; PayPal charges USD for EGP/SAR/AED shoppers and the attempt is refused even if a merchant row enables them; inactive, no rule, no credentials, wrong mode, disabled currency, amount cap, product exclusion, wallet |
| GA4 purchase dispatched once | Partly met | One `analytics_event_dispatches` row per order however often paid is applied; **sending to GA4 is M18** |
| Inherited 1: resolver bound | Met | Real place-order end to end with Stripe: order, snapshot, `ip_country`, attempt and `next.client_secret` |
| Inherited 2: payment currency in `payment-options` | Met | A gateway whose currency would cross the margin floor is listed `available: false` with `payment_currency_below_margin` |
| Inherited 3: only verified paths to paid | Met | See above; webhook body claiming success while the provider says processing gives `payment_processing` |
| Inherited 4: refunds and supplier cost reversal | Met | `RefundTest`: partial, full, double refused, provider refusal to review, timeout resolved without a second create, permissions, validation |
| Inherited 5: `order_item_financials` | Met | Revenue, supplier cost, fee, FX, gross, net checked; FAILED attempt books no cost; written once and corrected on refund |
| Inherited 6: GeoIP | Met as contract | Null and header drivers tested; GeoLite2 not built (needs a package and database) |

### Tests and results

- Command: `php artisan test --compact` (MySQL `gaming-store_testing`)
- Result: **1463 passed, 0 failed, 0 skipped (8070 assertions), 97.6 s**, up from 1344.
- Separate processes and connections (`PaymentConcurrencyTest`): a webhook worker and a return-page status-check worker racing on the same payment, 3 rounds (one paid transition, one ledger row, one GA4 row each time); the same signed event received by two workers at once (one stored row).
- In-process only: duplicate and out-of-order webhooks, duplicate capture, refund double-submit and the refund unit unique index, Paymob single in-flight refund, reconciliation, timeouts (faked connection failures).
- Not tested, and why: every live provider API (no credentials); Stripe EGP/SAR presentment and fee expansion, PayPal create/capture key retention, Paymob callback `hmac` location and retry policy (all `UNVERIFIED`); real queue workers (sync queue in tests); CSRF on webhooks cannot be observed in tests (Laravel skips it under test), the routes are simply outside the `web` group.

### Outstanding requirements

- Sandbox verification of every adapter and each `UNVERIFIED` item in `INTEGRATIONS.md` 2 to 4; BD1 before Stripe goes live.
- Gateways are seeded inactive with no credentials or merchant currencies, so nothing is eligible until they are configured in the database. Admin screens and write-only credential routes are M15.
- Retry-payment cannot switch gateway (`gateway_change_unsupported`): the customer places the order again. Architecture 11.3 allows switching before paid; it needs re-pricing in the new currency.
- A replayed place-order returns `next: null`; the client must call retry-payment.
- Wallet gateway and wallet refunds are M11 (BD3). `orders:expire-unpaid` does not cancel provider intents; a late payment goes to review.
- GeoLite2 needs `geoip2/geoip2` and a MaxMind licence key and database (BD9); not installed. The header driver is safe only behind Cloudflare with the origin locked down.
- GA4 sending is M18. The risk engine's `block` still holds for review; automatic refund on `block` was not added.
- Supplier cost reversals flagged `cost_reversal_pending` are excluded from cost but not yet reconciled against the supplier's later `REFUNDED` status.

### Design changes made

- `PROJECT_ARCHITECTURE.md` 9.4 and 12 (what is built and settled), ADR-028 (PayPal postback verification, replacing the M0 offline decision), ADR-029 (Stripe SDK on Laravel HTTP).
- `INTEGRATIONS.md` 2 to 4: adapter status and conservative handling of `UNVERIFIED` items.
- `API_DOCUMENTATION.md` 3a, 3b, 5c, 6, limiters.

### Next milestone

M11: Wallet, promotions, affiliates, reviews, support. It must bind `CheckoutCoupons`, add the wallet gateway adapter (BD3) and reverse affiliate commission on refund (refunds now exist).


---

## M11: Wallet, promotions, coupons, affiliates, reviews, support

Date completed: 2026-11-10
Completed by: Senior Backend Engineer

### Summary of completed work

Real coupons behind the M7 `CheckoutCoupons` contract (now the binding), the wallet ledger and the wallet as an internal payment gateway (BD3: credits and refunds only, no top-up), wallet refunds linked to the M10 refund flow, affiliate clicks, attribution, commission accrual and reversal, verified-purchase reviews with moderation, support tickets for customers and staff with internal notes, refund requests, and admin JSON for coupons, promotions, affiliates, reviews, tickets, refund requests and customer wallets. Backend only; no new packages. The wallet calls no external provider.

### Files created

- `app/Domains/Wallet/`: `DTOs/WalletEntry`, `Exceptions/WalletOperationRefused`, `Repositories/WalletRepository`, `Services/{WalletLedger,WalletPaymentService,WalletRefundService,WalletService}`
- `app/Domains/Payments/`: `Contracts/InternalLedgerGateway`, `Adapters/Wallet/WalletPaymentGateway`
- `app/Domains/Promotions/`: `Services/{DatabaseCheckoutCoupons,CouponService,PromotionService}`, `Repositories/CouponRepository`, `Listeners/RedeemCouponWhenPaid`
- `app/Domains/Affiliates/`: `Services/{AffiliateTracker,AffiliateCommissionService,AffiliateService}`, `Repositories/AffiliateRepository`, `Listeners/TrackAffiliateCommission`
- `app/Domains/Reviews/`: `Services/ReviewService`, `Repositories/ReviewRepository`
- `app/Domains/Support/`: `Services/{SupportTicketService,RefundRequestService}`, `Repositories/SupportTicketRepository`, `Notifications/SupportTicketReplied`, `Listeners/CompleteRefundRequests`
- `app/Console/Commands/Wallet/VerifyWalletLedgerCommand` (`wallet:verify-ledger`, daily 03:30), `config/affiliates.php`
- HTTP: `Controllers/Account/{WalletController,ReviewController,SupportTicketController,RefundRequestController}`, `Controllers/Storefront/{ProductReviewController,AffiliateController}`, `Controllers/Admin/{Promotions/{Coupon,Promotion}Controller,Affiliates/AffiliateController,Reviews/ReviewController,Support/{SupportTicket,RefundRequest}Controller,Customers/CustomerWalletController}`; 17 Form Requests; 17 API Resources
- Tests: `tests/Feature/Wallet/{WalletLedgerTest,WalletPaymentTest,WalletConcurrencyTest}`, `tests/Feature/Promotions/{CouponTest,CouponConcurrencyTest,PromotionAdminTest}`, `tests/Feature/Affiliates/AffiliateTest`, `tests/Feature/Reviews/ReviewTest`, `tests/Feature/Support/SupportTicketTest`, `tests/Support/{M11Fixtures.php,Concurrency/m11_worker.php}`
- Changed: `AppServiceProvider` (coupon binding, three listeners, `account` limiter), `OrderStateMachine` (release rule), `PaymentAttemptService` (internal-ledger branch), `PaymentEligibilityService` and `EligibilityContext`/`EligibilityContextFactory` (user id, wallet rules), `RefundService`, `RefundExecutor`, `CreateRefundRequest` and `RefundController` (refund destination), `CheckoutController` (affiliate attribution), `config/payments.php` (`wallet` adapter), `routes/{account,admin,api,checkout,console}.php`, `tests/Pest.php`

### Migrations

`2026_11_10_210001_add_wallet_and_coupon_limit_checks`: CHECK `chk_cw_balance_nonneg` (`customer_wallets.balance >= 0`) and `chk_c_usage` (`coupons.used_count <= usage_limit`). Rolled back and re-applied on `gaming-store_testing`. **Not applied to the app database `gaming-store`**; run `php artisan migrate`.

### Features implemented

- Coupons (`DatabaseCheckoutCoupons`): active window, minimum order, product/category/platform restriction (eligible lines only, ADR-025 allocation unchanged), first-order-only, global and per-buyer limits (per buyer = account or keyed email hash, so guest checkout cannot bypass a per-user limit), fixed coupons in another currency converted at the stored rate. `reserve` locks the coupon row inside the placement transaction and re-checks everything; `release` (expiry, cancel, failure of a never-paid order, including `manual_review` to `cancelled`) is idempotent; `paid` makes the use `redeemed`, which is never released. `UnavailableCheckoutCoupons` is kept, with its test.
- Wallet ledger: see architecture 14 and ADR-030. Payment: place-order or retry-payment with `gateway: wallet` debits and marks paid in one transaction through `PaymentStateApplier`; a refusal (insufficient, frozen, unverified) marks the attempt failed and leaves the order `pending_payment` with no debit. Daily payment reconciliation re-reads the ledger.
- Wallet refunds: a refund of a wallet payment credits the wallet; staff may send any refund to the wallet (`destination: wallet`, registered customers only) without calling the provider. Both are keyed `refund:{public_id}` and safe to re-send.
- Affiliates: click endpoint and encrypted `aff_ref` cookie (token hashed at rest); last-click attribution after the order commits (never self-referral, 30-day window); `pending` commission at `completed` (percentage of the base total less succeeded refunds); full refund reverses it, partial refund reduces it; a paid-out commission is never changed and raises `affiliates.paid_commission_refunded`; admin CRUD with write-only payout details, commission list and approval.
- Reviews: verified purchase = item of the customer's own paid order with a delivered unit, verified email, one per item; `pending` until approved; public list shows approved only with first name and initial; product rating from approved reviews, recomputed under the product lock; feature approved only; audited.
- Support: customer open, list, show, reply; staff queue, view, reply, internal note, assign (to staff with `support.reply`), status and priority (audited); reply email has a link only. Refund requests: customer request (one open per paid order), staff approve through RefundService or reject; completed when the refund succeeds.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Ledger balance equals sum | Met | `WalletLedgerTest` (mixed movements, each `balance_after`, `isConsistent`, `wallet:verify-ledger` detecting a tampered balance); every wallet test asserts balance = sum, including after the races |
| Concurrent debit test | Met, **separate processes** | `WalletConcurrencyTest`: two debits of 7 against 10 (3 rounds): one posted, one `wallet_insufficient_balance`, balance 3; two wallet orders against a balance for one (3 rounds): one paid, one failed, one debit. DB check also proven |
| Idempotent credit | Met | Same key twice in process returns the first entry; same credit from two processes at once gives one entry; key reuse with another amount refused |
| Coupon limits under concurrency | Met, **separate processes** | `CouponConcurrencyTest`: last use (3 rounds), once-per-customer (3 rounds), limit 2 with 3 buyers. Each worker calls `reserve` inside its own transaction, as placement does; a full two-process place-order race with a coupon was not added (the M7 placement races cover the rest of that path) |
| Verified-purchase reviews | Met | `ReviewTest`: other customer's item, nothing delivered, unpaid, unknown item, unverified email, duplicate; moderation before publication |
| Affiliate commission reversal on refund | Met | `AffiliateTest`: full refund reverses, partial reduces, paid-out unchanged with alert |
| Ticket flows; internal notes hidden from customers | Met | `SupportTicketTest`: customer and staff flows; note absent from customer show, list and reply responses; **resource-level proof**: `Account\SupportTicketResource` given a ticket whose loaded relation holds the note still omits it, and the message resource resolves a note to `[]`; reply email carries no text |
| Inherited 1: `CheckoutCoupons` bound, limits under concurrency, release on expire/fail/cancel | Met | Binding test; release on `orders:expire-unpaid`, failed, cancelled and `manual_review` to `cancelled`; never after paid |
| Inherited 2: wallet adapter, no path to paid bypassing M10 | Met | Debit and paid commit together; a forced failure in `PaymentStateApplier` after the debit rolls the debit back; settling twice is refused; `/webhooks/wallet` rejected |
| Inherited 3: affiliate accrual and reversal | Met | As above |
| No N+1 on new listings | Met | Lazy loading throws in tests; agent queue query count is constant for 2 and 7 tickets |

### Tests and results

- Command: `php artisan test --compact` (MySQL `gaming-store_testing`)
- Result: **1598 passed, 0 failed, 0 skipped (8929 assertions), 126.6 s**, up from 1463.
- Separate processes and connections: wallet debit race, concurrent idempotent credit, wallet payment race, three coupon reservation races (`m11_worker.php`; concurrent creation of a wallet on first credit is exercised by the credit race).
- In-process only: the debit-then-failure rollback (a test double throws inside the transaction), duplicate settle, wallet refunds and re-poll, coupon release and redemption, affiliate accrual and reversal, reviews, tickets, refund requests.
- Existing tests changed: `CartTest` "refuses every code" now binds `UnavailableCheckoutCoupons` explicitly and is renamed, since the default binding is now real (same as M10 did for the currency resolver). `EligibilityAndCheckoutTest` "never offers the wallet, which has no adapter until M11" renamed to "... to a customer without a wallet balance"; its body is unchanged and still passes.

### Outstanding requirements

- The wallet gateway needs, in the database, `is_active` and an enabled `payment_gateway_currencies` row for the base currency (seeded inactive, like all gateways). It must cover the whole order: no split payment with a card. A wallet attempt that fails leaves the order to expire; the customer cannot switch gateway on the same order (M10 limitation).
- Coupons: `stackable` is stored but only one coupon per cart exists (schema). A late verified payment on an order whose reservation was released (cancelled, then paid) does not re-reserve the use.
- Affiliates: commission accrues on `completed` only (not on `partially_fulfilled`); payouts (`affiliate_payouts`) and the customer affiliate dashboard are not built.
- Reviews have no permission of their own in `SECURITY.md` 4; moderation uses `catalog.manage`. Decide whether to add one (it changes the seeded roles and `RoleAccessTest`).
- Support: guest tickets and attachments are not built; tickets have no SLA alerts (M17).
- Admin screens are M14/M15; DataTables endpoints for these resources are not built.

### Design changes made

- `PROJECT_ARCHITECTURE.md`: 6.2 release rule, 9.4 lock order adds `coupons`, 14 what is built, ADR-030 (wallet as an internal-ledger gateway).
- `DATABASE_DESIGN.md`: wallet keys and check constraint, coupon redemption rules and check constraint.
- `API_DOCUMENTATION.md`: 2b, 3a coupon note, 3b wallet `next`, 4b, 5c refund destination, 5d, new error codes, `account` limiter.

### Next milestone

M12: Storefront. Risks it inherits: the checkout must handle `next.type: "wallet"` (already paid, no redirect) and a null `next` after a refused wallet attempt; coupon errors arrive as `message` on 422 `coupon_invalid`; the affiliate landing must call `POST /storefront/affiliates/click` (CSRF, cookie); product pages read reviews from `/catalog/products/{slug}/reviews`. M13 needs the 4b routes (wallet, reviews with `/reviews/eligible`, tickets, refund requests). M14/M15 must build screens on 5d.

---

## M16: MCP server

Date completed: 2026-11-10
Completed by: AI Automation / MCP Engineer

### Summary of completed work

A dedicated MCP server (`mcp-server/`, TypeScript, official SDK, stdio) and the protected Laravel API it calls (`/api/v1/mcp`): 27 read tools, 6 write tools and `get_action_status`, token authentication with per-token scopes, per-account rate limiting, FormRequest validation, API Resources, one `mcp_audit_logs` row per call, the standard error envelope, PII masking, hard denials, and the two-step write confirmation (pending action, then admin approval or a single-use confirmation token behind MCP elicitation). Writes run through the existing services, so the margin guard, publication rules, coupon rules, the order state machine and the supplier gate all apply. No new composer packages.

PM scope decision: M16 ran before M14/M15. Tokens are issued and revoked with `php artisan mcp:token` and `/admin/api/mcp-tokens` (`service_accounts.manage`); actions are approved through `/admin/api/mcp-actions` (`mcp_actions.approve`). No Blade or frontend JS; the screens are M15. Recorded in `IMPLEMENTATION_ROADMAP.md` under M16.

### Files created

- `backend/app/Domains/Mcp/`: `Enums/{McpScope,McpWriteTool}`; `DTOs/{McpCaller,PreparedAction}`; `Contracts/McpWriteAction`; `Actions/{UpdateProductPriceAction,ChangeProductStatusAction,ActivateProductAction,DeactivateProductAction,CreateCouponAction,TriggerCatalogSyncAction,RetryVerifiedFailedOrderAction}`; `Repositories/{McpCatalogRepository,McpOrderRepository,McpAnalyticsRepository,McpOperationsRepository,McpPricingRepository,McpPendingActionRepository}`; `Services/{McpPendingActionService,McpActionRegistry,McpTokenService,McpAuditRecorder,McpCatalogReader,McpAnalyticsReader,McpOperationsReader,PiiMasker,McpFormat}`
- `backend/app/Http/`: `Middleware/Mcp/{RecordMcpCall,AuthenticateMcpToken,RequireMcpScope,RequireMcpWriteAccess}`; `Controllers/Mcp/{McpController,SessionController,ProductController,OrderController,AnalyticsController,OperationsController,ActionController}`; `Controllers/Admin/Mcp/{McpTokenController,McpActionController}`; 13 Form Requests under `Requests/Mcp` and `Requests/Admin/Mcp`; 7 Resources under `Resources/Mcp`
- `backend/app/Console/Commands/Mcp/{McpTokenCommand,ExpirePendingActionsCommand}`, `backend/config/mcp.php`, `backend/routes/mcp.php`
- Tests: `backend/tests/Feature/Mcp/{McpAuthTest,McpReadToolsTest,McpWriteActionsTest,McpTokenAndLimitsTest,McpCanaryTest}`, `backend/tests/Support/McpFixtures.php`
- `mcp-server/`: `src/{index,config,laravel-client,errors,logger,rate-limiter,tools,server}.ts`, `test/{helpers,config.test,laravel-client.test,server.test}.ts`, `scripts/smoke.mjs`, `README.md`, `package.json`, `package-lock.json`, `tsconfig.json`, `tsconfig.test.json`, `.gitignore` (node_modules, dist, dist-test), `.env.example` (empty values)
- Changed: `AuditLogger` (actor `mcp`/`n8n` from a hidden context key set by service-account authentication, when no user is signed in), `ServiceAccount` (`HasApiTokens`, `isUsable`), `McpPendingAction` and `McpAuditLog` (new columns), `CouponService::applyGuarded` (creator may be `mcp` when the caller passes it; admin input never carries it), `AppServiceProvider` (`mcp` limiter), `bootstrap/app.php` (route file, `mcp` middleware group, `mcp.scope` alias), `routes/{admin,console}.php`, `.env.example` (four empty `MCP_*` variables), `tests/Pest.php` (requires the fixtures file)
- Docs: `MCP_SERVER.md` rewritten for what is built; `API_DOCUMENTATION.md` section 8 and status; `PROJECT_ARCHITECTURE.md` ADR-024 revisited, ADR-031, ADR-032; `DATABASE_DESIGN.md` MCP columns; `SECURITY.md` service-account paragraph; `IMPLEMENTATION_ROADMAP.md` scope decision

### Migrations

`2026_11_11_010001_add_confirmation_columns_to_mcp_tables`: `mcp_pending_actions.requested_token_id`, `confirmation_hash`, `decided_at`, `decision_note`; `mcp_audit_logs.error_code`, `request_id`. Rolled back and re-applied on `gaming-store_testing`. **Not applied to the app database `gaming-store`**; run `php artisan migrate`.

### Features implemented

- Tools and routes: see `MCP_SERVER.md` section 4. Read-only by default in two layers: the server registers write tools only with `MCP_READ_ONLY=false`, a writable account and the scope (it reads `GET /api/v1/mcp/session`); Laravel refuses writes for read-only accounts and missing scopes before validating arguments. New tokens are read-only unless writes are allowed explicitly; default lifetime 90 days.
- `unavailable` results: visitor and cart conversion (no GA4 data until M18) and scheduler heartbeats (M17). The checkout-to-payment rate is real. Sales and profit come from `order_item_financials` (settled orders only, stated in the response).
- Hard denials: no route for codes, credentials, refunds, settings, customer records or queries; masked email, phone and name; no IP, delivery values, code masks, provider ids, provider messages, sync error text or reconciliation details.
- `retry_verified_failed_order`: only when the latest supplier attempt is `rejected` or `not_accepted_verified`; refused for `pending_verification`, `accepted`, `submitting`, `succeeded` or no attempt; re-checked under row locks at execution; always tier 2, executed as the approving staff member through `FulfilmentAdminService` and the state machine.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Tests in MCP_SERVER.md section 8 | Met | Laravel: no token, wrong scope (every read and write tool), read-only account, PII masking, audit written, pending-action lifecycle with expiry, argument tampering, reuse, another token, blocked retry for `pending_verification`. Server: schemas and error mapping with Laravel mocked |
| All tools listed | Met | 27 read tools, 6 write tools, `get_action_status`; `tools/list` asserted in `server.test.ts` and the smoke test; one Laravel route each (`McpTokenAndLimitsTest` counts 36 routes) |
| Read-only default proven in both layers | Met | Server: write tools absent with the default, with a read-only account and without the scope (unit and smoke). Laravel: `read_only` 403 with the scope on a read-only account; issued tokens read-only by default |
| Setup docs with Desktop and Code config | Met | `mcp-server/README.md` and `MCP_SERVER.md` 7: Claude Desktop JSON, `claude mcp add`, `.mcp.json` with `${EGS_API_TOKEN}`; placeholder tokens only |
| PM: versioned route group, token auth, scopes, rate limit, validation, Resources, audit, envelope, no Eloquent in controllers | Met | Architecture tests pass; `mcp_audit_logs` rows for ok, denied, invalid, confirmation_required and rate_limited |
| PM: writes need write scope and two-step confirmation through existing services | Met | `McpWriteActionsTest` (36 tests) |
| PM: rate limits | Met | Per account in Laravel (429, audited, other accounts unaffected, resets); token bucket in the server |
| PM: canary | Met | `McpCanaryTest`: a code, PIN, redemption URL, its mask, supplier and payment credentials, webhook and HMAC secrets, a supplier transaction id, a player id and customer email, phone and name are absent from every read tool, a write tool, `get_action_status` and `mcp_audit_logs` (token with every scope) |

### Tests and results

- Command: `php artisan test --compact` (MySQL `gaming-store_testing`)
- Result: **1697 passed, 0 failed, 0 skipped (10066 assertions), 131.0 s**, up from 1598 (95 new tests in `tests/Feature/Mcp`, 4 from architecture datasets over the new enums and DTOs).
- Command: `cd mcp-server && npm test` (tsc, then node:test): **34 passed, 0 failed**. `npm run build`: clean. `npm run smoke`: passed (`dist/index.js` started over stdio twice against a local mock API: read-only lists 27 tools and calls `list_products`, `invalid_input` and `not_found` are structured; with writes enabled and `products:write` only, 31 tools).
- Not tested, and why: the MCP server against a running Laravel instance (the smoke test uses a mock; the new migration is not applied to the app database and no app token exists); elicitation with real Claude Desktop or Claude Code clients (tested with the SDK client over an in-memory transport); queue workers (sync queue and `Queue::fake`).
- Existing tests changed: none. `tests/Pest.php` gains one `require_once` for the MCP fixtures.

### Outstanding requirements

- Apply the migration to `gaming-store`, issue a token with `php artisan mcp:token issue`, and run the server once against the real API.
- M15: admin screens for tokens (`/admin/api/mcp-tokens`) and the AI action approval queue (`/admin/api/mcp-actions`).
- Streamable HTTP transport is not built (`MCP_TRANSPORT` accepts only `stdio`).
- If a process dies between approval and execution the action stays `approved` (visible in the queue, never re-run automatically).
- `get_low_margin_products` examines at most 5000 variants per call (`scan_truncated` reports it).
- Conversion metrics and scheduler health stay `unavailable` until M18 and M17.

### Design changes made

- ADR-031: the server uses the SDK's low-level `Server` (marked for advanced use in SDK 1.x) so schema failures return structured `invalid_input` errors.
- ADR-032: single-use, token-bound, hashed confirmation token for elicitation approval of tier 1 actions only.
- ADR-024 revisited: SDK kept on 1.x, pinned at 1.32.2 with zod 4.6.7.
- `MCP_SERVER.md`: `GET /session` added for server start-up; the confirmation flow describes the built endpoints; transports reduced to stdio.

### Next milestone

M12 (storefront) or M14 (admin core) as the PM schedules; M15 must add the MCP token and approval screens on the M16 JSON endpoints.


---

## M17: n8n workflows

Date completed: 2026-11-10
Completed by: n8n Automation Engineer

### Summary of completed work

The Laravel automation API for n8n (`/api/v1/automation`, 42 routes), dedicated credentials, the outbox to n8n, scheduled-task heartbeats, and all 58 workflows of `N8N_AUTOMATIONS.md` with one document each, a README, a credentials example and a static validator. **The workflows are authored and validated, not executed: n8n is not installed on this machine, and none has run.**

PM scope decisions (recorded in `IMPLEMENTATION_ROADMAP.md`): M17 ran before M14 and before BD9. No Blade or frontend JS: credentials go through `php artisan automation:credentials` and `/admin/api/automation-credentials` (`service_accounts.manage`); the screen is M15. All alerts go through the Z02 notify sub-workflow, whose Slack or Telegram branch is chosen by `EGS_ALERT_CHANNEL`; no workflow names a channel.

### Files created

- `backend/app/Domains/Automation/`: `Enums/{AutomationScope,AutomationLogKind,AutomationTask,CustomerMessageStatus}`; `DTOs/{AutomationCaller,SignedRequest}`; `Models/AutomationCustomerMessage`; `Repositories/{AutomationReportRepository,CustomerSegmentRepository}`; `Services/{AutomationSignature,AutomationCredentialService,AutomationWorkflowRegistry,AutomationCallRecorder,AutomationExecutionService,AutomationAlertService,AutomationTaskDrivers,AutomationJobService,AutomationOutboxWriter,AutomationOutboxDispatcher,AutomationReportService,FinancialReportService,AutomationMonitorService,SystemMonitorService,SchedulerMonitor,ScheduledTaskHeartbeat,MarketingDataService,AiSuggestionService,CustomerMessagingService}`; `Listeners/RecordScheduledTaskHeartbeat`
- `backend/app/Domains/Customers/Services/CustomerContactPolicy`
- `backend/app/Http/`: `Middleware/Automation/{RecordAutomationCall,AuthenticateAutomationRequest,RequireAutomationScope}`; `Controllers/Automation/{AutomationController,ExecutionController,ReportController,MonitorController,JobController,MarketingController,CustomerMessagingController}`; `Controllers/Admin/Automation/AutomationCredentialController`; `Requests/Automation/{AutomationQueryRequest,ExecutionRequest,AlertRequest,StoreSuggestionRequest,PrepareCustomerMessageRequest,LogCustomerMessageRequest}`; `Requests/Admin/Automation/IssueAutomationCredentialRequest`
- `backend/app/Console/TaskSchedule.php`; `backend/app/Console/Commands/Automation/{AutomationCredentialsCommand,AutomationWorkflowsCommand,FlushOutboxCommand}`
- `backend/config/automation.php`, `backend/routes/automation.php`, `backend/database/factories/Automation/AutomationCustomerMessageFactory.php`
- Tests: `backend/tests/Feature/Automation/{AutomationAuthTest,AutomationExecutionTest,AutomationOutboxTest,AutomationCustomerMessagingTest,AutomationReportsTest,AutomationCredentialTest,ScheduledTaskHeartbeatTest,N8nWorkflowValidationTest}`, `backend/tests/Support/AutomationFixtures.php`
- `n8n/workflows/<category>/<ID>-<slug>.json` (58), `n8n/documentation/<ID>-<slug>.md` (58), `n8n/README.md`, `n8n/credentials.example.md`, `n8n/scripts/validate-workflows.mjs`
- Changed: `OrderStateMachine` and `SupportTicketService` (outbox writes inside their transactions), `McpOperationsReader` (real scheduler check), `NotificationPreferenceChannel` (`sms`), `AutomationLog`, `AutomationOutbox`, `MorphMap`, `AppServiceProvider` (`automation` limiter, heartbeat listener and singleton), `bootstrap/app.php` (route file, `automation` group, `automation.scope`), `routes/admin.php`, `routes/console.php` (now calls `TaskSchedule::register`), `tests/Pest.php` (fixtures), `.env.example` (six empty variables)
- Docs: `N8N_AUTOMATIONS.md`, `API_DOCUMENTATION.md` section 7, `DATABASE_DESIGN.md` (automation tables, `sms` channel, heartbeats), `PROJECT_ARCHITECTURE.md` (section 5, 16, ADR-033, ADR-034), `SECURITY.md`, `DEPLOYMENT.md`, `IMPLEMENTATION_ROADMAP.md`

### Migrations

Rolled back and re-applied on `gaming-store_testing`. **Not applied to the app database `gaming-store`**; run `php artisan migrate`.

- `2026_11_11_020001_add_call_columns_to_automation_logs`: `kind`, `service_account_id`, `endpoint`, `http_status`, `error_code`, `duration_ms`, `ip`, `request_id`, stored generated `execution_unique_key`; `uq_alog` moved onto (workflow_id, execution_unique_key) so call rows can share a key; `idx_alog_kind_status`. Rollback deletes call rows.
- `2026_11_11_020002_create_automation_customer_messages_table`
- `2026_11_11_020003_add_delivery_columns_to_automation_outbox`: `last_http_status`, `last_error`, `failed_at`, `idx_ao_failed`
- `2026_11_11_020004_add_dedupe_hash_to_ai_suggestions`: `dedupe_hash`, `uq_ais_dedupe`

### Features implemented

- Credentials: `n8n` service account with its own encrypted HMAC secret and a Sanctum token with exact `automation:` scopes; privileged scopes (`jobs:run`, `suggestions:write`, `customers:message`) only when named; token and secret shown once, never audited or logged.
- Signed requests: bearer token plus HMAC-SHA256 over timestamp, nonce, method, path with query, workflow id, execution key and raw body (ADR-033); 5-minute window; nonce refused on reuse; only enabled workflows (`automation:workflows enable`); IP allow-list; per-account rate limit; standard error envelope; an `automation_logs` call row for every request, refused ones included (redacted query, never the body).
- Duplicate-execution protection: `POST /executions` start/finish keyed by workflow and execution key; duplicates return `skipped_duplicate`; failed keys can be restarted; job endpoints act only for a running execution and once per execution.
- Outbox: written in the transaction of the state change (order created, paid, delivered, failed; ticket staff reply or status change), identifiers only; delivered by `automation:flush-outbox` (every minute) with header token and HMAC, `SKIP LOCKED` lease, 6 retries with backoff, then `failed` with status or reason (never the body). Off unless `AUTOMATION_OUTBOX_ENABLED=true`.
- Heartbeats (inherited gap from M9 and M16): every scheduled task writes `scheduled_task_runs` through the scheduler's own events; `GET /monitor/scheduler` (F04) and the MCP `get_system_health` scheduler check compare each task with its cron (`ok`, `late`, `failing`, `never_run`, `driven_by_n8n`). ADR-014 enforced: the three n8n-drivable tasks skip in the scheduler when the setting says `n8n`, and the job endpoint refuses otherwise.
- Reports and monitors reuse the M16 readers (`order_item_financials` for revenue and profit) and fixed aggregate queries; every monitor returns `alert`.
- Customer messaging: SMS only (ADR-034); `CustomerContactPolicy` decides eligibility server-side (opt-in preference for order and support updates, latest SMS marketing consent for marketing, active customer account with a phone, never guests); Laravel writes the text; sends are logged and deduplicated.
- AI marketing: drafts stored as `pending_review` only, deduplicated, restricted to each workflow's own types; nothing published. The LLM is the n8n `LlmProvider` credential.

### Endpoints returning `unavailable`

| Endpoint | Part | Reason |
|---|---|---|
| `GET /monitor/exceptions` (F03) | all | Exceptions go to log files only; no exception store or tracker |
| `GET /monitor/latency` (F06) | web request latency | Not recorded (no APM) |
| `GET /monitor/database` (F05) | replication | Single MySQL server |
| `GET /reports/expenses` (E01) | operating expenses | Not recorded in the store |
| `GET /reports/gateway-performance` (B07) | provider latency, decline reasons | Not stored per attempt |
| `GET /reports/customer-retention` (B08) | guest retention | Guest orders are not linked to accounts |
| `GET /reports/pricing-recommendations` (B10) | competitor prices, price elasticity | No licensed source or history |
| `GET /data/trending` (C) | external market trends | No external source connected |
| `GET /reports/weekly-business` (B01) | visitor and cart conversion | GA4 is M18 (from the M16 reader) |

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Endpoint auth tests: token | Met | `AutomationAuthTest`: no token, garbage, expired, disabled account, MCP account, user token; staff session ignored |
| HMAC | Met | Missing or malformed headers (7 cases), wrong secret, tampered body, signature bound to path, query, workflow and key |
| Replay | Met | Same nonce refused (`replay_detected`), one execution row; a badly signed request does not burn the nonce |
| Stale timestamp | Met | 6 minutes old, 1 hour old, 6 minutes ahead refused; 299 s accepted |
| Duplicate execution | Met | `AutomationExecutionTest`: second start `skipped_duplicate` with one row; after success still refused; failed restarts with attempt 2; finish only once; job queued once per execution |
| Scopes, rate limit (PM) | Met | Every route group without its scope 403; wildcard grants nothing; 429 per account, audited, others unaffected, resets |
| Outbox signing and retry (PM) | Met | `AutomationOutboxTest`: signature recomputed from the sent body; header token; 500 then backoff 60 s then 300 s; failed after the last delay with F08 alert; connection failure retried; leased events skipped; written in the state change's transaction (rolled back with it); identifiers only |
| Consent filtering (PM) | Met | `AutomationCustomerMessagingTest`: opt-in required for order SMS; guest, disabled preference, marketing-only consent, no phone, suspended account refused; latest consent wins (granted then revoked excluded), email-only consent, disabled marketing SMS, staff, no phone excluded; review, reactivation and recommendation segments apply the same rule; log idempotent |
| Heartbeats (PM) | Met | `ScheduledTaskHeartbeatTest`: completed with duration, non-zero exit failed, exception class only; scheduler skips n8n-driven tasks; F04 and MCP check `ok` with fresh heartbeats and `late`/`failing`/`never_run`/`driven_by_n8n` otherwise |
| `unavailable` instead of fabricated numbers (PM) | Met | `AutomationReportsTest`: 8 endpoints return `unavailable` with a reason; empty store gives no alert and no invented figures |
| AI output only as drafts (PM) | Met | `pending_review`, deduplicated, wrong type for the workflow 403, product description unchanged |
| Every JSON parses, has an error workflow, no credentials embedded | Met, statically | `node n8n/scripts/validate-workflows.mjs` (and `N8nWorkflowValidationTest`): 58 workflows; Z01 exempt from the error-workflow rule (it cannot be its own); credentials by name only; secret scan; node types and versions checked against n8n's source; JS syntax of every Code node and expression. Negative run on a broken copy reported all 11 seeded faults |
| One doc per workflow with all required headings | Met | 58 documents, 9 headings each, checked by the validator |
| Labelled not executed | Met | Every document, the README, workflow `meta.egsStatus`, `N8N_AUTOMATIONS.md` |
| Workflows behave correctly in n8n | **Not verified** | n8n is not installed; nothing has run. Node parameter shapes were taken from n8n's source and docs, not from a running instance |

### Tests and results

- Command: `php artisan test --compact` (MySQL `gaming-store_testing`)
- Result: **1838 passed, 0 failed, 0 skipped (10783 assertions), 137.4 s**, up from 1697 (141 new: 135 in `tests/Feature/Automation`, the rest from architecture datasets over the new classes).
- Command: `node n8n/scripts/validate-workflows.mjs`: passed, 58 workflows and 58 documents (infrastructure 3, daily-operations 11, weekly-business-intelligence 10, smart-marketing 10, customer-automation 9, financial-monitoring 7, system-monitoring 8).
- Existing tests changed: `McpReadToolsTest` asserted the scheduler check was `unavailable` (the M17 gap); it now asserts `degraded` with source `scheduled_task_runs` on an empty heartbeat table, and new tests prove `ok` and each problem state. `SchemaAcceptanceTest` "creates exactly the tables in the design" gains `automation_customer_messages`. `CodeCanaryTest` now turns the outbox on and asserts an `order.delivered` event exists, so its `automation_outbox` sink is no longer empty (stronger, not weaker).
- Not tested, and why: any workflow in n8n (not installed); the n8n side of the signature (the Code node is checked for syntax only; Laravel's verifier is tested with the same canonical form); a real SMS provider, Slack, Telegram or LLM; `schedule:run` end to end (it spawns real artisan processes; the heartbeat listener is tested through the scheduler's own events); Redis nonce storage (tests use the array cache).

### Outstanding requirements

- Install n8n, create credentials, issue the Laravel credential, import, run each workflow once against staging and only then publish (`n8n/README.md`). Until then every workflow is unexecuted.
- BD9: alert channel (Slack or Telegram), SMS provider (the D workflows' SMS node sends `{to, text, reference}` and will need adapting) and LLM key.
- Apply the four migrations to `gaming-store`; set `AUTOMATION_OUTBOX_ENABLED`, `N8N_WEBHOOK_URL`, `N8N_OUTBOUND_SECRET`, `N8N_WEBHOOK_AUTH_TOKEN` when n8n exists.
- M15: admin screens for automation credentials and the AI suggestion review queue (approval creating drafts is not built; suggestions can only be stored).
- Customers cannot yet set SMS preferences or consent (account pages are M13), so no customer is eligible for n8n messages until then.
- The Error Trigger does not give the failed run's execution key; Z01 makes Laravel close the newest running execution of that workflow. A run that dies without reaching Z01 leaves its window `started` (refused as a duplicate; the next window runs).
- `automation_logs` grows by about one row per request (roughly 15 to 20 thousand a day with every workflow active); `logs:prune` (13-month retention) is not built yet.
- Not built from the architecture schedule list: `carts:detect-abandoned` (D06 reads active carts by `last_activity_at` instead), `analytics:*`, `seo:generate-sitemaps`, `logs:prune`.

### Design changes made

- ADR-033 (signature over a canonical form with an explicit nonce) and ADR-034 (SMS as the only, opt-in secondary channel).
- `automation_logs` holds call rows; `uq_alog` moved onto a generated column. New `automation_customer_messages`; outbox delivery columns; suggestion dedupe hash; `sms` preference channel.
- Schedule definitions moved to `App\Console\TaskSchedule` so the monitor can read them in HTTP requests.
- B workflows staggered every 5 minutes from Monday 07:00; D06 at minute 20; E06 window 15 minutes; documentation file names `<ID>-<slug>.md`.

### Next milestone

M12 (storefront) or M14 (admin core) as the PM schedules. M15 must add the automation credential and AI review screens; M13 must let customers set SMS preferences and consent.

---

## Mobile app

Plan and milestones: `MOBILE_APP_ROADMAP.md` (M-A1 to M-A20). Code: `application/` (see `application/README.md`).

| Milestone | Name | Status |
|---|---|---|
| M-A1 | Flutter foundation | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A2 | Backend mobile authentication (G1) | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A3 | Backend stateless storefront, checkout and locale (G2, G3) | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A4 | Backend catalog discovery and app config (G7, G9) | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A5 | Backend account orders, profile, devices, notifications, email links (G4, G5, G6 inbox, G10) | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A6 | Backend mobile payment returns (G8) | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A7 | Data layers and repositories (all features), contract fixtures (G11) | Complete (2026-11-10, not yet committed; awaiting PM verification) |
| M-A8 to M-A20 | | Not started |

### M-A1: Flutter foundation

Date completed: 2026-11-10. Completed by: LFA with SFD.

- Built: flavors `dev`/`staging`/`prod` (Android `productFlavors`, ids `shop.boxcards.app[.dev|.staging]`, `env_*.json` with non-secret values, cleartext only in dev, `allowBackup=false`); strict `analysis_options.yaml` with `riverpod_lint`; tokens transcribed from `DESIGN_TOKENS.md` and the Figma variables (colours, dark semantic theme, spacing, radii and the directional key-cut shape, type scale with Arabic rules, effects, motion with reduced motion); dark-first `ThemeData` with `AppColors` and `AppTypography` extensions; bundled OFL fonts (Changa, Readex Pro, IBM Plex Mono) with licences and source URLs; brand assets and launcher icons from `mark-master.png` on ink-950 (Android legacy and adaptive, iOS); gen-l10n `en` and `ar` with RTL switching; Dio client with request id, locale, currency, cart token, idempotency, auth (single-flight refresh), retry and redacting log interceptors; `ApiEnvelope`, `PageMeta`, `ApiError`, sealed `Failure`, error mapper for every row of roadmap 2.3, `Result`; `Money` on `decimal` (JSON numbers rejected) and a string-based formatter; secure and preferences stores; go_router with guards (auth, verified email, maintenance, update required) and a deep-link parser; `FLAG_SECURE` and sensitive clipboard through an in-app Android method channel; `tool/ci.sh`.
- Tests: `flutter test`: 119 passed, 0 failed (money, error mapper, envelope, auth/idempotency/retry interceptors, redaction, client headers, guards, deep links, router widget tests incl. RTL switch, secure and preferences stores, tokens, security channel, env files, ULID, four goldens: home and not-found in LTR and RTL). `flutter analyze` and `dart analyze --fatal-infos`: no issues. `flutter build apk --flavor dev -t lib/main_dev.dart --dart-define-from-file=lib/core/config/env_dev.json --debug`: built. `bash tool/ci.sh`: passed.
- Not tested, and why: the Kotlin channel on a device (no emulator run here; the Dart side is tested against a mocked channel); iOS (no macOS host).
- Deviations: `custom_lint` not added (riverpod_lint 3 is an analysis-server plugin and conflicts with it; `flutter analyze` does not run plugins, so CI also runs `dart analyze`); `go_router_builder` and `app_links` deferred (three routes; no verified app-link host until G8, so the link listener and intent filters come with M-A13); `remote_config.dart` not created (G9 does not exist; an `Availability` provider drives the guards meanwhile); `device_integrity.dart` not created (open decision 6, no plugin chosen); unused utils (`debouncer`, `date_format`, `validators`) not created; the template's web and desktop platform folders removed (Android and iOS only); the token refresher refuses every refresh until G1/M-A7, so a 401 signs out; colour values missing from `DESIGN_TOKENS.md` (for example `ember-600` and the status `-900` tints) were read from the Figma `Primitives`/`Theme` variables; the money formatter matches the backend's `bcadd` truncation.
- Outstanding: owner confirmation of the application id (open decision 2) and the staging host (`staging.boxcards.shop` is a placeholder); iOS schemes per flavor (macOS host); release signing (M-A20).
- Next milestone: M-A2 (backend mobile authentication) and M-A7 data layers as scheduled.

### M-A2: Backend mobile authentication (G1)

Date completed: 2026-11-10. Completed by: BEL.

- Built: `POST /api/v1/mobile/auth/{register, login, two-factor-challenge, refresh, logout, logout-all}` and `GET /api/v1/mobile/auth/me` in a stateless `api` group (`routes/mobile.php`, `API_DOCUMENTATION.md` 1b, ADR-035). `HasApiTokens` on `User`; `sanctum` guard bound to the `users` provider (`config/auth.php`); `mobile.customer` middleware group (`auth:sanctum`, `EnsureCustomerToken` requiring a bearer token with the exact `customer` ability, `account.active`, `throttle:account`). Identity domain: `MobileAuthService`, `MobileTokenService`, `MobileTokenRepository`, DTOs `MobileLoginData`, `MobileLoginResult`, `IssuedTokens`, enum `TokenRevocationReason`, models `MobileRefreshToken` and `MobileLoginChallenge` with factories and morph aliases. `AuthenticationService` refactored behind `verifyCredentials`, `recordSuccessfulLogin` and `failedLogin` (web behaviour unchanged), `RegistrationService::createCustomer` split from `register`. Config `config/mobile.php` (`mobile.auth.*`). Limiters: `auth` on register and login, `otp` on the challenge (now keyed by the challenge token when there is no session; web unchanged), new `mobile-refresh` (per token and per IP). Audit `auth.mobile_new_device` and `auth.mobile_refresh_token_reused` (plus security event `auth.refresh_token_reused`). Command `auth:prune-mobile-tokens`, hourly in `App\Console\TaskSchedule`. Docs: `API_DOCUMENTATION.md`, `PROJECT_ARCHITECTURE.md` (ADR-035, scheduled commands), `SECURITY.md` section 1 and 9, `DATABASE_DESIGN.md` section 1.
- Migrations: `2026_11_12_010001_create_mobile_refresh_tokens_table`, `2026_11_12_010002_create_mobile_login_challenges_table` (applied to the app and test databases).
- Tests: `tests/Feature/Mobile/MobileAuthTest.php` (19) and `MobileTokenTest.php` (21): register, login success and failure, device name required, staff refused (with and without 2FA), suspended and closed refused, 2FA by TOTP and recovery code, wrong code, attempt limit, expired and unknown challenge, refresh rotation, reuse revoking the family with audit and security event, expired access and refresh tokens, wildcard ability refused, suspended account, logout and logout-all, `me`, customer token refused by MCP, automation and admin APIs, MCP and n8n tokens refused by mobile routes, rate limits on login, register, challenge and refresh, prune and its schedule, web login unchanged. Full suite: `php artisan test --compact` 1888 passed, 0 failed. `vendor/bin/pint --dirty --format agent`: passed.
- Deviations: refresh uses a dedicated `mobile-refresh` limiter (5/min per presented token, 60/min per IP) instead of `auth`, because a refresh carries no email and 5/min per IP would throttle customers behind carrier NAT; the 2FA route uses `otp` as the web route does. "New device" means a device name not seen in the customer's unexpired refresh tokens (no device identifier exists yet). Refresh is strictly single use (no grace window). `SchemaAcceptanceTest`'s table inventory gained the two new tables. The app's email verification link still needs a session (G10).
- Outstanding: M-A3 adds `auth:sanctum` to account, cart and checkout routes; G5 device list can read `mobile_refresh_tokens`.
- Next milestone: M-A3 (stateless storefront, checkout and locale).

### M-A3: Backend stateless storefront, checkout and locale (G2, G3)

Date completed: 2026-11-10. Completed by: BEL.

- Approach (ADR-036): `routes/checkout.php` and `routes/account.php` are registered a second time under `/api/v1/mobile` in the stateless `api` group (`mobile.checkout.*`, `mobile.storefront.cart.*`, `mobile.account.*`), calling the same controllers, services, Form Requests, Resources and limiters. Checkout and cart: `AuthenticateOptionalCustomerToken` (no header = guest, any bearer must be a customer token, else 401; suspended 403). Account: middleware group `mobile.account` (`auth:sanctum`, `EnsureCustomerToken`, `account.active`). Controllers branch on `$request->hasSession()`; the web routes are unchanged and still never accept bearer tokens (sharing them would need a CSRF exemption for `Authorization` requests, which the session-first `sanctum` guard would turn into a CSRF bypass). The affiliate click route moved to `routes/affiliates.php` (same URL and name, web only).
- Built: headers `X-Cart-Token` (existing 64-character token, SHA-256 in `carts.session_token`, returned once in the header and `meta.cart_token`; bearer + token adopts the guest cart, the existing merge-on-sign-in), `X-Currency` (`ResolvesDisplayCurrency`, overrides the cookie, 422 unless active; `CurrencyConverter::isActiveCurrency`), `X-Guest-Order-Token` (Orders domain: `GuestOrderToken` model and factory, `GuestOrderTokenSource` enum, `IssuedGuestOrderToken` DTO, `GuestOrderTokenRepository`, `GuestOrderTokenService`; issued by place-order including replays, OTP verify (rotation, carries the link proof) and the signed link; status, verify, capture, retry need a token that opens the order; guest reveal needs link and OTP proofs on one token; audited `orders.guest_order_token_issued`, `orders.guest_order_link_opened`), `ValidateGuestAccessSignature` (both access routes verify the signature of the canonical web URL), recent auth per access token (`RecentAuthService`, `personal_access_tokens.password_confirmed_at`, carried over by `MobileTokenService::refresh`), `checkout` limiter keyed by session, else bearer user, bearer token, cart token, guest order token, else IP. Locale (G3): `SetLocaleFromRequest` (alias `locale`) appended to the `api` group, the web checkout routes and the `account` group; `app.supported_locales` (`en`, `ar`); order: session requests use `users.locale`, then `Accept-Language`, then `en`; stateless requests use `Accept-Language`, then `en`; `Content-Language` on responses. `ContentTranslator` (scoped) and `ContentTranslationRepository` translate product name and short description, platform and category names, variant titles and cart lines from `content_translations` with the English fallback (one query per page in Arabic, none in English); search suggest and popular default to the request locale. `lang/ar/validation.php`, `lang/ar/auth.php`, `lang/ar/passwords.php`, `lang/ar.json` (envelope, success and customer-facing domain messages); `JsonExceptionRenderer` and `Controller::success` translate messages; `ApplicationException::localizedMessage()`/`localizedErrors()` with placeholder keys for `rate_stale`, `rate_missing`, `price_changed`. Config `orders.guest_order_token_ttl_hours` (24). Command `orders:prune-guest-order-tokens`, hourly. `current_password:web` became `current_password` (default guard) in the confirm-password and 2FA requests; `TwoFactorController::confirm` writes the session key only when there is a session.
- Migrations: `2026_11_13_010001_create_guest_order_tokens_table`, `2026_11_13_010002_add_password_confirmed_at_to_personal_access_tokens_table` (applied to the app database with `migrate --force`; the test database migrates fresh).
- Tests: `tests/Feature/Mobile/MobileStorefrontTest.php` (16), `MobileCheckoutTest.php` (16), `MobileAccountTest.php` (19): header cart lifecycle with coupon, token isolation, no cookie or CSRF, cart adoption on mobile sign-in, currency header (listing, variants, cart, cookie override, malformed, unknown, inactive), Arabic names with fallback, variant and cart translations, translation query budget, Arabic validation and domain messages with unchanged codes, saved web locale precedence; guest place-order with headers only, Idempotency-Key replay (one order, new token), mismatch, customer bearer orders, locale stored on the order, verify-payment with the token, wrong-order, expired and customer-order tokens refused on status, verify, capture and retry, prune, web session unchanged, signed link and OTP on the token with rotation, OTP alone not enough, tampered and expired links, expired token, wrong code; limiters per cart token and per bearer user; account wallet, reviews, refund requests, tickets, 2FA enrolment, bearer-only, reveal 423 then confirm-password then reveal, per-device confirmation, carry-over on refresh without extending the window, non-owner 404, tokens without the exact `customer` ability, staff and service-account tokens, staff web session, suspended account. Full suite: `php artisan test --compact` 1942 passed, 0 failed (1888 after M-A2; +51 new M-A3 tests, the rest are new dataset cases of existing generic tests such as the model factory test picking up `GuestOrderToken`). `vendor/bin/pint --dirty --format agent`: passed.
- Existing tests changed: `tests/Feature/Schema/SchemaAcceptanceTest.php` table inventory gained `guest_order_tokens` (intentional schema extension). `tests/Support/MobileFixtures.php` gained stateless helpers (no existing helper changed). No other existing test was changed.
- Deviations: cart merge for mobile uses the existing adoption path (send `X-Cart-Token` with the bearer token on the first cart call after login) instead of a `cart_token` field on login/register or a merge endpoint. A link-only token is issued when the signed link is opened without a token, so the link proof survives until OTP verification. Stateless requests ignore a saved `users.locale` (the bearer token is authenticated after the locale middleware; the app always sends `Accept-Language`). Untranslated (admin-only) messages stay English in Arabic responses. The `checkout` limiter resolves the bearer user itself because the framework runs `throttle` before the route's authentication middleware.
- Outstanding: G4 guest order detail (units) via the guest order token (M-A5); G8 app return URLs (M-A6); Arabic `content_translations` data entry for the catalog (admin translation screens are M15).
- Next milestone: M-A4 (catalog discovery and app config).

### M-A4: Backend catalog discovery and app config (G7, G9)

Date completed: 2026-11-10. Completed by: BEL.

- Approach (ADR-037): the public storefront reads moved from `routes/api.php` to `routes/storefront.php`, registered twice in the stateless `api` group (`/api/v1`, names `api.v1.storefront.*` unchanged; `/api/v1/mobile`, names `mobile.storefront.*`), same controllers. `POST /storefront/currency` (sets a cookie) stays web-only in `routes/api.php`, which also holds `GET /api/v1/app/config`; its twin `GET /api/v1/mobile/app/config` is in `routes/mobile.php`.
- Built (G7): `GET storefront/catalog/products/{slug}` (`CatalogController::show`, `StorefrontCatalogService::detail`, `ProductRepository::storefrontDetail`, DTO `ProductDetail`, `ProductDetailResource`): card fields, sanitized HTML description, plain-text instructions and terms, absolute gallery, regions, rating, availability, variants summary, delivery field definitions, SEO; 404 unless active. Delivery fields: DTO `DeliveryFieldSpec` built by `DeliveryFieldValidator::specsFor`; `validate()` now iterates the same specs and `DeliveryFieldResource` serializes them (single source). `GET storefront/platforms` and `storefront/categories` (`StorefrontTaxonomyService`, DTO `CatalogNode`, `CatalogNodeResource`, listed-product counts in one grouped query each via `ProductRepository::listedCountsByPlatform/ByCategory`). `GET storefront/countries` (`CountryDirectory`: one "active" definition used by the list and by the `country` rule of validate, payment-options and place-order; ICU Arabic names). `GET storefront/home` (`StorefrontHomeService`, DTOs `HomePage`, `HomeSection`, `HomeItem`, enum `HomepageAutoRule`, `HomeSectionResource`, storefront `BannerResource`): active sections in order, manual items resolved (hidden ones dropped), auto rules through the listing query (new `ProductListingFilter::flag` for trending/featured, `ProductRepository::storefrontTop/storefrontByIds`), featured approved reviews (`ReviewService::featuredForHome`), live banners by placement, structured links (`LinkTargetResolver`, DTO `LinkTarget`, enum `LinkTargetType`). All product cards are priced by `StorefrontCatalogService::cards`, the listing's pricing code (refactored into a shared `card()`), in one batch. Absolute media URLs: `MediaUrls` (`catalog.media.base_url`, env `MEDIA_BASE_URL`, else the disk URL made absolute); `ProductCardResource.image` keeps `path` and `alt` and adds `url`, `width`, `height`, `formats`. Caching: `PublicCacheHeaders` (alias `cache.public`): `public, max-age` 60 (detail, home, app config), 300 (platforms, categories), 3600 (countries), ETag with 304, `Vary`.
- Built (G9): `GET /api/v1/app/config` (`AppConfigController`, `AppConfigRequest`, `AppConfigService`, DTOs `AppConfig`, `AppUpdateStatus`, `AppConfigResource`): versions per platform, optional `platform`+`version` giving `update.required/available` by SemVer (`SemanticVersion` value object), maintenance flag and localized message, feature flags, locales, currencies, media base URL, support/terms/privacy/refund URLs, public payment identifiers of active gateways (`PaymentPublicCredentials`). Settings group `app` (enum `AppConfigSetting`), defaults in `config/mobile.php` `app` (env `MOBILE_*`); command `mobile:app-config show|set`. Stripe `publishable_key` (and PayPal `client_id`, Paymob `public_key`) in the gateway's encrypted credentials per mode (`config/payments.php` `public_credentials`, prefix check `pk_test_`/`pk_live_`); command `payments:public-key <gateway> <value> --mode= [--clear]`, audited `payments.public_credential_updated`. `lang/ar.json`: two strings.
- Migrations: none (no schema change; new keys in existing JSON and settings rows).
- Tests: `tests/Feature/Mobile/CatalogDiscoveryTest.php` (23 tests, 36 cases with datasets): detail on both bases with absolute images, SEO and no cost fields; Arabic text, field labels and sanitized translated HTML with fallback; 404 for draft, inactive, deleted, unknown; detail price equals listing price in EGP; cache headers and 304; detail query budget; published delivery fields equal `specsFor`; add-to-cart with values built from the definitions passes, and breaking each rule (pattern, select, number, email, unknown key, missing required) fails; listing `image.url` from the disk and from a CDN base; platforms (order, counts, inactive hidden, uploaded logo only), categories (parents first, inactive parent hides children, secondary counted), Arabic names, taxonomy query budgets; countries on both bases, Arabic, list equals what checkout validation accepts; home on both bases (order, inactive hidden, manual items and dropped hidden items, links), auto rules, featured reviews, banner schedule (future, ended, inactive, deleted, other placement hidden), link mapping (8 cases), carousel prices equal listing prices via `X-Currency`, Arabic, home query budget. `tests/Feature/Mobile/AppConfigTest.php` (11 tests, 26 cases): content on both paths, settings over defaults via the command (and refused values), maintenance on and off with en/ar and custom message, force-update boundary (8 cases incl. 1.3.9/1.4.0/1.4.0-rc.1/1.9.0 vs 1.10.0/build metadata), invalid query (4), publishable key null then set then cleared, refused values (secret, restricted, live-on-sandbox, mode without credentials, unknown gateway), mismatched stored key published as null, active gateways only, no secret values or field names, query budget. `tests/Unit/Shared/SemanticVersionTest.php` (3 tests, 16 cases). Budgets (cold cache): detail 15, platforms 6, categories 6, countries 3, home 30, app config 8; none grows with rows. Full suite: `php artisan test --compact` 2033 passed, 0 failed (1942 after M-A3; 78 new M-A4 cases, the rest are new dataset cases of existing generic tests over DTOs and enums). `vendor/bin/pint --dirty --format agent`: passed.
- Existing tests changed: none. `tests/Support` unchanged.
- Deviations: the publishable key has no admin screen (gateway credentials have none yet, M15); it is set with `payments:public-key` and only for a mode that already has credentials. App settings likewise use `mobile:app-config` (no admin settings screen exists). `features.*` flags are advisory for the app and do not switch server routes off. Media has format conversions only (WebP, AVIF), no resized variants, so images expose `formats`, not sizes. Country names in Arabic come from PHP intl (ICU), because `content_translations.translatable_id` is numeric and countries are keyed by ISO code. Home `auto` rules are defined by `homepage_sections.settings.rule` with defaults per seeded key; `trust_indicators` and `newsletter` return no items. Product `description` is HTML (sanitizer allow-list), `instructions` and `terms` plain text. Default support and legal URLs are null until configured (no legal page slugs exist yet).
- Outstanding: set real app versions, legal URLs and the Stripe publishable key per environment (BD1 still open for Stripe); Arabic catalog translations data entry (M15); admin screens for app settings and public payment identifiers (M15).
- Next milestone: M-A5 (account orders, profile, devices, notifications, email links).

### M-A5: Backend account orders, profile, devices, notifications, email links (G4, G5, G6 inbox, G10)

Date completed: 2026-11-10. Completed by: BEL.

- Approach (ADR-038): every account route is in `routes/account.php`, so it serves the web session (`/api/v1/account`) and the bearer token (`/api/v1/mobile/account`) with the same controllers; the guest order detail is in `routes/checkout.php` (both prefixes). Contract: `API_DOCUMENTATION.md` 4c and 1a.
- Built (G4): `GET /account/orders` (`OrderController`, `ListOrdersRequest` with `status`, `from`, `to`, DTO `CustomerOrderFilter`, `CustomerOrderRepository::paginateForCustomer`, `OrderSummaryResource`: number, statuses, Money totals, item count, absolute thumbnail URL); `GET /account/orders/{public_id}` (`OrderPolicy` registered with `Gate::policy`, deny as not found; `CustomerOrderRepository::loadDetail`; `OrderDetailResource`: items, units with `revealable` from the new `CodeRevealService::isRevealable` (shares the paid and delivered checks with the gate), `masked_code`, `first_revealed_at`, payment attempts without provider identifiers, refunds, `refund_state`, status timeline from `order_events`). `OrderStatusReader::paymentStatus` made public for reuse. Guest detail `GET /checkout/guest/orders/{public_id}` (`GuestOrderController::show`): status proof (placing or OTP-verified session; placement or OTP guest order token), `meta.guest_access`, `no-store`.
- Built (G5): `GET/PATCH /account/profile` (`ProfileController`, `UpdateProfileRequest`, DTO `ProfileChanges`, `ProfileService`, `ProfileResource`; audited `account.profile_updated` without the phone); `PUT /account/password` (`PasswordController`, `ChangePasswordRequest`, `AccountSessionService::changePassword`: caller kept, other families revoked `password_changed`, other database sessions deleted, remember token rotated, audited); `GET/DELETE /account/devices` (`DeviceController`, `AccountSessionService`, DTO `AccountDevice`, enum `AccountDeviceType`, `DeviceResource` with masked IPs, `MobileTokenRepository::liveFamiliesForUser/hasLiveFamily/revokeAllForUserExcept`, new `WebSessionRepository` for database sessions with HMAC ids, exception `CurrentDeviceRevocation` 409 `current_device`; audited `account.devices_viewed`, `account.device_revoked`). `TokenRevocationReason` gains `device_revoked`, `password_changed`, `password_reset`. `PasswordResetService` now signs out every device and session after a reset.
- Built (G6 inbox): `GET /account/notifications` (+ `unread-count`, `{id}/read`, `read-all`) (`NotificationController`, `ListNotificationsRequest`, `InboxRepository`, `InboxNotificationResource` rendering title and body in the request language from enum `InboxNotificationKind`, link `{type, value, url}` with new `LinkTargetType::Order/Ticket` and `LinkTargetResolver::forAccount`); `GET/PATCH /account/notification-preferences` (`NotificationPreferenceController`, `UpdateNotificationPreferencesRequest`, `NotificationPreferenceService`, DTO `PreferenceSetting`, exception `NotificationPreferenceMandatory` 422). Channel fix: `OrderDelivered`, `OrderFulfilmentFailed` and `SupportTicketReplied` sent mail only and never wrote the inbox; they now choose `mail` and `database` through `NotificationPreferenceService::channelsFor` (orders and security email mandatory; guests email only), with `databaseType` (kind) and `toDatabase` (kind, number, link; no codes). A customer who switches support email off no longer receives the ticket-reply email.
- Built (G10): `GET /email/verify/{id}/{hash}` without `auth` and `signed` middleware: `EmailVerificationController::verify` checks the signature itself and `EmailVerificationService` checks the id and the hash of the current address; JSON gets the envelope (403 when invalid), a browser is redirected to `auth.links.email_verified_url` (env `EMAIL_VERIFIED_URL`, `{status}`), default `APP_URL/?email_verification={status}`. `GET /reset-password/{token}` redirects to `auth.links.password_reset_url` (env `PASSWORD_RESET_URL`, `{token}`, `{email}`) with `no-store` and `no-referrer`, else keeps the JSON echo. Bearer resend: `POST /account/email/verification-notification`. No Blade page built. `lang/ar.json`: 18 strings. `.env.example`: the two keys.
- Migrations: none (new enum values in VARCHAR columns; `notifications`, `notification_preferences` and `sessions` exist).
- Tests (all Pest, MySQL; every account case runs for a web session and a mobile bearer token through `accountCall`, fixtures `tests/Support/AccountFixtures.php`): `tests/Feature/Account/AccountOrdersTest.php` (25 cases: list ownership, newest first, pagination meta, Money totals, item count, status and date filters, 4 validation cases, thumbnail URL and query budget (at most 6 order-related queries), detail with units, revealable, masked code, payments, timeline and canary (no code, PIN, redemption URL, provider ids, staff note, pricing breakdown), 404 for another customer, guest and unknown orders, reveal with the unit id from the detail, guest detail with placement and OTP tokens, 404 for no token, link-only, other order, expired token, customer order on the guest route, browser session with and without the order); `AccountProfileSecurityTest.php` (36 cases: profile get, patch all fields with normalization and audit without phone, partial patch and null clear, 6 validation cases, web locale effect vs mobile `Accept-Language`, 3 password validation cases, password change keeping the caller and revoking another device, its refresh token, a database session and the remember token, password reset revoking devices, device list with masked IP and current flag, revoke another device, refuse the current device 409, 404 for another customer's and unknown devices, database web sessions listed with HMAC id, masked IPv6 and revoked); `AccountNotificationsTest.php` (27 cases: real notifications write the inbox for all four kinds and send mail, preferences respected (inbox off, support mail off, order mail always), guests email only and no OTP in the inbox, canary over the notifications table, inbox response and mail, list with localized text, link, pagination and unread filter, read, read-all, unread count, 404 for another customer's notification, preference matrix with defaults and consent, patch and audit, mandatory refusal for orders and security, 4 validation cases); `EmailLinksTest.php` (8 cases: verify without session with configured landing and `already_verified`, default landing, JSON without session or token, tampered signature, other hash and changed address refused, another signed-in user, bearer resend, reset redirect with encoding and headers, JSON echo when unset). Full suite: `php artisan test --compact` 2135 passed, 0 failed (2033 after M-A4: 96 new M-A5 cases, plus 6 dataset cases of the architecture tests over the new DTOs and enums). `vendor/bin/pint --dirty --format agent`: passed. `migrate --force`: nothing to migrate.
- Existing tests changed: none. `tests/Pest.php` only gains the `require_once` of the new fixtures file.
- Deviations: the planned `/account/sessions` is `/account/devices` (mobile families plus database browser sessions). Browser sessions are listed only with `SESSION_DRIVER=database` (`meta.web_sessions_listed`). A stateless client keeps choosing its language with `Accept-Language` (the bearer is authenticated after the locale middleware); `profile.locale` is for the app to read and for web sessions. `display_currency` is stored but does not override the cookie or `X-Currency`. The email address cannot be changed (no verified change flow exists). Password reset now also signs out every device (a security improvement beyond the brief). The verification link now verifies the link's user even when another user is signed in in that browser (previously 403). `/digital-codes` remains planned (the masked code is in the order detail).
- Outstanding: set `PASSWORD_RESET_URL` and `EMAIL_VERIFIED_URL` per environment (an app link needs M-A6 `/.well-known` files; the web reset page arrives with M12); push notifications (M-A18).
- Next milestone: M-A6 (mobile payment returns).

### M-A6: Backend mobile payment returns (G8)

Date completed: 2026-11-10. Completed by: BEL.

- Approach (ADR-039): the client names a channel, never a URL; the server builds every provider return and cancel URL from `config/payments.php`. Contract: `API_DOCUMENTATION.md` 1e.
- Built (channel): optional `client` (`web` or `app`, enum `PaymentClient`, `Rule::enum`) on `PlaceOrderRequest` and `RetryPaymentRequest` through the `ResolvesPaymentClient` concern; default `web` on the session routes and `app` on `/api/v1/mobile`. Passed through `CheckoutPaymentService::startAfterPlacement`/`retry` to `PaymentAttemptService::start` and stored per attempt in the new column `payments.client` (VARCHAR(8), default `web`). `client` is part of the place-order fingerprint.
- Built (URLs): `PaymentReturnUrlBuilder` + DTO `PaymentReturnUrls`. Web: `payments.return_url` (unchanged, `url()`) and new `payments.cancel_url` (`PAYMENTS_CANCEL_URL`, defaults to the return page). App: `payments.app_return_url` / `app_cancel_url` (defaults `/app/payments/return|cancel/{order}?attempt={attempt}`), paths only, joined to `APP_URL` (not the request host), HTTPS required in production, otherwise the attempt fails `gateway_unavailable` before any provider call. `payments.stripe_app_return_url` (`PAYMENTS_STRIPE_APP_RETURN_URL`) for PaymentSheet. `CreatePaymentRequest` gains `cancelUrl`, `client`, `sdkReturnUrl`.
- Built (providers): PayPal sends `payment_source.paypal.experience_context.return_url` and `cancel_url` for both channels (verified in the Orders v2 schema 2.36); Paymob `redirection_url` is the channel's return URL (verified field); Stripe's PaymentIntent create is unchanged and `next.return_url` is added for app attempts only (Stripe iOS requires a custom scheme, verified in Stripe's docs).
- Built (routes, `routes/app_links.php`, registered outside every middleware group): `GET /app/payments/return/{order}` and `/cancel/{order}` (`AppPaymentReturnController`, `whereUlid`, `storefront` limiter): 302 to the web return or cancel page with no query string, `no-store`, `no-referrer`, 404 for an unknown order; never verifies or changes anything. `GET /.well-known/assetlinks.json` and `/.well-known/apple-app-site-association` (`AppLinkAssociationController`, `AppLinkAssociationService`, `cache.public:3600`): from `config/mobile.php` `app_links` (`MOBILE_ANDROID_PACKAGES` default `shop.boxcards.app`, `MOBILE_ANDROID_SHA256_FINGERPRINTS`, `MOBILE_IOS_APP_IDS`), malformed values dropped, `[]` and `{"applinks":{"details":[]}}` when unconfigured. `public/` has no `.well-known` directory; the nginx config in `DEPLOYMENT.md` 6 already passes the paths to Laravel (documented).
- Migrations: `2026_11_14_010001_add_client_to_payments_table`.
- Tests: `tests/Feature/Mobile/MobilePaymentReturnTest.php` (31 cases: PayPal web return/cancel, configured web cancel, app return/cancel, app links built on `APP_URL` whatever the URL root, 3 refused app templates with no provider call; Paymob `redirection_url` for web and app; Stripe `next.return_url` app only, configured scheme, PaymentIntent create unchanged; mobile place-order defaults to `app`, session place-order defaults to `web`, explicit `client`, client-supplied `return_url`/`redirect_url`/`redirection_url` ignored, 3 rejected `client` values, retry from the app keeps `app` and a retry can name `web`, unknown `client` on retry; return link redirects without query, cancel link, `success=true` changes nothing and calls no provider, 3 not-found paths; well-known empty and configured files, content type, cache header, no cookies). Full suite: `php artisan test --compact` 2168 passed, 0 failed (2135 after M-A5: 31 new M-A6 cases, plus 2 dataset cases of the architecture tests over the new DTO and enum). `vendor/bin/pint --dirty --format agent`: passed. `migrate --force`: the new migration ran.
- Existing tests changed: none.
- Deviations: the channel is stored on the attempt, not the order (each retry records its own; a retry without `client` takes its route group's default, which is `app` for every app call). The web return page is not built until M12, so an app link opened in a browser redirects to a page that is 404 until then (or to `PAYMENTS_RETURN_URL`). Stripe's iOS SDK does not support universal links, so `next.return_url` is the HTTPS app link until `PAYMENTS_STRIPE_APP_RETURN_URL` is set to the app's custom scheme (the app defines none yet). The web return URL still uses `url()` (request host); unchanged here.
- `UNVERIFIED` (INTEGRATIONS.md): PayPal's approval link rel and order status when `payment_source.paypal` is sent (the adapter accepts `approve` and `payer-action`); Paymob `redirection_url` for non-card methods and into an installed app; Stripe Android's use of `returnURL`. Sandbox and device verification remain open.
- Outstanding: owner values for `MOBILE_ANDROID_SHA256_FINGERPRINTS`, `MOBILE_IOS_APP_IDS` and the app's Stripe scheme (open decision 4); the app's intent filters and associated domains (Flutter milestones).
- Next milestone: M-A7 (data layers and repositories).

### M-A7: Data layers and repositories (all features), contract fixtures (G11)

Date completed: 2026-11-10. Completed by: SFD with BEL.

- Contract fixtures (G11, `API_DOCUMENTATION.md` 1f): Pest suite `backend/tests/Feature/MobileContract` (`AuthContractTest`, `StorefrontContractTest`, `CartCheckoutContractTest`, `PaymentsContractTest`, `AccountContractTest`, `FixtureSafetyContractTest`; helper `tests/Support/ContractFixtures.php`, required from `tests/Pest.php`) calls every `/api/v1/mobile` route the app uses with factories, with a frozen clock, and writes `docs/api-fixtures/<area>/<name>.json`: 141 fixtures in 18 areas (account 10, app_config 3, auth 15, cart 12, catalog 12, checkout 14, code_reveal 8, guest_orders 6, home 2, notifications 8, orders 5, payments 11, product 7, reviews 6, search 3, security 6, support 10, wallet 3). Success responses plus 422 validation, 422 domain (`coupon_invalid`, `price_changed`, `cart_empty`, `otp_invalid`, `two_factor_invalid`, `two_factor_expired`, `capability_not_supported`, `review_not_eligible`, `notification_preference_mandatory`), 401 (`unauthenticated`, `refresh_token_invalid`), 403 (`reveal_denied`, `email_unverified`), 404, 409 (`idempotency_mismatch`, `order_not_payable`, `review_exists`, `ticket_closed`, `refund_request_open`, `current_device`, `two_factor_already_enabled`), 423 `recent_auth_required` and 429 (login and reveal, with `Retry-After`). Arabic fixtures for app config, home, catalog listing, platforms, categories, countries, product detail, variants and reviews. Normalized ULIDs (case kept), UUIDs, integer ids (also in paths; ids from 900000 are sentinels and kept), order and ticket numbers, request ids, timestamps and every token or secret; money and other values exact; sorted keys, pretty-printed. Drift fails a normal run; `UPDATE_API_FIXTURES=1` rewrites. Reveal fixtures hold only the canary `FIXTURE-CODE-0000`; the safety test scans every fixture for secret key material, bearer values, TOTP secrets, real codes and numeric money (no real key material found; the gateway test credentials are the existing `*_test_fixture` values, and `sk_`/`whsec_` never appear).
- Contract bug found and fixed: the response that issues a guest cart token (`InteractsWithCart::withCartCookie`) re-encoded the body from `getData(true)`, so empty objects such as an item's `delivery_values` became `[]` instead of `{}` in that one response. It now decodes to objects. Regression test in `tests/Feature/Mobile/MobileStorefrontTest.php`.
- App: `tool/sync_fixtures.sh` copies `docs/api-fixtures` to `application/test/fixtures/api` (committed, so Flutter CI needs no PHP); `tool/ci.sh` runs `sync_fixtures.sh --check` when `../docs/api-fixtures` exists and fails on `double` in feature, shared data and shared domain code.
- App data and domain layers for all 17 features of roadmap 2.1 (app_config, auth, home, catalog, search, product, cart, checkout, payments, orders, code_reveal, wallet, reviews, support incl. refund requests, notifications inbox and preferences, account, security): freezed + json_serializable DTOs, remote data sources on `/mobile/...` paths, repository contracts and implementations returning `Result<T>` with mapped `Failure`s, freezed entities, generated Riverpod providers. Shared DTOs/entities in `lib/shared` (cart, payment `next`, order payment status, guest order token, product card, image, link). `build.yaml` sets `field_rename: snake`, `explicit_to_json`.
- Use cases: `PlaceOrder` (one ULID key per body hash, persisted with the hash and order id in secure storage before sending; same body reuses the key across retries and restarts, a changed body gets a new key; `meta.duplicate` routes to the existing order; `next: null` calls retry-payment once; `pendingOrder()`/`finish()`), `ResolvePayment` (capture for PayPal, none for wallet, else verify; status after 2, 3, 5 s then every 10 s up to 2 minutes, then "still processing"; paid only on `payment_status: paid`; transient failures keep polling), `RevealCode` (423 -> password callback -> confirm-password -> one retry; 429 and other failures returned, never retried), `UpdateRegionalSettings` (profile locale/currency, then local preferences).
- Core changes: `MobileTokenRefresher` and `refreshClientProvider` replace the refusing stub (single flight already in `AuthInterceptor`; a 4xx such as `refresh_token_invalid` clears the tokens and signs out, a transport error keeps the session); `CartTokenInterceptor` captures `X-Cart-Token` from responses; new `GuestOrderTokenInterceptor` attaches the per-order token; `SecureStore` pending-checkout keys; `MoneyConverter`; `decodeEnvelope` (decode errors become `DecodingFailure`); `Failure implements Exception`.
- Tests: backend 48 new (47 contract cases + 1 regression); full suite `php artisan test --compact` 2216 passed (sequential, about 6 minutes). Flutter 349 (119 before, 230 new): every success fixture decodes and round-trips (91), every error fixture maps to its `Failure` (50), repository tests for all features against the fixtures through the real API client and interceptors (52, checking the recorded method, path and query), use cases (idempotency reuse and new key, restart, duplicate, `next: null`, polling schedule with a fake clock, reveal recent-auth flow), token lifecycle through the provider graph (concurrent 401s refresh once; reuse signs out; offline refresh keeps the session), interceptors (cart token capture and send-back, guest token per order, `X-Currency`/`Accept-Language`), reveal safety (redacting log drops reveal bodies, no storage write, auto-disposed providers), no-double scan.
- Deviations: no Notifiers for cart, checkout or polling (the brief limited this milestone to presentation-agnostic providers; controllers come with the UI milestones). Search history (local) is not built: it is UI state with no API, deferred to M-A11. The app config model lives in `features/app_config` instead of `core/config/remote_config.dart`. Wallet statement amounts stay decimal strings (the API sends no currency per line). Faker is not deterministic across runs here, so the contract fixtures set every displayed value explicitly.
- Outstanding: push device registration (M-A18); provider SDK launch (Stripe, PayPal, Paymob) is M-A13.
- Next milestone: M-A8 (Figma mobile app design).

