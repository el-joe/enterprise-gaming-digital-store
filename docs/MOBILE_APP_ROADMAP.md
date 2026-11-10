# Mobile App Roadmap (boxcards.shop customer app)

Status: plan (2026-11-10). M-A1 (Flutter foundation), M-A2 (backend mobile authentication, gap G1 closed) and M-A3 (backend stateless storefront, checkout and locale, gaps G2 and G3 closed) are done; every later milestone is not started. Flutter project: `application/` (Flutter 3.47.9, Dart SDK ^3.13, package `application`, Android `applicationId` `shop.boxcards.app` as proposed in open decision 2, still changeable before the first store upload). Backend facts below were checked against the code on this date; file paths are given as evidence.

Related documents: `API_DOCUMENTATION.md` (contract), `PROJECT_ARCHITECTURE.md` (ADRs), `SECURITY.md`, `DESIGN_TOKENS.md`, `IMPLEMENTATION_ROADMAP.md` (BD1 to BD9, brand decision), `INTEGRATIONS.md` (payment providers).

---

## 1. Team, roles and working rules

| Role | Owns |
|---|---|
| Lead Flutter architect (LFA) | Architecture, `core/`, networking, security, CI scripts, code review of every Flutter milestone, release configuration |
| Senior Flutter developer (SFD) | Data layers, feature UI, widget and integration tests, accessibility and performance pass |
| Backend lead (BEL) | Every backend gap in section 4.2 (Laravel: Services + Repositories where query logic exists + readonly DTOs + backed enums + Form Requests + API Resources + Pest tests; no Livewire), contract fixtures, `API_DOCUMENTATION.md` updates |
| PM/BA (PM) | Scope, acceptance criteria, Figma brief and review, owner approval gate, store listing, open decisions, `PROGRESS.md` |

Working rules:

1. **One commit per milestone**, message `M-Ax: <title>`, with the attribution lines required by the repository. No commit mixes two milestones.
2. A milestone is complete only when, on the milestone commit:
   - `cd application && flutter analyze` reports no issues;
   - `cd application && flutter test` passes;
   - for any backend change, `cd backend && php artisan test --compact` passes and `vendor/bin/pint --dirty --format agent` was run.
3. No fake integrations, no placeholder images, no `TODO` left in code that is reported complete, no invented API capability. A missing endpoint is a backend task (section 4.2), never a client-side workaround.
4. Exact package versions are resolved with `flutter pub add <package>` at implementation time and committed in `pubspec.lock`. This document does not name versions.
5. `API_DOCUMENTATION.md` and `PROGRESS.md` are updated in the same commit as the backend change that alters the contract.
6. Money is never a `double`, anywhere (models, arithmetic, formatting, analytics).
7. The client never marks an order paid. After any payment step it calls `verify-payment` or `status`; the server is the source of truth (architecture 9.1).
8. No feature UI is built before the owner approves the app designs (M-A9).

---

## 2. Architecture

Feature-first clean architecture. Dependencies point inwards: `presentation -> domain <- data`. `domain` is pure Dart (no Flutter, no Dio, no JSON). `data` maps DTOs to entities. `core` and `shared` hold cross-cutting code only; features never import each other's `data` or `presentation` (a feature uses another feature's `domain` contract through a provider).

### 2.1 Directory tree

