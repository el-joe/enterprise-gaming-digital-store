# Progress Tracker

Milestones are defined in `IMPLEMENTATION_ROADMAP.md`. Add a section per milestone using the template below, newest at the bottom.

## Status board

| Milestone | Name | Status |
|---|---|---|
| M0 | Discovery and architecture | Complete |
| M1 | Figma design | Complete (web Phase 1 and Phase 2 approved by the owner 2026-11-10) |
| M2 | Canva assets | Complete (6 Canva heroes kept as is; Canva then dropped by owner decision 2026-11-10, remaining assets made as original vectors) |
| M3 | Foundation | Complete |
| M4 | Full schema | Complete |
| M5 | Catalog and CMS services | Complete |
| M6 | Currency and pricing | Complete |
| M7 | Orders and checkout core | Complete (coupons bound at M11; payment currency bound at M10) |
| M8 | Supplier engine and Reloadly | Complete (Reloadly mocked only, awaiting sandbox verification) |
| M9 | Fulfilment and digital codes | Complete (reveal pages and account order list are M13) |
| M10 | Payments | Complete (all three adapters mocked only, awaiting sandbox verification; Stripe also awaits BD1) |
| M11 | Wallet, promotions, affiliates, reviews, support | Complete (wallet is internal, no provider; affiliate payouts, guest tickets and admin screens not built) |
| M12 | Storefront | Complete (Blade pages EN/AR; web checkout requires sign-in by owner decision 4, flag `storefront.guest_checkout`; payments mocked only; newsletter, wishlist and account area not built) |
| OD-1 | Owner decisions, backend (`OWNER_DECISIONS.md` 1, 3, 4, 5, 6) | Complete (2026-11-11, not committed; Flutter only DTO fields and fixtures) |
| M13 | Customer account | Complete (2026-11-11, not committed; every spec section 9 module in EN/AR including wishlist; ticket attachments not built, no API) |
| M14 | Admin core | Complete (2026-11-11, not committed; layout, dashboard, DataTables base, orders, suppliers and payments in EN/AR; M15 sections hidden until built) |
| M15 | Admin remaining | Complete (2026-11-11, not committed; every spec Phase 9 module in EN/AR; some settings stored for later milestones, see the M15 open items) |
| M16 | MCP server | Complete (run before M14 by PM decision; admin screens for tokens and approvals are M15; stdio only; not yet run against a live Laravel instance) |
| M17 | n8n workflows | Complete (run before M14 and BD9 by PM decision; Laravel automation API, outbox and heartbeats built and tested; 58 workflows authored and validated, **not executed**: n8n is not installed here; admin screen is M15) |
| M18 | SEO and analytics | Complete (2026-11-11, not committed; GA4 Measurement Protocol and Search Console mocked only, awaiting credentials; Lighthouse not run: not installed, manual headless-Chrome check recorded) |
| M19 | Full test pass | Complete (2026-11-12; D1 fixed during the run, D2 after it; final run backend 2,890 and app 1,660 passed; coverage matrix in `TEST_COVERAGE.md`; manual checklist 70 steps: 68 PASS, 1 FAIL (D2, since fixed), 1 BLOCKED (covered by tests)) |
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
| GA4 Measurement Protocol | Built (M18: `AnalyticsEventDispatcher`, `SendAnalyticsEvent`, server `purchase` once per order) | Yes (`Http::fake` only) | **Awaiting credentials**; never called live | No |
| Google Search Console | Built (M18: OAuth connect on the Laravel HTTP client, daily fetch, admin report) | Yes (`Http::fake` only) | **Awaiting credentials**; OAuth and API never called live | No |
| Firebase Cloud Messaging (push) | Built (M-A18: `FcmClient` on the Laravel HTTP client, service-account JWT signed with openssl, `PushChannel`, `SendPushMessage`); app on `firebase_messaging` | Yes (`Http::fake` and a generated test key only) | **Awaiting credentials, mocked only**: no Firebase project, service account or app config file; never called live; `FCM_ENABLED` off | No |
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

## M12: Storefront

Date completed: 2026-11-11
Completed by: Senior Laravel Frontend Developer

### Summary of completed work

Every spec Phase 4 storefront page as server-rendered Blade with Tailwind 4 and plain JavaScript modules through Vite (no Livewire, no framework), in English (LTR, unprefixed) and Arabic (RTL, `/ar/...`), from the approved Figma file `JkUUSR3WGBYC9ewy2qRFQO` (pages Storefront, Checkout, RTL). Pages read through the existing services and API resources (the same arrays the JSON returns) and every write goes through the existing JSON routes with CSRF; errors show beside fields from the envelope. Money is only ever rendered from server `formatted` strings; the JavaScript does no money arithmetic. Owner decisions of 2026-11-10 applied: hero carousel (decision 8), Western digits with isolated LTR amounts in Arabic (2), checkout shows the display amount and the exact charged amount from the payment-options and place-order responses (3), web checkout requires sign-in behind one flag (4).

### Files created

