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
| M7 | Orders and checkout core | Complete (place-order and coupons fail closed until M10 and M11 bind their contracts) |
| M8 | Supplier engine and Reloadly | Complete (Reloadly mocked only, awaiting sandbox verification) |
| M9 | Fulfilment and digital codes | Complete (reveal pages and account order list are M13) |
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
| Reloadly Gift Cards | Built (`ReloadlyGiftCardAdapter`) | Yes (`Http::fake` only) | **Awaiting sandbox verification**; no credentials; never called live | No |
| Stripe | Not built | No | Awaiting credentials | No |
| PayPal | Not built | No | Awaiting credentials | No |
| Paymob | Not built | No | Awaiting credentials | No |
| Open Exchange Rates | Built | Yes (`Http::fake` only) | Awaiting credentials; OXR has no sandbox | No |
| GA4 Measurement Protocol | Not built | No | Awaiting credentials | No |
| Google Search Console | Not built | No | Awaiting credentials | No |
| n8n | Not built | No | Not installed on this machine | No |

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