```
application/
├── lib/
│   ├── main_dev.dart                      # flavor entry points; each calls bootstrap(AppEnv)
│   ├── main_staging.dart
│   ├── main_prod.dart
│   ├── bootstrap.dart                     # zone guard, error reporting hook, ProviderScope
│   ├── app.dart                           # MaterialApp.router, theme, locale, directionality
│   ├── core/
│   │   ├── config/
│   │   │   ├── app_env.dart               # Flavor enum, base URL, app link host (from --dart-define-from-file)
│   │   │   ├── env_dev.json               # non-secret values only (committed)
│   │   │   ├── env_staging.json
│   │   │   ├── env_prod.json
│   │   │   └── remote_config.dart         # model of GET /app/config (min version, maintenance, keys)
│   │   ├── network/
│   │   │   ├── dio_client.dart            # Dio factory, timeouts, base options
│   │   │   ├── api_envelope.dart          # { success, message, data, meta } + PageMeta
│   │   │   ├── api_error.dart             # { success:false, message, code, errors, request_id }
│   │   │   ├── failure.dart               # sealed Failure hierarchy (2.3)
│   │   │   ├── error_mapper.dart          # DioException + envelope -> Failure
│   │   │   ├── result.dart                # Result<T> = Ok | Err(Failure)
│   │   │   └── interceptors/
│   │   │       ├── auth_interceptor.dart        # Bearer token, single-flight refresh on 401
│   │   │       ├── locale_interceptor.dart      # Accept-Language: en | ar
│   │   │       ├── currency_interceptor.dart    # X-Currency (gap G2)
│   │   │       ├── cart_token_interceptor.dart  # X-Cart-Token for guests (gap G2)
│   │   │       ├── idempotency_interceptor.dart # Idempotency-Key on place-order, reused on retry
│   │   │       ├── request_id_interceptor.dart  # X-Request-Id (ULID) per request
│   │   │       ├── retry_interceptor.dart       # GET only + place-order with same key; honours Retry-After
│   │   │       └── redacting_log_interceptor.dart # debug builds only; redacts (section 6.5)
│   │   ├── storage/
│   │   │   ├── secure_store.dart          # flutter_secure_storage wrapper (tokens, cart token, guest order tokens)
│   │   │   └── preferences_store.dart     # non-sensitive prefs: locale, currency, onboarding flag
│   │   ├── router/
│   │   │   ├── app_router.dart            # go_router config, ShellRoute with bottom navigation
│   │   │   ├── routes.dart                # typed route names and paths
│   │   │   ├── guards.dart                # auth, verified email, force-update, maintenance redirects
│   │   │   └── deep_links.dart            # app_links -> route mapping (payment return, guest access, verify, reset)
│   │   ├── theme/
│   │   │   ├── tokens/
│   │   │   │   ├── color_tokens.dart      # ink/volt/ember/ultra/aqua/flux/sun + status (DESIGN_TOKENS.md)
│   │   │   │   ├── semantic_colors.dart   # surface-*, line-*, content-*, accent-* (Dark mode)
│   │   │   │   ├── spacing.dart           # 4px base scale
│   │   │   │   ├── radii.dart             # xs..key, KeyCutBorder (directional, mirrors in RTL)
│   │   │   │   ├── typography.dart        # Changa / Readex Pro / IBM Plex Mono scale, Arabic rules
│   │   │   │   ├── elevation.dart         # shadow-card/raised/overlay, glow-volt/ember
│   │   │   │   └── motion.dart            # 120/200/360 ms, cubic-bezier(.2,.8,.2,1), reduced motion
│   │   │   ├── app_theme.dart             # ThemeData (dark first) + ThemeExtensions
│   │   │   └── theme_extensions.dart
│   │   ├── l10n/
│   │   │   ├── arb/app_en.arb
│   │   │   ├── arb/app_ar.arb
│   │   │   └── l10n.dart                  # gen-l10n output import, context.l10n
│   │   ├── money/
│   │   │   ├── money.dart                 # Money(amount: Decimal, currency) ; fromJson rejects numbers
│   │   │   ├── currency_info.dart         # code, symbol, exponent (from /storefront/currencies)
│   │   │   └── money_formatter.dart       # string-based, locale-aware digits, never via double
│   │   ├── security/
│   │   │   ├── secure_screen.dart         # FLAG_SECURE / iOS capture obscuring for reveal screen
│   │   │   ├── clipboard_guard.dart       # sensitive copy + timed clear
│   │   │   └── device_integrity.dart      # root/jailbreak signal (warn only unless owner decides)
│   │   └── utils/
│   │       ├── ulid.dart                  # idempotency and request ids
│   │       ├── debouncer.dart
│   │       ├── date_format.dart           # locale-aware, server UTC -> local
│   │       └── validators.dart            # email, password, OTP format (server stays authoritative)
│   ├── features/
│   │   ├── app_config/                    # GET /app/config, force update, maintenance (gap G9)
│   │   ├── auth/                          # login, 2FA challenge, register, forgot password, token lifecycle
│   │   ├── home/                          # sections, banners (gap G7)
│   │   ├── catalog/                       # listing, filters, platforms, categories
│   │   ├── search/                        # suggest, popular, local history
│   │   ├── product/                       # detail, variants, delivery fields, product reviews
│   │   ├── cart/                          # cart, items, coupon
│   │   ├── checkout/                      # countries, validate, payment options, place order, guest OTP
│   │   ├── payments/                      # Stripe sheet, PayPal and Paymob hosted flows, verify/capture, polling
│   │   ├── orders/                        # history, detail, units, status
│   │   ├── code_reveal/                   # confirm password, reveal (account and guest), secure display
│   │   ├── wallet/                        # balance, statement (read only, BD3)
│   │   ├── reviews/                       # my reviews, eligible items, submit
│   │   ├── support/                       # tickets, messages, refund requests
│   │   ├── notifications/                 # inbox, preferences, push registration
│   │   ├── account/                       # profile, locale, currency, sessions/devices
│   │   └── security/                      # 2FA enrol/confirm/disable, recovery codes, change password
│   │       # every feature folder has the same shape:
│   │       # <feature>/
│   │       # ├── data/
│   │       # │   ├── datasources/<feature>_remote_data_source.dart
│   │       # │   ├── models/                 # freezed + json_serializable DTOs mirroring API Resources
│   │       # │   └── repositories/<feature>_repository_impl.dart
│   │       # ├── domain/
│   │       # │   ├── entities/               # freezed, no JSON
│   │       # │   ├── repositories/<feature>_repository.dart   # abstract contract
│   │       # │   └── usecases/               # only where logic spans repositories (place order, reveal)
│   │       # └── presentation/
│   │       #     ├── controllers/            # Riverpod Notifier/AsyncNotifier (generated)
│   │       #     ├── pages/
│   │       #     └── widgets/
│   └── shared/
│       └── widgets/
│           ├── app_button.dart            # primary (volt), secondary, ghost; key-cut shape
│           ├── app_text_field.dart        # server field-error binding (errors map dot keys)
│           ├── money_text.dart            # LTR-isolated amount inside RTL text
│           ├── mono_text.dart             # SKUs, codes: always LTR, IBM Plex Mono
│           ├── product_card.dart
│           ├── network_image_box.dart     # cached_network_image + sized error state (no placeholder art)
│           ├── async_value_view.dart      # loading / error(Failure) / data
│           ├── empty_state.dart
│           ├── error_view.dart            # message + request_id
│           ├── otp_input.dart
│           ├── status_chip.dart           # order/payment/ticket status
│           ├── paginated_list.dart        # meta-driven infinite scroll
│           └── brand_logo.dart            # logo-master / mark-master assets
├── assets/
│   ├── brand/logo-master.png              # copied from backend/resources/assets/images/brand/
│   ├── brand/mark-master.png
│   └── fonts/                             # Changa, Readex Pro, IBM Plex Mono (bundled, OFL)
├── test/
│   ├── fixtures/api/                      # recorded JSON exported by backend contract tests (M-A7)
│   ├── core/                              # money, error mapper, interceptors, router guards
│   ├── features/<feature>/                # data (fixtures), domain, controllers, widgets
│   └── helpers/                           # fixture loader, fake Dio adapter, pumpApp(locale, rtl)
├── integration_test/                      # end-to-end flows against a dev backend (M-A19)
├── tool/
│   ├── ci.sh                              # format check, analyze, test, coverage
│   └── sync_fixtures.sh                   # copies backend contract fixtures into test/fixtures/api
└── l10n.yaml
```

### 2.2 Key conventions