- Routes: `routes/storefront_pages.php` (registered per language in `bootstrap/app.php`, last, after every other group); payment return `GET /checkout/orders/{order}/return` (`storefront.checkout.return`, the configured `payments.return_url`).
- Controllers `app/Http/Controllers/Web/`: `HomeController`, `PlatformController`, `CategoryController`, `ProductController`, `SearchController`, `CartController`, `CheckoutController`, `CheckoutReturnController`, `OrderController`, `PageController`, `AuthPageController`, `AccountController`, `Concerns/RendersProductListing`. Requests `app/Http/Requests/Web/{ListingPageRequest,SearchPageRequest}`.
- Support: `app/Http/Support/{LocalizedRoutes,StorefrontRequest}`, `app/View/Composers/StorefrontLayoutComposer`, `app/Providers/StorefrontServiceProvider` (`url()->localized()`), middleware `Storefront/{ContentSecurityPolicy,SetStorefrontLocale}`, `config/storefront.php`, `app/Domains/Cms/Services/HomeTranslations` (shared by the home JSON and page), `app/Domains/Orders/Exceptions/GuestCheckoutDisabled`.
- Views: `layouts/{storefront,auth}`, `components/store/*` (header, footer, region-switcher, product-card, tile, dial, icon, section-head, breadcrumbs, empty-state, art, stars, field), `storefront/*` (home, category, platforms/index|show, product, search, cart, checkout, checkout-return, orders/show|access, pages/show, account, auth/login|register|two-factor|forgot-password|reset-password|verify-email, partials/*), `errors/{404,500,503,minimal}`.
- Front end: `resources/css/app.css` (tokens from DESIGN_TOKENS.md as `@theme`, key-cut utilities, logical RTL), `resources/js/app.js`, `lib/{config,http,forms,payment}.js`, `modules/{menu,popover,currency,search,hero,tabs,filters,product,cart,checkout,payment-return,retry-payment,reveal,guest-access,auth-form,logout}.js`; `vite.config.js` (self-hosted Google fonts Changa, Readex Pro, IBM Plex Mono through `laravel-vite-plugin/fonts`; banner art as build inputs so `Vite::asset` resolves). `package-lock.json` (npm).
- Translations: `lang/en/storefront.php`, `lang/ar/storefront.php` (same keys), one string in `lang/ar.json`.
- Tests: `tests/Feature/Storefront/{StorefrontPagesTest,CheckoutPagesTest,AuthPagesTest}.php`, `tests/Support/StorefrontFixtures.php`.
- Changed: `bootstrap/app.php`, `bootstrap/providers.php`, `routes/web.php` (welcome route and view removed), `routes/auth.php` (reset page middleware), `Storefront/CheckoutController` (guest refusal), `Storefront/HomeController` (shared preload), `Storefront/GuestOrderCodeController` (browser redirect), `Storefront/Auth/NewPasswordController` (browser page), `CartService::unitCount`, `SearchService::results`, `PageService::findPublished`, `AuthenticationService::PENDING_KEY` (now public), `components/brand/logo.blade.php` (inline style removed for the CSP; size comes from the width and height attributes).

### Pages and routes

| Page | EN / AR URL | Notes |
|---|---|---|
| Home | `/`, `/ar` | Admin sections and banners (home JSON service); hero carousel; trust; newsletter band |
| Platforms | `/gaming`, `/gaming/{slug}` | BD8 neutral tiles; logo slot hidden unless an admin uploaded one; platform art by index |
| Category | `/{slug}` | Filters (platform, delivery, price in the display currency), sort, pagination; noindex with 2+ filters; canonical to the unfiltered page |
| Product | `/products/{slug}` | Region and amount selection, delivery fields exactly as the cart validates, reviews, related, sticky mobile buy bar |
| Search | `/search?q=` | Instant suggestions (header and page), popular, recent searches in localStorage only; noindex |
| Bag | `/cart` | Quantity, remove, coupon via JSON; server fragment refresh |
| Checkout | `/checkout` | Sign-in required by default; country, display currency, payment options; "What you will be charged" box; Stripe Payment Element, PayPal and Paymob redirects, wallet |
| Payment return | `/checkout/orders/{id}/return` | verify-payment or PayPal capture, then status polling; never marks paid |
| Order result / guest access | `/orders/{id}` | Owner or session; signed email link leads to the email one-time code form; reveal with re-auth (customer) or link + OTP (guest); code hidden after 60 s or on tab switch |
| Content / legal | `/pages/{slug}` | Published CMS page, else built-in summaries for about, contact, terms, privacy, refund-policy |
| Auth | `/login`, `/register`, `/two-factor-challenge`, `/forgot-password`, `/reset-password/{token}`, `/email/verify` | Replace the JSON landing pages for browsers |
| Account | `/account` | Placeholder until M13 |
| Errors | 404 (storefront chrome, language from the path), 500/503/419/429/403 (minimal, no database) | |

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Every page responsive at 375, 768, 1280 | Met | Headless Chrome screenshots of home, platforms, category, product (EN and AR), search, login, bag and checkout at the three widths during the build; mobile-first Tailwind with logical utilities |
| Feature tests per page, both locales where it matters | Met | 49 tests, 368 assertions (see below) |
| No console errors | Met for the pages checked | Headless Chrome console on home, `/ar`, `/gaming`, `/login`, `/cart`, `/search` at three widths: no errors or CSP violations. The Stripe Payment Element was not loaded (no publishable key) |
| Matches Figma | Met with the deviations below | |
| Checkout shows display and payment amounts | Met | Charge box and Pay button use `display_amount`, `payment_amount` and `payment_currency` from payment-options; order page shows display total, payment amount and currency |
| Guest checkout end to end with mocks | Replaced by owner decision 4 | Customer checkout end to end with mocked Stripe (product page, bag, checkout, payment-options, place-order, return page, verify-payment, paid order page) plus: guest redirected to sign-in and back; place-order refuses a browser guest (401 `guest_checkout_disabled`) with the flag off and accepts one with it on |
| Prices only from server money values | Met | Templates print `formatted`; JS inserts response strings only |
| No inline scripts or styles beyond CSP-nonced data | Met | Strict CSP header (nonce, no `unsafe-inline`); test asserts every script has `src` or the nonce, every style block the nonce, no `style=` or inline handlers |

### Tests and results

- New: `StorefrontPagesTest` (28), `CheckoutPagesTest` (13), `AuthPagesTest` (8): home EN/AR with prices, hero controls, hreflang and CSP; display currency cookie; email-verification banner; platforms index and detail EN/AR, 404; category filters, sort, empty filters, invalid filter redirect, noindex, reserved slugs not shadowed; product variants, delivery fields, sanitized description, related, AR, draft 404; search results, popular counting, empty states; built-in and CMS pages; 404 EN/AR, 500 without chrome; language switch URLs; `/` and `/ar` serve the home pages with no country redirect (`/eg` is 404); every banner image in the Vite manifest; bag empty and filled, fragment; checkout access by flag in both directions, Arabic refusal message; customer E2E with mocked Stripe; retry offer; order 404 for strangers; return page without disclosure; guest email link, code form, order; JSON link answer kept; auth pages EN/AR, redirects, 2FA pending gate, reset page vs JSON echo, reset page language.
- Full suite: `php artisan test --compact` (sequential, MySQL) **2265 passed, 0 failed, 15964 assertions, 154 s** (2216 after M-A7; +49 M12 tests). `vendor/bin/pint`: passed. `npm run build`: passed.
- Existing tests changed (adaptation, not weakening): `Orders/CheckoutTest` "treats two guest carts…" and "places a guest order…" and the worker environment of `Orders/CheckoutConcurrencyTest` switch `storefront.guest_checkout` on, because they test guest placement and web guest checkout is now off by default.
- Tests render without a Vite build (`storefrontWithoutBuild()`); `public/build` is not committed.

### Deviations from Figma

- No wishlist heart in the header (wishlist is planned, not built). Newsletter band shows "sign-up opens soon" instead of a form (`/newsletter` is not built).
- Checkout has no pre-payment email one-time code (no backend for it; signed-in customers only by owner decision). The guest one-time code is on the guest order access page (M9).
- Listing filters: platform is single choice (the listing takes one platform); no region filter (no storefront region list) and no "in stock only" toggle (no listing filter). Sort control sits above the grid.
- Product cards link to the product page; the bag button on a card is visual (a card has no variant to add).
- Platform and product tiles use original vector dials drawn in SVG from the design (not the Figma raster exports); Logo Slot hidden unless an admin uploaded an image (component note).
- Legal pages are short built-in summaries until the owner publishes the full texts in the CMS.
- Footer platform column links to all platforms and search instead of a fixed brand list.

### Outstanding requirements

- M13: account dashboard, orders list and detail pages, digital codes, wallet, reviews, tickets, notifications, security (`/account` is a placeholder; links in emails to `/account/orders/{id}` need M13 pages, the result page `/orders/{id}` already works for owners); a full code-reveal UX (basic reveal with password re-auth and auto-hide is on the order page).
- M18: JSON-LD (Product, BreadcrumbList, Organization), sitemaps, redirects table middleware, GA4/GTM, Lighthouse run. Canonical, robots and hreflang tags are in place.
- OD-1 (backend, after M12): payment currency per gateway and GeoIP display currency; the page already renders whatever payment-options and place-order return.
- Stripe Payment Element is untested against a real key (sandbox verification pending); the CSP allows js.stripe.com, api.stripe.com and hooks.stripe.com.
- The stateless `/api/v1/mobile` place-order still accepts guests; the app's no-guest rule is M-A13.
- Product structured content (images, descriptions, Arabic translations) needs catalog data entry (M15).

### Design changes made

- `API_DOCUMENTATION.md`: web pages note, place-order 401 `guest_checkout_disabled`, browser reset page, browser guest link redirect.

### Next milestone

M13: Customer account.

---

## M13: Customer account

Date completed: 2026-11-11
Completed by: Senior Laravel Frontend Developer, alone and in sequence. Not committed.

### Summary of completed work

Every spec section 9 module as server-rendered Blade pages with Tailwind 4 and plain JavaScript modules through Vite (no Livewire), in English (LTR) and Arabic (RTL, `/ar/...`), from the approved Figma file `JkUUSR3WGBYC9ewy2qRFQO` (Account page 2:5: dashboard, orders, order details, code reveal flow, wallet, wishlist, tickets, security at 1440 and 375; RTL page 2:7: Account / Digital Codes at 390). Pages read through the existing repositories, services and API resources; every change goes through the JSON account routes with the session and CSRF, and envelope errors show beside the fields. Money is only printed from server `formatted` strings (wallet amounts are formatted by `CurrencyConverter::present` in the controller). The `/account` placeholder is replaced, the email links `/account/orders/{id}` and `/account/tickets/{id}` resolve to real pages, and the basic reveal of `/orders/{id}` now uses the same reveal component as the account (owner decision 7).

### Pages and routes

All under `routes/storefront_pages.php` (registered per language), middleware `auth` and `account.active`, `noindex,nofollow`, strict nonce CSP.

| Page | EN / AR URL | Route name | Notes |
|---|---|---|---|
| Dashboard | `/account`, `/ar/account` | `storefront.account` | Orders (total, in progress), wallet credit, unrevealed codes; "a code is ready" notice; recent orders; picked for you (featured, else top listed) |
| Orders | `/account/orders` | `storefront.account.orders` | Status and period filters (invalid query redirects to the unfiltered page), table from md, cards below, pagination |
| Order detail | `/account/orders/{public_id}` | `storefront.account.orders.show` | `no-store`; units with masked codes and the reveal component; progress timeline; display price, payment amount and currency, payment status, method, refunds; retry payment when unpaid and open; refund request form when paid with no open request; "Need help" opens a ticket about the order. OrderPolicy: another customer's or a guest order is 404 |
| Digital codes | `/account/codes` | `storefront.account.codes` | `no-store`; every delivered code of paid orders, masked, state filter (not revealed, revealed, refunded) and search (product or order number); per-unit reveal |
| Wallet | `/account/wallet` | `storefront.account.wallet` | Balance, "credits and refunds only" (BD3), statement |
| Wishlist | `/account/wishlist` | `storefront.account.wishlist` | Saved products as listing cards in the display currency, remove button; heart toggle added to the product page buy box and a heart link in the header |
| Reviews | `/account/reviews` | `storefront.account.reviews` | Items waiting for a review (write), own reviews with status (edit, back to moderation) |
| Tickets | `/account/tickets`, `/account/tickets/new`, `/account/tickets/{public_id}` | `storefront.account.tickets(.create, .show)` | List and thread side by side from lg; reply; new ticket optionally about an own order (`?order=`); internal notes never shown |
| Notifications | `/account/notifications`, `/account/notifications/preferences` | `storefront.account.notifications(.preferences)` | Inbox with links to account pages, mark read, mark all read; preference matrix (mandatory cells locked) |
| Profile | `/account/profile` | `storefront.account.profile` | Name, country, language, display currency (automatic = country currency by `DisplayCurrencyResolver`); email read only |
| Security | `/account/security` | `storefront.account.security` | `no-store`; change password; two-step verification on (QR code, setup key, confirm, recovery codes shown once) and off; code reveal history (granted and denied); signed-in devices with sign-out; sign out everywhere else |
| 2FA QR | `/account/security/two-factor/qr` | `storefront.account.two-factor-qr` | SVG of the pending enrolment, `no-store`, 404 when none pending |

Email verification banner with "send the link again" on every account page while the email is unverified.

### Code reveal (owner decision 7)

`<x-store.code-reveal>` and `<x-store.code-unit>` (used by the account order page, the digital codes page and `/orders/{id}`) and `resources/js/modules/reveal.js`: masked by default (the stored `masked_code` only; the code is never in a page before a reveal); step 1 confirm intent on a first reveal; step 2 password when the server answers 423 `recent_auth_required` (`POST /api/v1/account/confirm-password`, then the reveal is retried; guests use the link and email code, unchanged); step 3 the code, PIN, redemption link (https only) and instructions written as text into a native `<dialog>`, with copy and hide; step 4 the 429 limit with a support link. The code is wiped from the DOM when the dialog closes, after `fulfillment.web_reveal_hide_seconds` (60) with a visible countdown, and at once on `visibilitychange` to hidden or `pagehide`. A copied code is cleared from the clipboard after 60 seconds (`navigator.clipboard.writeText('')`, retried on the next focus where the browser refused). Nothing is written to localStorage, sessionStorage or data attributes. Reveal responses keep `Cache-Control: no-store, private` and `Pragma: no-cache`; pages that hold reveal buttons are `no-store, private`.

### Files created

- Controllers `app/Http/Controllers/Web/Account/`: `DashboardController`, `OrderController`, `DigitalCodeController`, `WalletController`, `WishlistController`, `ReviewController`, `TicketController`, `NotificationController`, `ProfileController`, `SecurityController`, `Concerns/PresentsAccountData`. `app/Http/Controllers/Account/WishlistController` (JSON).
- Requests: `app/Http/Requests/Web/Account/{AccountPageRequest,OrderListPageRequest,CodeListPageRequest}`, `app/Http/Requests/Account/{UpdateReviewRequest,SignOutOtherDevicesRequest,StoreWishlistItemRequest}`.
- Domain: `app/Domains/Catalog/Services/WishlistService`, `app/Domains/Shared/Services/QrCodeSvg` (in-house QR encoder, byte mode, level M, versions 1 to 20; checked module for module against bacon/bacon-qr-code 3.1 on 61 payloads, then that package was removed again, since CLAUDE.md allows no dependency change without approval). `app/View/Composers/AccountLayoutComposer`.
- Views: `layouts/account`, `components/account/{nav,time,status,badge,page-head,ticket-status,review-form}`, `components/store/{code-reveal,code-unit}`, `account/{dashboard,codes,wallet,wishlist,reviews,profile,security}`, `account/orders/{index,show}`, `account/tickets/{index,create}`, `account/notifications/{index,preferences}`, `account/partials/{orders-table,pagination}`.
- JavaScript: `modules/{account-form,account-action,account-nav,two-factor,wishlist}.js`; `modules/reveal.js` rewritten.
- Translations: `lang/en/account.php`, `lang/ar/account.php` (same keys, tested).
- Tests: `tests/Feature/Storefront/{AccountPagesTest,AccountAuthorizationTest,AccountRevealTest}.php`, `tests/Feature/Account/AccountM13RoutesTest.php`, `tests/Unit/Shared/QrCodeSvgTest.php`, `tests/Support/AccountPageFixtures.php`.

### Files changed

- `routes/storefront_pages.php` (account group; placeholder route removed), `routes/account.php` (PATCH `/reviews/{id}`, POST `/devices/sign-out-others`, `/wishlist`).
- `ReviewService::revise`, `ReviewRepository::findMine`, `AccountSessionService::signOutOthers`, `TwoFactorService::pendingOtpauthUri`, `CustomerOrderRepository::{findByPublicId,findForCustomer,dashboardCounts,recentForCustomer,latestSealedUnit,paginateCodeUnits,recentRevealAttempts}`, `ProductRepository::storefrontPricedByIds`, `StorefrontCatalogService::cardsForIds`, `Account/ReviewController::update`, `Account/DeviceController::signOutOthers`.
- `config/fulfillment.php` (`web_reveal_hide_seconds` = 60), `StorefrontServiceProvider` (account composer), `layouts/storefront` (wishlist route, account JS strings), `components/store/header` (wishlist heart), `components/store/icon` (account icons), `storefront/partials/buy-box` and `Web/ProductController` (wishlist toggle), `storefront/orders/show` (shared reveal component), `lib/forms.js` (`formValues` sends only the checked radio).
- Removed: `Web/AccountController`, `storefront/account.blade.php` and the `storefront.account.*` placeholder strings.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| All spec section 9 modules | Met | Dashboard, profile, orders, order details, digital code history, wallet and transactions, wishlist, support tickets, reviews, notifications, security; ticket attachments not built (no API) |
| Object-level authorization tests | Met | `AccountAuthorizationTest` (14): another customer's order (EN and AR), guest order, ticket (EN and AR) are 404; no list page shows another customer's orders, codes, tickets, reviews, wishlist, wallet, notifications or devices; reveal (even after the intruder's own password confirmation), ticket reply, review edit, device sign-out, notification read and payment retry are 404; refund request and ticket about another customer's order are 422 "This order was not found." with nothing created; wishlist actions only touch the caller's rows |
| Code reveal UX with re-auth | Met | `AccountRevealTest` (10): component and 60 s hide and clipboard wiring on all three pages in EN and AR, configurable; 423, wrong password, confirm, reveal with `no-store` and `Pragma`; revealed state afterwards; no code in pages, logs or audit; reveal module has no browser storage and is wired to visibility, timers, clipboard clearing, 423 and 429. Headless Chrome: intent, 423 password step, wrong password error beside the field, code and PIN shown with countdown, wiped on tab hide and on Hide, re-reveal without a password inside the window, nothing in localStorage or sessionStorage |
| Feature tests per page in both locales | Met | `AccountPagesTest` (48) |
| Responsive at 375, 768, 1280; no console errors or CSP violations | Met | Headless Chrome (CDP) over 15 pages x 3 widths x EN/AR (87 loads): no console errors, exceptions or CSP violations, no horizontal overflow; screenshots compared with Figma. The only console lines in the interactive run were the browser's network entries for the intended 423 and 422 responses |

### Tests and results

- New: 89 tests (`AccountPagesTest` 48, `AccountAuthorizationTest` 14, `AccountRevealTest` 10, `AccountM13RoutesTest` 11, `QrCodeSvgTest` 6).
- Full suite: `php artisan test --compact` (sequential, MySQL) **2410 passed, 0 failed, 17080 assertions, about 163 s** (2321 after OD-1). `vendor/bin/pint --dirty`: passed. `npm run build`: passed.
- Existing test changed (adaptation, not weakening): `Storefront/AuthPagesTest` "sends a signed-in customer ... to the account page" now expects the dashboard greeting "Welcome back, Sara Ali" (Figma) instead of the placeholder's "Hello, Sara Ali"; it still checks the name and the sign-out control.
- Headless checks ran against a preview copy of the data in the testing database (rebuilt by `migrate:fresh` at the start of every test run); the development database was not touched.

### Deviations from Figma

- Orders list: no free-text order search and no column sorting (the order history filter has status and dates only); filters are a status select and a period select with an Apply button; pagination is previous / next with "Showing x to y of z" instead of numbered pages and a per-page select.
- Order detail: the revealed code shows in the reveal dialog (Figma step 3) rather than inline in the unit row; the row then reads "Revealed <time>". The "With supplier" pill is the unit status text. "Paid with" shows the gateway, not the card's last four (not stored for customers).
- Reveal flow: no "one-time email code instead" link in the password step (no such re-auth API for customers); no per-hour remaining count or "try again in N minutes" (the 429 carries no count); the intent step shows a generic region reminder (the order detail has no region field).
- Dashboard: "Codes unrevealed" card links to the sealed codes filter; product tiles use the M12 vector dials.
- Security: the two-step toggle is a password-confirmed form (on: QR, setup key, code, recovery codes; off: password) rather than a bare switch; device rows show the masked IP and user agent (no city: no GeoIP city data).
- Navigation adds Reviews, Notifications and Profile items not drawn in the Figma sidebar, plus Sign out.
- Digital codes (RTL): instructions ("how to use") come with the reveal response, so they appear in the reveal dialog rather than as a collapsible card section; the platform initials badge is the platform tile.

### Outstanding requirements

- Ticket attachments: the `support_ticket_attachments` table exists but no upload API; the ticket page says attachments are not supported yet.
- A JSON `GET /account/digital-codes` for the app is still not built (the web page reads the repository; the app uses the order detail).
- Email links are unprefixed (`/account/orders/{id}`), so an Arabic customer lands on the English page and can switch language; a redirect by the saved profile locale is not built.
- The profile display currency is honoured by `DisplayCurrencyResolver` only when no `currency` cookie or header is set; the page says so.
- Pre-existing M12 glitch noticed: the custom `overline` utility in `app.css` shares its name with Tailwind's core `overline` text decoration, so overline labels (for example on product cards) also draw a line over the text. M13 views avoid the class; fixing it for M12 pages is left for the owner's review.
- The reveal "one-time email code" alternative for customers and a 429 retry time need backend support.

### Design changes made

- `API_DOCUMENTATION.md`: section 4 table and new section 4d (review edit, sign out other devices, wishlist, the web QR route).

### Next milestone

M14: Admin core.

---

## M14: Admin core

Date completed: 2026-11-11
Completed by: Senior Laravel full-stack developer, alone and in sequence. Not committed.

### Summary of completed work

The admin panel core as server-rendered Blade with Tailwind 4 and plain JavaScript modules through Vite (no Livewire), in English (LTR) and Arabic (RTL), from the approved Figma file `JkUUSR3WGBYC9ewy2qRFQO` (Admin page 2:6, Phase 1 and Phase 2 rows; RTL page 2:7 "RTL Admin Order"), in the **Admin Light** theme mode (the semantic tokens rebound under `[data-theme=admin-light]`), with the logo on its dark chip and the mark in the collapsed rail. Pages only read; every change goes through `/admin/api` JSON routes with CSRF, a named permission and an audit entry, and envelope errors show beside the fields. Tables are server-side through Yajra DataTables 13.3.1 with the `datatables.net` 3.1.5 front end (no jQuery). Money is only printed from server-formatted strings (`CurrencyConverter::present`).

### Pages and routes

`routes/admin_pages.php` (group `admin.web`: session, CSRF, nonce CSP, admin language; names `admin.*`). Guests go to `/admin/login`; staff without two-step verification are sent to `/admin/two-factor/setup`; a session that never passed the challenge is signed out (`two_factor:redirect`); customers get 403.

| Page | URL | Permission | Notes |
|---|---|---|---|
| Sign in, challenge, 2FA setup | `/admin/login`, `/admin/two-factor`, `/admin/two-factor/setup` (+ `/qr`) | none / staff | Post to the existing `/login`, `/two-factor-challenge` and account two-factor routes; recovery codes shown once |
| Language | `POST /admin/locale` | none | Session choice, else the staff member's locale; returns to the admin page it came from |
| Home | `/admin` | any section | Redirects to the first permitted screen; 403 when none |
| Dashboard | `/admin/dashboard?days=7\|30\|90` | `dashboard.view` | KPIs with change against the previous window and sparklines (net revenue, orders placed, new customers, net profit with `reports.profit`, open orders with "stuck over 1 hour", failed payments), revenue and profit bar charts (server-drawn SVG, values table), supplier balances (`suppliers.view`), gateway success (`payments.view`), top products, recent orders (`orders.view`). Revenue, profit and top products reuse the M16 analytics readers (`order_item_financials`) |
| Orders | `/admin/orders` (`?q=` from the top-bar search) | `orders.view` | Table, filters, saved filters, export, bulk "check supplier status" |
| Order detail | `/admin/orders/{public_id}` | `orders.view` | `no-store`. Reconciliation panel (check now, retry locked while ambiguous), manual review approve and reject with reason, purchased units (masked code only), fulfilment attempts with "mark verified not accepted" (reason and evidence), payments, refunds with a refund form (units, reason, destination, note), append-only timeline, payment snapshot (display, payment amount and currency, base, rate), customer (email masked without `customers.view_pii`), risk assessment. Each action shown only to its permission and confirmed in a dialog |
| Suppliers | `/admin/suppliers` | `suppliers.view` | Summary (pending verification, lowest balance, below alert level), table, "labelled until verified" note |
| Supplier detail | `/admin/suppliers/{id}` | `suppliers.view` | Capability matrix of every adapter (current supplier highlighted), health and circuit breaker, balance, alert level, per-request cap, recent health checks, test connection (`suppliers.manage`), sync catalog and prices (`suppliers.sync`), settings form (status, priority, fallback, fallback supplier, low balance alert; `suppliers.manage`), credentials as configured or not per mode with a replace form (`suppliers.credentials`), sync history, mappings table, API logs table (`suppliers.logs`) |
| Payments | `/admin/payments` (gateways), `/gateways/{id}`, `/transactions`, `/webhooks`, `/refunds`, `/disputes`, `/reconciliation` | `payments.view`; webhooks `payments.webhook_logs`; refunds `refunds.view` | Tabs shown per permission. Gateway cards: status, mode, charge currency (OD-1), credentials state, currencies, active rules, "awaiting sandbox verification". Gateway page: status and mode form (`payments.manage`), write-only credentials (`payments.credentials`), currencies, eligibility rules with enable/disable (`payments.manage`). "Reconcile now" (`payments.manage`). One server-side table per tab |

### DataTables base

- `app/DataTables/AdminDataTable` and one class per table (`Orders/OrdersDataTable`, `Suppliers/{SuppliersDataTable,SupplierApiLogsDataTable,SupplierMappingsDataTable}`, `Payments/{PaymentsDataTable,WebhooksDataTable,RefundsDataTable,DisputesDataTable,ReconciliationItemsDataTable}`), registered in `AdminTables`; `routes/admin.php` registers data, layout, saved filter and export routes per table with the table's permission.
- The client never names a database column: columns, sort expressions (at most three), prefix search columns and filter options are declared in the class; the request is replaced by that allow-list before Yajra reads it, page length is one of 10/25/50/100, and only declared columns plus `DT_RowId` leave the server (`only`). HTML cells come from `Cell` (escaped parts); everything else is escaped by Yajra. Filters and search run in Yajra's filter callback (`recordsTotal` is the scope, `recordsFiltered` the result); context filters (the supplier of a nested table) narrow the scope and are required.
- `resources/js/admin/table.js`: DataTables 3 with an `ajax` function over the existing `http.js` helper (CSRF, envelope errors), Tailwind theme in `app.css`, debounced search, filters, numbered pager, page length, column visibility, row selection and bulk bar, saved filters (apply, save with "open with these filters", delete), CSV/XLSX export with status polling and automatic download, skeleton while loading, empty state with "clear filters", RTL pager arrows.
- Layout and saved filters per staff member: `admin_table_states` (new) and `saved_filters` (existing), through `AdminTablePreferences` (audited).
- Exports: `export_jobs` row, queued `ExportAdminTable` job, `AdminTableExporter` with in-house `CsvTableWriter` (BOM) and `XlsxTableWriter` (ZipArchive, inline strings), formula cells neutralised, 50 000 row cap, private disk, 24-hour expiry, owner-only download re-checked against the table permission, request and download audited.

### Form and error modules

`admin/form.js` (JSON forms: method, confirm dialog, nullable and list fields, envelope errors beside fields with `[data-error-for]` dot keys, form-level alert), `admin/action.js` (one-click actions with the confirmation dialog, optional written reason sent to the audit log), `admin/dialog.js` (native `<dialog>` in the layout, danger tone, reason field with minimum length), `admin/shell.js` (collapsible rail remembered in this browser only, mobile drawer, sign out). `lib/forms.js` `formValues` now reads `name[]` checkboxes as a list (used by the refund unit choice).

### Files created

- Routes: `routes/admin_pages.php`; additions in `routes/admin.php` (tables, exports, saved filters, state, bulk actions, order reject, gateway update and credentials, eligibility rule toggle, payments reconcile, reconciliation resolve).
- PHP: `app/DataTables/*` (base, `Column`, `Filter`, `Cell`, `AdminTables`, `Payments/PaymentReference`, nine tables); `app/Http/Controllers/Admin/Pages/{AuthPageController,HomeController,LocaleController,DashboardController,OrderPageController,SupplierPageController,PaymentPageController}`, `Admin/Tables/{TableController,BulkActionController}`, `Admin/Payments/PaymentGatewayController`; requests `Admin/Tables/{AdminTableRequest,TableViewRules,SaveTableStateRequest,SaveFilterRequest,ExportTableRequest,BulkIdsRequest}`, `Admin/Pages/{SwitchLocaleRequest,DashboardRequest}`, `Admin/Fulfillment/RejectOrderRequest`, `Admin/Payments/{UpdateGatewayRequest,SetGatewayCredentialsRequest,ToggleRuleRequest,ResolveReconciliationRequest}`; `app/Http/Support/{AdminNavigation,AdminTablePage}`; `app/Http/Middleware/Admin/SetAdminLocale`; `app/View/Composers/AdminLayoutComposer`; domain `Analytics/{Repositories/AdminDashboardRepository,Services/AdminDashboardService}`, `Orders/Services/AdminOrderReader`, `Suppliers/Services/SupplierOverview`, `Payments/Services/PaymentGatewayAdminService`, `Settings/{Models/AdminTableState,Services/AdminTablePreferences,Services/AdminTableExporter,Services/Export/*,Jobs/ExportAdminTable}`; `config/admin.php` (navigation, payment tabs, credential fields per adapter, table limits, badge tones); factory `Settings/AdminTableStateFactory`.
- Views: `layouts/{admin,admin-auth}`, `components/admin/{badge,status,card,crumb,field,form-alerts,kpi,table}`, `admin/{dashboard,auth/*,orders/*,suppliers/*,payments/*,partials/bar-chart}`.
- Front end: `resources/js/admin.js` (entry), `resources/js/admin/{table,form,action,dialog,shell}.js`; Admin Light tokens and admin utilities in `resources/css/app.css`; 13 icons added to `components/store/icon`.
- Translations: `lang/en/admin.php`, `lang/ar/admin.php` (same keys, tested); admin field names in `lang/ar/validation.php`.
- Tests: `tests/Feature/Admin/{AdminAccessTest,AdminTablesTest,AdminTableExportAndPreferencesTest,AdminOrderPagesTest,AdminSupplierAndPaymentPagesTest}.php`, `tests/Support/AdminFixtures.php`.

### Files changed

- `bootstrap/app.php` (admin page group, `admin.locale` alias, admin guest redirect, `locale` on the admin API group), `EnsureTwoFactor` (`redirect` mode for pages; JSON unchanged), `StorefrontServiceProvider` (admin composer), `vite.config.js` (admin entry), `package.json` / `package-lock.json` (`datatables.net` 3.1.5 exact, npm).
- `FulfilmentAdminService::reject` and `::reconcileMany`, `OrderFulfilmentController::reject`, `RefundService` (staff refunds audited `refunds.created`), `MorphMap` (`admin_table_state`).
- Tests changed (adaptation, not weakening): `Schema/SchemaAcceptanceTest` lists the new `admin_table_states` table.

### Migrations

`2026_11_16_010001_create_admin_table_states_table` (reversible). Applied by the test runs on `gaming-store_testing`; **not applied to the app database** — run `php artisan migrate`.

### Packages

`datatables.net` **3.1.5** (exact, `dependencies`, npm only; one package added to `package-lock.json`). DataTables 3 has no jQuery dependency, so jQuery was **not** added. No styling or extension packages; nothing else added. `backend/.yarn/`, `.yarnrc.yml` and `yarn.lock` untouched.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Validation errors beside fields using the envelope | Met | Dot keys from 422 envelopes (`filters.status`, `credentials.secret_key`, `reason`, `evidence`, `priority`) are rendered in `[data-error-for]`; tests assert the envelope keys for tables, saved filters, exports, supplier settings, gateway credentials and activation, rule toggle, order approve/reject, reconciliation; headless Chrome shows the Arabic errors beside "reason" and "evidence" |
| Each table server-side | Met | Nine tables, all Yajra server-side; tests for paging, total and filtered counts, prefix search (literal `%` and `_`), filters, allow-listed sort, ignored client columns, declared columns only, escaping, PII masking, invalid filter and page length, and constant query counts (no N+1) for every table |
| Order timeline, retry and reconcile with permissions | Met | Detail page tests; reconcile single and bulk audited; retry shown only for verified-failed units with `orders.review`, "Retry (locked)" while an attempt is ambiguous; reject (unpaid cancel, paid refund needing `refunds.create`, refused with an open supplier attempt) |
| Supplier capability matrix rendered | Met | Every capability across adapters with levels, current supplier marked; credentials never rendered (canary) |
| Permission-based navigation and route denial | Met | Navigation per permission set and per seeded role (Operations, Finance, Support, Catalog = 403 home); M15 entries hidden; payment tabs per permission; every page and every table, export, state and saved-filter route 403 without its permission and 200 with it; every action route 403 without its permission |
| Export permission and audit | Met | 403 without the table permission; owner-only download (404 for others, 403 after losing the permission); request and download audited; expiry 410; failure leaves no file |
| Responsive 1280/768, usable 375; no console errors or CSP violations | Met | Headless Chrome (CDP) over 12 pages × 3 widths × EN/AR (72 loads) on preview data in the testing database: no console errors, exceptions or CSP violations, no horizontal page overflow, tables loaded rows in both languages. Interactive run: search, filter, empty state and clear, sort, paging, column hiding kept after reload, saved filter, selection bar, XLSX export download, confirmation dialog, envelope errors beside fields |

### Tests and results

- New: 123 tests in `tests/Feature/Admin` (660 assertions): access and navigation 53, tables 26, exports and preferences 10, orders 13, suppliers, payments and dashboard 21; plus one model factory case for `AdminTableState`.
- Full suite: `php artisan test --compact` (sequential, MySQL) **2534 passed, 0 failed, 17920 assertions, about 169 s** (2410 after M13). `vendor/bin/pint --dirty`: passed. `npm run build`: passed.

### Deviations from Figma

- Admin frames are drawn in the dark theme; built in the Admin Light theme mode as briefed (Figma's light dashboard frame 18:1295 is the reference).
- No notification bell in the top bar (no staff notification feed exists); the top bar adds a language switch. The top-bar search searches orders only.
- Dashboard: revenue and profit are bar charts per day (server-drawn SVG, no chart library), not a line with a "previous period" series; KPI "vs previous" uses the previous window of the same length; customers KPI counts new customers; "Export report" not built (export is per table).
- Tables: no searchable or multi select widget (native selects; no package allowed); filters wrap onto a second line at 1280 when dates are shown; the "Saved: …" control is a menu button.
- Order detail: "Escalate to manual" is "Mark as verified not accepted" (the existing reason + evidence action); no 04:12 countdown (the attempt shows next check and deadline); no "Print" button.
- Supplier detail: credentials show configured/not configured per mode with a replace form (no masked "••••3a2f" hint, by the write-only rule); "Add supplier" is not on the list (supplier creation stays in the JSON API; M15 settings).
- Payments: gateway cards link to a gateway page instead of a modal; eligibility rules can only be switched on or off here (rule editing is M15).

### Outstanding requirements (M15 and later)

- M15 screens: catalog, customers, pricing and currency, marketing, content, support, reviews, users and roles, settings (including eligibility rule editing, supplier creation, app settings and public payment identifiers), MCP tokens and action approval, automation credentials and AI review. Their navigation entries exist in `config/admin.php` with `route => null` and appear once a route is set.
- Staff notification feed (Figma bell), dashboard "export report", order notes (`orders/{id}/notes` not built), `supplier-mappings/suggestions` and `bulk`.
- Exports need a queue worker in production (`QUEUE_CONNECTION=database`): without one the UI keeps "waiting for the queue worker". Expired export files are not yet pruned by a scheduled task.
- The admin language for JSON messages follows `Accept-Language` after the staff member's saved locale (`SetLocaleFromRequest`); the page switch only changes the session.

### Design changes made

- `API_DOCUMENTATION.md` section 5e; `DATABASE_DESIGN.md` `admin_table_states`; `PROJECT_ARCHITECTURE.md` section 4 npm row.

### Next milestone

M15: Admin remaining (Step 8).

---

## M15: Admin remaining

Date completed: 2026-11-11
Completed by: Senior Laravel full-stack developer, alone and in sequence. Not committed.

### Summary of completed work

Every remaining spec Phase 9 module as server-rendered Blade with Tailwind 4 and plain JavaScript through Vite (no Livewire, no new packages), in English and Arabic (RTL), on the M14 base (DataTables, layout, form and action modules). Built with semantic theme tokens only, so the dark theme the owner chose (`config('admin.theme')`, committed during M15) and Admin Light both render; `data-theme` behaviour unchanged. Pages only read; every change is a JSON route with its own permission, a Form Request, a Service and an audit entry; envelope errors show beside the fields. Secrets are write-only everywhere (canary tests).

### Modules and pages

| Module | Pages | Permission |
|---|---|---|
| Catalog | products list (bulk publish, unpublish, delete) and editor (basics, delivery, categories and regions, descriptions, required delivery fields, variants, status, images from the media library, supplier mappings, Arabic text, SEO); platforms, categories, regions, attributes, delivery fields; supplier mappings with bulk mapping and bulk on/off | `catalog.view` (writes `catalog.manage`, publish `catalog.publish`), `mappings.manage` |
| Content | pages (legal pages included), blog posts and categories, FAQs and groups, menus with items, redirects, media library with upload; Arabic text and SEO cards | `content.*`, `seo.redirects`, `seo.manage` |
| Pricing and currency | pricing rules, country prices, currencies (active/inactive, markup, rounding; base and gateway charge currencies shown read-only per OD-1), exchange rates (fetch now, held rates approve/reject, manual override), country display currency (OD-1) | `pricing.view`, `pricing.manage`, `currency.manage` |
| Marketing | coupons, promotions, affiliates (write-only payout details, commissions with approval), banners, homepage sections with items | `marketing.*`, `content.homepage` |
| Customers | list (email masked and not searchable without `customers.view_pii`), detail (KPIs, recent orders, wallet ledger, wallet adjustment, risk, notes, tickets, devices and biometric keys with revoke, audit trail, data export and erasure), block and unblock | `customers.view`, `customers.manage`, `customers.wallet_adjust`, `audit.view` |
| Support | ticket queue, thread with replies and internal notes (staff only, never in customer views or exports), assignment, status and priority; refund requests with approve through the refund flow or reject; review moderation | `support.*`, `refunds.create`, `catalog.manage` |
| Users and roles | staff list, invite (reset link, 2FA enforced at first page), roles, explicit (E) grants by Super Admin only, suspend, 2FA reset; role permission matrix | `users.manage`, `roles.manage` |
| Settings | general, orders, checkout (guest checkout read-only from config), pricing defaults, currency, security and risk, suppliers (with supplier creation), notifications, SMTP, analytics, mobile app config (`mobile:app-config` keys incl. `web_checkout_redirect` and force-update versions), automation drivers; write-only secrets; payment eligibility rule editing and the public payment key on the gateway page | `settings.view`, `settings.manage`, `settings.manage_critical`, `payments.manage`, `payments.credentials`, `suppliers.manage` |
| AI and automation | AI draft review, MCP action approval queue, MCP tokens (issue shown once, revoke), n8n credentials (token and HMAC secret shown once, revoke) and workflow switches | `ai_suggestions.review`, `mcp_actions.approve`, `service_accounts.manage` |

Routes and bodies: `API_DOCUMENTATION.md` section 5f.

### Shared building blocks

- **Section tabs**: `config('admin.sections')` and `AdminNavigation::tabs()` (payments tabs moved there; `paymentTabs()` kept); `admin/partials/tabs`.
- **Declarative screens** (`app/Http/Support/AdminScreens`): `Field` (text, textarea, html, number, decimal as string, select, multi, csv, toggle, datetime, json, password), `ResourceScreen`, `ResourceScreens` (21 screens in `Screens/`), `Options`, `ProductForm`; one `ResourcePageController` and `admin/resource/{index,form}`; `<x-admin.input>`. The list, create and edit routes are registered in a loop with each screen's permissions; the edit page is read-only without the manage permission.
- **Translations and SEO cards** on every translatable record (`admin/partials/{translations,seo}`).
- **JS**: `admin/form.js` gained `data-json`, `data-csv`, `data-rows` (list of objects), `__field__` URL placeholders, multipart upload, redirect and one-time reveal of a returned token or secret; `admin/action.js` is delegated (row actions inside tables, table redraw after success, `data-redirect`); `admin/table.js` redraws on `admin:table-refresh` and sends a bulk action's own body and note; `lib/forms.js` `formValues` builds nested lists and reads controls owned through `form="id"` (permission matrix).
- **DataTables**: 45 new tables in `app/DataTables/{Catalog,Content,Customers,Marketing,Pricing,Support,Users,Automation}`; `Cell::action()`/`actions()` for escaped row buttons; `AdminDataTable::enumOptions()` and `booleanFilter()`.

### Files created

- Controllers: `Admin/Pages/{ResourcePageController,CatalogPageController,PricingPageController,CustomerPageController,SupportPageController,UsersPageController,SettingsPageController,AutomationPageController}`, `Admin/Content/TranslationController`, `Admin/Seo/SeoMetadataController`, `Admin/Currency/CountryController`, `Admin/Customers/CustomerController`, `Admin/Users/{StaffController,RoleController}`, `Admin/Settings/SettingsController`, `Admin/Automation/{AiSuggestionController,AutomationWorkflowController}`.
- Requests: `Admin/Content/SaveTranslationsRequest`, `Admin/Seo/SaveSeoMetadataRequest`, `Admin/Currency/UpdateCountryRequest`, `Admin/Suppliers/{BulkMappingRequest,BulkMappingStatusRequest}`, `Admin/Customers/{CustomerReasonRequest,SetRiskRequest,AddCustomerNoteRequest,AnonymizeCustomerRequest}`, `Admin/Users/{InviteStaffRequest,SyncStaffRolesRequest,SyncPermissionsRequest,SetStaffStatusRequest,CreateRoleRequest}`, `Admin/Settings/{SaveSettingsRequest,SetSecretRequest}`, `Admin/Payments/{SaveEligibilityRuleRequest,SetPublicKeyRequest}`.
- Domain: `Catalog/Services/{ContentTranslationAdminService,ProductAdminReader}`, `Seo/Services/SeoMetadataService`, `Currency/Services/CountryCurrencyService`, `Customers/Repositories/CustomerAdminRepository`, `Customers/Services/{CustomerAdminService,CustomerPrivacyService}`, `Identity/Services/StaffAdminService`, `Settings/Services/AdminSettingsService`, `Automation/Services/AiSuggestionReviewService`.
- Console: `admin:prune-exports` (hourly at :20), `customers:export`, `customers:anonymize`.
- Config: `config/admin_settings.php`; `config/admin.php` (`sections`, `translations`, `seo`, tones).
- Views: `admin/{resource,catalog,customers,support,users,settings,automation,pricing,marketing,content}/*`, `admin/partials/{tabs,translations,seo}`, `admin/payments/_rule-form`, `components/admin/input`.
- Tests: `tests/Feature/Admin/{AdminResourceScreensTest,AdminCatalogPagesTest,AdminCustomerAndSupportPagesTest,AdminUsersAndRolesTest,AdminSettingsTest,AdminAutomationPagesTest,AdminM15RouteDenialTest}.php`.

### Files changed

`AdminNavigation`, `AdminLayoutComposer`, `AdminDataTable`, `AdminTables`, `Cell`, `routes/{admin,admin_pages}.php`, `TaskSchedule`, `MorphMap` (`role` alias for audit subjects), `BiometricKeyRevocationReason` (`revoked_by_staff`), `AccountSessionService` (`devicesForStaff`, `revokeByStaff`), `PaymentGatewayAdminService` (`saveRule`, `deleteRule`), `PaymentPublicCredentials` (`forGateway`), `SupplierMappingService` (`bulkCreate`, `setActive`), `SupportTicketService` (`assignableStaff`, `staffAuthors`), `SupportTicketRepository` (customer public id on the thread), `RefundRequestService` (`orderUnits`), `AutomationWorkflowRegistry` (audited switches), `AdminTableExporter` (`pruneExpired`), `PaymentPageController` and `admin/payments/gateway` (rule editing, public key), `components/admin/table` (`actions` prop), `lang/{en,ar}/admin.php`, the four JS modules above, `products/bulk` route now also behind `catalog.publish|catalog.manage`.

### Migrations

None. No new packages (composer or npm). `backend/.yarn/`, `.yarnrc.yml` and `yarn.lock` untouched.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Every spec Phase 9 module exists | Met, with the open items below | Pages and routes above; 9.1, 9.3, 9.4 and 9.6 were M14 |
| Permissions and 403 | Met | Every new page and JSON route is 403 without its permission (`AdminM15RouteDenialTest`, per-screen datasets); navigation per permission and per seeded role; read-only pages without the manage permission |
| DataTable endpoints | Met | Every new table returns rows server-side for its permission and 403 without it; `postTable()` now fails on a Yajra `error` key (a row that fails to render) |
| Validation errors in the envelope | Met | 422 dot keys asserted (fields, `values.*`, `countries.0`, `rows`, `filters.*`); headless Chrome showed them beside the fields (platform, JSON setting, mobile version) |
| Audit | Met | Each new mutation asserts its `audit_logs` action (translations with field names only, SEO, country currency, bulk mapping per mapping, customer actions with reason, staff and roles, settings per key, secrets without value, rules, public key, AI decisions, workflows) |
| Credentials write-only | Met | Settings secret canary (`SETTINGS_SECRET_CANARY`) absent from every settings page, the response, the stored row and the audit log; gateway secret canary absent with the public key shown; MCP token, n8n token and HMAC secret only in the issue response, never in the page or tables; affiliate payout details configured or not only |
| No self-escalation | Met | `AdminUsersAndRolesTest`: own account refused, granting unheld permissions refused, (E) only by Super Admin, Super Admin role fixed, own roles not editable |
| No console errors or CSP violations at 1280, 768, 375 in EN and AR | Met | Headless Chrome (pipe CDP, no packages) over 34 pages x 3 widths x EN/AR (204 loads) in the dark theme on preview data in the testing database: no exceptions, console errors or CSP violations; tables loaded rows; the only network errors were media files of factory rows without files (403 on `/storage/...`, data, not code); a horizontal overflow on Settings at 768 was fixed (re-run clean). Interactive run: envelope errors beside fields, JSON parse error, MCP token revealed once, role matrix save, review approval from the row |
| Full suite, Pint, build | Met | see Tests |

### Tests and results

- New tests in `tests/Feature/Admin` (seven files above, datasets included).
- Full suite: `php artisan test --compact` (sequential, MySQL): **2756 passed, 0 failed, 19668 assertions, about 196 s** (222 more tests) (2534 after M14). `vendor/bin/pint --dirty`: passed. `npm run build`: passed.
- Existing tests changed (adaptation, not weakening): `AdminAccessTest` "hides M15 sections" became "shows the M15 sections to holders of their permissions" (now asserts they appear and that others stay hidden); the seeded-role navigation dataset now expects Catalog and Customers for Operations Manager, Pricing for Finance Manager, Support and Customers for Support Agent, and a home for Catalog Manager (was 403 because nothing existed). `tests/Support/AdminFixtures.php` `postTable()` additionally asserts no Yajra `error` (stronger).

### Deviations from Figma

- Built with semantic tokens; screenshots checked in the dark theme now set by the owner (Admin Light also works through the same tokens).
- No searchable select widget (native selects, as M14); media ids are typed into number fields with a media library page that shows each id, rather than a picker.
- Product editor: the image card attaches a media id (upload happens in the media library); the supplier card lists mappings and adds one, without the "Primary supplier" single select of the frame.
- Customer detail: no "Reset 2FA" for customers (customers have no 2FA); notes and audit trail as in the frame; "Edit" profile is not offered (customers own their profile).
- Ticket thread: no canned replies and no attachments (no API); the reply kind is a radio pair instead of tabs; no SLA badge (no SLA data).
- Settings: groups as in the frame, plus Orders, Pricing defaults, Security, Mobile app and Automation; "Send test email" not built (mail settings are stored, not applied); Branding and Localization are not separate groups (logo is a brand asset; languages are fixed EN/AR by BD5).
- Users: the matrix is saved per role column (one Save per column) rather than instantly per checkbox.

### Decisions made

- Wallet adjustments stay single-step (no maker-checker): the spec and SECURITY.md do not require it; each adjustment needs a reason, an idempotency key and `customers.wallet_adjust`, and is audited.
- Guest checkout is shown read-only: it is the config flag `storefront.guest_checkout` (owner decision 4), not a DB setting. The gateway charge currency is shown, never edited (OD-1).
- Reviews keep `catalog.manage` (M11 open item unchanged).
- AI review approval records the decision only; nothing is published or drafted automatically.

### Outstanding requirements

- Settings stored but not yet read by code (labelled on screen): General, Notifications, SMTP (mail still reads `.env`), Analytics ids and secret (M18).
- Not built: cross-selling and upselling (`product_relations` has no admin API), product attribute values and country restrictions on the product editor (API exists), affiliate payouts, staff notification feed, ticket attachments and canned replies, AI draft-to-content creation, customer profile editing by staff, "send test email", dashboard export.
- Media picker for media id fields; searchable selects.
- Apply nothing new to the app database (no migrations in M15).

### Design changes made

- `API_DOCUMENTATION.md` section 5f; `SECURITY.md` section 4 (staff administration rules); `DATABASE_DESIGN.md` (`revoked_by_staff`, export file pruning).

### Next milestone

M18 (SEO and analytics): the SEO fields and analytics settings stored here are ready to be consumed.

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
| M-A8 | Figma mobile app design | Delivered (2026-11-10) |
| M-A9 | Owner approval gate | Approved (2026-11-10) |
| M-A10 | Auth and onboarding UI (with biometric unlock, owner decision 6) | Complete (2026-11-11, not yet committed; awaiting PM verification) |
| M-A11 | Home, catalog and search UI | Complete (2026-11-11, not yet committed; awaiting PM verification) |
| M-A12 | Product UI | Complete (2026-11-11, not yet committed; awaiting PM verification) |
| M-A13 | Cart, checkout and payments UI | Complete (2026-11-11, not yet committed; awaiting PM verification and LFA payments review) |
| M-A14 | Orders and code reveal UI | Complete (2026-11-11, not yet committed; awaiting PM verification and LFA security review) |
| M-A15 | Wallet UI | Complete (2026-11-11, not yet committed; awaiting PM verification) |
| M-A16 | Account and security UI | Complete (2026-11-11, not yet committed; awaiting PM verification and LFA security review) |
| M-A17 | Support, reviews, refund requests and notifications UI | Built (2026-11-11, not yet committed; tests written but not run by owner instruction; awaiting the final test run and PM verification) |
| M-A18 | Push notifications (G6 push, owner decision 9: FCM) | Built (2026-11-11, not yet committed by this milestone; tests written but not run by owner instruction; FCM awaiting credentials, mocked only; awaiting the final test run, the on-device check and PM verification) |
| M-A19 | Accessibility, performance and integration tests | Built (2026-11-11, not yet committed; tests written but not run by owner instruction; awaiting the final test run, the on-device run and PM verification) |
| M-A20 | Release configuration and store assets | Not started |

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

### M-A8: Figma mobile app design

Date delivered: 2026-11-10 (for review; M-A9 owner approval pending). Completed by: PM (product design), feasibility review by LFA and SFD outstanding.

- Figma file `JkUUSR3WGBYC9ewy2qRFQO`, new pages **Mobile App — EN** (`74:14`), **Mobile App — AR (RTL)** (`74:15`) and **Mobile App — Components** (`74:13`). 97 frames per locale (194 total), 93 at 390x844 and 4 Android 360x800 checks (home, product detail, checkout, code reveal). Full screen list with EN/AR node ids, states, deviations and open questions: `MOBILE_APP_ROADMAP.md`, M-A8.
- Sections per flow (EN / AR): 01 Launch & onboarding `79:306` / `79:589`; 02 Authentication `80:644` / `80:1105`; 02b Reset password `96:10783` / `96:10879`; 03 Home `82:1186` / `82:1898`; 04 Catalog & search `83:1884` / `83:2789`; 04b Browse tab `96:11237` / `96:11595`; 05 Product detail `85:2229` / `85:2780`; 06 Cart `86:2625` / `86:3216`; 07 Checkout & payment `87:2948` / `87:3462`; 08 Payment results `87:3762` / `87:4062`; 09 Orders `89:3448` / `89:4000`; 10 Code reveal `90:3744` / `90:4260`; 11 Guest order access `91:3890` / `91:4177`; 12 Wallet `92:3955` / `92:4129`; 13 Reviews `92:4346` / `92:4568`; 14 Support `93:4247` / `93:4516`; 15 Refund request `93:4645` / `93:4782`; 16 Notifications `94:4419` / `94:4633`; 17 Account & settings `94:5055` / `94:5481`; 18 Security & devices `95:5315` / `95:5996`; 19 Android 360x800 checks `96:5361` / `96:5854`.
- 15 mobile components (app bar, bottom nav, sheet header, list tile, chip, OTP input, code reveal card, status banner, snackbar, skeleton, product tile, platform chip, status bar, home indicator, A11y note), directional ones with LTR/RTL variants. Existing variables, text and effect styles and library components reused; logo components used as supplied (never recoloured); no Canva, no generated imagery (original vector set reused).
- States: empty, loading (skeleton), error and offline for data screens; results paid, processing, failed, expired; code reveal masked, re-auth sheet (423), wrong password, revealed with copy and clipboard note, capture-hidden, denied, rate limited; checkout guest and customer with country, PayPal/Paymob/Stripe/wallet options and notices, price changed and payment in progress.
- Accessibility: native Figma annotations (semantics labels, 48 dp targets) and A11y sticky notes per flow (contrast ratios computed from the tokens, focus order, live regions).
- Docs: `MOBILE_APP_ROADMAP.md` M-A8 (status, screen list, deviations, open questions). No repository code changed; nothing committed.
- Next: LFA/SFD feasibility review and PM scope checklist, then M-A9 owner approval (open questions in the roadmap, store-policy question from section 5).


### M-A10: Auth and onboarding UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render): **launch** feature (`lib/features/launch`): splash with the `GET /app/config` check sending `platform` and the build version (`core/config/app_version.dart`, kept equal to the pubspec by a test), force update (installed and minimum version, opens the Play listing), maintenance (server message, retry, support link), offline and failure states (request id), onboarding language (applies and mirrors at once) and display currency (chips from the config, charge-currency notice, skip), and the one-time rooted/jailbroken sheet (warn, never block). **auth** presentation: sign in (local checks, 422 field errors beside the fields, 429 banner "Too many attempts / Try again in N seconds" and a disabled "Try again in m:ss" button from `Retry-After`), two-step verification (6-box OTP input with one-time-code autofill, LTR digits, expiry countdown, recovery code, `two_factor_invalid` under the field, `two_factor_expired` and local expiry back to sign in; the challenge is memory-only), register (summary banner "Check N fields" plus field errors, terms checkbox), verify-email prompt (resend with 60 s cooldown or `Retry-After`, "I've verified" re-reads `me`), forgot password (opens `links.forgot_password` in the browser), sign out (confirm dialog, revoke, wipe, delete the local biometric key). Router: starts on `/splash`; new `RouteAccess.guestOnly` (login, two-factor, register, forgot password) sends a signed-in customer to `from`, to verify email after registering, or through the biometric offer first; `safeReturnLocation` never returns to a guest-only route. Failures map to localized text (`core/l10n/failure_messages.dart`); 136 new strings in `app_en.arb` / `app_ar.arb` (Western digits, LTR isolates for versions).
- **Biometric unlock (owner decision 6, ADR-040)**: `biometric_signature` 13.3.0 (pinned; reasons in `application/README.md`) creates an ECDSA P-256 key in the Android Keystore / iOS Secure Enclave, strong biometric only, invalidated when biometrics change. Domain use cases `EnrolBiometricUnlock` (create key, `POST biometric-key`, on 423 password sheet -> `confirm-password` -> register once more, key deleted on any failure), `DisableBiometricUnlock`, `UnlockWithBiometric` (signs a random nonce; invalidated key turns unlock off and falls back to the password) and `ConfirmRecentAuthWithBiometric` (challenge -> sign -> `confirm-biometric`, exposed as `confirmRecentAuthWithBiometricProvider` for M-A14). Offer once after the first sign-in on a device with biometrics; app lock after 5 minutes in the background and at a cold start (`AppLockGate` above the navigator, state kept), password fallback through `confirm-password`; switch on the home account card until the Security screen (M-A16). New `BiometricLockedFailure` for 423 `biometric_locked`. Android: `MainActivity` is a `FlutterFragmentActivity`, `USE_BIOMETRIC`; iOS: `NSFaceIDUsageDescription`.
- Native: the app's channel `shop.boxcards.app/security` gained `deviceIntegrity` (Android: test-keys, `su` and Magisk paths; iOS: jailbreak paths and a sandbox write, new Swift handler inside `AppDelegate.swift`), `deviceName` (`device_name` for sign-in), `openExternalUrl` (https only) and `openStoreListing` (Android; iOS has no App Store id yet). No keystore and no signing config were created or changed.
- Shared widgets (Mobile App components `74:13`) in `lib/shared/widgets`: `MobileAppBar`, `StatusBanner`, `showAppSnackbar`, `OtpInput`, `SheetHeader`, `AppListTile`, `AppChip`, `OptionTile`, `AppTextField`, `CountdownBuilder`, `IconBadge`, `FlowScaffold`; `AppButton` gained icon, loading and full width. All from the theme tokens, logical paddings, 48dp targets, semantics labels and live regions.
- **Backend (contract gap, fixed with tests)**: the app had no way to reach a password reset (`POST /forgot-password` is a session route and `GET /app/config` published no reset URL). Added `links.forgot_password` (setting `forgot_password_url`, env `MOBILE_FORGOT_PASSWORD_URL`, default `APP_URL/forgot-password`, settable with `mobile:app-config`): `AppConfigSetting`, `AppConfigService`, `config/mobile.php`, `.env.example`, `API_DOCUMENTATION.md` 1d. `AppConfigTest`: new case (default, set, refused `javascript:`), the content case asserts the link, and the "no secret field name" case now checks field names on the JSON keys (the public link legitimately contains "password") while still scanning the rest of the body for the words. Contract fixtures: `app_config/config_{ok,ar}` regenerated; 8 new biometric fixtures (`auth/biometric_*`: registered, 423, challenge, failed, confirmed, used challenge, not registered, deleted) from a new `AuthContractTest` case; the normalizer replaces the random `challenge` value. Synced to `application/test/fixtures/api` (150 files).
- Tests: Flutter `flutter test` **512 passed** (350 before; new: biometric use cases and plugin adapter 19, biometric repository against fixtures 4, auth controllers 29, launch controllers 12, auth pages en/ar 30, launch pages en/ar 15, tap-target and label guidelines 10, goldens 26 (13 states x LTR/RTL in `test/goldens/m_a10`), app version 2, native bridge 3; plus error mapper 1, guards 3, fixture decoders 4; the two home goldens were regenerated for the account card). `flutter analyze`, `dart analyze --fatal-infos`, `dart run build_runner build`, `bash tool/ci.sh`: passed. `flutter build apk --flavor dev ... --debug`: built (Kotlin compiled; the KGP warning for `biometric_signature` is the plugin's documented, harmless notice). Backend: `php artisan test --compact` (sequential) **2837 passed**, 20929 assertions (2835 before: the new forgot-password app config case and the new biometric contract case); `vendor/bin/pint --dirty --format agent`: passed.
- Bugs found by the new tests and fixed: guest-only routes were treated as protected for signed-out users; the onboarding and integrity controllers could be disposed mid-action (now watched or keep-alive).
- Deviations from Figma: maintenance uses the brand mark instead of the vault-dial art (no generated or exported imagery); onboarding step 2 has no country field (the server takes the country from the profile or GeoIP, owner decision 1) and is titled "Choose your display currency"; forgot password opens the web page instead of an in-app email form (no stateless endpoint); verify email has "I've verified my email" instead of "Open email app" (no mail-app plugin); register has no "Send me offers" checkbox (no API field); Material icons stand in for the Figma icon set; the biometric offer, lock screen, password sheet and rooted-device sheet were not drawn in M-A8 and follow the auth page pattern; system status bar and home indicator are the platform's.
- Not tested, and why: biometric prompts and the root check on real devices (no emulator with biometrics here); iOS build (no macOS host).
- Outstanding (M-A11 onwards): Security screen with the biometric switch and sign out (M-A16); reveal re-auth with `confirmRecentAuthWithBiometricProvider` and `X-Device-Integrity` from `deviceIntegrityProvider` (M-A14); checkout route as `RouteAccess.signedIn` with cart merge on sign-in (M-A13); app links for the verify-email and reset links (`app_links` still deferred); iOS App Store id for "Update now"; key attestation (ADR-040); a figure for the maintenance art if wanted.
- Next milestone: M-A11 (home, catalog and search UI).

### M-A11: Home, catalog and search UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render): **tab shell** (`StatefulShellRoute.indexedStack`, Mobile Bottom Nav `75:568`: Home, Browse, Cart, Orders, Account; each tab keeps its stack); **home** (03.1 to 03.4) from `GET /storefront/home`: header (logo, environment badge outside prod, display currency button, search), search field, hero carousel, promo banners and the server sections in order (products as two-column Product Tiles, platforms as Platform Chips, categories as chips, banners, FAQ, reviews; unknown item types and empty sections skipped), links through the `LinkTargetResolver` targets (`core/router/link_targets.dart`: product, category, platform, search, https `url` in the browser, order to the Orders tab, anything else ignored); **browse** (04.8: categories with product counts and child chips, platform strip with See all); **platforms** (04.1, three-column grid); **listing** (04.2 to 04.5: title from the translated taxonomy, Filters · n and Sort chips, filters sheet with delivery, sort and a price range sent as decimal strings and validated with `Decimal`, product count, infinite scroll, skeleton, empty with Clear filters, load-more error bar with Retry); **search** (04.6, 04.7: suggestions after 300 ms for 2 to 100 characters, `commit=1` on submit, results, popular terms, local history newest first, deduplicated, capped at 10, removable per term and clearable, stored in `shared_preferences` key `prefs.search_history`, never secure storage; no results state); **currency switcher** (03.5, Home header and Account tab): `GET /storefront/currencies` (app config currencies as fallback), saved on the profile when signed in (`UpdateRegionalSettings`), else locally; `pricingContextProvider` (display currency + language) is watched by every priced provider, so home and every open listing reload from page 1 in the new currency. Account tab placeholder keeps the M-A10 account card (biometric switch, sign out), display currency and language until M-A16; Cart, Orders and the product page (`/products/:slug`) show "Coming in the next update" until M-A13, M-A14 and M-A12.
- **Hero carousel (owner decision 8)**: `CarouselAutoplay` (`core/utils/carousel_autoplay.dart`) advances every 5 s; paused while touched, swiped or focused and restarted with a full interval when the interaction ends; stopped when not visible (inactive tab or covered route via `TickerMode`, app in the background, scrolled out of the viewport); never with `MediaQuery.disableAnimations`; never with one slide. Page dots, slides labelled "Banner n of m".
- **Money**: prices only from server money objects (`price_from.formatted`, else the string formatter with the currency exponent; `core/money/money_display.dart`), LTR islands, Western digits; no `double` (CI check passes).
- **Shared components** (`lib/shared/widgets`): Product Tile (`76:385`), Platform Chip (`81:358`), `AppBottomNav`, Skeleton (`76:324`), Logo Slot (`4:409`, dev and staging only, BD8), `NetworkImageBox`, `PillButton`, `StoreHeader`, `EmptyState`, `FailureView` (offline banner and "No connection", or the error with request id), `SectionHeader`, `ProductGrid`. `core/state/loadable.dart` (`Loadable<T>`) for controller state. `AppChip` labels now ellipsize instead of overflowing (long Arabic sort labels).
- **Package**: `cached_network_image` **4.0.5** (pinned exactly; reasons in `application/README.md`), the roadmap's catalog-media cache; used only through `catalogImagesProvider` (tests override it). No other package added. Images come only from API URLs; while loading, on error or without an image, tiles show a plain-text tile on a `color/field/*` token (no generated or bundled art).
- **Backend**: no change (no contract bug found).
- Tests: Flutter `flutter test` **621 passed** (512 after M-A10; 109 new: storefront controllers 20 (home, currency-change invalidation of home and listing incl. the signed-in profile path, currency options, pagination to the last page, concurrent load-more, failed page and retry, filters restart, empty and error, price validation, listing title, category grouping, search debounce, commit and history, history cap/dedupe/remove/clear, corrupt history, link targets, suggestion locations), hero carousel 9 (auto-advance and wrap, RTL, touch pause and restart, swipe, focus, reduce motion, inactive tab, scrolled off-screen, single slide), storefront pages en/ar 52 (home loaded with server prices as LTR and API-only image URLs, skeleton, server error with request id and retry, offline and retry, hero link, search promo link, currency switch reloading prices, tabs, browse, platforms, taxonomy error, infinite scroll, load-more error and retry with no automatic loop, filters and clear, invalid price bounds, sort, empty catalog, search suggestions/submit/history/clear, suggestion and popular navigation, per-term removal, no results, tap-target and label guidelines on five screens), goldens 28 (14 states x LTR/RTL in `test/goldens/m_a11`); the two shell home goldens were regenerated for the new home; router, auth (sign out now from the Account tab) and launch tests updated from the placeholder home to the home page key). `flutter analyze`, `dart analyze --fatal-infos`, `dart run build_runner build`, `bash tool/ci.sh`: passed.
- Deviations from Figma: the home header has the display currency button instead of the notifications bell (notifications are M-A17); no "LIMITED TIME" overline on the hero (no API field); home renders the server's sections (their titles and order) instead of fixed "Platforms / categories / Featured" blocks; category chips are links (no "All" selected chip); no region filter in the sheet and no region line on tiles (the API has no region list, and cards carry no region; the tile shows the category instead); the filters button reads "Show results" (no count before applying); no "⋮" menu on the listing; product tiles are not marked "Sold out" (cards have no availability; a card without `price_from` shows "Unavailable"); browse category cards have no art (no API image); error and offline states use an icon badge instead of the cloned illustrations (no generated or exported art); the cart badge on the bottom nav is not shown until the cart UI (M-A13); search results are the committed suggestions (the API has no full-text results endpoint).
- Not tested, and why: image disk caching and real network images on a device (no emulator run; tests use a fake loader); iOS (no macOS host).
- Outstanding (M-A12 onwards): product detail replaces the `/products/:slug` placeholder (M-A12); cart badge and cart tab (M-A13); orders tab and order links (M-A14); Account/Settings screens (M-A16); notifications bell (M-A17); a region filter needs a regions endpoint (backend, if wanted); a search results endpoint if the owner wants full result pages; `cached_network_image` 4.0.7 can be taken after its two-week age.
- Next milestone: M-A12 (product UI).

### M-A12: Product UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render): **product page** `/products/:slug` (05.1 to 05.6, replaces the M-A11 placeholder; pushed above the tabs, back returns, opened cold it goes home on back): gallery from `images[].url` (swipeable with page dots when there are several; the plain-text tile of BD8 on a `color/field/*` token with platform, category and, outside production, the empty Logo Slot shows until an image has loaded), delivery badge, "Category · Platform", name, rating and review count, delivery time; notices `price_may_vary` (product or selected variant: "Price may vary / The final price is confirmed at checkout"), "Not available right now" when no variant can be bought, and a banner for a refused add; **region chips** (only when the variants span more than one region; names from the product's `regions`) and **amount tiles** three per row (variant title and "Pay {server price}"; sold-out, custom-amount and unpurchasable variants stay visible, dimmed and disabled with "Sold out", "Not available yet" or "Not available", announced as disabled radios); switching region keeps the denomination when it can be bought there; **delivery-field form** generated from `delivery_fields` (label, help text, required, `text`, `number` with a digit keyboard and Arabic-Indic digits converted to Western digits, `email`, `select` as a sheet of Option Tiles); description (sanitized server HTML turned into native text blocks: paragraphs, headings, lists, line breaks, bold and italic; scripts, images and links are never loaded or executed: `core/utils/safe_html.dart`, `shared/widgets/html_text.dart`), instructions and terms as escaped plain text; **sticky footer** with the selected variant's server price (LTR island, "× n" with a quantity), quantity stepper (48dp buttons, 1 to `max_quantity_per_order` capped at the request limit 10, limit note), "Add to cart" (disabled while nothing can be bought, busy while adding); **reviews** section (rating summary from `meta.rating_avg`/`rating_count`, first three reviews, "See all reviews") and the **reviews page** `/products/:slug/reviews` (10 per page, infinite scroll, load-more error row with Retry and no automatic retry, empty and error states); loading skeleton (05.5), error with request id and retry, offline, not found (05.6, "Back to store"). Writing reviews stays M-A17.
- **Delivery-field validation** (`features/product/domain/delivery_field_rules.dart`) mirrors `DeliveryFieldValidator` (ADR-037) in the server's order: PHP-style trim, empty (required or skipped), length in code points against `max_length` (255 by default), type (`number` = ASCII digits, `email`, `select` value), then `pattern` anchored as `^(?:pattern)$` with Unicode. Checks run on "Add to cart", then live as the customer types; the first field with a problem scrolls into view. The server stays authoritative: 422 `errors.delivery_values.{key}` is shown beside its field (typing clears it), `errors.quantity` beside the stepper, anything else (`variant_id`, `item_unavailable` "out of stock", cart full) in the banner with the server's text; other failures use the localized failure messages. Only trimmed, non-empty values of defined fields are sent.
- **Add to cart**: `CartRepository.addItem` (`POST /mobile/storefront/cart/items`); a guest's `X-Cart-Token` is captured and sent back by the existing `CartTokenInterceptor`; on success the "Added to cart." snackbar with **View cart** (Cart tab, still the M-A13 placeholder) and the cart count updated from the returned cart. A second tap while adding is ignored.
- **Cart badge** (left out by M-A11): `CartCount` (keep-alive, `features/cart/cart_providers.dart`) reads `GET /storefront/cart` only when there is a cart (signed in, or a guest holding a cart token), re-reads when the session changes, takes the count from every cart write, keeps the last count on a failed read; the Bottom Nav (`75:568`) shows the volt count pill on the Cart icon's end side (mirrors in RTL, Western digits, 99+), announced as "Cart, n items".
- **Deep links**: `/products/:slug` is a public route and opens the page directly; `parseDeepLink` now returns `ProductLink` for `https://<app link host>/products/{slug}` and `/en|ar/products/{slug}` (the web's product URL).
- **Money**: prices only from the variant offers' server money objects (`formatted`), LTR isolates inside Arabic sentences (`ltrIsolate`), Western digits; ratings handled as decimal strings (`ratingLabel`, `ratingStars` on `Decimal`); no `double` (CI check passes).
- **Package**: none added. **Backend**: no change (no contract bug found).
- Tests: Flutter `flutter test` **763 passed** (621 after M-A11; 142 new): product rules 52 (delivery-field matrix over the fixture's `text` field and every server type: `text` optional and length, `number`, `email`, `select`, `pattern`, type before pattern, a pattern Dart cannot compile left to the server, PHP trim, send-only-known values, Western digits; variant options and block reasons, initial selection, region switch, quantity limit; ratings; description HTML incl. scripts, images and links dropped), product controllers 23 (parallel load, 404 and variants failures, retry, currency change reload, unavailable, `price_may_vary`, variant selection and region switch, quantity bounds, live checks after the first attempt, digit normalization, add success with the cart count, 422 `delivery_values` beside the field, the `cart/add_item_validation_failed` fixture (quantity beside the stepper, `variant_id` in the banner), `item_unavailable`, per-order limit, offline, double tap; cart count for guests without and with a token, session change, failed read; reviews paging, failed next page, failed first page), product pages en/ar 44 (05.1 content with LTR prices and API image, 05.2 sections, skeleton, server error with request id and retry, offline, not found and back to store, unavailable with disabled semantics, price may vary, region switch, stepper, local validation, 422 field error, `item_unavailable` banner, add and View cart with the tab badge, number and select fields, reviews page infinite scroll, reviews empty and failed, deep-link location, 360 wide without overflow, tap-target and label guidelines on both pages), goldens 22 (11 states x LTR/RTL in `test/goldens/m_a12`: product, details and reviews, price may vary with field error, nothing to buy, added, loading, not found, offline, reviews list, cart badge, 19.2 at 360x800); updated: deep-link parser cases (product links now accepted), the home hero test (now opens the product page). `flutter analyze --fatal-infos`, `dart analyze --fatal-infos`, `dart run build_runner build`, `bash tool/ci.sh`: passed.
- Bugs found by the new tests and fixed: an 0.5px overflow of the rating row at 360 wide in Arabic.
- Deviations from Figma: the footer shows the selected amount's server **price** (and "× n"), not a "Total" (totals come only from the server, in the cart, M-A13); amount tiles show the server's variant title ("10 USD") instead of "$10"; no "⋮" overflow menu (no action is defined; sharing would need a package); the app bar shows the product name only after the title block scrolls away (05.1 has none, 05.2 has it); "About this product" instead of "About this card"; reviews show the API's real reviews without the "Sample content" flag and without initials avatars; the product page shows three reviews and a "See all reviews" page (not drawn); the "Not sold in your country" notice (05.3) is not shown because the variants call sends no `country` (a guest has none; the cart's `block_reason` reports it at checkout); the quantity stepper is 48dp tall (40 drawn) for the touch target and reads minus, count, plus left to right in both languages as drawn; Material icons stand in for the Figma icon set; the gallery is swipeable with dots when there are several images (one drawn).
- Not tested, and why: real network images and a device run (no emulator here; tests use the fake loader); iOS (no macOS host); an incoming https app link on a device (no intent filters yet, below).
- Outstanding (M-A13 onwards): Android intent filters / iOS associated domains and the link listener for `ProductLink` and the other app links (owner fingerprints, open decision 4; the parser and route are ready); the cart screen (M-A13) should call `CartCount.updateFrom` after each write; backend: region names are not translated in Arabic (`regions` returns English names in `ar`), the variants endpoint exposes no customer-safe block reason (the app derives sold out, custom amount and not available), and a contract fixture with `number`, `email`, `select` and `pattern` fields would pin the client checks to recorded data (today they are tested against the documented rules); email checks are a practical subset of PHP `FILTER_VALIDATE_EMAIL` (the server decides rare forms); review writing (M-A17).
- Next milestone: M-A13 (cart, checkout and payments UI).

### M-A13: Cart, checkout and payments UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render): **cart tab** `/cart` (06.1 to 06.5): lines (name, variant, SKU, delivery values, line total as an LTR island), quantity stepper 1 to 10 (`PATCH`), remove (`DELETE`), 422 `errors.quantity` beside the line, other write failures in a snackbar, coupon apply/remove with the server's `coupon_invalid` reason, `coupon.error` and `limited_by_margin`, server totals (subtotal, discount with the code, total), `block_reason` text per line with checkout disabled until `checkout_ready`, "Prices were updated" after `price_changed` at checkout or a `rate_stale`/`rate_missing` line, `price_may_vary` notice, loading skeleton, empty ("Browse the store"), error with request id, offline; `CartCount.updateFrom` after every write; `CartRevision` reloads the cart after placement and after a final payment answer. **Checkout** `/checkout` (07.2 to 07.7, 19.3): account email, country (profile country when it is a checkout country; searchable sheet), cart repriced by `validate`, payment options with label, methods or "Charged in {currency}", the exact amount charged, the `charged_in_payment_currency` notice, disabled options with their reason, terms (with a link to `links.terms`), summary with the display total and "You will be charged {amount} in {currency} by {gateway}" (owner decision 1), pay button labelled per gateway ("Pay E£1530.00", "Continue to PayPal", "Pay $31.50 with wallet"). **Payment** `/payments/:order` (07.6, 07.7, 08.1 to 08.4): in progress with "Return to PayPal" and "Check payment status", confirming, paid, still processing ("Check again", last checked time), failed (cancel link, final failure, or the latest attempt ended while the order is still payable; "Try payment again", attempt number), expired, provider unavailable (retry), not supported (Stripe/unknown step); summary card (order, amount, gateway, status as text and colour).
- **Owner decision 4** (login to check out): `/checkout` and `/payments/:order` are `RouteAccess.signedIn`; a guest goes to `/login?from=/checkout` and returns; the guest cart is adopted by the first call with the bearer token; a 401 from place-order (`guest_checkout_disabled` is now kept in `UnauthenticatedFailure.code`) signs out so the guard asks to sign in again. **Owner decision 3**: `features.web_checkout_redirect` turns the cart button into "Complete purchase on boxcards.shop" (opens `links.web_checkout` in the system browser) and `/checkout` shows that state and loads nothing; `checkout_enabled: false` disables checkout.
- **Place order** through the unchanged M-A7 `PlaceOrder` (key per body hash in secure storage, reused across retries and restarts; duplicate to the existing order; `next: null` calls retry-payment once). Handled: `price_changed` (re-quote, dialog with old and new totals; the next attempt has a new body and a new key), `cart_empty` (back to the cart), `idempotency_in_progress` (explained, key kept), `rate_stale`/`rate_missing` (re-quote), item errors (back to the cart), other domain errors with the server's localized text.
- **Payments** (`features/payments/payment_flow.dart`, the feature's public entry): PayPal `approve_url` and Paymob `redirect_url` open in the system browser through the app's existing channel (`NativeBridge.openExternalUrl`, https only; no launcher package); wallet goes to the status. Every confirmation is the M-A7 `ResolvePayment` (capture for a PayPal step started in this session, else verify; 2 s, 3 s, 5 s, then every 10 s for 2 minutes); the app shows paid only on `payment_status: paid`. A return link only triggers the server check; a cancel link reads the status once. Final answers clear the pending checkout; leaving a result screen clears it too. **Resume**: the launch check sends a signed-in customer with a pending order to its payment screen and verifies again. `resolvePaymentProvider` is now keep-alive (used by the keep-alive flow).
- **App links** (`core/router/app_link_listener.dart`): one listener on `app_links` for `/app/payments/{return,cancel}/{order}` (query ignored) and the M-A12 product links; links wait while the launch check, onboarding, maintenance or force update is shown; another host or http is ignored; signed out, the payment screen asks to sign in and nothing is verified. `parseDeepLink` now also returns the cancel link (`PaymentReturnLink.cancelled`). Android: `autoVerify` intent filter for the flavor's `APP_LINK_HOST` (read from `lib/core/config/env_<flavor>.json` in `build.gradle.kts`) with `/app/payments/`, `/products/`, `/en/products/`, `/ar/products/`; `flutter_deeplinking_enabled` false. iOS: `Runner.entitlements` placeholder `applinks:$(APP_LINK_HOST)` (not referenced by the Xcode project yet) and `FlutterDeepLinkingEnabled` false. No keystore or signing configuration was created.
- **Package**: `app_links` **7.2.2** (pinned exactly; Apache-2.0; reasons in `application/README.md`). No other package. **Stripe**: `flutter_stripe` not added (the API offers app attempts only a PaymentSheet `client_secret`, no hosted page; a large native SDK; Stripe at launch depends on BD1 and open decision 12): the Stripe option is shown disabled "Not available in the app yet".
- **Shared**: `QuantityStepper` moved from the product widgets to `shared/widgets/quantity_stepper.dart` (cart and product); `failureDetail` (request id for server errors in banners); 128 new strings in `app_en.arb` / `app_ar.arb`.
- **Backend**: no change (no contract bug found).
- Tests: Flutter `flutter test` **909 passed** (763 after M-A12; 146 new): cart and checkout controllers 33 (cart load, guest without and with token, offline and retry, quantity and badge, remove to empty, 422 quantity, write failure, coupon applied/refused/removed, blocked line, revision reload; checkout mode in-app/web/disabled, web link opened, checkout not loaded in web mode; guard redirect to sign in and back, payment route access; profile country and quote, country outside the list, display versus charge and disabled options, terms required, place order body and PayPal launch with nothing confirmed, key reuse after a network error, key reuse after a restart with the same secure storage, `next: null` retry, provider unavailable, duplicate to the existing order, `price_changed` with a new key, `cart_empty` at place and at validate, `idempotency_in_progress`, 401 `guest_checkout_disabled`, item error), payment flow and app links 22 (wallet, PayPal launch, browser failure and reopen, capture on return, a return link never marks paid, polling schedule and 2-minute end on the fake clock, early paid, failed latest attempt, cancelled is expired and failed is final, cancel link, retry, attempts exhausted and `order_expired`, Stripe not launched, close clears the pending checkout, confirmation failure kept on screen, resume at launch, no resume when signed out or nothing placed; links: return opens the screen and verifies, link queued during the launch check, foreign host and http ignored, cancel link, signed out), pages en/ar 54 (06.1 to 06.5, quantity and remove, offline, guest sign-in from the cart, web mode, 07.2 to 07.7, pay with PayPal to 07.6, idempotency in progress, guest opening checkout, web checkout page, 08.1 to 08.4, close, tap-target and label guidelines on cart, checkout and a result, 360 wide), goldens 36 (18 states x LTR/RTL in `test/goldens/m_a13`, incl. 19.3 checkout at 360x800), deep-link parser 1 (cancel link). Updated: the product "View cart" test and the tab-switch test now find the cart; the M-A12 `cart_badge` goldens were regenerated (the Cart tab now shows the cart). `flutter analyze --fatal-infos`, `dart analyze --fatal-infos`, `dart run build_runner build`, `bash tool/ci.sh`: passed. `flutter build apk --flavor dev ... --debug`: built; the merged manifest carries the intent filter with the dev host.
- Bugs found by the new tests and fixed: the cart controller read its state before the first build; a link dispatched inside the router's own notification was overwritten (now dispatched after the route change).
- Deviations from Figma: no guest checkout frame (07.1) and no guest OTP/order access (owner decision 4); the account email card says "Order emails go to this address" instead of "Codes go to your account and this email" (codes are not emailed); the paid notice says "Your codes are in Orders" instead of "Receipt sent" (the app does not know an email was sent); no wallet balance in the wallet option (payment options carry none; an ineligible wallet is simply not listed); "Attempt n" without "of 5" (the API has no maximum); the failed copy does not claim "You were not charged" unless the order is closed; Stripe is shown disabled; the cart has no back arrow (tab root); Material icons stand in for the Figma icon set; status icons are token circles instead of the drawn art.
- Not tested, and why: real PayPal/Paymob sandbox pages and an incoming verified link on a device (no emulator here; no owner fingerprints, so links are not verified yet); iOS (no macOS host).
- Outstanding (M-A14 onwards): owner SHA-256 fingerprints and Apple Team ID for verified links (open decision 4), iOS `APP_LINK_HOST` per scheme and `CODE_SIGN_ENTITLEMENTS` on the macOS host (M-A20); Stripe PaymentSheet if the owner wants Stripe in the app (BD1, open decision 12, a custom-scheme `PAYMENTS_STRIPE_APP_RETURN_URL`); the status endpoint reports no attempt outcome, so a declined Paymob card shows "failed" only after the 2-minute window (from the order detail) unless the customer uses the cancel link (backend: an attempt status on `status`/`verify-payment` would shorten it); "View order" and "Go to orders" open the Orders tab placeholder until M-A14 (done in M-A14); app links for guest access (routed in M-A14), email verification and reset remain unrouted; `features.wallet_enabled` is advisory and not used by the checkout (done in M-A15).
- Next milestone: M-A14 (orders and code reveal UI).

### M-A14: Orders and code reveal UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render; Figma `74:14`/`74:15` sections 09, 10, 11 and 19.4, Code Reveal Card `76:594`): **Orders tab** `/orders` replaces the placeholder (09.1 to 09.3): status chips (one API `status` each), `GET /account/orders` 20 per page driven by `meta` (next page 600 px before the end, failed page kept with a retry bar, no retry loop), pull to refresh, skeleton, empty (and empty for a filter), error with request id, offline; signed out it offers sign-in (`from=/orders`) and guest order access; a sign-in, sign-out and `CartRevision` reload it. **Order detail** `/orders/:order` (09.4, 09.5): status and date, lines and units (masked code, unit status, "Revealed" after a first reveal; a unit opens its code when `revealable`), refund notice from `refund_state` and the succeeded refunds (wallet or payment method), total in the display currency and the charged amount when the payment currency differs (owner decision 1), payment status, provider and attempt, timeline; "Retry payment" for a `pending_payment` order whose latest attempt ended (new `PaymentFlow.retryFromOrder`, the M-A13 retry flow, then the payment screen); "Get help" (`links.support`); "Request refund" to `/orders/:order/refund` (placeholder for the M-A17 form) when paid and no refund is pending or complete. **Code reveal** `/orders/:order/units/:unit` (10.1 to 10.6, 19.4): `RevealController` (auto-disposed family) runs the M-A7 `RevealCode` with `X-Device-Integrity` from `deviceIntegrityProvider` (owner decision 5; `compromised` shows the warning, nothing blocked); on 423 `confirmRecentAuthWithBiometricProvider` first, else (or on any biometric outcome other than confirmed) the password sheet: wrong password stays in the sheet with the server's message, closing it returns to masked; one retry. 429 shows the wait in minutes and a disabled "Try again in m:ss", never retried; `reveal_denied`, unverified email (with "Verify email") and 404 have their own states. Revealed: code and PIN (LTR mono), Copy, Hide, "Hides in m:ss", "Read code aloud", "Open redemption page" (https only, system browser), how-to-redeem from the response.
- **Owner decision 7 as built**: masked by default; hidden after 60 s (controller timer), on `hidden`/`paused`/`detached` (`AppLifecycleListener`), on Hide, when iOS capture starts and when the screen is left (provider disposed); covered by an opaque panel while `inactive` (both platforms); clipboard cleared after 60 s by the keep-alive `clipboardGuardProvider` (holds no text; also after leaving the screen, and on resume when the timer fell due in the background). Android clears only its own sensitive clip (label check); iOS cannot read whose text is on the clipboard without the paste notice, so it writes an empty string after 60 s (limitation: a later copy by the customer within the 60 s is cleared too; documented in `application/README.md`).
- **Screen protection** through the existing channel `shop.boxcards.app/security`: new `ScreenSecurity` (`screenSecurityProvider`, replaces `SecureScreen`/`SecureScreenController`): `setSecure(true)` when a code screen's controller is created and `setSecure(false)` when it is disposed, reference counted. Android `setSecure` (`FLAG_SECURE`) already existed in `SecurityChannel.kt` and is now used, only while a code screen is open. iOS (`AppDelegate.swift`, no Xcode project change): `setSecure` arms a native blur over every window on `willResignActive`/`willDeactivate` (app-switcher snapshot), `isCaptured` and the event channel `shop.boxcards.app/security/capture` (`UIScreen.capturedDidChangeNotification`) hide the code while recording or mirroring (10.5). iOS cannot block a screenshot.
- **Accessibility**: Copy, Hide and Reveal have their own labels and hints; the code box reads "Code hidden. Ends in 0000." or "Code shown. Use Read code aloud to hear it."; the code is announced only by "Read code aloud" (character by character); hides are announced; 48dp targets; RTL with LTR code, mask and order-number islands; Western digits with Arabic month names (`core/utils/date_format.dart`).
- **Guest order access** (owner decision 4: no guest checkout; existing guest web orders only): `/guest-orders` (paste the delivery link; reached from the Account tab and the signed-out Orders tab) and `/guest-orders/:order` (11.1 to 11.5): the signed link (`GuestLinkInbox`, memory only) is opened through its `/mobile` twin, then the order email, `otp/send` (60 s resend cooldown or `Retry-After`), the 6-digit code (`otp_invalid` text under the boxes), `otp/verify`, the guest order detail and its codes through the same reveal screen (`/guest-orders/:order/units/:unit`, guest target, no re-auth). Expired link 11.5; no link and no usable token asks to open the email's link again; "Create account" prompt for signed-out guests.
- **App links**: `parseDeepLink` returns the new `AccountOrderLink` for `/account/orders/{id}` (also `/en|ar/...`, the customer delivery email, `OrderDelivered::link`), routed to the detail (the guard signs in first and returns); the guest access link `/api/v1/checkout/guest/orders/{id}/access?expires=&signature=` is now routed to guest access. Android intent filter adds `/account/orders/`, `/en/account/orders/`, `/ar/account/orders/` and the pattern `/api/v1/checkout/guest/orders/.*/access` (checked in the merged manifest). "View order" on a paid result opens the detail; "Go to orders" the tab; inbox and banner `order` targets open the detail.
- **Domain change**: `RevealCode` takes `confirmRecentAuth` (biometric then password, from the screen) and `deviceIntegrity`; `CodeRevealRepository.reveal` and the data source send `X-Device-Integrity`. The M-A7 `askPassword` path is kept and still tested.
- **Packages**: none added. **Backend**: no change. The app needs no JSON `GET /account/digital-codes`: the order detail already gives every unit's `public_id`, status, `revealable` and `masked_code` (API_DOCUMENTATION.md 4c), and lists never carry a code. No contract bug found.
- Tests: Flutter `flutter test` **1047 passed** (909 after M-A13; 138 new): reveal controller 20 (423 -> biometric success; 423 -> biometric fails -> password; password without biometrics; wrong password; cancel; 429 with retry time and no retry; 403/404; unverified email; auto-hide at 60 s on the test clock; hide on pause, cover on inactive; iOS capture; Hide; `setSecure` on and off with the provider's life; copy; the real Android guard sends `clearSensitive` 60 s after a copy, also after a hide; https-only redemption; `X-Device-Integrity` on the wire for account and guest reveals), orders and guest controllers 18 (paging through `meta`, failed next page, filter, retry, signed out, reload on `CartRevision`, retry payment through `PaymentFlow`, refund eligibility, 404, guest link -> email -> code -> order, `otp_invalid`, expired link, OTP already done, no link, paste validation, link parser, the account and guest app links incl. signed out), pages en/ar 46 (list, scroll paging, chips, empty, offline and retry, signed out; detail, unit to code, refund placeholder, retry payment, not found, "View order"; code screen through the sheet, copy, hide, wrong password, denied, rate limit, device warning, 60 s and background hide, capture, screen-reader labels and the announcement only on request, 360 wide, provider disposed and nothing in secure storage or preferences after leaving; guest flow end to end incl. the guest reveal; expired link; paste from the Account tab; tap-target and label guidelines), goldens 44 (22 states x LTR/RTL in `test/goldens/m_a14`, incl. 19.4 at 360x800; canary `FIXTURE-CODE-0000` only; checked under another time zone), reveal safety +4 (screen controller auto-disposed; no logging, storage, analytics or file APIs in the reveal screen; state prints no code; the header is logged without the code), reveal use case +3, screen and clipboard security 7 (replacing 3). Updated: the link-target test (`order` opens the detail). `flutter analyze --fatal-infos`, `dart analyze --fatal-infos`, `dart run build_runner build`, `bash tool/ci.sh`: passed. `flutter build apk --flavor dev ... --debug`: built.
- Bugs found by the new tests and fixed: the guest controller wrote state before its first build; the re-auth sheet popped the code screen as well when the customer closed it; status pills and the PIN row overflowed with long labels.
- Deviations from Figma: the masked state shows the stored mask with bullets (`••••0000`) and no PIN row (the detail does not say whether a unit has a PIN); "How to redeem" before the first reveal says the steps come with the code (the instructions arrive only in the reveal response); a "Read code aloud" button and a "Hides in m:ss" line were added (accessibility, owner decision 7); the email-unverified state offers "Verify email" (the M-A10 screen with resend) instead of "Resend verification email"; guest 11.1 asks for the order email (the access response does not return the masked email) and the guest code card opens the shared code screen instead of an inline card; the order list has no "Back" arrow (tab root) and the detail's overflow menu is not built (its actions are buttons); help and refund buttons are stacked; the guest flow adds a "paste your delivery link" page (the reveal needs the link proof, an order number cannot replace it); Material icons stand in for the Figma icon set.
- Not verified without a device: `FLAG_SECURE` on a real Android screen (screenshot, recording and recents), the clipboard sensitivity flag and clear on Android 10+ in the background, iOS (no macOS host: the Swift for `setSecure`, the privacy blur and capture events is not compiled here), biometric prompts, and incoming verified app links (no owner fingerprints yet).
- Outstanding (M-A15 onwards): the refund request form behind the placeholder (M-A17); the server's `apple-app-site-association` lists only `/app/*`, so iOS universal links for `/products/`, `/account/orders/` and the guest access link need `components` added on the backend (done in M-A15; the Associated Domains setup stays at M-A20); owner fingerprints and Team ID (open decision 4); the reveal audit could log the device integrity verdict in the risk engine (OD-1 follow-up); key attestation (ADR-040).
- Next milestone: M-A15 (wallet UI).

### M-A15: Wallet UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controller, the page only renders; Figma `74:14`/`74:15` section 12, frames 12.1 `92:3782`/`92:3956`, 12.2 `92:3849`/`92:4025`, 12.3 `92:3899`/`92:4073`, and the 07.2 wallet option `87:2565`): **Wallet** `/wallet` (`RouteAccess.signedIn`), reached from a new "Wallet" row on the Account tab. `WalletController` loads `GET /account/wallet` and the first page of `GET /account/wallet/transactions` together; the balance card shows the server's money with the Active or Frozen badge and the no-top-up note (BD3); frozen adds the warning banner and a sticky "Contact support" (`links.support`, hidden without a link); the statement is newest first, 20 per page, `meta` paging (next page 600 px before the end; a failed page keeps the list with a retry bar and is never retried by itself; stale answers after a reload are dropped), pull to refresh (the list stays; a failed refresh keeps it). Loading skeleton, empty (12.3), error with request id and offline states. A sign-in, a sign-out and `CartRevision` reload it.
- **Statement lines**: credit or debit icon, the type (Refund, Credit from support, Payment, Adjustment by support, Reversal, or a generic label for an unknown type), the order number when the line belongs to an order, the date (local time, Arabic month names, Western digits), the signed amount and the balance after. A line with an order opens the M-A14 order detail. The server's `note` (English only) is shown only on lines without an order (the staff reason of an adjustment; the web account page shows it too). Ledger amounts stay decimal strings (no currency per line, M-A7): `walletAmountText` writes them with the wallet currency's symbol and exponent from `GET /app/config` (else the code and the balance's scale) through `MoneyFormatter`, truncating like the server, never through floating point; a `+` only on an amount that shows above zero. Amounts and order numbers are LTR islands in Arabic.
- **`features.wallet_enabled`** (M-A13 open item) is honoured through `walletEnabledProvider`: off hides the Account entry, makes `/wallet` say "Wallet isn't available" without loading anything, and drops the wallet option from checkout's payment options. Before the launch check has a configuration the wallet counts as enabled (the server still decides whether the wallet can pay).
- **Checkout wallet balance** (M-A13 open item): the balance is already returned by `GET /account/wallet`, so the payment-options contract is unchanged. When the options list the wallet, the checkout controller reads the wallet once per quote (without delaying the options) and the option reads "Store credit · balance $40.00" as drawn; a failed read only hides the balance.
- **Accessibility**: the balance card reads "Store credit balance $40.00, Active"; each line reads its type and order, amount, balance and date, with "Opens the order" when linked; rows at least 64dp, buttons 48dp (tap-target and label guidelines tested).
- **Backend**:
  - The statement had no link to an order, which the scope needed. `WalletTransactionResource` now adds `order: { public_id, order_number } | null` for the customer's own order behind a purchase debit (`payment` reference) or a refund credit (`refund` reference); staff adjustments and any order that is not the requesting customer's give null. `WalletRepository::statement` eager-loads the references and their orders per page (query count independent of the page size, tested). Additive: the session API, the admin statement (own resource) and the web wallet page are unchanged. `API_DOCUMENTATION.md` 4b updated.
  - `apple-app-site-association` listed only `/app/*`. `mobile.app_links.paths` now mirrors the Android intent filter and the app's link listener: `/app/*`, `/products/*`, `/en/products/*`, `/ar/products/*`, `/account/orders/*`, `/en/account/orders/*`, `/ar/account/orders/*`, `/api/v1/checkout/guest/orders/*/access` (no catch-all; tested). `AppLinkAssociationService` doc and `API_DOCUMENTATION.md` 1e updated.
  - Contract fixtures: `wallet/transactions_ok` now holds a linked purchase debit and an unlinked refund credit (and `wallet/wallet_ok` the matching balance); new `wallet/wallet_frozen`. Synced into `application/test/fixtures/api`.
- **Packages**: none added. **Shared**: 27 new strings in `app_en.arb` / `app_ar.arb`; `test/helpers/wallet_fakes.dart` wired into the harness.
- Tests: backend: `MobilePaymentReturnTest` (association lists every app path; never claims the whole site), `WalletPaymentTest` (+3: the order behind a purchase debit and a refund credit, none for an unlinked credit and no internal reference fields; never another customer's order; linked orders loaded in a fixed number of queries), `AccountContractTest` (+1 frozen fixture; statement fixture with a linked line). Full suite `php artisan test --compact` (sequential, run twice, the second after the coordinator's rerun request): **2842 passed** (20971 assertions). Flutter `flutter test` **1130 passed** (1047 after M-A14; 83 new): wallet controller 24 (load, paging and last page, failed next page and retry, one page at a time, refresh and failed refresh, failed balance or statement until retry, empty, `wallet_enabled` off and back on, signed out then signed in, `CartRevision`, stale page dropped, currency from the configuration and the fallback, support link, amounts: sign, truncation, KWD, malformed, credit by sign; checkout: balance in the wallet option, option dropped when off, no balance call without the option, failed balance), pages en/ar 36 (Account entry and back, types with order numbers, LTR amounts in RTL, linked line opens the order detail, unlinked does not, loading, empty, frozen with and without a support link, server error and retry, offline and retry, paging with a failed page and retry, pull to refresh, `wallet_enabled` off, signed out to sign in, screen-reader labels and guidelines, 360 wide, checkout balance and hidden option), goldens 22 (11 states x LTR/RTL in `test/goldens/m_a15`), fixture decoder +1 (`wallet/wallet_frozen`); updated: the wallet repository test (linked `order`, paging query). `flutter analyze --fatal-infos`, `dart analyze --fatal-infos`, `dart run build_runner build`, `bash tool/ci.sh`: passed.
- Bugs found by the new tests and fixed: a credit below the currency's smallest unit showed "+$0.00"; the row chevron was mirrored twice in Arabic (Material chevrons already follow the text direction).
- Deviations from Figma: the frozen frame (12.2) shows no statement, the app keeps the statement under the frozen card (the history stays useful and refunds still arrive); a linked line has a chevron (it opens the order, not drawn); unlinked staff lines show the server's note under the type (English only, as on the web); "Wallet isn't available" for `wallet_enabled` off is not drawn (uses the shared empty state); Material icons stand in for the Figma icon set and the empty state uses the shared icon badge instead of the receipt art (as in M-A11 to M-A14).
- Not verified without a device: incoming iOS universal links (no macOS host, no Team ID yet).
- Open items: the M-A14 order tile and unit row chevrons have the same double mirroring in Arabic (pre-existing, outside this milestone; `order_widgets.dart` lines 243 and 352: use `Icons.chevron_right` alone); 07.3 "wallet insufficient" is still not shown (the options leave out a wallet that cannot pay, and the app does not know the wallet's charge amount to compare); statement notes are not translated (server text); owner Team ID for `MOBILE_IOS_APP_IDS` and the Associated Domains setup (M-A20); the wallet entry moves into the M-A16 Account hub (17.1) (done in M-A16).
- Next milestone: M-A16 (account and security UI).

### M-A16: Account and security UI

Date completed: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render and forward; Figma `74:14`/`74:15` sections 17 `94:5055`/`94:5481` and 18 `95:5315`/`95:5996`, frames 17.1 to 17.4 and 18.1 to 18.7, components `74:13`):
  - **Account hub** (the Account tab, 17.1): profile header (volt avatar with the initials, name, email, Verified / Not verified badge), Orders, Wallet (only with `features.wallet_enabled`; the balance from `GET /account/wallet` as the row value), Notifications and Support (placeholders until M-A17), Profile, Security (two-step state), Settings, Sign out (17.4). Signed out: sign in and create account (return to `/account`), guest order access (M-A14), Support, Settings. The temporary M-A10/M-A11 account card (`account_card.dart`) and the language button are removed; the wallet row moved into the hub.
  - **Profile** (17.2): name, read-only email with the badge and "Contact support to change your email", resend of the verification email (60 s cooldown or `Retry-After`) when unverified, phone, country (`GET /storefront/countries`), language and display currency. `PATCH /account/profile` with the changed keys only; local name checks; 422 messages beside `name`, `phone`, `country_code`. A saved profile updates the signed-in user.
  - **Settings** (17.3): language (saved on the profile first when signed in, applied only after the server accepted it), display currency (M-A11 sheet; owner decision 1), Notifications, the legal and help links from `GET /app/config` (only those configured; https, system browser), the version, Sign out. No theme row (the design has none; dark only).
  - **Security** (18.1): two-step verification, change password, the biometric unlock switch (moved from the account card; off deletes the server key and the local key, owner decision 6), signed-in devices with the count, the recent-auth note, sign out everywhere (`POST /mobile/auth/logout-all`).
  - **Turn on two-step verification** (18.2, 18.3): the password (the API needs `current_password`), the QR code of the `otpauth://` URI and the key in groups of four with copy, the 6-digit code, then the recovery codes once with "Copy all" and "I saved my codes" (leaving asks first). 409 `two_factor_already_enabled` and 422 `two_factor_invalid` handled. **Turn off** (18.4): password sheet.
  - **Change password** (18.7): current, new, confirmation; mismatch and empty checked first; 422 beside the fields; success signs other devices out (server) and, because the server revokes every biometric key of the account (ADR-040), deletes the local key and flag and shows "Turn biometric unlock on again" on Security.
  - **Devices** (18.5, 18.6): this device first ("Current"), the others by last use with the masked IP; sign out one after a confirmation (404 drops it and reloads); "Sign out of all other devices" with the current password; the note when web sessions are not listed.
- **Secrets** (hard rules): passwords are only passed through; the 2FA key and the recovery codes live only in the auto-disposed `TwoFactorSetupController` (the key is dropped once confirmed, the codes on "I saved my codes"), `toString` is redacted, nothing is written to secure storage, preferences, files or logs (requests stay `sensitive`/`noRetry`); copies go through the M-A14 clipboard guard (cleared after 60 s); `ScreenSecurity` is on while the key or the codes are on screen.
- **QR code**: the mobile API returns the `otpauth://` URI, not an SVG (the SVG route is a web session route), and showing only the key would drop the drawn QR, so `core/utils/qr_code.dart` ports the backend's in-house `QrCodeSvg` encoder to Dart and `QrCodeView` paints it. Verified module for module against the backend's output for 8 payloads (forced and automatic masks, versions 1 to 13). No package.
- **Data layer**: `AccountRepository.signOutOtherDevices` (`POST /mobile/account/devices/sign-out-others`, `current_password`, sensitive, no retry). `SignOutController.signOutEverywhere`.
- **Bug fixed**: `updateRegionalSettingsProvider` was auto-disposed while its callbacks ran after the profile call, so a signed-in language or currency change threw `UnmountedRefException` (the M-A11 tests only covered signed out). Now keep-alive; covered by new tests.
- **Shared**: `AppButtonVariant.danger`, `StatusPill`, `QrCodeView`; 111 new strings in `app_en.arb` / `app_ar.arb` (6 obsolete account-card strings removed); `test/helpers/security_fakes.dart` wired into the harness (`FakeSecurityRepository`, devices fixture; `FakeAuthRepository.logoutAllCalls`).
- **Packages**: none. **Backend**: no change. **Native**: no change (no APK build needed).
- Tests: Flutter `flutter test` **1268 passed** (1130 after M-A15; 138 new): account controllers 19, security controllers 14, security safety 4, account pages en/ar 38, security pages en/ar 22, goldens 30 (15 states x LTR/RTL in `test/goldens/m_a16`), QR encoder 10, account repository +1, and the updated tests of the old account tab (router language switch through Settings, sign out from the hub, tabs, wallet entry showing the balance; the M-A15 Account-entry golden refreshed). `bash tool/ci.sh` (generated code, fixture sync, no-double, format, `flutter analyze --fatal-infos`, `dart analyze --fatal-infos`, tests with coverage): passed.
- Deviations from Figma: the hub has no "My reviews" and "Refund requests" rows (their screens are M-A17; the brief's row list was followed); Notifications sits in the hub and Settings; the two-step enrolment starts with a password step (the API requires `current_password`; not drawn); the recovery codes have no "Download" button (no file is ever written; "Copy all" only); "Change password" shows "Signs out your other devices" instead of "Last changed …" (the API has no date); the Security screen adds the biometric switch and "Sign out everywhere" (not drawn; owner decision 6 and the brief); the recent-auth note says biometrics count where unlock is on (owner decision 6) instead of "Biometric unlock never replaces it"; the display currency reads "Automatic" while the server picks it; Material icons stand in for the Figma icon set; list tiles keep the shared 32dp icon box.
- Not verified without a device: `FLAG_SECURE` on the enrolment and codes screens on a real phone (same channel as M-A14), the biometric prompts, scanning the drawn QR with an authenticator app (the matrix equals the backend's, which was checked against bacon/bacon-qr-code in M13).
- Open items: My reviews, Refund requests, Notifications and Support screens (M-A17); the M-A15 open item about double-mirrored chevrons in `order_widgets.dart` is already resolved in the code (`Icons.chevron_right` alone); no "last changed" date for the password in the API; the profile phone has no client-side E.164 check (the server's message is shown).
- Next milestone: M-A17 (support, reviews, refund requests and notifications UI).

### M-A17: Support, reviews, refund requests and notifications UI

Date: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed. **Tests written but not run** (owner instruction mid-milestone: a single full run happens at the end; no golden PNGs were created). Code generation, `flutter gen-l10n`, `dart format`, `flutter analyze` and `dart analyze --fatal-infos` are clean.

- Built (presentation on the M-A7 data and domain layers; Riverpod codegen controllers, pages only render and forward; Figma `74:14`/`74:15` sections 13 to 16, frames 13.1 to 16.3, components `74:13`):
  - **Reviews** (13.1 to 13.3): "My reviews" `/account/reviews` (items ready to review from `GET /reviews/eligible`, the customer's reviews with the moderation status Pending / Published / Not published and "Edit"); write a review `/account/reviews/new/:item` (subject taken from the eligible list, never from the caller; 48dp star rating checked first, optional title and text, server limits as `maxLength`); edit `/account/reviews/:review/edit` (`PATCH /reviews/{id}`, new in the data layer; the edit goes back to moderation and the form says so). 403 `email_unverified` (banner and "Verify email"), 409 `review_exists`, 422 `review_not_eligible` as banners; 422 field messages beside `rating`, `title`, `body`. **Eligibility gating** (`features/reviews/review_eligibility.dart`, the reviews feature's public entry): "Write a review" appears on the product page and on delivered order lines only when the API lists that order item (verified purchase); signed out, with `features.reviews_enabled` off or after a failed read nothing is shown. "My reviews" row in the Account hub (only with `reviews_enabled`).
  - **Support** (14.1 to 14.4): tickets `/account/support` (number, status: Open, Waiting for us, Answered, Resolved, Closed; subject; order or topic; last activity; `meta` paging), "New request" `/account/support/new` (topic chips from the API's categories, optional linked order picked from the customer's orders (first 50), subject 3 to 190, message; query `order` and `category` prefill it; 422 for an order that is not the customer's shows beside the picker), the thread `/account/support/:ticket` (customer messages on the end side, the store's on the start side; reply composer; empty reply checked locally), closed state (loaded `closed`, or a 409 `ticket_closed` reply): notice, no composer, "New request" with the same order and topic. Internal staff notes never appear (the server leaves them out). **No attachments**: the API has no upload; the message helper says so. Signed out the screen asks to sign in and offers the web help page; `features.support_enabled` off says support is not in the app. The order detail's "Get help" now opens a new request about the order (the web help page when support is off).
  - **Refund requests** (15.1, 15.2): the form `/orders/:order/refund` replaces the M-A14 placeholder: order card (number, paid amount and date), reason 10 to 2000 characters, optional link to an open ticket about this order, "How refunds work". **No unit choice**: the API refunds per request (staff choose the units). 409 `refund_request_open` (with "Refund requests"), 409 `refund_not_allowed`, and 422 `order_public_id` (another customer's or an unknown order, M13) as banners; 422 `reason` beside the field. After sending: the list `/account/refunds` with "Request sent". The list shows order, date, reason and status (Requested, Approved, Not approved, Completed) and opens the order. **Status on the order detail**: the latest request for the order from `GET /refund-requests` (first 50, newest first; `features/support/refund_status.dart`), shown as a note; "Request refund" is hidden while a request is `requested` or `approved`. "Refund requests" row in the Account hub.
  - **Notifications** (16.1 to 16.3): inbox `/account/notifications` (newest first, paged; unread on a raised card with the volt dot; "n unread" and "Mark all read"; age "Just now", "n min ago", "n h ago" or the date); a tap marks it read (`POST /notifications/{id}/read`) and opens its target through `LinkTargetResolver` targets (`destinationFor` now maps `ticket` to the thread; `openLocation` pushes order, ticket and account screens so back returns to the inbox). Preferences `/account/notifications/settings`: the 4 categories x 3 channels of the API (in the app, email, SMS); required cells locked on; each switch saves at once (`PATCH` with that cell), a refused save puts it back with the server's message; the push note ("coming later", M-A18; no Firebase). **Bell** in the home header with the unread badge (`UnreadNotifications`, `features/notifications/notification_badge.dart`: read at sign-in, kept current by the inbox meta, mark read and mark all; 0 signed out; home pull to refresh reads it again), and the hub's Notifications row with "n new". Settings' Notifications row opens the preferences.
- **Shared/core**: `core/state/paged_list.dart` (`PagedListState`, `PagedLoader`: the M-A14/M-A15 paging rules in one place), `shared/widgets/paged_list_view.dart`, `select_field.dart` (field + option sheet), `subject_card.dart`, `notification_bell.dart`; `AppTextField` multi-line (`minLines`, `maxLines`, `maxLength`); `AppListTile.semanticsLabel`; `StatusPill` tones info, danger, accent. `ComingSoonPage` and its strings removed (no placeholder left). 133 new strings in `app_en.arb` / `app_ar.arb` (3 obsolete removed).
- **Packages**: none. **Backend**: no change (no contract gap: eligibility comes from `/reviews/eligible`, the refund request status from `/refund-requests`). **Native**: no change.
- Tests written, **not run** (to be run in the final full run, goldens with `--update-goldens`): `test/features/engagement/engagement_controllers_test.dart` (eligibility gating for product and order line, signed out, `reviews_enabled` off, failed read; review create with the rating check and trimmed body, not-eligible item, 422 beside fields, `review_not_eligible`, `email_unverified`, `review_exists`, edit found across pages and PATCH, foreign review not found, my reviews paging and signed out; ticket create with local checks and the prefilled order, 422 for a foreign order beside the picker, reply, empty reply, 409 `ticket_closed`, loaded closed, tickets paging failure, `support_enabled` off; refund create with the ticket filter and trimmed reason, 422 foreign order, 409 open, 409 not allowed, 422 reason, the order detail's latest request, failed list; inbox paging with the badge from meta, the badge at sign-in, 0 signed out and kept on failure, open marks read and leads to the ticket and the order, failed mark read, mark all read and its failure, `ticket`/`order` link targets, notification age; preferences save, mandatory never off, refused save reverts, failed load and retry), `engagement_pages_test.dart` (in `en` and `ar`: my reviews statuses and edit, write with rating check, email unverified / already reviewed / 422, not eligible and empty, offline and retry, the product page entry with and without an eligible purchase, the order line entry; tickets statuses, thread and reply, closed after 409 and new request, new request with topic, order picker and checks, foreign order beside the picker, "Get help" from the order, signed out, error and empty; refund form from the order through the list, 409/409/422 banners, the order detail note hiding the button, the hub row; home bell badge to the inbox, tap marks read and opens the ticket with back to the inbox, order notification, mark all read, offline and empty, preferences required and saving, refused save, tap-target and label guidelines), goldens `test/goldens/engagement_golden_test.dart` (`test/goldens/m_a17/`, 20 states x LTR/RTL: 13.1, 13.2, 13.3 unverified and empty, 14.1, 14.2, 14.3, 14.4 closed and empty, 15.1, 15.2 open and list, 16.1, 16.2 offline and empty, 16.3, the home bell, the hub, the order detail with a request and a review entry, the thread at 360 wide), `account_repositories_test.dart` (+1: review PATCH body, path and 404), and the updated M-A14/M-A16 tests (refund opens the form; the hub rows open the new screens). `test/helpers/engagement_fakes.dart` holds the fakes, wired into the harness.
- **Goldens of earlier milestones that will change** (intended; regenerate with `--update-goldens`): the home header now has the bell (`m_a11` home states, `shell_home_*`), the Account hub has three more rows (`m_a16` hub states, the `m_a15` Account entry).
- Deviations from Figma: the inbox's "⋮" is a settings button (its only action); preferences list every channel of the API per category (the frame shows a subset and names rows "Order updates by email"); "Request sent" says "You can follow its status here" (no reply time is promised by the API); refund rows have no refunded amount (the request carries only the refund's id and status); ticket rows show "updated <date>" for every ticket; Material icons stand in for the Figma icon set; empty states use the shared icon badge, not the drawn illustrations.
- Open items: ~~email links to `/account/tickets/{id}` are not app links yet~~ (done in M-A18); the order detail finds its refund request in the first 50 requests (a backend `order` filter on `GET /refund-requests` or the request in the order detail would remove the limit); the eligible list is the server's first 100 items; the unread badge refreshes at sign-in, in the inbox and on home pull to refresh only (M-A18: also on every push received in the foreground or tapped); ticket and review deep links from email are web pages until then.
- Next milestone: M-A18 (push notifications).

### M-A18: Push notifications

Date: 2026-11-11. Completed by: BEL (backend) and SFD (app), one developer, alone and in sequence. Owner decision 9 (FCM). **Tests written but not run** (owner instruction: a single full run at the end; no golden PNGs were created; no migration was run against `gaming-store`). `vendor/bin/pint --dirty` and `php -l` are clean; `dart run build_runner build`, `flutter gen-l10n`, `dart format`, `flutter analyze --fatal-infos` and `dart analyze --fatal-infos` are clean; `flutter build apk --flavor dev --debug` builds without any Firebase file (push off), and was also checked once with a throwaway fake `google-services.json` (removed afterwards) to prove the guarded Gradle plugin applies.

- **Backend** (ADR-042, `API_DOCUMENTATION.md` 1h, `DATABASE_DESIGN.md` `mobile_push_tokens`, `INTEGRATIONS.md` 8):
  - Device tokens: `POST` and `DELETE /api/v1/mobile/push-token` behind `mobile.customer` and the new `mobile-push` limiter (10/min per customer, 60/min per IP), `RegisterPushTokenRequest` (token charset and length, platform `android`/`ios`), `PushTokenController`, `PushTokenService` (one token per sign-in family, replaced on refresh, a token used by another sign-in moves, 403 `push_not_available` for a non-app token, audited `account.push_token_registered` / `account.push_token_removed` without the token). Table `mobile_push_tokens` (migration `2026_11_17_010001`, token encrypted plus SHA-256 hash, model `MobilePushToken`, factory, morph alias `mobile_push_token`). Cleanup in `MobileTokenRepository` on every revocation (logout, logout-all, device revocation by the customer or staff, refresh reuse, password change keeps the caller's, reset, account inactive) and `auth:prune-mobile-tokens` (orphaned tokens); sends select live families only (`PushTokenRepository`).
  - Preferences: channel `push` for `orders` and `support` only, on by default (`NotificationPreferenceService::isAvailable`); 422 `notification_preference_unavailable` for marketing or security; the matrix is now 14 cells. The web preferences page keeps its three columns.
  - Sender: `config/push.php` and `.env.example` (`FCM_ENABLED` empty = off, `FCM_PROJECT_ID`, `FCM_CREDENTIALS_PATH` outside the web root); `FcmClient` (FCM HTTP v1, RS256 JWT through `openssl_sign`, access token cached encrypted, outcome mapping: `UNREGISTERED` / invalid registration token / `SENDER_ID_MISMATCH` delete the token, 429/5xx/401/network retry with `Retry-After`, a bare 404 never deletes); `PushChannel` (`Notification::extend('push')`, debug-level no-op while disabled); `SendPushMessage` (queue `notifications`, 5 tries, backoff 30 s / 2 min / 10 min / 30 min, the token read at run time so it never sits in the queue, configuration errors fail without retry).
  - Sends: `OrderDelivered` (also partial), `SupportTicketReplied` (staff reply) and the new `PaymentFailed` (inbox kind `payment_failed`, inbox and push, no email; dispatched after commit from `PaymentStateApplier` for a verified provider failure that leaves a customer's order unpaid with no open attempt; not for a customer cancellation). Payload: the inbox title and body in `users.locale` and string data `type`, `category`, `link_type`, `link_value`, `order_id` or `ticket_id`; never a code, PIN, amount, message body or provider message. Arabic strings in `lang/ar.json`.
  - App links: `/account/tickets/*`, `/en/account/tickets/*`, `/ar/account/tickets/*` added to `mobile.app_links.paths` (apple-app-site-association) and the Android intent filter.
- **App**:
  - Packages: `firebase_core` 5.1.0 and `firebase_messaging` 17.0.1, pinned exactly (newest releases at least two weeks old on 2026-11-11: both 2026-10-23; README). Gradle plugin `com.google.gms.google-services` 4.5.0 (4.6.0 is from today), applied only when a `google-services.json` exists; `google-services.json` and `GoogleService-Info.plist` git-ignored.
  - `features/push`: `PushMessaging` interface and `FirebasePushMessaging` (starts only when the flavor's config exists; registration tokens, see open items), `PushTokenRepository` (`POST`/`DELETE /mobile/push-token`, sensitive in logs), `deepLinkForPush` (order, ticket, anything else nothing), `PushNotifications` (keep-alive: starts after launch without asking, registers the token when signed in and allowed, again on rotation and at sign-in; sign-out and sign-out everywhere remove it on the server before logout, then delete it locally), `ForegroundPush` and `PushMessageHost` (foreground message: badge refresh, inbox invalidated, info snackbar with "Open"), taps from the background and cold start through `AppLinkListener.open` (same queue, guards and routes as app links), `PushPrompt` and `PushPermissionSheet` (explainer once per installation after the first paid result, `PushOfferOnPaid` on the paid screen, then the system prompt; never on launch; flag `prefs.push_prompt_shown`).
  - Notification settings: the "coming later" note is replaced by real `push` switches (orders, support) from the API, and a banner when this phone does not allow notifications ("Turn on notifications" asks the system) or when the build has no Firebase. Inbox icon for `payment_failed`.
  - Links: `AccountTicketLink` (`/account/tickets/{id}`, `/en|ar/...`; `/account/tickets/new` opens a new request) routed to the ticket thread.
  - Native: Android `POST_NOTIFICATIONS`, default channel meta-data, channel `updates` created in `MainActivity` with EN/AR names (`res/values*/strings.xml`), ticket intent-filter paths. iOS placeholders: `aps-environment` in `Runner.entitlements` (not referenced by the project yet), `UIBackgroundModes` `remote-notification` and `FirebaseAppDelegateProxyEnabled` in `Info.plist`.
  - 17 EN and AR strings with RTL (the explainer, settings banners, snackbar).
- Tests written, **not run**:
  - Backend: `tests/Feature/Mobile/PushNotificationsTest.php` (registration and audit without the token, same token 200, replacement on refresh, one per device, a token moving between accounts, validation, non-app token 403, rate limit, unregister; cleanup on logout, logout-all, device revoke, password change, refresh reuse, prune and a revoked family never sent to; disabled no-op logged at debug; delivered-order payload with the canary code absent, ticket payload, Arabic text, payment failure payload without amount or provider message, preference off means no push but the inbox entry, `via()` channels, `UNREGISTERED` / invalid token / sender mismatch deletion with `Http::fake`, bare 404 and bad message kept, retry outcomes with `Retry-After`, OAuth token cached, missing credentials fail without sending; JWT signed with a generated RSA key and verified with its public key, an unusable key refused). Updated: `AccountNotificationsTest` (14-cell matrix, push unavailable for marketing and security, `fax` as the unknown channel), `MobilePaymentReturnTest` (AASA ticket paths), `SchemaAcceptanceTest` (table list). Contract: `AuthContractTest` (push token) and `AccountContractTest` (`preferences_unavailable`).
  - App: `test/features/push/push_routing_test.dart` (payload routing order, ticket, unknown, unsafe ids; ticket email links), `push_controllers_test.dart` (token lifecycle with fakes: register, rotation, retry after a failure, nothing without permission, signed out or Firebase; sign-in later; sign-out and sign-out everywhere unregister; prompt due once after the purchase, accept, not due when answered or unavailable; foreground badge refresh and events), `push_pages_test.dart` (EN and AR: never on launch, explainer after the first paid order then the system prompt, "Not now", not without Firebase or when allowed; background tap, cold start, unknown target, signed-out tap asks to sign in, ticket email link; foreground snackbar with "Open"; settings switches for orders and support only, saving a push cell, "Turn on" and the unavailable note), `account_repositories_test.dart` (+1 group: push token repository against the fixtures; preferences 14 cells and `preferences_unavailable`), `fixture_contract_test.dart` (push decoders), `preferences_store_test.dart` (new flag). Goldens `test/goldens/push_golden_test.dart` (`m_a18/`: explainer LTR and RTL, and at 200% text). Fakes `test/helpers/push_fakes.dart`, wired into the harness (push is unavailable unless a test turns it on).
- **Contract fixtures hand-written (must be regenerated)**: new `notifications/push_token_registered`, `push_token_unchanged`, `push_token_validation_failed`, `push_token_deleted`, `preferences_unavailable`; changed `notifications/preferences_ok` and `preferences_updated` (push cells). Written by hand to match `contractFixtureJson`, synced to the app (`tool/sync_fixtures.sh`) and bundled for the integration tests (`tool/bundle_fixtures.py`). In the final run: `UPDATE_API_FIXTURES=1 php artisan test --compact tests/Feature/MobileContract`, then `bash application/tool/sync_fixtures.sh` and `python3 application/tool/bundle_fixtures.py`, and review the diff.
- **Goldens of earlier milestones that will change** (intended): the M-A17 notification settings states (`m_a17` 16.3: push switches instead of the "coming later" note).
- Deviations: push for `order_fulfilment_failed` is not sent (not in the M-A18 scope; the inbox and email have it); payment failure has no email (inbox and push only); the explainer has no Figma frame (built from the integrity sheet's tokens); the Android notification small icon is the launcher icon (no monochrome vector made); push for marketing or security does not exist.
- Owner actions: create the Firebase project(s) and the Android and iOS apps; put `google-services.json` per flavor in `application/android/app/src/<flavor>/` (or one combined file in `android/app/`) and each `GoogleService-Info.plist` on the macOS host; upload the APNs key, enable Push Notifications and Background Modes in Xcode; on the server store the service-account key outside the web root, set `FCM_PROJECT_ID`, `FCM_CREDENTIALS_PATH`, `FCM_ENABLED=true`, run `php artisan migrate`, and run a worker for the `notifications` queue (details: `INTEGRATIONS.md` 8, `application/README.md` Push).
- Open items: on-device acceptance with a dev Firebase project (roadmap M-A18); firebase_messaging 17 deprecates `getToken`/`onTokenRefresh`/`deleteToken` in favour of Firebase Installation ID registration, whose server send target is not in the HTTP v1 API the backend uses (kept on registration tokens with documented ignores; revisit when FlutterFire and FCM document the FID target); a monochrome notification icon; `FirebaseAppDelegateProxyEnabled` and the entitlements to be confirmed on the macOS host; the commits `24667c0` and `04fbe25` ("feat: add mobile push notification system", "feat: implement push notification system with DTOs and enums") were made by someone else while this milestone was in progress: they contain part of this work and also the owner's `DatabaseSeeder.php` change and `backend/.yarn`, `.yarnrc.yml`, `yarn.lock`; this milestone made no commit.
- Next milestone: M-A20 (release configuration and store assets), then the final full test run.

### M-A19: Accessibility, performance and integration tests

Date: 2026-11-11. Completed by: SFD, alone and in sequence. Not committed. **Tests written but not run** (owner instruction: one full run at the end; no golden PNGs created). `dart run build_runner build`, `dart format`, `flutter analyze` and `dart analyze --fatal-infos` are clean; `flutter build apk --flavor dev --debug` builds (the SDK `integration_test` plugin was added as a dev dependency). Push (M-A18) untouched. Backend: no change.

**Accessibility audit** of every screen of M-A10 to M-A17 (code review against the tokens and the M-A8 accessibility notes; checks written as tests, not run):

| # | Area | Finding | Fix |
|---|---|---|---|
| 1 | Reduced motion | Every check read `MediaQuery.disableAnimations`, which is Android "Remove animations" only. Flutter reports iOS "Reduce Motion" separately (`AccessibilityFeatures.reduceMotion`), so on iOS the carousel still advanced and nothing was reduced. | `MotionTokens.reduced(context)` covers both; used by the carousel autoplay and dots, the product scroll-to-field, the payment status ring, the bottom nav and the new widgets below. |
| 2 | Reduced motion | Bottom nav selected pill animated 200 ms always. | `MotionTokens.resolve(context, MotionTokens.base)`. |
| 3 | Reduced motion | Page transitions (Android predictive back / zoom, iOS slide) ignored reduced motion. | `ReducedMotionPageTransitionsBuilder` in the theme: cross-fade in place; the wrapped builder still runs, so the iOS edge swipe and predictive back keep working. |
| 4 | Reduced motion | Indeterminate spinners kept spinning (button loading, splash, code screen and guest order loading, currency sheet, device sign-out, ticket send) and the language apply bar was an indeterminate `LinearProgressIndicator`. | `BusyIndicator`: the same spinner, or a still hourglass under reduced motion; labelled "Loading" unless inside a labelled button. The code screen's loading spinner had no label; it has now. |
| 5 | Live regions | Revealing a code announced nothing (hiding did). | "Code shown. Use Read code aloud to hear it." is announced once when the code appears (the code itself only on "Read code aloud"). Checked: add to cart (snackbar, a live region), payment status (title is a live region), checkout summary, every `StatusBanner` error, field errors and the quantity are already live regions. |
| 6 | Text 200% | `AppChip` and `PillButton` had a fixed 36dp height: at 2x the label (40dp line) was cut. | Minimum 36dp with 4dp vertical padding; same look at 1x. |
| 7 | Text 200% | Cart count pill fixed at 16dp. | Minimum 16dp. |
| 8 | Text 200% | App bar title (`heading/md`, 26dp line) cut in the 48dp bar above 1.85x. | Title capped at 1.8x (`MobileAppBar.titleMaxTextScale`), one line with ellipsis; the page header keeps its full size. |
| 9 | Text 200% | Home promo banner fixed at 112dp: two-line title plus subtitle overflowed. | Minimum 112dp, full width, image and gradient fill the box. |
| 10 | Text 200% | Product amount tiles fixed at 76dp. | Minimum 76dp with vertical padding. |
| 11 | Text 200% | Quantity count fixed 32dp wide (two digits wrapped). | Minimum 32dp wide. |
| 12 | Text 200% | OTP digits (`heading/lg`) and the account initials were cut in their fixed boxes. | Scaled down to fit (`FittedBox`); boxes keep their size. |
| 13 | Text 200% | Store header currency button: the theme's 12dp vertical padding pushed the code out of the 56dp header; the search field button had a fixed 48dp height. | No vertical padding on the currency button (48dp minimum keeps the target); search field minimum 48dp. |
| 14 | Text 200% | Amounts at the end of a row (cart totals, checkout total, payment option charge) could overflow or squeeze the label. | `FitAmount`: at most 200dp (160dp for the charge chip), scaled down, never cut or wrapped; natural size at 1x. |
| 15 | Contrast | Unchecked radio rings used `line/strong` (ink-600): 1.9:1 on canvas, below the 3:1 of WCAG 1.4.11 for a state indicator. | `content/muted` (6.3:1) in `OptionTile`, `PaymentOptionTile` and the payment method rows. |
| 16 | Contrast | Token check (WCAG 2.1): text `content/primary` 15.7 to 18.7:1, `secondary` 8.7 to 10.4:1, `muted` 5.3 to 6.3:1 on every surface; `accent/text` 14.3:1+, status colours 6.1:1+ on surfaces and 5.9:1+ on their tints; ink on volt 17:1, on ember 6.4:1, on danger solid 5.3:1; focus ring 5.0:1+. `content/disabled` 3.0 to 3.3:1 (disabled, exempt). | None needed; ratios asserted in `contrast_test.dart`. Open: field outlines (`line/default`, 1.4:1) and OTP box borders (`line/strong`, 1.9:1) are below 3:1; every field has a visible label, so this is a design decision for the owner. |
| 17 | RTL | `Icons.logout` does not mirror itself (its arrow should leave through the end side). | `DirectionalIcons.logout`: the same glyph with `matchTextDirection`. No hand-swapped icons exist; `chevron_right`, `arrow_back`, `open_in_new`, `send_outlined`, `star_half` mirror by themselves and are used as they are; the search "fill" arrow (`north_west`, not mirrored by Flutter) keeps its `Transform.flip`. |
| 18 | Labels | All 16 icon-only buttons have a tooltip or a `Semantics` label (the bell and the rating stars through `Semantics`). | None needed; swept by the guideline test. |
| 19 | Focus order | Checkout, product and code reveal are single columns whose order follows the task (what, choices, notices, summary, action last); footers sit after the body. | None needed; asserted in both directions by `traversal_order_test.dart`. |
| 20 | 48dp | Targets checked per milestone already. | Swept on all 33 main screens in both locales. |
| 21 | Rule 6 | `StaticStars.size` was a `double` in `lib/features` (the no-double check of `tool/ci.sh` and `no_double_test.dart` would fail). | `num`, converted at the `Icon`. |

**Performance** (review and fixes):
- Startup: `PreferencesStore.open()` and the secure session read run in parallel before `runApp`; everything else (app config, integrity, biometrics, catalog) already starts from the splash after the first frame.
- Images: `NetworkImageBox` already decoded at the slot width; the decode width is now rounded up to 64 px steps (at most 1536 px) so slots a few pixels apart share one decoded image in the cache. `cached_network_image` keeps originals on disk.
- Rebuild scope: `select` on the session (Account hub, settings), the startup state (update and maintenance screens) and the device list (security); the security page keeps the two-step disable controller alive without rebuilding on its states.
- Lists: the wallet statement (infinite scroll) is a `ListView.builder` (it built every loaded row's widget on each rebuild); listing, orders, notifications, tickets and reviews were already builders. `prefer_const_constructors` is enforced by the analyzer (clean).
- Frame timing: `integration_test/perf_test.dart` flings home and a 200-product listing under `watchPerformance`; profile mode asserts p90 build and raster at most 16 ms and at most 5% missed build budgets (physical device only). App size: `flutter build apk --analyze-size` command in the README. Numbers to record after the on-device run.

**Integration tests** (SDK `integration_test`, no third-party package, no backend): `integration_test/app_test.dart` runs `journeys/auth_journey.dart` (launch, onboarding, login, two-step code), `shopping_journey.dart` (home, product, add to cart, cart, checkout, PayPal hand-off, app-link return, paid; EN and AR), `reveal_journey.dart` (orders, detail, reveal with the confirm-password sheet, 60 s auto-hide), `security_journey.dart` (security, two-step on, recovery codes), `support_journey.dart` (new request, reply), `notifications_journey.dart` (bell, ticket target, order target). They run the real app on the harness fakes of `test/helpers`; the contract fixtures are compiled into `integration_test/fixtures/api_fixtures.g.dart` by `tool/bundle_fixtures.py` (checked by `tool/ci.sh`) because a device has no `test/fixtures`; `ApiFixture.bundle` switches the loader. Drivers: `test_driver/integration_test.dart`, `test_driver/perf_driver.dart`.

**Widget and unit tests written, not run**: `test/accessibility/semantics_guidelines_test.dart` (33 screens x en/ar: `androidTapTargetGuideline`, `labeledTapTargetGuideline`, `textContrastGuideline`), `text_scale_test.dart` (21 key screens x en/ar at 2x and 360dp: no overflow, no text cut vertically; the app bar cap), `traversal_order_test.dart`, `reduced_motion_test.dart` (Android and iOS settings; shown/hidden announcements), `test/core/theme/contrast_test.dart`, `test/shared/performance_widgets_test.dart`; screen catalog `test/helpers/screens.dart`.

- **Deviation**: the roadmap asks for flows "against a seeded dev backend with provider sandboxes"; by owner instruction for this milestone they run on the fakes and contract fixtures (no live backend). The fixtures are the backend's own contract recordings (G11), so the request and response shapes are the server's; a live-backend smoke run remains open.
- **Goldens expected to change** (intended; regenerate with `--update-goldens` and review): unchecked radio rings in `m_a10` onboarding language and currency, `m_a11` currency and filter sheets, `m_a12` select-field sheets if captured, `m_a13` checkout states, `m_a16` settings; the mirrored sign-out icon in the RTL `m_a16` hub, settings, security and devices states; the `m_a16` settings state while a language applies (spinner instead of a bar), if captured.
- **Open items**: the on-device run (journeys on an emulator, frame timing on a physical device in profile mode) and its numbers; app size totals; field outline contrast (finding 16) for the owner; the ticket thread builds its messages eagerly (bounded by the thread length; a builder if threads grow long); the product page rebuilds as a whole on each keystroke in a delivery field (cheap today; split if profiling shows jank); a live-backend smoke run of the journeys; screen-reader passes with TalkBack and VoiceOver on hardware (the automated checks do not replace them).
- Next milestone: M-A18 (push, after the owner's provider decision) or M-A20.

### Final test run (app), 2026-11-12

The single run of the whole app suite after M-A17 to M-A19 (owner rule: tests written per milestone, run once at the end).

- **Before**: `flutter test` 1,502 passed, 158 failed. **After**: 1,660 passed, 0 failed; `bash tool/ci.sh` passed (generated code, fixture sync and bundle, no-double, format, `flutter analyze` and `dart analyze` with fatal infos, tests with coverage). Backend tests not run here.
- **Sign out** (3 tests): `PushNotifications.unregister` skipped the server call when sign-out came before the deferred push start, and the start then ran on a disposed provider. `unregister` now awaits the one-time start first; `_start` returns when the provider is gone.
- **Page transitions** (reduced-motion cross-fade, iOS and Android): go_router 18 detects the app type through the separate `material_ui` package, does not recognise Flutter's `MaterialApp` and gave every route a `NoTransitionPage`, so the app had no page transitions, no iOS edge swipe back and no reduced-motion cross-fade. Routes are now built with `materialRoute` (`lib/core/router/material_route.dart`, Flutter's `MaterialPage`); the shell and the splash stay instant (`instantPage`: the shell's navigation key must not exist twice while one animates out).
- **200% text** (43 tests): the hero carousel grows to the measured height of its slides (`measureTextHeight`, `lib/shared/widgets/text_measure.dart`; 200dp at normal size); the product tile badge keeps to the end and ellipsizes; the Logo Slot word scales down inside its fixed box; status and order link rows of the ticket thread and the notifications count row wrap (`Wrap`); status pills, order totals and the delivery time at row ends are capped (`TrailingLimit`, `FitAmount`) and pills wrap; the gallery category is one line; custom app bar titles share the 180% cap and are one line (ticket and order numbers).
- **Ticket and notifications pages** (11 + 10 tests): the ticket header row overflowed at normal size (wrap, above); the notification settings form was a lazy `ListView`, so the second "Required" cell and the push banner were never built below the fold (now a scroll view with a column: a short fixed form, every cell reachable); the reply field was 46dp tall (now at least 48dp).
- **Header**: with the bell (M-A17) the dev "Development" badge was cut ("Developme..."); the logo and badge now share the space through a layout that keeps the badge's full label while the logo can scale down to 72dp.
- **Test fixes** (assertions): `engagement_pages_test` "empty, error with request id and retry" tapped "Retry" for a server error, which shows "Try again" (03.3, `FailureView`); "settings: required locked on" expected one "Push notifications" text, but M-A18 added the push cells with that title, so it now checks the banner's own text. **Test wiring** (no assertion changed): the foreground push test settles before tapping "Open" (the snackbar was still at height 0); the sign-out page test brings the button fully on screen above the tab bar; the code reveal traversal test hides the code at the end (the 60 s auto-hide timer outlived the test); the wallet loading test waits for the page transition (restored) before looking for the skeleton. `review_widgets.dart`: a comment containing the word `double` tripped the CI grep; reworded.
- **Goldens created** (44, generated after the screens were fixed and reviewed): `m_a17` (40: hub, home bell, my reviews, write review, review unverified, tickets, ticket thread and 360, ticket closed, new ticket, refund request, open and list, notifications, empty, offline, settings, order detail) and `m_a18` (4: push explainer and at 200%).
- **Goldens updated** (40, each reviewed before and after): home header bell (`m_a11` home, sections, loading, error, offline, currency sheet; `shell_home`); account hub rows "My reviews" and "Refund requests" (`m_a16` hub and hub 360, `m_a15` account wallet entry); radio ring contrast (`m_a10` onboarding language, `m_a11` currency sheet, `m_a13` checkout, 360, summary, notices, country sheet, price changed, `m_a15` checkout wallet); RTL sign-out icon (`m_a16` settings, security, devices, device revoke). No regression accepted.
- **Open**: user text in another script keeps the page direction (an English review in Arabic shows its full stop on the left), as in the approved `m_a12` reviews; a per-text direction would be a design decision.

## Design approval gate (M1 and M-A9), 2026-11-10

The owner approved both designs in the Figma file `JkUUSR3WGBYC9ewy2qRFQO` ("Approve both"):
- the web design, Phase 1 and Phase 2 (storefront, account, admin and RTL pages);
- the mobile app design from M-A8 (pages `74:14` EN, `74:15` AR, `74:13` components).

This unblocks M12 to M15, M18 and M-A10 to M-A17.

**Open design questions** (M-A8 list) were not answered at the gate. They are built as drawn, and the owner can change them later:
- Latin digits in Arabic, and currency code formatting until symbols are confirmed;
- guest checkout in the app;
- a warning, not a block, on rooted devices;
- no biometric unlock;
- codes auto-hide after 2 minutes or when the app goes to the background;
- the hero carousel advances only when swiped;
- a "buy on the web" state only if the store-policy review requires it.

**Build order** (one agent at a time, owner rule): M12, M13, M14, M15, M18, then M-A10 to M-A19, then M19. M20 and M-A20 need credentials, servers and developer accounts.

## OD-1: Owner decisions, backend

Date completed: 2026-11-11. Completed by: BEL (senior Laravel backend developer), alone and in sequence. Not committed.

### Summary of completed work

The backend side of `OWNER_DECISIONS.md` decisions 1, 3, 4, 5 and 6 (the implementation table at the end of that file). Design: `PROJECT_ARCHITECTURE.md` ADR-040 (biometric re-auth) and ADR-041 (display and payment currency); API: `API_DOCUMENTATION.md` section 1g.

### Files created

- Currency: `Enums/DisplayCurrencySource.php`, `DTOs/DisplayCurrencyDecision.php`, `Services/DisplayCurrencyResolver.php`.
- Fulfillment: `Enums/DeviceIntegrity.php`.
- Identity: `Enums/RecentAuthMethod.php`, `Enums/BiometricKeyRevocationReason.php`, `Models/MobileBiometricKey.php`, `Models/MobileBiometricChallenge.php`, `DTOs/BiometricChallenge.php`, `Services/BiometricAuthService.php`; factories for both models.
- HTTP: `Controllers/Mobile/Auth/BiometricController.php`, `Requests/Mobile/Auth/RegisterBiometricKeyRequest.php`, `Requests/Mobile/Auth/ConfirmBiometricRequest.php`.
- Tests: `tests/Feature/Currency/DisplayCurrencyTest.php`, `tests/Feature/Mobile/BiometricAuthTest.php`; fixture `docs/api-fixtures/checkout/place_order_guest_checkout_disabled.json`.

### Migrations

- `2026_11_15_010001_remove_charge_display_currency_only_from_payment_gateways` (data only).
- `2026_11_15_010002_add_device_integrity_to_digital_code_accesses_table`.
- `2026_11_15_010003_add_recent_auth_method_to_personal_access_tokens_table`.
- `2026_11_15_010004_create_mobile_biometric_keys_table`.
- `2026_11_15_010005_create_mobile_biometric_challenges_table`.

### Features implemented

1. **Display currency by country (decision 1).** `ResolvesDisplayCurrency` (used by every storefront controller, `StorefrontRequest` and so every Blade page) now delegates to `DisplayCurrencyResolver`: `X-Currency`, `currency` cookie, saved `users.display_currency` (explicit, always win); else the country currency of `users.country_code`, else of the GeoIP country, only when active with a fresh rate; else USD. Cached on the request. `GeoIpResolver::sourceHeader()` added; `PublicCacheHeaders` adds the GeoIP header to `Vary` on priced public reads.
2. **Payment currency per gateway (decision 1).** `PaymentGateway::chargeCurrency()` (`default_currency`: Paymob EGP, others USD, seeded); `PaymentEligibilityService::paymentCurrency` ignores the display currency; `charge_display_currency_only` removed from the seeder and existing rows. Place-order, payment-options, snapshot, margin floor, `price_changed`, idempotency and refunds are unchanged code paths. The wallet charges USD and its ledger is in the base currency (USD), so they are consistent.
3. **App config (decision 3).** `features.web_checkout_redirect` (default false, `MOBILE_WEB_CHECKOUT_REDIRECT`) and `links.web_checkout` (default `/checkout` on `APP_URL`, `MOBILE_WEB_CHECKOUT_URL`), both settable with `mobile:app-config`.
4. **No guest checkout in the app (decision 4).** The `/api/v1/mobile` place-order refuses guests with 401 `guest_checkout_disabled` under `storefront.guest_checkout`; `features.guest_checkout_enabled` is published true only while that flag is on.
5. **Device integrity (decision 5).** `X-Device-Integrity` on mobile reveals stored in `digital_code_accesses.device_integrity` and the reveal audit; never blocks.
6. **Biometric re-auth (decision 6).** Key registration (password-confirmed token), revocation, single-use challenge and `confirm-biometric`, which sets `password_confirmed_at` (and `recent_auth_method = biometric`) on the current token; `mobile-biometric` limiter; audit and security events; keys revoked on password change and reset, after five bad signatures, and dead once the device's sign-in ends.

### Behaviour changes to existing endpoints

- Display currency without an explicit choice is the country currency instead of USD (signed-in customer with a profile country; guests only with `GEOIP_DRIVER=header`).
- `payment-options` and place-order: Paymob is offered and placeable for any display currency (EG declared and IP country still required) and always charges EGP; a gateway whose merchant account also enables the display currency now still charges its fixed currency.
- `POST /api/v1/mobile/checkout/place-order` by a guest: 401 `guest_checkout_disabled` by default (was allowed).
- `GET /app/config`: `features.guest_checkout_enabled` false by default (was true); new `features.web_checkout_redirect` and `links.web_checkout`.
- `Vary` on `/storefront/home` and `/storefront/catalog/products/{slug}` includes the GeoIP header when the header driver is on.
- Reveal audit rows carry `device_integrity`.

### Tests changed (behaviour changed by a decision)

- `Payments/EligibilityAndCheckoutTest`: the "display in USD" case left the "does not offer Paymob" dataset and became a positive test (Paymob offered for USD and SAR displays, charging EGP 500.00 for USD 10.00); "refuses Paymob at place-order for a non-EGP order" became "places a Paymob order from a USD display, charging EGP once at the snapshot rate", plus a kept refusal for a non-Egyptian declared country; "Stripe and PayPal internationally" now expects `['paymob']` for an Egyptian customer instead of `[]`. New: Stripe still charges USD when the merchant also enables EGP.
- `Mobile/AppConfigTest`: the features map now has `guest_checkout_enabled: false` and `web_checkout_redirect: false`, plus `links.web_checkout`; new cases for the guest flag and the redirect flag.
- `Mobile/MobileCheckoutTest`, `Mobile/MobilePaymentReturnTest`, `MobileContract/CartCheckoutContractTest`: the guest place-order blocks set `storefront.guest_checkout = true` (they test the guest flow, which stays available behind the flag); new tests for the 401 and its fixture.
- `Mobile/MobileAccountTest`: new device-integrity cases.

### Tests and results

- Backend: `php artisan test --compact` (sequential) 2321 passed, 16272 assertions, about 2.7 minutes (2265 before OD-1, so 56 more cases; new files `DisplayCurrencyTest` 17 and `BiometricAuthTest` 16, the rest in the adapted files listed above). `vendor/bin/pint --dirty` clean.
- Contract fixtures regenerated with `UPDATE_API_FIXTURES=1`: `app_config/config_ok.json` and `config_ar.json` changed (flags and `links.web_checkout`), `checkout/place_order_guest_checkout_disabled.json` added; copied to the app with `application/tool/sync_fixtures.sh`.
- Flutter: `AppFeaturesDto`/`AppFeatures.webCheckoutRedirect` (default false) and `LinksDto`/`AppLinks.webCheckout` added and regenerated; repository test asserts them. `application/tool/ci.sh` passed, 350 tests.
- Schema: `SchemaAcceptanceTest` lists the two new tables; `DATABASE_DESIGN.md` documents them and the two new columns.

### Outstanding requirements

- Key attestation (Play Integrity key attestation, Apple App Attest) is not verified: the server trusts the app to create the biometric key with user authentication required (ADR-040).
- Public cached storefront reads stay anonymous: a signed-in app user sees the profile currency there only if the app sends `X-Currency` (cart, checkout and account use the profile directly).
- With the default null GeoIP driver, guests browse in USD; set `GEOIP_DRIVER=header` behind Cloudflare in production.
- No admin screen for a gateway's charge currency or for biometric keys; the device list does not show whether biometric unlock is on.
- The risk engine does not yet read `device_integrity`.
- No contract fixtures for the biometric endpoints yet (the app builds them at M-A10/M-A16).

### Next milestone

M13 (customer account), per the build order.

---

## M18: SEO and analytics

Status: Complete (2026-11-11, not committed). Scope `SEO_STRATEGY.md`. Blade, Tailwind and plain JS; no Livewire; no new composer or npm packages.

### Summary of completed work

Server-built JSON-LD on every storefront page; hreflang and canonical rules centralised and tested; a sitemap index with per-type, per-language children and a `robots.txt` route; the redirects runtime (admin 301/302/410, removed products 410, slug-change 301s, trailing slash and case normalisation, 404 logging); a `SeoResolver` (metadata, then templates, then defaults); blog pages; GA4/GTM behind a consent banner with a nonce-safe loader; the server-side GA4 `purchase` through the Measurement Protocol from the M10 queue row; Search Console OAuth, daily fetch and admin report. GA4 and Search Console are mocked with `Http::fake` only and **awaiting credentials**.

### Files created

- Domain: `Seo/DTOs/{ResolvedSeo,RedirectMatch}`, `Seo/Services/{SeoResolver,RedirectResolver,NotFoundLogger,SlugRedirectRecorder}`, `Seo/StructuredData/{StructuredDataBuilder,PageSchema}`, `Seo/Sitemaps/{SitemapEntry,SitemapSource,SitemapGenerator}`, `Seo/SearchConsole/{SearchConsoleClient,SearchConsoleConnection,SearchConsoleSync,SearchConsoleReport,SearchConsoleException}`, `Analytics/Services/{AnalyticsSettings,PageAnalytics,MeasurementProtocolClient,AnalyticsEventDispatcher}`, `Analytics/Jobs/SendAnalyticsEvent`, `Analytics/Exceptions/MeasurementProtocolException`.
- HTTP: `Middleware/Storefront/ResolveSeoUrls` (global), `Controllers/Web/BlogController`, `Controllers/Web/Seo/{SitemapController,RobotsController}`, `Controllers/Admin/Pages/SearchConsolePageController`, `Controllers/Admin/Seo/SearchConsoleController`, `Requests/Admin/Seo/{SearchConsoleReportRequest,SearchConsoleCallbackRequest}`; `routes/seo.php`.
- Console: `seo:generate-sitemaps` (hourly at :40), `analytics:dispatch-pending` (every 5 minutes), `seo:fetch-search-console {--days=3}` (daily 06:15), all in `TaskSchedule`.
- Config: `config/seo.php`; `.env.example` `SEO_BLOCK_ALL_CRAWLERS`, `SEO_SITEMAP_DISK` (no real values).
- Views: `storefront/blog/{index,show}`, `storefront/partials/consent`, `errors/410`, `errors/partials/popular`, `admin/content/search-console`. JS: `lib/analytics.js`, `modules/consent.js`.
- Tests: `tests/Feature/Seo/{StructuredDataTest,CanonicalRulesTest,RedirectRuntimeTest,SitemapTest,AnalyticsTest,MeasurementProtocolTest,SearchConsoleTest}.php`, `tests/Support/SeoFixtures.php`.

### Files changed

`bootstrap/app.php` (global `ResolveSeoUrls`, `routes/seo.php`, storefront CSP `:storefront`), `ContentSecurityPolicy` (Google hosts only on storefront pages and only while a tag is configured), `LocalizedRoutes::alternates`, `StorefrontServiceProvider` (scoped page state, slug-change listeners), `Web/{Home,Category,Platform,Product,Page,Checkout}Controller`, `RendersProductListing::listingSeo`, `RecordPurchaseEvent` (queues the send after commit), `RedirectService` (chain collapse on save), `Redirect` (cache invalidation), `BlogPostService` (visible posts), `ProductRepository::listedForSitemap` and `StorefrontCatalogService::sitemapProducts`, `config/admin_settings.php` (analytics group applied, new fields and secret), `config/admin.php` (Content > Search Console tab), `routes/{storefront_pages,admin,admin_pages}.php`, layout `storefront` (canonical, hreflang from the canonical, verification meta, JSON-LD, analytics config and events, consent banner), `components/store/{breadcrumbs,footer}`, views category, platform show, product, page, home, buy box, 404, admin settings, `resources/js/modules/{product,checkout}.js`, `lang/{en,ar}/{storefront,admin}.php`. **Deleted `public/robots.txt`** (the web server would serve it before Laravel; the route replaces it).

### Migrations

None (the M4 tables `redirects`, `not_found_logs`, `seo_metadata`, `seo_templates`, `analytics_event_dispatches`, `search_console_metrics` and `settings` are used as designed).

### Features implemented

- **Structured data** (`StructuredDataBuilder`, printed once per page by the layout as `application/ld+json` with the CSP nonce, `JSON_HEX_TAG` so catalog text cannot close the script): Organization and WebSite with SearchAction on every page (localized URL and `inLanguage`); BreadcrumbList from the visible breadcrumb; Product with Offer (one priced variant) or AggregateOffer (low/high, `offerCount`, each Offer) at the rendered display-currency price, availability, `priceValidUntil` (7 days); AggregateRating only when `rating_count` (approved reviews only) is above zero; CollectionPage with the page's products on categories and platforms; BlogPosting on posts; FAQPage from the home FAQ section only while `analytics.faq_schema` is on (default off, strategy section 4). EN and AR.
- **Canonical and hreflang**: hreflang links (en, ar, x-default) are generated from the canonical URL, so filters, sort, tracking parameters and currency never reach an alternate; only `page` is kept. One filter or sort canonicalizes to the unfiltered listing; two or more filters, an empty (thin) listing and search are `noindex,follow` without canonical or hreflang; paginated pages are self-canonical with "Page N". Admin `seo_metadata` robots, canonical override (hreflang then omitted), title, description and Open Graph image win; then `seo_templates` with tokens; then defaults (`SeoResolver`, per language).
- **URLs**: trailing slash removed with a 301 (query kept); after a 404, an upper-case path whose lower-case form is a storefront page gets a 301; no country segment anywhere (`/eg`, `/us/...` are 404).
- **Redirects runtime** (`ResolveSeoUrls` + `RedirectResolver`): after a route miss, active admin redirects answer 301 or 302 (query kept when the target has none) or 410 with the designed page in the path's language; lookups cached per path (misses too) under a version bumped on every save or delete; chains followed up to 5 hops, loops and longer chains fall back to 404; `hits` counted. Admin saves collapse chains (`RedirectService::afterSave`); a slug change on a product, platform, category, page or blog post creates 301s for every language and collapses or removes old ones. Soft-deleted products are 410. Other storefront misses are upserted into `not_found_logs` (normalised path, no query string; file probes and JSON paths skipped); the 404 and 410 pages show popular searches.
- **Sitemaps**: `/sitemap.xml` index, children `{static,categories,platforms,products,pages,blog}-{en,ar}-{n}.xml` with `xhtml:link` alternates (en, ar, x-default) and `lastmod`; chunked at `seo.sitemaps.chunk_size` (50,000); written to the `local` disk in a run directory and swapped in; generated hourly and on the first request when missing. Excludes drafts, inactive or empty listings, unpublished pages and posts, and any URL `noindex` in that language. **robots.txt** route: allows public content, disallows `/admin`, `/api`, `/checkout`, `/cart`, `/account`, `/search`, `/orders` (and the `/ar` twins), never CSS/JS, references the index; `SEO_BLOCK_ALL_CRAWLERS=true` answers `Disallow: /`.
- **Blog pages** `/blog`, `/blog/{slug}` (EN/AR, translations, sanitized body, cover with width/height), so the strategy's blog URLs, sitemap and Article markup exist.
- **Analytics (browser)**: settings group `analytics` is now read (`AnalyticsSettings`). With a GTM id (or a GA4 id alone) the page gets the public ids in `#store-config`, a consent banner (SECURITY.md: cookie consent gates GTM and GA; cookie `bc_consent`, 180 days, footer "Cookie settings"), and `lib/analytics.js` inserts the tag with the page nonce only after "Accept" and when idle. CSP adds `https://www.googletagmanager.com`, `https://*.google-analytics.com`, `https://*.analytics.google.com` only then, and never on admin pages. Events `view_item` (product), `add_to_cart` (after the bag accepted it), `begin_checkout` (checkout) from server values; `PageAnalytics` and the JS allow-list refuse `purchase` and `refund`. Checkout sends the `_ga` client id with place-order only after consent.
- **Analytics (server)**: `purchase` from the M10 `analytics_event_dispatches` row through the Measurement Protocol (`AnalyticsEventDispatcher`, queued `SendAnalyticsEvent` with backoff 60 s/5 min/15 min/1 h, re-queued by `analytics:dispatch-pending`); a per-row lock and the `sent` status make it exactly once; 5 failed attempts mark it `failed`; while awaiting credentials or with `server_purchase` off the row stays `pending` without spending attempts. EU region option. No personal data; pseudonymous client id with ad consent denied when the browser sent none.
- **Search Console**: Settings > Analytics card with connect (Google OAuth with offline access, read-only scope, session state, PKCE S256), the redirect URI to register, and disconnect (revoke and delete). Refresh token encrypted, write-only, audited without the value; access tokens cached encrypted. `seo:fetch-search-console` stores totals, queries and pages per day; Content > Search Console report (7/28/90 days: clicks, impressions, CTR, average position, top 20 queries and pages, "top rows only" notice). Laravel HTTP client only.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Structured data validated on samples | Met | `StructuredDataTest` parses the JSON-LD of real pages (product one and several variants, display currency, rating, Arabic, script-breaking name, category, platform, blog post, FAQ on/off) and checks the required properties. Not run through Google's Rich Results Test (needs a public URL) |
| Sitemap index | Met | `SitemapTest`: index, children per type and language, alternates, lastmod, exclusions, chunking, regeneration, schedule |
| Hreflang | Met | `CanonicalRulesTest` and `SitemapTest` (en, ar, x-default; query handling) |
| Redirects, 404 and 410 | Met | `RedirectRuntimeTest` (14 cases) |
| Canonical rules tested | Met | `CanonicalRulesTest` (17 cases incl. datasets) |
| Lighthouse run recorded, not claimed | **Not run** | Lighthouse is not installed and no packages may be added. A manual headless-Chrome check is recorded below instead; these are not Lighthouse scores |
| GA4 and GTM settings | Met | Admin analytics group applied; `AnalyticsTest` (config only when set, CSP, consent, events, validation, verification tag) |
| Search Console OAuth and reporting, mocked | Met, awaiting credentials | `SearchConsoleTest` (11 cases, `Http::fake`) |
| The browser never sends `purchase` | Met | `AnalyticsTest`: no `purchase` literal in any JS or Blade file, the JS allow-list, server refusal, and an end-to-end paid order (product, bag, checkout, return, order pages) with no `purchase` on any page while the server sends exactly one Measurement Protocol `purchase` with the browser's client id |

### Lighthouse and performance check

Lighthouse: **not run** (no Lighthouse CLI on this machine; installing packages is not allowed). Instead, a manual check with the installed Google Chrome 157 headless over the DevTools protocol (pipe, no packages): mobile viewport 412x915 at DPR 2.625, 4x CPU slowdown, 150 ms RTT and 1.6 Mbps down, cache disabled, `php artisan serve` on the production Vite build with preview data in the testing database (8 products), analytics not configured. Measured (ms):

| Page | FCP | LCP | LCP element | CLS | Requests / KB | Console errors |
|---|---|---|---|---|---|---|
| `/` | 2340 | 2340 | hero `img[fetchpriority=high]` | 0 | 22 / 462 | none |
| `/ar` | 2332 | 2332 | hero `img[fetchpriority=high]` | 0 | 21 / 453 | none |
| `/products/steam-wallet-20` | 1772 | 1772 | price `p` (no product image in preview data) | 0 | 22 / 347 | none |
| `/ar/products/steam-wallet-20` | 1800 | 1800 | price `p` | 0 | 22 / 352 | none |
| `/gift-cards` | 1828 | 1828 | banner `img[fetchpriority=high]` | 0 | 21 / 355 | none |
| `/ar/gift-cards` | 1872 | 1872 | banner `img[fetchpriority=high]` | 0 | 21 / 360 | none |

Every page: 4 font preloads, no image without width and height, one JSON-LD script. With a GTM id configured (banner shown, tag not loaded before consent) the English product page measured LCP 2296 ms, CLS 0, no CSP violations. `php artisan serve` is single-threaded; numbers are indicative only. Finding: the home page and listings mark 2 to 3 images `fetchpriority=high` (hero slides or banner plus card art); only the LCP image should be (open item).

### Tests and results

- New: 77 cases in `tests/Feature/Seo` (StructuredData 11, CanonicalRules 17, RedirectRuntime 14, Sitemap 7, Analytics 10, MeasurementProtocol 7, SearchConsole 11).
- Full suite: `php artisan test --compact` (sequential, MySQL) **2835 passed, 0 failed, 20772 assertions, about 201 s**. `vendor/bin/pint --dirty --format agent`: passed. `npm run build`: passed.
- No existing test changed or weakened. Two failures in the first full run were fixed in code: the architecture rule (Seo used a Catalog repository; now through `StorefrontCatalogService`) and app payment return links with upper-case ids (now excluded: prefixes `app` and `checkout`, and case normalisation only redirects to storefront page routes).

### Deviations

- Sitemaps are plain XML on the `local` disk, served by Laravel (strategy said static gzip files); the web server or CDN can compress. Child names include the language (`products-en-1.xml`).
- Robots also disallows `/orders` (order result pages are personal).
- Hreflang alternates are emitted only on indexable pages that are their own canonical.
- Listings with no products are `noindex,follow` (thin content) and are left out of the sitemap.
- Inactive-but-returning products still 404 (the storefront detail only shows active products); the strategy's "200 with OutOfStock" needs a product-page change (open item). A soft-deleted product is 410; "301 to the platform when a replacement exists" is done with an admin redirect (no replacement field in the schema).

### Outstanding requirements

- Credentials: GA4 measurement id and Measurement Protocol secret, GTM container, Search Console OAuth client and property; then verify against `/debug/mp/collect` and a live OAuth consent (M20). Register the redirect URI shown on Settings > Analytics.
- Server `refund` event (no row is written yet); browser `view_item_list`, `select_item`, `remove_from_cart`, `add_payment_info`.
- Lighthouse run when Lighthouse is available (M20 requires recorded results).
- Only the LCP image should carry `fetchpriority=high` on home and listings.
- Inactive products at 200 with OutOfStock (strategy section 7).
- Rich Results Test on a public staging URL.
- `seo_templates` have no admin screen (rows can be seeded); `not_found_logs` has no admin list yet.

### Design changes made

- `API_DOCUMENTATION.md` section 5g; `INTEGRATIONS.md` sections 6.1 and 7.1 (built, awaiting credentials); `SEO_STRATEGY.md` status line; integration status table above.

### Next milestone

M19 (full test pass), or the app milestones per the build order.


---

## M19: Full test pass

Status: **Complete with open defects** (2026-11-12, not committed). Completed by the M19 QA pass, working alone (owner rule: no sub-agents). The full suites were not re-run (owner rule: tests run once at the end).

### Summary of completed work

- The single final run of the automated suites (owner rule 2026-11-11) is recorded: backend `php artisan test --compact` **2,890 passed, 0 failed** (MySQL `gaming-store_testing`, commit `5e340e8`); app `flutter test` and `bash tool/ci.sh` **1,660 passed, 0 failed** (commit `2c7e2e9`).
- `docs/TEST_COVERAGE.md`: every Phase 15 item and the Step 12 testing items mapped to the backend and app test files and test names that cover them, with a status, plus what the suites cannot prove.
- `docs/MANUAL_TEST_CHECKLIST.md`: numbered storefront (EN and AR) and admin steps with expected results, executed in headless Chrome against preview data in the testing database, with the results table, defects and observations.
- Two defects: D1 fixed during the run, D2 fixed after it with the owner's approval.

### Files created

- `docs/TEST_COVERAGE.md`, `docs/MANUAL_TEST_CHECKLIST.md`.

### Files changed

- `backend/resources/js/modules/cart.js`: D1 fix, the header bag badge follows quantity changes and removals on the bag page (it stayed stale until a reload). Assets rebuilt with `npm run build`.
- `backend/tests/Feature/Storefront/CheckoutPagesTest.php`: regression test "returns the cart lines on quantity change and remove, and the bag module updates the header badge from them".
- `docs/PROGRESS.md` (this section and the status board).

### Migrations

None.

### Acceptance

| Item | Result | Evidence |
|---|---|---|
| Entire Pest suite green on MySQL | Met | Final run 2,890 passed, 0 failed (`5e340e8`) |
| MCP server suite | Met | `cd mcp-server && npm test`: 34 passed, 0 failed, re-run by the PM after M19 (mocked Laravel API; a live start against Laravel is M20) |
| Coverage for each Phase 15 item mapped in a table | Met | `TEST_COVERAGE.md`: of 28 counted items, 15 Covered, 9 Covered with mocks (live sandbox verification is M20), 3 Partially covered, 1 Not covered here |
| Manual test checklist completed for storefront and admin | Met (D2 fixed after the run) | `MANUAL_TEST_CHECKLIST.md`: 70 steps, 68 PASS, 1 FAIL (A22, defect D2), 1 BLOCKED (A07b) |

### Tests and results

- Command: none of the full suites re-run. One file run alone after the D1 fix: `php artisan test --compact tests/Feature/Storefront/CheckoutPagesTest.php` **14 passed** (118 assertions). `vendor/bin/pint --dirty --format agent`: passed. `npm run build`: passed.
- Note: that file run rebuilt `gaming-store_testing` with `migrate:fresh` (as every test run does), so the preview data was seeded again afterwards; the manual results are all from the reseeded data.
- Manual run: 2026-11-12, `php artisan serve` on 127.0.0.1:8123 with environment overrides only (testing database, isolated `LARAVEL_STORAGE_PATH`, database cache and sessions, sync queue, log mail, outbox and FCM off, outbound HTTP to a closed local proxy), Google Chrome 157 headless over the DevTools protocol with external hosts unresolvable. `backend/.env` and the app database `gaming-store` were not read or touched. The server and Chrome were stopped at the end.
- Not tested, and why: live provider calls (no credentials; M20); the on-device app suite (no emulator or device); iOS (not built here); Lighthouse (not installed); the n8n runtime (not installed); the MCP server suite (`npm test`, last run at M16, not part of the final run).

### Defects

| Id | Status | Summary |
|---|---|---|
| D1 | Fixed (not committed) | Bag page: the header bag badge did not follow quantity changes or removals until a reload |
| D2 | **Fixed** | **Fixed 2026-11-12** (owner approved): the staff sign-in and two-step pages use a page limiter `admin-auth-pages` (60 per minute per IP); attempts stay limited on `POST /login` (`auth`, 5 per minute per email and IP) and `POST /two-factor-challenge` (`otp`). Regression tests in `tests/Feature/Admin/AdminAccessTest.php`. Original finding: `GET /admin/login` shares the 5-per-minute `auth` limiter by IP, so a sixth load of the staff sign-in page within a minute shows 429. Suggested: throttle only the sign-in POST with `auth`. Left for an owner or security decision |

### Coverage gaps found

- No test reconciles a PayPal payment (`payments:reconcile-open` or `reconcile-daily`); Stripe, Paymob refunds and the wallet are covered.
- The admin refund from the order page could not be exercised in the browser on the preview data (A07b); it is covered by `AdminOrderPagesTest` and `RefundTest`.
- The MCP server suite passed its re-run (34/34, mocked Laravel API); the server has never run against a live Laravel instance.

### Outstanding requirements (M20)

- Sandbox verification of every external integration, resolving each `UNVERIFIED` item in `INTEGRATIONS.md`: Stripe (and the BD1 merchant entity), PayPal, Paymob, Reloadly, Open Exchange Rates, GA4 Measurement Protocol, Search Console OAuth, FCM, and the n8n workflows on a real n8n with the BD9 alert channel.
- Run the on-device suite (`integration_test` journeys on an emulator; frame timing on a physical device) and an iOS build.
- Lighthouse run recorded (Performance, SEO, Accessibility), not claimed.
- Re-run the MCP server suite and start the server against a running Laravel.
- Add the PayPal reconciliation test.
- Production checks from `SECURITY.md` section 15: Supervisor workers, scheduler, Redis, Nginx and SSL, backups with a tested restore, real mail delivery, GeoIP behind Cloudflare.

### Next milestone

M20 (production readiness), which needs credentials, a server and developer accounts.
