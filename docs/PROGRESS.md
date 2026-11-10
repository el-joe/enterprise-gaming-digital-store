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