- **Flavors**: `dev`, `staging`, `prod`. Android `productFlavors` (`applicationIdSuffix` `.dev` / `.staging`), iOS schemes documented for later (no macOS here). Values come from `--dart-define-from-file=lib/core/config/env_<flavor>.json`; these files hold only non-secret values (base URL, app link host). Provider publishable keys come from the server (`GET /app/config`), not from the build.
- **Envelope**: every response is decoded through `ApiEnvelope<T>`; `meta` pagination (`current_page`, `per_page`, `total`, `last_page`) feeds `PageMeta`. Reveal responses are decoded in memory only.
- **Money** (`core/money`): `Money { Decimal amount; String currency; String? formatted }`. `fromJson` accepts only a string `amount` (a JSON number is a decode error, mirroring the backend's 422 for numbers). Backend sends both `Money::jsonSerialize` (`{amount, currency}`, 6 dp) and `CurrencyConverter::present` (`{amount, currency, formatted}`, currency exponent). Arithmetic is only for display grouping; totals always come from the server (cart quote, payment options). `expected_total` for place-order is sent back exactly as the string the server returned.
- **RTL**: `Directionality` from locale; only `EdgeInsetsDirectional`, `AlignmentDirectional`, `BorderRadiusDirectional`. Logos, amounts, SKUs and codes are never mirrored (`Directionality(textDirection: TextDirection.ltr)` islands). Arabic text styles drop letter spacing and uppercase and add about 15% line height from 20 px up.

### 2.3 Typed failures (error mapping)

| Source | Failure |
|---|---|
| No connection, DNS, TLS | `NetworkFailure` |
| Timeout | `TimeoutFailure` (retryable) |
| 401 `unauthenticated` | `UnauthenticatedFailure` -> refresh once, else sign out to login |
| 403 `two_factor_required` | `TwoFactorRequiredFailure` |
| 403 `email_unverified` | `EmailUnverifiedFailure` |
| 403 `reveal_denied`, `forbidden` | `ForbiddenFailure(code)` (generic message) |
| 404 | `NotFoundFailure` |
| 409 (`idempotency_in_progress`, `idempotency_mismatch`, `order_not_payable`, `review_exists`, `ticket_closed`, `refund_request_open`, ...) | `ConflictFailure(code)` |
| 423 `recent_auth_required` | `RecentAuthRequiredFailure` -> confirm-password sheet, then retry |
| 422 `validation_failed` | `ValidationFailure(fieldErrors)` bound to fields by dot key |
| 422 domain code (`price_changed`, `coupon_invalid`, `item_unavailable`, `gateway_not_eligible`, `otp_invalid`, `rate_stale`, ...) | `DomainFailure(code, message, errors)` |
| 429 | `RateLimitedFailure(retryAfter)` |
| 503 `maintenance` | `MaintenanceFailure` -> maintenance screen |
| 5xx | `ServerFailure(requestId)` (request id shown, no body) |

---

## 3. State management and packages

**Choice: Riverpod with code generation (`flutter_riverpod`, `riverpod_annotation`, `riverpod_generator`).**

Why Riverpod over Bloc for this app:
- Most screens are "fetch, cache, refresh, paginate" over a REST API; `AsyncNotifier` and `AsyncValue` model this with far less boilerplate than events and states per screen.
- Providers are also the dependency-injection graph (Dio, repositories, secure store), so there is no second DI container, and tests override any provider directly.
- Family providers fit parameterised resources (product by slug, order by public id, ticket by id) and auto-dispose clears sensitive state (code reveal) when a screen leaves.
- Cross-cutting reactions (currency or locale change invalidates catalog, cart and payment options; sign-out invalidates every account provider) are one `ref.invalidate` per dependency instead of bloc-to-bloc wiring.
- Code generation keeps provider signatures type-safe; Bloc's explicit event log is less valuable here because the server owns the financial state machine.

| Concern | Package |
|---|---|
| State and DI | `flutter_riverpod`, `riverpod_annotation`, `riverpod_generator`, `riverpod_lint`, `custom_lint` |
| Routing | `go_router` (typed routes via `go_router_builder`) |
| HTTP | `dio` |
| Models | `freezed`, `freezed_annotation`, `json_serializable`, `json_annotation`, `build_runner` |
| Decimal money | `decimal` |
| Secure storage | `flutter_secure_storage` |
| Preferences | `shared_preferences` (non-sensitive only) |
| i18n | `flutter_localizations` (SDK), `intl`, `gen-l10n` (`l10n.yaml`) |
| Images | `cached_network_image` (catalog media only; never for anything code-related) |
| Hosted payments | `flutter_web_auth_2` (ASWebAuthenticationSession / Custom Tabs with callback) with `url_launcher` as fallback for external links |
| Stripe | `flutter_stripe` (PaymentSheet, matches `client_secret` from the adapter) |
| Deep links | `app_links` |
| Screen security | a maintained FLAG_SECURE / capture-protection plugin chosen at M-A1, or a 30-line platform channel if none is maintained |
| Device info | `package_info_plus` (version for force update), `device_info_plus` (device name for tokens) |
| Push (M-A18) | `firebase_core`, `firebase_messaging` (only if the owner approves FCM) |
| Lints | `flutter_lints` (present) plus stricter rules in `analysis_options.yaml` |
| Tests | `flutter_test`, `mocktail`, `integration_test` (SDK), `http_mock_adapter` or a fake `HttpClientAdapter`, golden tests with `alchemist` or built-in `matchesGoldenFile` (both locales) |

Versions are resolved with `flutter pub add` during M-A1 and later milestones; none are pinned here.

---

## 4. API contract mapping

Legend: paths are under `/api/v1` unless they start with `/` at root (session auth routes in `routes/auth.php`). **Gx** refers to a backend gap in 4.2; screens marked with a gap cannot ship until it closes.

### 4.1 Screen to endpoint map

| Feature / screen | Existing endpoints (verified with `php artisan route:list`) | Needs |
|---|---|---|
| Splash, force update, maintenance | none | G9 `GET /app/config` |
| Login | `POST /login` (session only) | G1 token login |
| 2FA challenge | `POST /two-factor-challenge` (session pending state) | G1 token challenge |
| Register | `POST /register` (signs in by session) | G1 returns tokens |
| Forgot / reset password | `POST /forgot-password`, `POST /reset-password` | G10 reset link opens app or a web page |
| Email verification | `POST /email/verification-notification` (session) | G1 bearer support, G10 link without session |
| Home | none for storefront (admin only: `homepage-sections`, `banners`) | G7 `GET /storefront/home` |
| Platforms, categories | none for storefront | G7 |
| Catalog listing + filters | `GET /storefront/catalog/products` (`category`, `platform`, `region`, `delivery_type`, `min_price`, `max_price`, `sort`, `page`, `per_page`) | G7 absolute image URL, G3 translated names |
| Search | `GET /storefront/search/suggest`, `GET /storefront/search/popular` | none (history is local) |
| Product detail | `GET /storefront/catalog/products/{slug}/variants`, `GET /storefront/catalog/products/{slug}/reviews` | G7 `GET /storefront/catalog/products/{slug}` (description, images, instructions, delivery fields, terms) |
| Currency switcher | `GET /storefront/currencies`, `POST /storefront/currency` (cookie) | G2 `X-Currency` header |
| Cart | `GET/POST /storefront/cart`, `/cart/items`, `PATCH/DELETE /cart/items/{id}`, `POST/DELETE /cart/coupon` | G2 header cart token, no CSRF |
| Checkout: country | `POST /checkout/validate` needs `country` | G7 `GET /storefront/countries` |
| Checkout: payment options | `GET /checkout/payment-options?country=&email=` | G2 |
| Checkout: place order | `POST /checkout/place-order` + `Idempotency-Key` | G2, G8 `client=app` return URLs |
| Payment: Stripe | `next.type=stripe` + `client_secret`; `POST /checkout/orders/{id}/verify-payment` | G9 publishable key, G8 Stripe return URL |
| Payment: PayPal | `next.type=paypal` + `approve_url`; `POST /checkout/orders/{id}/paypal/capture` | G8 PayPal return/cancel URLs |
| Payment: Paymob | `next.type=redirect` + `redirect_url`; `verify-payment` | G8 app return URL |
| Payment: wallet | `next.type=wallet` (already paid) | G2 |
| Retry payment | `POST /checkout/orders/{id}/retry-payment` | G2 |
| Order status polling | `GET /checkout/orders/{id}/status` | G2 (session-bound today) |
| Guest OTP | `POST /checkout/guest/otp/send`, `POST /checkout/guest/otp/verify` | G2 guest order token |
| Guest code access / reveal | `GET /checkout/guest/orders/{id}/access` (signed), `POST /checkout/guest/orders/{order}/units/{unit}/reveal` | G2, G4 guest order detail with units |
| Orders history / detail | none (planned in API doc section 4) | G4 |
| Code reveal (account) | `POST /account/confirm-password`, `POST /account/orders/{order}/units/{unit}/reveal` | G1 bearer, G2 token-scoped recent auth, G4 unit ids |
| Wallet | `GET /account/wallet`, `GET /account/wallet/transactions` | G1 |
| Reviews | `GET /account/reviews`, `GET /account/reviews/eligible`, `POST /account/reviews` | G1 |
| Support tickets | `GET/POST /account/tickets`, `GET /account/tickets/{id}`, `POST /account/tickets/{id}/messages` | G1 |
| Refund requests | `GET/POST /account/refund-requests` | G1 |
| Profile | none | G5 |
| Security: 2FA | `POST/DELETE /account/security/two-factor`, `POST /account/security/two-factor/confirm` | G1 (confirm sets a session key today) |
| Security: devices | none | G5 (token list and revoke) |
| Notifications inbox and preferences | none (table `notifications`, `notification_preferences` exist) | G6 |
| Push | none | G6 device registration + provider (M-A18) |

### 4.2 Backend gaps (verified)

| ID | Gap | Evidence |
|---|---|---|
| G1 | **Mobile token auth.** `User` has no `HasApiTokens` (only `ServiceAccount` does); the only guard is `web` (session); login, 2FA challenge and logout operate on the session; 2FA pending state lives in the session for 300 s. Needed: `POST /api/v1/mobile/auth/{register,login,two-factor-challenge,refresh,logout}`, `GET /api/v1/mobile/auth/me`, `device_name` required, short-lived access token (Sanctum, expiry, abilities `customer`) plus a rotating refresh token (hashed, single use, family revoked on reuse), revoke on logout, staff accounts refused, `auth` limiter kept, audit of new device sign-in. 2FA pending state as a signed, short-lived challenge token instead of the session. | `app/Domains/Identity/Models/User.php:38`, `app/Domains/Identity/Models/ServiceAccount.php:26`, `config/auth.php:40-45`, `config/sanctum.php` (`guard => ['web']`, `expiration => null`), `app/Domains/Identity/Services/AuthenticationService.php` (`login`, `completeTwoFactorChallenge`, `logout` take `Session`), `routes/auth.php` |
| G2 | **Closed by M-A3 (ADR-036, `API_DOCUMENTATION.md` 1c).** **Stateless storefront and checkout context.** Account routes use `web`+`auth` (session); checkout and cart routes are in the `web` group (CSRF); the guest cart is the encrypted `cart_token` cookie; display currency is the `currency` cookie; order visibility for guests is a session list (`OrderStatusReader::SESSION_KEY`); guest OTP and signed-link proofs are session keys; reveal recent-auth is the session key `auth.password_confirmed_at`; the `checkout` limiter keys on the session id. Needed for bearer clients: `auth:sanctum` on account and checkout routes (or a parallel `api/v1/mobile` group reusing the same services), `X-Cart-Token` and `X-Currency` headers, a per-order **guest order token** returned by place-order and OTP verify (hashed, scoped, expiring) used for status, verify, capture and guest reveal, recent-auth stored per access token, limiter keys by token or user. | `bootstrap/app.php` (`account` group, `routes/checkout.php` under `web`), `app/Http/Controllers/Storefront/Concerns/InteractsWithCart.php`, `app/Http/Controllers/Storefront/CheckoutController.php` (`session()->push`), `GuestOrderController.php`, `GuestOrderCodeController.php`, `app/Http/Controllers/Account/OrderCodeController.php:reveal`, `ConfirmPasswordController.php:15`, `app/Providers/AppServiceProvider.php:117-120` |
| G3 | **Closed by M-A3 (`SetLocaleFromRequest`, `ContentTranslator`, `lang/ar`).** **Locale.** No middleware sets the locale (`setLocale` and `Accept-Language` appear nowhere in `app/` or `bootstrap/`), so `app()->getLocale()` stored on orders is always the default and storefront resources return English base columns although `content_translations` exists (ADR-015). Needed: `Accept-Language` (`en`, `ar`) middleware for API groups and translated names/descriptions in storefront resources; error messages localized. | grep over `backend/app`, `backend/bootstrap`; `database/migrations/2026_11_10_040015_create_content_translations_table.php`; `CheckoutController::placeOrder` (`locale: app()->getLocale()`) |
| G4 | **Customer orders.** No `GET /account/orders` or `/account/orders/{public_id}`; the app cannot list orders or obtain unit public ids needed by reveal. Same for a guest order detail (units) after OTP. Needed: list, detail with items, units (status, `revealable`, `first_revealed_at`, never the code), totals as money. | `routes/account.php` (only reveal), `API_DOCUMENTATION.md` section 4 lists them as planned; `PROGRESS.md` M9 note "account order list is M13" |
| G5 | **Profile and devices.** No profile `GET/PATCH` (name, locale, display currency, country, phone), no change-password route, no session/device list or revoke. | `routes/account.php`; `API_DOCUMENTATION.md` section 4 (`/profile`, `/sessions` planned); `users.locale`, `users.display_currency` in `2026_11_10_020001_add_store_columns_to_users_table.php` |
| G6 | **Notifications and push.** Tables exist but no customer routes; `NotificationPreferenceChannel` has `mail`, `database`, `sms` only; no device-token table or push channel. Needed: `GET /account/notifications`, mark read, `GET/PATCH /account/notification-preferences`, and (M-A18) `POST/DELETE /account/devices` + a `push` channel through the chosen provider. Payloads carry identifiers and text only, never codes. | `database/migrations/2026_11_10_020005_create_notifications_table.php`, `..._020009_create_notification_preferences_table.php`, `app/Domains/Notifications/Enums/NotificationPreferenceChannel.php` |
| G7 | **Catalog discovery.** No storefront endpoints for product detail (only listing and variants), platforms, categories, home sections or banners (admin CRUD only), or countries (checkout requires an active ISO country). `ProductCardResource.image` returns a storage `path`, not a URL. Delivery field definitions (player ID etc.) are not exposed, yet `POST /cart/items` validates `delivery_values`. | `routes/api.php`, `app/Http/Resources/Storefront/ProductCardResource.php:35`, `VariantOfferResource.php`, `app/Http/Controllers/Admin/Cms/HomepageSectionController.php`, `BannerController.php`; `API_DOCUMENTATION.md` 3a (`country` active ISO code) |
| G8 | **Payment return to the app.** `payments.return_url` is one web URL for every client. PayPal `createPayment` sends no `return_url`/`cancel_url` (`experience_context`), so `approve_url` has no defined return for an app. Paymob `redirection_url` is the web return URL. Stripe confirms client side (fine for PaymentSheet) but redirect-based methods need a return URL. Needed: place-order and retry-payment accept `client: web|app`; for `app` the server builds the return URL from an allow-list (HTTPS app link on the store domain, for example `https://boxcards.shop/app/payments/return/{order}`), never from a client-supplied URL; PayPal gets `return_url` and `cancel_url`; plus `/.well-known/assetlinks.json` and `apple-app-site-association` served by Laravel. | `config/payments.php:31`, `app/Domains/Payments/Services/PaymentAttemptService.php:189-192`, `app/Domains/Payments/Adapters/PayPal/PayPalPaymentGateway.php:82-109`, `Paymob/PaymobPaymentGateway.php:86-87` |
| G9 | **App config.** No endpoint for minimum supported app version, maintenance flag, feature flags, Stripe publishable key (no `publishable` key exists anywhere in `app/` or `config/`), media base URL, support and legal URLs. Needed: `GET /api/v1/app/config` (public, cached, no secrets). | grep for `publishable` in `backend/app`, `backend/config` |
| G10 | **Email links for app users.** `GET /email/verify/{id}/{hash}` requires `auth` (session) plus `signed`; a user registered in the app who opens the link in a mail client has no session. `GET /reset-password/{token}` returns JSON until the M12 page exists. Needed: verification that relies on the signed URL alone (still bound to the user id and hash) with an app link or web page, and a reset page the app can open. | `routes/auth.php`, `app/Http/Controllers/Storefront/Auth/NewPasswordController.php:show` |
| G11 | **Contract fixtures.** No mechanism exports real resource JSON for client tests. Needed: Pest tests under `tests/Feature/MobileContract` that call each endpoint used by the app with factories and write normalized JSON to `docs/api-fixtures/` (timestamps and ids normalized), failing on drift. | `tests/Feature` has no contract suite |

Not a gap (exists and is used as-is): reviews, tickets, refund requests, wallet, 2FA enrolment, currencies, search, cart and checkout logic, payment options, verify and capture, guest OTP, reveal service and its audit. Wishlist (table `wishlist_items`, no routes) is in the web spec but not in the app scope; see open decisions.

---

## 5. Payments on mobile

Common rules for every provider:
- Place-order is sent with an `Idempotency-Key` (ULID) generated once per checkout attempt and stored with the pending checkout; a network retry or app restart re-sends the **same key and same body**, so the server replays (`Idempotency-Replayed: true`) instead of creating a second order. A changed body gets a new key.
- `expected_total` and `currency` are the strings the customer saw in the last validate / payment-options response.
- `meta.duplicate: true` (open order with the same contents) routes to that order's status screen instead of paying again.
- `next: null` on 201 or on a replay means no attempt was started: the app calls `retry-payment`.
- After any provider UI closes (success, cancel or crash), the app calls `POST verify-payment` (or `paypal/capture` for PayPal) and then polls `GET status` with backoff (2 s, 3 s, 5 s, then every 10 s up to 2 minutes, then a "still processing, we will notify you" state) until `is_final` or `payment_status` is `paid`. The app shows "paid" only when the server says so.
- On app restart with a pending order in secure storage, the app resumes at the status screen and verifies again.

| Provider | Adapter behaviour (verified) | Mobile flow |
|---|---|---|
| Stripe | `StripePaymentGateway::createPayment` creates a PaymentIntent with `automatic_payment_methods.enabled=true` and returns `{type: "stripe", client_secret}`. | `flutter_stripe` PaymentSheet initialised with the publishable key from `GET /app/config` (G9) and the `client_secret`; `returnURL` set to the app scheme for redirect methods (G8). On sheet completion or cancel: `verify-payment`, then poll. Availability depends on BD1 (merchant entity). |
| PayPal | `PayPalPaymentGateway::createPayment` creates an Orders v2 order (`intent=CAPTURE`) and returns `{type: "paypal", provider_order_id, approve_url}`; capture is a server call (`paypal/capture`), paid only on `COMPLETED`. | Open `approve_url` in an authentication session (`flutter_web_auth_2`) whose callback is the app link from G8. On return: `POST paypal/capture`, then poll status. On cancel: show retry options (`retry-payment`). Blocked until G8 adds `return_url`/`cancel_url`. PayPal never charges EGP, SAR or AED; the payment-options `notice` `charged_in_payment_currency` must be shown. |
| Paymob | `PaymobPaymentGateway::createPayment` creates a v1 intention (EGP only) and returns `{type: "redirect", redirect_url}` to Unified Checkout; `redirection_url` is the return URL. | Open `redirect_url` in an authentication session; Paymob redirects to the app return link (G8). The app ignores every query parameter Paymob appends (including `success`) and calls `verify-payment`, then polls. The hosted flow is used, not Paymob's native SDK, because the adapter is built for the intention + Unified Checkout flow. |
| Wallet | `WalletPaymentGateway` debits the ledger at placement; `next = {type: "wallet"}`; eligible only for a signed-in, verified, active customer whose balance covers the amount. | No provider UI: go straight to status polling. |

### Store policy risk (needs owner and legal review before store submission)

> **Risk, not a conclusion.** Apple App Store Review Guidelines (section 3.1, in-app purchase, including rules on digital content, gift cards and stored value) and Google Play's Payments policy restrict selling digital goods and content consumed in the app through payment methods other than the platforms' own billing, with exceptions (for example goods consumed outside the app, physical goods, person-to-person services, regional and "reader" or external-link programmes) whose scope changes over time and differs by country. This app sells third-party game gift cards and top-ups paid by Stripe, PayPal and Paymob. Whether these products fall inside or outside those rules, in each launch country, has **not** been determined by this team. Before any store submission the owner must obtain a legal/policy review that decides: (a) whether purchases may be offered in the app at all, (b) whether platform billing is required for some or all products, (c) whether an external purchase link or entitlement programme applies, and (d) the listing category and age rating. M-A20 cannot pass until that decision is recorded in `IMPLEMENTATION_ROADMAP.md`. If the review says purchases cannot be offered, the app can still ship browse, orders, code reveal and support, and the checkout feature is disabled by a `GET /app/config` flag rather than removed from the code.

---

## 6. Security

### 6.1 Tokens and storage
- Access and refresh tokens, the guest cart token and guest order tokens live only in `flutter_secure_storage` (iOS Keychain `first_unlock_this_device`, not synced to iCloud; Android Keystore-backed storage). Never in `shared_preferences`, files, logs or crash reports.
- Access tokens are short-lived; the auth interceptor refreshes once (single-flight, queued requests) on 401 and signs out when refresh fails or reuse is detected (G1).
- Logout calls the revoke endpoint, then wipes secure storage and invalidates every account provider. "Sign out of all devices" uses G5.
- Optional local biometric unlock (owner decision) only gates opening the app; it never replaces the server's recent-auth check for reveal.

### 6.2 Certificate pinning (optional)
Not recommended for launch. The API sits behind a TLS terminator whose certificates rotate; a stale pin bricks every installed app until users update. If the owner wants it: pin the SPKI hash of the issuing intermediate plus one backup key, never the leaf, with a kill switch in `GET /app/config` and a documented rotation runbook. Platform TLS validation (no custom trust, no cleartext: Android `usesCleartextTraffic=false` in staging and prod, iOS ATS defaults) is mandatory either way.

### 6.3 Code reveal screen
- Secure flag on the reveal route only: Android `FLAG_SECURE` (blocks screenshots, recording and the recents thumbnail); iOS cannot block screenshots, so the screen hides the code when capture is detected and when the app resigns active (app switcher snapshot). The limitation on iOS is stated in the release notes.
- Flow: tap Reveal -> if 423 `recent_auth_required`, password sheet -> `confirm-password` -> retry reveal once. The `code-reveal` limiter (10/hour) means the client never retries automatically.
- The code, PIN and redemption URL are held in a widget-scoped state that is cleared on dispose, on app background and after a timeout; never written to storage, Riverpod keep-alive caches, Dio caches, analytics, crash reports or logs. No HTTP cache is used for reveal (`Cache-Control: no-store` is also sent by the server).
- Copy uses the Android 13+ sensitive clipboard flag and clears the clipboard after 60 s where the platform allows.
- Re-viewing a code later is a new audited reveal call; the app keeps no copy.

### 6.4 Device integrity
Root/jailbreak detection is a signal, not a control (it is bypassable). Default plan: show a warning on the reveal screen and send a header flag the server can log; blocking is an owner decision. The server-side controls (owner checks, recent auth, rate limits, audit) remain the real protection.

### 6.5 No secrets, logging, obfuscation
- The app contains no secret: only base URLs and, at runtime, publishable provider values from `GET /app/config`. Provider secret keys stay in Laravel's encrypted settings.
- Release builds log nothing to the console. Debug logging goes through `redacting_log_interceptor`, which drops bodies of reveal, auth and payment responses and masks keys matching the backend's `RedactSensitiveLogData` list (`password`, `token`, `secret`, `authorization`, `code`, `pin`, `client_secret`, `redemption*`, `card*`, plus `refresh_token`, `Idempotency-Key`).
- Release Android builds: R8 minify and shrink resources; Flutter `--obfuscate --split-debug-info=build/symbols/<version>`; symbols archived privately for crash de-obfuscation, never in the repository.
- `android:allowBackup="false"` (or backup rules excluding secure storage); no exported components beyond the launcher and verified app links.

---

## 7. Milestones

Order: design-independent work first (M-A1 to M-A7), then design and the approval gate (M-A8, M-A9), then UI (M-A10 to M-A17), then push and release. Backend milestones may run in parallel with M-A1 and M-A7 as long as M-A7's data sources for a gap are only merged after that gap closes.

### M-A1: Flutter foundation
- **Status**: Done (2026-11-10). `flutter analyze` and `dart analyze` (riverpod_lint) clean, 119 tests passing (goldens in LTR and RTL), dev debug APK builds; details and deviations in `PROGRESS.md` (Mobile app, M-A1) and `application/README.md`.
- **Owner**: LFA (SFD supports).
- **Scope**: directory tree of 2.1; flavors (`dev`, `staging`, `prod`) with Android `productFlavors` and `env_*.json`; set `applicationId` per the owner's decision (open decision 2); packages of section 3 that the foundation needs; `analysis_options.yaml` strict rules and `riverpod_lint`; theme tokens transcribed from `DESIGN_TOKENS.md` (colours, semantic dark theme, spacing, radii incl. directional key-cut shape, type scale, effects, motion); bundled fonts; brand assets copied from `backend/resources/assets/images/brand/`; launcher icon from `mark-master.png`; gen-l10n with `app_en.arb` and `app_ar.arb` and RTL switching; Dio client, all interceptors, envelope, error mapper, `Result`, `Failure`; `Money`; secure store; go_router skeleton with guards and deep-link parser; `tool/ci.sh`.
- **Deliverables**: app boots per flavor to a token-styled shell (no feature screens); `tool/ci.sh`.
- **Acceptance**: `flutter analyze` clean; `flutter test` passes; `flutter build apk --flavor dev -t lib/main_dev.dart --dart-define-from-file=lib/core/config/env_dev.json` succeeds; switching to `ar` mirrors the shell; no hard-coded colour or text outside tokens and ARB.
- **Tests**: Money (`fromJson` rejects numbers, preserves 6 dp, formats by exponent without double, Arabic digits option); error mapper for every row of 2.3; auth interceptor single-flight refresh; idempotency interceptor reuses the key on retry; retry interceptor never retries non-idempotent POSTs; redaction; router guards; token round-trip in secure store (mocked); golden tests of tokens in LTR and RTL.

### M-A2: Backend mobile authentication (G1)
- **Status**: Done (2026-11-10, awaiting PM verification and commit). Routes under `/api/v1/mobile/auth` (register, login, two-factor-challenge, refresh, logout, logout-all, me), `mobile_refresh_tokens` and `mobile_login_challenges`, ADR-035, `API_DOCUMENTATION.md` section 1b; 40 new Pest tests, full backend suite 1888 passed. Details and deviations in `PROGRESS.md` (Mobile app, M-A2).
- **Owner**: BEL.
- **Scope**: `HasApiTokens` on `User`; `MobileAuthService` (Identity domain) reusing `AuthenticationService` checks and `TwoFactorService`; refresh-token model and migration (hashed, family id, `used_at`, `revoked_at`, `expires_at`, device name, last used); DTOs (`MobileLoginData`, `IssuedTokens`); enums (`TokenRevocationReason`); Form Requests; `MobileTokenResource`; routes under `/api/v1/mobile/auth` with `auth`/`otp` limiters; customer-only (staff refused); 2FA challenge token; audit of sign-in from a new device; Sanctum expiration for customer tokens.
- **Deliverables**: endpoints, migration, `API_DOCUMENTATION.md` section "Mobile auth", `SECURITY.md` update.
- **Acceptance**: login with and without 2FA, refresh rotation, refresh reuse revokes the family, logout revokes access + refresh, suspended user refused, staff refused, rate limits applied; session (web) auth unchanged.
- **Tests**: Pest feature tests per route and failure path; unit tests for rotation and reuse detection; regression run of existing auth tests.

### M-A3: Backend stateless storefront, checkout and locale (G2, G3)
- **Status**: Done (2026-11-10, awaiting PM verification and commit). Stateless twins of the cart, checkout and account routes under `/api/v1/mobile` (same route files and controllers), headers `X-Cart-Token`, `X-Currency`, `X-Guest-Order-Token` and `Accept-Language`, table `guest_order_tokens`, column `personal_access_tokens.password_confirmed_at`, ADR-036, `API_DOCUMENTATION.md` section 1c. Details, test counts and deviations in `PROGRESS.md` (Mobile app, M-A3).
- **Owner**: BEL.
- **Scope**: bearer support for `/api/v1/account/*` and checkout/cart routes without CSRF for token requests (session behaviour for the web unchanged); `X-Cart-Token` and `X-Currency` resolution in `InteractsWithCart` and `CatalogController`; guest order token issued by place-order and OTP verify, accepted by status, verify, capture, retry and guest reveal; recent-auth per access token for reveal and 2FA changes; limiter keys by token/user; `SetLocaleFromRequest` middleware (`en`, `ar`) and translated storefront resources via `content_translations`.
- **Deliverables**: middleware, services, migrations (guest order tokens), doc updates.
- **Acceptance**: a full guest and customer checkout runs with headers only (no cookies) in feature tests; another token or another guest token gets 404 on someone else's order; reveal still enforces owner, verified email, recent auth, paid order, delivered unit and audit; Arabic names returned with `Accept-Language: ar`.
- **Tests**: Pest: header cart lifecycle, cart adoption on sign-in, currency header, guest token scope and expiry, reveal with token-scoped recent auth, locale fallback; existing checkout tests stay green.

### M-A4: Backend catalog discovery and app config (G7, G9)
- **Owner**: BEL.
- **Scope**: `GET /storefront/home` (published homepage sections and banners), `/storefront/platforms`, `/storefront/categories`, `/storefront/catalog/products/{slug}` (description, gallery, instructions, terms, delivery field definitions, region list, rating), `/storefront/countries` (active), absolute media URLs in storefront resources, `GET /app/config` (min version per platform, maintenance, checkout enabled flag, Stripe publishable key from gateway settings, media base, support and legal URLs).
- **Acceptance**: query budgets in line with 2a (listing at most 10 queries cold); no cost, margin, supplier or secret field in any response; BD8 respected (platform logos only where rights are confirmed).
- **Tests**: Pest per endpoint, query-count tests, a test that `app/config` contains no secret key names.

### M-A5: Backend account orders, profile, devices, notifications, email links (G4, G5, G6 inbox, G10)
- **Owner**: BEL.
- **Scope**: `GET /account/orders`, `GET /account/orders/{public_id}` (items, units with reveal eligibility, never codes), guest order detail via guest order token; `GET/PATCH /account/profile`; `PUT /account/password`; `GET /account/devices`, `DELETE /account/devices/{id}` (tokens); `GET /account/notifications`, `POST /account/notifications/{id}/read`, `GET/PATCH /account/notification-preferences`; email verification by signed URL without session, plus an app-link or web landing; reset-password landing.
- **Acceptance**: other customers' records are 404; masked data only; pagination meta present.
- **Tests**: Pest object-level authorization tests per route; verification link test without session.

### M-A6: Backend mobile payment returns (G8)
- **Owner**: BEL (LFA reviews).
- **Scope**: `client` field on place-order and retry-payment; allow-listed app return URL per order; PayPal `experience_context.return_url`/`cancel_url`; Paymob `redirection_url` for app clients; Stripe return URL in `next` for app clients; `/.well-known/assetlinks.json` and `/.well-known/apple-app-site-association` from config (package name, SHA-256 cert fingerprints, team id: values supplied by the owner, not invented).
- **Acceptance**: a client cannot supply an arbitrary URL; web flow unchanged; mocked provider tests assert the URLs sent.
- **Tests**: Pest with `Http::fake` for each adapter; well-known files content tests. Sandbox verification remains open as recorded in `PROGRESS.md`.

### M-A7: Data layers and repositories (all features)
- **Owner**: SFD (BEL delivers G11 fixtures).
- **Scope**: G11 contract fixture export in Laravel; `tool/sync_fixtures.sh`; for every feature in 2.1: remote data source, freezed DTOs mirroring the API Resources exactly, entities, repository contract and implementation, use cases for place order (idempotency, duplicate, `next` handling), payment resolution (verify/capture then poll), reveal (recent-auth retry), token lifecycle; controllers' non-UI logic (Notifiers) for cart, checkout and polling.
- **Acceptance**: every endpoint in 4.1 has a data source method; DTOs parse every recorded fixture; no `double` in any model (enforced by a test that scans generated models for `double` money fields); no UI code.
- **Tests**: unit tests per repository against recorded fixtures (success and each documented error code); polling state machine tests with fake clock; place-order replay test; reveal never persisted (test that secure store and caches are untouched).

### M-A8: Figma mobile app design
- **Owner**: PM (LFA and SFD review feasibility).
- **Scope**: in the existing Figma file `JkUUSR3WGBYC9ewy2qRFQO`, a new page set "Mobile App" reusing the existing variable collections (`Primitives`, `Theme` Dark, `Layout`), text and effect styles and components; native mobile frames (390x844 base, 360 Android check) for: splash/force update/maintenance, onboarding (language, currency), login, 2FA, register, verify email, forgot password, home, platforms, category listing with filters, search, product detail with variants and delivery fields, cart, checkout (guest and customer, country, payment options with notices), payment in progress, result states (paid, processing, failed, expired), orders list, order detail, code reveal (with re-auth sheet and secure state), guest order access + OTP, wallet, reviews, support tickets and thread, refund request, notifications, profile, security/2FA/recovery codes, devices, settings; every frame in EN (LTR) and AR (RTL); empty, loading, error and offline states; dark-first; accessibility annotations (touch targets 48 dp, contrast, focus order).
- **Deliverables**: Figma pages, a screen list with node links appended to this document's M-A8 section, open questions for the owner.
- **Acceptance**: every screen in 4.1 has EN and AR frames and its states; tokens and components only (no detached styles); no placeholder imagery (BD8: plain-text platform tiles and empty `Logo Slot` where rights are unconfirmed; sample reviews flagged "Sample content").
- **Tests**: design review checklist signed by LFA (feasibility) and PM (scope).

### M-A9: Owner approval gate
- **Owner**: PM.
- **Scope**: present M-A8 to the owner; record approval or change requests with date in `PROGRESS.md`; iterate M-A8 until approved.
- **Acceptance**: written owner approval of the app designs. **No feature UI milestone (M-A10 onwards) starts before this.** The store policy question (section 5) is raised at this gate.

### M-A10: Auth and onboarding UI
- **Owner**: SFD. **Depends**: M-A2, M-A5 (verification links), M-A9.
- **Scope**: splash with config check (force update, maintenance), language and currency onboarding, login, 2FA challenge (code or recovery code), register, verify-email prompt and resend, forgot password, sign out.
- **Acceptance**: matches Figma in EN and AR; server field errors shown beside fields; rate-limit message with retry time.
- **Tests**: widget tests per page (both locales), controller tests, golden tests of key states.

### M-A11: Home, catalog and search UI
- **Owner**: SFD. **Depends**: M-A3, M-A4, M-A9.
- **Scope**: home sections and banners, platforms, categories, listing with filters and sort, infinite scroll, search with suggestions, popular terms and local history (clearable), currency switcher (invalidates priced data).
- **Acceptance**: prices only from server money objects; RTL layout correct; images from the API only.
- **Tests**: widget tests, pagination tests, currency-change invalidation test.

### M-A12: Product UI
- **Owner**: SFD. **Depends**: M-A4, M-A9.
- **Scope**: product detail, variant selection (region, denomination), availability and `price_may_vary`, delivery field form from definitions, add to cart, product reviews list.
- **Acceptance**: unpurchasable variants disabled with reason; delivery field errors mapped from `errors.delivery_values.{code}`.
- **Tests**: widget tests per state, form validation mapping.

### M-A13: Cart, checkout and payments UI
- **Owner**: SFD (LFA reviews payments). **Depends**: M-A3, M-A6, M-A9.
- **Scope**: cart (update, remove, coupon, `block_reason` display), checkout (country, guest email and consent, payment options with `notice`), place order with idempotency, Stripe PaymentSheet, PayPal and Paymob hosted flows with app-link return, wallet, verify/capture, status polling screen, retry payment, guest OTP and order access.
- **Acceptance**: no client-side "paid" state without server confirmation; app kill during payment resumes correctly; `price_changed` and `rate_stale` re-quote the cart; duplicate order handled.
- **Tests**: controller tests with mocked repositories for every `next` type, return-link handling, kill-and-resume test, polling backoff with fake time.

### M-A14: Orders and code reveal UI
- **Owner**: SFD (LFA reviews security). **Depends**: M-A5, M-A9.
- **Scope**: orders list and detail, unit statuses, reveal with re-auth sheet, secure screen, copy with clipboard guard, redemption link, instructions; guest reveal path.
- **Acceptance**: section 6.3 met on Android (screenshot blocked) and documented for iOS; reveal state cleared on background.
- **Tests**: widget tests for 423 flow and `reveal_denied`; test that the reveal provider is auto-disposed and nothing is written to storage.

### M-A15: Wallet UI
- **Owner**: SFD. **Depends**: M-A3, M-A9.
- **Scope**: balance, status (frozen), statement with pagination; no top-up (BD3).
- **Tests**: widget tests with fixtures, signed-amount display in RTL.

### M-A16: Account and security UI
- **Owner**: SFD. **Depends**: M-A5, M-A9.
- **Scope**: profile edit, locale and currency, change password, devices list and revoke, 2FA enrol (QR and secret display, confirm, recovery codes shown once with secure screen), disable 2FA.
- **Tests**: widget and controller tests, recovery codes never persisted.

### M-A17: Support, reviews, refund requests and notifications UI
- **Owner**: SFD. **Depends**: M-A5, M-A9.
- **Scope**: tickets list, create (category, order link), thread and reply, `ticket_closed`; refund request form; eligible items and review submission, my reviews with status; notifications inbox and preferences.
- **Tests**: widget tests, error-code mapping tests.

### M-A18: Push notifications
- **Owner**: BEL (backend), SFD (app). **Depends**: owner decision on provider (open decision 3).
- **Scope**: the backend has no push channel today (G6). If FCM is approved: device registration endpoints, a `push` notification channel and preference, sending for order delivered, payment failed, ticket reply; app permission prompt after first purchase, token registration and refresh, tap routing to order or ticket. Payloads carry ids and short text only; never codes or amounts beyond what the inbox shows.
- **Acceptance**: notifications arrive on a real Android device in the dev flavor with a dev Firebase project; disabling the preference stops sends.
- **Tests**: Pest with the provider faked; app tests for routing from payloads.

### M-A19: Accessibility, performance and integration tests
- **Owner**: SFD (LFA reviews).
- **Scope**: semantics labels, 48 dp targets, text scaling to 200% without clipping, contrast checks against tokens, screen-reader order in RTL, reduced motion; performance: startup time, image sizes, list jank (profile mode), app size; `integration_test` flows against a seeded dev backend with provider sandboxes where available (browse, cart, guest checkout to status, login with 2FA, reveal with re-auth, ticket).
- **Acceptance**: no accessibility blockers on the checklist; integration suite green on an Android emulator; results recorded in `PROGRESS.md`.

### M-A20: Release configuration and store assets
- **Owner**: LFA (release), PM (listing).
- **Scope**: Android release signing via `android/key.properties` read from the environment (file git-ignored; the document lists the keys `storeFile`, `storePassword`, `keyAlias`, `keyPassword`, never values); Play App Signing; version scheme; obfuscation and symbols archive; `minSdk`/`targetSdk` per owner decision; privacy manifest and data safety form inputs; iOS notes: no macOS host here, so iOS build, signing, `Info.plist` URL types and associated domains, privacy manifest and TestFlight are a documented checklist for a macOS machine; store listing assets (screenshots generated from the approved app in EN and AR, feature graphic, descriptions) without placeholder art.
- **Acceptance**: signed release `appbundle` builds for `prod` (with a locally provided keystore); store policy review decision recorded (section 5); privacy disclosures match actual data use.

---

## 8. Open decisions for the owner

1. **Store policy / payments in app** (section 5): legal and policy review of selling gift cards and top-ups with Stripe, PayPal and Paymob inside iOS and Android apps, per launch country. Blocks M-A20.
2. **App identity**: display name, Android `applicationId` and iOS bundle id (proposal `shop.boxcards.app`), Apple developer and Play console accounts, and a macOS build host for iOS.
3. **Push provider**: FCM (proposed) and who owns the Firebase project; or no push at launch.
4. **App link domain**: confirm `boxcards.shop` hosts `/.well-known` files and the `/app/...` return paths (G8).
5. **Guest checkout in the app**: keep (requires G2 guest order tokens) or require an account in the app.
6. **Device integrity**: warn only (proposed) or block rooted/jailbroken devices from code reveal.
7. **Certificate pinning**: off at launch (proposed) or on with a kill switch.
8. **Minimum OS versions**: Android `minSdk` and iOS deployment target.
9. **Wishlist** in app v1: it is in the web spec but has no API routes yet.
10. **Crash reporting and analytics** in the app (provider and consent), given privacy disclosures.
11. **Biometric app unlock**: offer or not (it never replaces server re-auth for reveal).
12. **Stripe at launch** depends on BD1 (merchant entity); without it the app launches with PayPal, Paymob and wallet.
