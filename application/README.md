# boxcards.shop customer app

Flutter app for boxcards.shop (Android and iOS). The plan, architecture and milestones live in
`../docs/MOBILE_APP_ROADMAP.md`; this README covers running and maintaining the code.

Status: **M-A15 wallet UI** (2026-11-11) on M-A14 (orders and code reveal UI), M-A13 (cart, checkout and payments UI), M-A12 (product UI), M-A11 (home, catalog and search), M-A10 (auth and
onboarding), the M-A7 data layers and the M-A1 foundation. The app starts on the splash (app config check: force update,
maintenance, offline), runs onboarding (language, display currency) once, and opens the tab shell
(Home, Browse, Cart, Orders, Account): home from `GET /storefront/home` with the hero carousel, browse
(categories and platforms), product listings with filters, sort and infinite scroll, search with
suggestions, popular terms and a local history, the display currency switcher, and the product
page (variants, delivery fields, add to cart, reviews) with the cart count on the Cart tab, the cart
tab, the signed-in checkout, the payment hand-off to PayPal and Paymob in the system browser with the
app-link return, the payment results, the Orders tab with the order detail, the protected code
reveal, guest order access by email code and the wallet (balance and statement). The Account tab
holds the M-A10 account card, the wallet entry, the currency, guest order access and the language
until M-A16.

## Requirements

- Flutter 3.47.9 (Dart ^3.13), Android SDK, JDK 17+.
- iOS builds need a macOS host (not available here; see roadmap M-A20).

## Flavors

| Flavor | Entry point | Android application id | Env file |
|---|---|---|---|
| dev | `lib/main_dev.dart` | `shop.boxcards.app.dev` | `lib/core/config/env_dev.json` |
| staging | `lib/main_staging.dart` | `shop.boxcards.app.staging` | `lib/core/config/env_staging.json` |
| prod | `lib/main_prod.dart` | `shop.boxcards.app` | `lib/core/config/env_prod.json` |

```sh
flutter run --flavor dev -t lib/main_dev.dart --dart-define-from-file=lib/core/config/env_dev.json
flutter build apk --flavor dev -t lib/main_dev.dart --dart-define-from-file=lib/core/config/env_dev.json --debug
```

The entry point checks that the env file matches its flavor and that staging and prod use HTTPS.
Env files hold **non-secret values only** (`FLAVOR`, `API_BASE_URL`, `APP_LINK_HOST`); publishable
provider keys arrive at runtime from `GET /api/v1/app/config` (gap G9). Cleartext HTTP is allowed in
the dev flavor only (`http://10.0.2.2:8000` is the host machine's Laravel server seen from the
Android emulator).

**Application id.** `shop.boxcards.app` is the proposal from open decision 2 of the roadmap; the owner
has not decided yet and **the id can change before the first store upload** (Android `namespace`,
`applicationId`, the Kotlin package `shop.boxcards.app` and the iOS bundle id move together). The
Dart package name stays `application`. The staging host `staging.boxcards.shop` is a placeholder
until the staging environment exists.

There is no release signing configuration and no keystore in this repository. Release signing,
R8 and symbol archiving are M-A20 (`android/key.properties`, git-ignored).

## Layout

```
lib/
  main_{dev,staging,prod}.dart   flavor entry points -> bootstrap(Flavor)
  bootstrap.dart                 zone guard, error hooks, stores, ProviderScope
  app.dart                       MaterialApp.router, dark theme, locale (RTL for ar)
  core/config/                   AppEnv + env_*.json
  core/network/                  Dio client, interceptors, ApiEnvelope/PageMeta, ApiError,
                                 Failure (sealed), error mapper, Result
  core/storage/                  SecureStore (tokens, cart and guest order tokens), PreferencesStore
  core/router/                   go_router, routes, guards, deep-link parser, shell pages
  core/theme/                    tokens (colours, spacing, radii/key-cut, type, elevation, motion),
                                 ThemeData and ThemeExtensions
  core/l10n/                     ARB files (en, ar), generated localizations, locale provider
  core/money/                    Money (Decimal), CurrencyInfo, MoneyFormatter
  core/security/                 screen security (Android FLAG_SECURE, iOS capture + privacy overlay),
                                 clipboard guard (60 s clear), device integrity
  core/utils/ulid.dart           request ids and idempotency keys
  core/platform/                 NativeBridge: device integrity, device name, open https page / store
                                 listing (the app's own `shop.boxcards.app/security` channel)
  core/utils/clock.dart          injectable clock (countdowns, app lock)
  shared/widgets/                BrandLogo, AppButton and the Mobile App components of the M-A8
                                 Figma page `74:13`: MobileAppBar, StatusBanner, app snackbar,
                                 OtpInput, SheetHeader, AppListTile, AppChip, OptionTile,
                                 AppTextField (server field errors), CountdownBuilder, IconBadge,
                                 FlowScaffold (sticky footer)
assets/brand/                    logo-master.png, mark-master.png (from backend/resources/assets/images/brand/)
assets/fonts/                    Changa, Readex Pro, IBM Plex Mono (OFL, licences next to the files)
test/                            core tests, helpers, goldens (en LTR and ar RTL)
tool/ci.sh                       CI gate
```

```
lib/features/<feature>/          app_config, auth, home, catalog, search, product, cart, checkout,
                                 payments, orders, code_reveal, wallet, reviews, support,
                                 notifications, account, security
  data/models/                   freezed + json_serializable DTOs, one per API Resource shape
  data/datasources/              <feature>_remote_data_source.dart: Dio calls on /mobile/... paths
  data/repositories/             <feature>_repository_impl.dart: Result<T>, DTO -> entity
  domain/entities/               freezed entities, no JSON
  domain/repositories/           abstract contracts
  domain/usecases/               only where logic spans repositories
  <feature>_providers.dart       Riverpod (generated) wiring: data source, repository, use cases
lib/shared/data/models/          DTOs used by several features (cart, payment `next`, product card)
lib/shared/domain/entities/      matching entities (Cart, PaymentNext, OrderPaymentStatus, ProductCard)
test/fixtures/api/               committed copy of docs/api-fixtures (backend contract fixtures)
tool/sync_fixtures.sh            copies docs/api-fixtures here; --check compares
```

`presentation/` (controllers, pages, widgets) exists for `auth` and `launch` (M-A10), for `home`,
`catalog` and `search` (M-A11), for `product` (M-A12), for `cart`, `checkout` and `payments`
(M-A13), for `orders` and `code_reveal` (M-A14) and for `wallet` (M-A15); the other features get
theirs in M-A16 onwards.
`features/payments/payment_flow.dart` is the payments feature's public entry (`PaymentFlow`,
`PaymentLaunch`), used by checkout, the app-link listener and the launch check.
`features/auth/auth_launch.dart` is the auth feature's launch hook (reload the user,
app lock at a cold start, biometric offer), called by the launch feature after the config check.

## Auth, onboarding and launch (M-A10)

- **Routing.** go_router starts on `/splash`. Guards (`core/router/guards.dart`): force update, then
  maintenance, then `signedIn`/`verifiedEmail` routes to `/login?from=`, then the guest-only routes
  (`/login`, `/login/two-factor`, `/register`, `/forgot-password`) send a signed-in customer on: to `from`
  (in-app paths only), to `/verify-email` after registering, through `/biometric-offer?next=` first when
  the one-time offer is due. Login is required before any `signedIn` route; checkout (M-A13) will be one.
- **Controllers** are Riverpod codegen notifiers (`features/*/presentation/controllers`); pages only render
  state and forward taps. Failures are mapped to localized text by `core/l10n/failure_messages.dart`;
  422 field errors (already localized by `Accept-Language`) are shown beside their fields; 429 shows
  "Too many attempts" and a disabled "Try again in m:ss" button from `Retry-After` (60 s without it).
- **Two-factor** challenge token and expiry are held in memory only (`PendingTwoFactor`), never stored.
- **Forgot password** opens `links.forgot_password` from `GET /app/config` in the system browser (the
  reset form is a session route; added to the app config by M-A10, `API_DOCUMENTATION.md` 1d).
- **Device name** for `device_name` comes from the native channel (`Build.MANUFACTURER`/`MODEL`,
  `UIDevice.model`), printable ASCII, at most 100 characters.
- **Version**: `core/config/app_version.dart` holds the build name sent with `platform` to
  `GET /app/config`; a test keeps it equal to `pubspec.yaml`.
- **Rooted / jailbroken**: a minimal native check (Android: `test-keys`, `su` paths, Magisk paths; iOS:
  jailbreak paths and a write outside the sandbox) behind the same channel; a sheet is shown once
  (`prefs.integrity_warning_shown`) and nothing is blocked (owner decision 5). `deviceIntegrityProvider`
  gives the `X-Device-Integrity` value for M-A14. Bypassable by design: a signal, not a control.

## Home, catalog and search (M-A11)

- **Shell.** `StatefulShellRoute.indexedStack` (`core/router/app_router.dart`) with the Mobile Bottom
  Nav (`shared/widgets/app_bottom_nav.dart`, Figma `75:568`); each tab keeps its stack. Routes:
  `/` home, `/browse`, `/browse/platforms`, `/browse/products?category=&platform=&sort=`,
  `/search?q=`, `/cart`, `/orders`, `/account`, `/products/:slug` (placeholder above the tabs).
- **Controllers** (Riverpod codegen, no logic in widgets): `HomeController`, `PlatformsController`,
  `CategoriesController`, `ListingController` (family by base query), `listingTitle`,
  `CurrencySwitcherController`, `StoreSearchController`. They expose `Loadable<T>`
  (`core/state/loadable.dart`: loading, loaded, failed with a typed `Failure`) instead of
  `AsyncValue`, so a failure stays until the customer retries (Riverpod 3 would retry a throwing
  provider on its own). Offline (`NetworkFailure`, `TimeoutFailure`) shows the offline banner and
  "No connection"; other failures show the error state with the request id (`FailureView`).
- **Prices and currency (owner decision 1).** Prices are only the server's money objects: the
  `formatted` string of `price_from` (else the string formatter with the currency's exponent; never
  `double`), shown as LTR islands with Western digits. `pricingContextProvider`
  (`core/money/pricing_context.dart`) is the display currency (`X-Currency`) plus the language;
  every priced provider watches it, so a currency switch reloads home and every open listing from
  page 1. The switcher (Home header, Account tab) lists `GET /storefront/currencies` (falls back to
  the app config currencies); signed in it saves the profile first (`UpdateRegionalSettings`),
  signed out the local preference.
- **Pagination.** `ListingController` requests `page`/`per_page` (20), appends while
  `meta.current_page < last_page`, ignores a second load while one runs, keeps the items when a
  page fails (04.5 bar with Retry, no automatic retry loop) and restarts at page 1 on any filter or
  sort change. The page loads the next page 600 px before the end and also when a short page does
  not fill the screen.
- **Filters.** Delivery (`instant_code`, `direct_topup`), sort (`popular`, `price_asc`,
  `price_desc`, `newest`, `name`) and a price range sent as decimal strings in the display
  currency, validated with `Decimal` (`validatePriceRange`). No region filter: the API has no
  region list to offer.
- **Search.** Suggestions after a 300 ms pause for 2 to 100 characters; submit sends `commit=1`
  and is saved in the **local history** (`features/search/data/search_history_store.dart`:
  `shared_preferences` key `prefs.search_history`, not secure storage; newest first, duplicates
  collapsed case-insensitively, at most 10 terms of 100 characters; remove one or clear all). The
  API has no full-text results endpoint, so results are the suggestions for the committed term:
  products open the product, platforms and categories open the listing.
- **Links.** Banner and home item links (`LinkTargetResolver` targets) map in
  `core/router/link_targets.dart`: `product` -> product page, `category` and `platform` -> listing,
  `search` -> search with the term, `url` -> system browser (https only), `order` -> order detail;
  anything else is ignored.
- **Hero carousel (owner decision 8).** `CarouselAutoplay` (`core/utils/carousel_autoplay.dart`)
  owns the timer: every 5 s; paused while a finger is down (touch or swipe) or a slide has focus,
  and a full interval restarts when the interaction ends; stopped when not visible (inactive tab or
  covered route through `TickerMode`, app in the background, scrolled out of the viewport); never
  with `MediaQuery.disableAnimations`; never with one slide.
- **Images.** Only `image.url` / logo URLs from the API, through `NetworkImageBox`
  (`shared/widgets/network_image_box.dart`), decoded at the laid-out width. While loading, on error
  or without an image, tiles show a plain-text tile on a `color/field/*` token chosen by slug, and
  outside production an empty Logo Slot (BD8). No bundled or generated art.
- **Components.** Product Tile (`76:385`, `shared/widgets/product_tile.dart`), Platform Chip
  (`81:358`, `platform_tile.dart`), Bottom Nav, Skeleton (`76:324`, `skeleton.dart`), Logo Slot,
  `PillButton`, `StoreHeader`, `EmptyState`/`FailureView`/`SectionHeader` (`state_views.dart`).

### Image cache package

**`cached_network_image` 4.0.5** (pinned exactly; MIT; `github.com/Baseflow/flutter_cached_network_image`).
Why: the roadmap's package for catalog media (section 3); it adds a disk cache (`flutter_cache_manager`,
sqflite) on top of Flutter's memory cache so the catalog does not re-download every image on each
launch or scroll, with no Firebase or native setup. 4.0.5 (published 2026-10-21) is the newest
release more than two weeks old; 4.0.7 was published on the day of this milestone. It is used only
through `catalogImagesProvider` for catalog media and never for anything code-related; tests override
that provider, so no plugin or network runs in tests. No other package was added in M-A11.

## Product (M-A12)

- **Routes.** `/products/:slug` (above the tabs; pushed from tiles, search and links, back returns;
  opened directly it goes home on back) and `/products/:slug/reviews`. `parseDeepLink` returns
  `ProductLink` for `https://<APP_LINK_HOST>/products/{slug}` and `/en|ar/products/{slug}`; since
  M-A13 the app-link listener opens them (section below).
- **Controllers** (`features/product/presentation/controllers/product_controllers.dart`):
  `ProductController` (family by slug) loads the detail and the variants together, watches
  `pricingContextProvider` (a currency or language change reloads), and holds the region and amount
  selection, quantity, delivery values, field problems and the add-to-cart state;
  `ProductReviewsController` pages the reviews (10 per page, `meta` driven, no automatic retry of a
  failed page). Pure rules live in the domain: `delivery_field_rules.dart` and `variant_options.dart`.
- **Variants.** Region chips appear only when the variants span several regions. Amounts that cannot
  be bought stay visible, dimmed and disabled with their reason: `out_of_stock` "Sold out",
  `is_custom_amount` "Not available yet", `purchasable: false` or no price "Not available" (the API
  sends no customer-facing block reason). Switching region keeps the denomination when possible.
  `price_may_vary` (product or variant) shows the warning notice. Quantity: 1 to
  `max_quantity_per_order`, never above the cart request's limit of 10.
- **Delivery fields (ADR-037).** The form is generated from `delivery_fields` and checked exactly as
  `DeliveryFieldValidator` does, in the same order: PHP-style trim, empty (required), length in code
  points, type (`number` ASCII digits, `email`, `select` value), then `pattern` as `^(?:pattern)$`
  with Unicode (a pattern Dart cannot compile is left to the server). Checks run on "Add to cart",
  then as the customer types. Arabic-Indic digits typed into `number` fields become Western digits.
  Only trimmed, non-empty values of defined fields are sent. The server is authoritative: 422
  `errors.delivery_values.{key}` shows beside the field, `errors.quantity` beside the stepper, any
  other error (for example `variant_id` "out of stock") in a banner with the server's text.
- **Add to cart** uses `CartRepository.addItem`; a guest's `X-Cart-Token` is captured and sent back
  by `CartTokenInterceptor`. Success shows "Added to cart." with **View cart** (Cart tab). Login is
  only needed at checkout (owner decision 4).
- **Cart count** (`CartCount` in `features/cart/cart_providers.dart`): read from `GET
  /storefront/cart` only when there is a cart (signed in, or a guest cart token), re-read on session
  change, updated from every cart write (`updateFrom`), last count kept on a failed read. The Bottom
  Nav draws it as the volt pill of `75:568` ("Cart, n items" for screen readers).
- **Description.** The server's sanitized HTML is turned into native text blocks
  (`core/utils/safe_html.dart`, `shared/widgets/html_text.dart`): paragraphs, headings, list items,
  line breaks, bold and italic. No web view; scripts, styles, images and links are never loaded or
  executed (links become their text). Instructions and terms are escaped plain text.
- **Prices** are the variant offers' server money objects (`formatted`), LTR islands, and
  `ltrIsolate` inside Arabic sentences (`ادفع \u2066USD10.00\u2069`); the footer shows the unit price
  ("× n"), never a client total. Ratings are decimal strings (`ratingLabel`, `ratingStars`).
- No package was added in M-A12.

## Cart, checkout and payments (M-A13)

- **Routes.** `/cart` (tab), `/checkout` and `/payments/:order` (above the tabs, both
  `RouteAccess.signedIn`). Owner decision 4: a guest who taps Checkout is sent to
  `/login?from=/checkout` by the guard and comes back after signing in; the guest cart is adopted by
  the first cart or checkout call with the bearer token (`X-Cart-Token` is sent once, then dropped).
  A 401 from place-order (`guest_checkout_disabled`, now kept as `UnauthenticatedFailure.code`, or
  an expired session) signs out, so the guard asks to sign in again.
- **Cart** (`CartController`): lines with quantity (1 to 10, the request limit) and remove, coupon
  apply and remove (the server's `coupon_invalid` reason under the field, `coupon.error`,
  `limited_by_margin`), server totals only, `block_reason` text per line (checkout disabled until the
  server says `checkout_ready`), "Prices were updated" after a `price_changed` at checkout or a
  `rate_stale`/`rate_missing` line, `price_may_vary` notice; loading, empty, error (request id) and
  offline states. Every write calls `CartCount.updateFrom`; `CartRevision` (bumped after placement and
  after a final payment answer) and the session, currency and language reload it.
- **Checkout mode** (`checkoutModeProvider`, owner decision 3): `features.web_checkout_redirect`
  turns the cart button into "Complete purchase on boxcards.shop", which opens `links.web_checkout`
  in the system browser; `/checkout` then shows the same state and loads nothing.
  `features.checkout_enabled: false` disables checkout with a note.
- **Checkout** (`CheckoutController`): countries (`GET /storefront/countries`), the profile's country
  when it is one of them, the account email, the cart repriced for the country (`validate`) and the
  payment options with the exact amount and currency each gateway charges (Paymob EGP, the others
  USD; owner decision 1). The summary shows the display total and "You will be charged {amount} in
  {currency} by {gateway}"; `charged_in_payment_currency` adds the notice under the selected option.
  Options that cannot be chosen stay visible and disabled with their reason
  (`payment_currency_below_margin`, `available: false`, Stripe "not in the app yet"). Terms must be
  accepted. Place order goes through the M-A7 `PlaceOrder` use case unchanged (key per body hash in the
  pending checkout, reused across retries and restarts; duplicate goes to the existing order;
  `next: null` calls retry-payment once). `price_changed` re-quotes and shows the old and new totals
  (Review new total / Back to cart; the next attempt has a new body and so a new key); `cart_empty`
  returns to the cart; `idempotency_in_progress` explains and keeps the key; `rate_stale` re-quotes;
  item errors point back to the cart; other domain errors show the server's localized text.
- **Payment hand-off** (`PaymentFlow`, keep-alive): PayPal `approve_url` and Paymob `redirect_url`
  open in the system browser through the app's own channel (`NativeBridge.openExternalUrl`, https
  only), so no URL launcher package was needed. Wallet goes straight to the status. The screen (07.6)
  offers "Return to PayPal" and "Check payment status". A return link, "Check payment status" or a
  resume runs `ResolvePayment` (PayPal capture for a step started in this session, else
  `verify-payment`, which also captures an approved PayPal order server-side; then 2 s, 3 s, 5 s and
  every 10 s for 2 minutes). Results: paid only on `payment_status: paid` (08.1); "still processing"
  after 2 minutes (08.2); failed (08.3) for a `failed` order (final), the cancel link, or a latest
  attempt that ended (`failed`, `cancelled`, `expired` in the order detail) while the order is still
  `pending_payment`, with "Try payment again" (`retry-payment`, `client: app`); expired (08.4) for a
  `cancelled` order or 409 `order_expired`; "Payment provider unavailable" when no attempt could be
  started. Leaving a result clears the pending checkout. The order detail
  (`GET /account/orders/{id}`) fills the summary (number, amount, gateway, attempt).
- **Resume after a restart.** The launch check (`StartupController`) asks `PaymentLaunch`: a
  signed-in customer with a pending checkout that has an order goes straight to that order's payment
  screen, which verifies again.
- **Stripe.** Not launched in the app: the API gives Stripe app attempts only a PaymentSheet
  `client_secret` (no hosted page), and `flutter_stripe` is a large native SDK whose launch also
  depends on BD1 and open decision 12. The Stripe option is shown disabled ("Not available in the app
  yet"); a Stripe `next` (only possible from a retry) shows "can't be completed in the app" with
  "Check payment status". Adding PaymentSheet later is a contained change in `PaymentFlow._launch`.
- **App links** (`core/router/app_link_listener.dart`, `AppLinkListener`): `https://<APP_LINK_HOST>
  /app/payments/{return,cancel}/{order}` (query ignored: `attempt`, Paymob's `success`, ...) hands
  the order to `PaymentFlow` and opens its screen (signed out: the guard asks to sign in and nothing
  is verified); `/products/{slug}` and `/en|ar/products/{slug}` push the product page. Links that
  arrive during the launch check, onboarding, maintenance or force update wait until the app shows a
  normal page. Guest access, email verification and reset links are parsed but not routed (they are
  not in the intent filters, so they keep opening in the browser).
- **Android** (`android/app/src/main/AndroidManifest.xml`): an `autoVerify` intent filter for
  `https://${appLinkHost}` with the path prefixes `/app/payments/`, `/products/`, `/en/products/`,
  `/ar/products/`; `appLinkHost` is read per flavor from `lib/core/config/env_<flavor>.json`
  (`APP_LINK_HOST`) in `build.gradle.kts`, never hard-coded. `flutter_deeplinking_enabled` is false so
  links reach only the listener. **iOS**: `ios/Runner/Runner.entitlements` holds
  `applinks:$(APP_LINK_HOST)` as a placeholder (not referenced by the Xcode project yet) and
  `FlutterDeepLinkingEnabled` is false in `Info.plist`.
- **Owner actions for verified links.** No keystore or signing config was created. The owner must
  supply the SHA-256 signing-certificate fingerprints of each Android flavor that should open links
  (server `MOBILE_ANDROID_SHA256_FINGERPRINTS`, packages in `MOBILE_ANDROID_PACKAGES`) and the Apple
  Team ID (`MOBILE_IOS_APP_IDS` as `TEAMID.<bundle id>`); on the macOS host (M-A20) set
  `APP_LINK_HOST` per scheme, `CODE_SIGN_ENTITLEMENTS = Runner/Runner.entitlements` and the
  Associated Domains capability. Until then the links open in the browser and the web return page
  applies; "Check payment status" in the app still works.

### App links package

**`app_links` 7.2.2** (pinned exactly; Apache-2.0; `github.com/llfbandit/app_links`). Why: the
roadmap's package for deep links (section 3); it delivers Android App Links and iOS Universal Links,
including the link that cold-starts the app (kept until the first listen) and scene-based iOS apps
(this app uses `SceneDelegate`), with no setup code in `MainActivity` or the delegates. 7.2.2
(published 2026-10-06) is the newest release and more than two weeks old. It is used only through
`linkSourceProvider` (tests feed a fake stream). No other package was added in M-A13.

## Orders and code reveal (M-A14)

- **Routes.** `/orders` (tab: 09.1 to 09.3), `/orders/:order` (detail, 09.4, 09.5),
  `/orders/:order/units/:unit` (code, 10.1 to 10.6), `/orders/:order/refund` (placeholder until the
  M-A17 form), all three `RouteAccess.signedIn`; `/guest-orders` (paste the delivery link),
  `/guest-orders/:order` (11.1 to 11.5) and `/guest-orders/:order/units/:unit` (guest code), public.
  "View order" on a paid result opens the detail and "Go to orders" the tab; notification and
  banner `order` targets open the detail.
- **Orders tab** (`OrdersListController`): `GET /account/orders`, 20 per page, `meta` paging (the
  next page 600 px before the end, a failed page keeps the list with a retry bar, no retry loop),
  status chips (one `status` per request: all, completed, processing, awaiting payment, partially
  refunded, refunded, failed, cancelled), pull to refresh, loading, empty (and empty for a filter),
  error with request id and offline states. Signed out it offers sign-in (`from=/orders`) and guest
  order access. A sign-in, a sign-out and `CartRevision` reload it.
- **Order detail** (`OrderDetailController`): status and date, lines with units (masked code and
  status; a unit opens its code screen when the server says `revealable`), refund notice
  (`refund_state` with the succeeded refunds and their destination), total in the display currency
  and the charged amount when the payment currency differs (owner decision 1), payment status,
  provider and attempt, timeline. "Retry payment" for a `pending_payment` order whose latest attempt
  ended goes through the M-A13 `PaymentFlow.retryFromOrder` (`retry-payment`, `client: app`) to the
  payment screen. "Get help" opens `links.support`; "Request refund" opens the M-A17 placeholder
  when the order is paid and no refund is pending or complete.
- **Code reveal** (`RevealController`, auto-disposed family per order, unit and guest flag): masked by
  default (the stored mask, `****7777`, never a code from a list). "Reveal code" runs the M-A7
  `RevealCode` with `X-Device-Integrity` from `deviceIntegrityProvider` (owner decision 5; a
  `compromised` device sees the warning banner, nothing is blocked). On 423 the controller runs
  `confirmRecentAuthWithBiometricProvider` first (owner decision 6), and on any other outcome the
  password sheet (10.2): "Confirm and reveal" sends `confirm-password`; a wrong password keeps the
  sheet with the server's message (10.3); closing it returns to masked. One retry of the reveal.
  429 shows "Too many reveals" with the minutes from `Retry-After` and a disabled "Try again in m:ss"
  (never retried); `reveal_denied` and an unverified email (with "Verify email") have their own
  banners; 404 is "Code not found". Revealed (10.4): the code and PIN (LTR, mono), "Copy code",
  "Hide", "Hides in m:ss", "Read code aloud", "Open redemption page" (https only, system browser) and
  the server's instructions under "How to redeem".
- **Owner decision 7, as built.** The code hides after **60 s** (controller timer; the countdown is
  display only), when the app goes to `hidden`/`paused`/`detached` (`AppLifecycleListener` on the
  screen), when the customer taps Hide, when iOS capture starts, and when the screen is left (the
  provider is disposed). While the app is `inactive` the screen is covered by an opaque panel (both
  platforms). Copy goes to `clipboardGuardProvider` (keep-alive, holds no text): cleared after
  **60 s**, also after leaving the screen, and on resume when the timer fell due in the background.
  Android: a sensitive clip with the app's label, cleared only while the clipboard still holds it.
  iOS and others: Flutter cannot read whose text is on the clipboard (and a read shows the iOS paste
  notice), so an empty string is written after 60 s; **limitation**: something the customer copied
  in between is cleared too.
- **Screen protection** (`screenSecurityProvider`, `core/security/secure_screen.dart`): the reveal
  controller calls `setSecure(true)` when it is created and `setSecure(false)` when it is disposed,
  reference counted, so only while a code screen is open. Android: `FLAG_SECURE` (screenshots,
  recording and the recents thumbnail are blank). iOS (`AppDelegate.swift`): `setSecure` arms a
  native blur over every window on `willResignActive` / `willDeactivate` (the app-switcher
  snapshot is taken before Flutter can draw), `isCaptured` and the event channel
  `shop.boxcards.app/security/capture` (`UIScreen.capturedDidChangeNotification`) hide the code
  while the screen is recorded or mirrored (10.5). **iOS cannot block a screenshot**; the secure
  note on iOS says what is protected instead.
- **Accessibility.** Copy, Hide and Reveal have their own screen-reader labels and hints; the code
  box reads "Code hidden. Ends in 0000." or "Code shown. Use Read code aloud to hear it." and is
  never read out by itself; "Read code aloud" announces it character by character on request; a hide
  is announced ("Code hidden after 60 seconds."). 48dp targets; RTL layouts with LTR code islands.
- **Never kept.** The code is in the controller state only while revealed; `RevealedCode` and
  `RevealScreenState` print `<redacted>`; no provider in `code_reveal` is keep-alive; nothing is
  written to secure storage, preferences, files, logs or analytics (tests scan the sources and
  check storage after a reveal); reveal requests stay `sensitive` and `noRetry` with
  `Cache-Control: no-store`.
- **Guest order access** (owner decision 4: no guest checkout; access stays for existing guest web
  orders). The signed delivery link (app link, or pasted on `/guest-orders`) is handed over through
  `GuestLinkInbox` (memory only) and opened through its `/mobile` twin (link proof, token stored by
  the M-A7 repository); then the order email (the API does not return it, so the customer types it),
  `otp/send`, the 6-digit code (`otp_invalid` under the boxes, 60 s resend cooldown or
  `Retry-After`), `otp/verify` (new token with both proofs), the guest order detail and the codes
  through the same reveal screen (no re-auth for guests; the server checks the proofs). An expired
  link is 11.5; with no link and no usable token the screen asks to open the email's link again.
- **App links** (`AppLinkListener`): `https://<APP_LINK_HOST>/account/orders/{order}` (also
  `/en|ar/...`, the customer delivery email) opens the detail (the guard asks to sign in first and
  returns), and `/api/v1/checkout/guest/orders/{order}/access?expires=&signature=` (the guest
  delivery email) opens guest access. Android intent filter: `/account/orders/`,
  `/en/account/orders/`, `/ar/account/orders/` and the pattern
  `/api/v1/checkout/guest/orders/.*/access`. iOS: since M-A15 the server's
  `apple-app-site-association` lists the same paths (`config/mobile.php` `app_links.paths`).
- **Dates** (`core/utils/date_format.dart`): local time, Arabic month names with Western digits.
- No package was added in M-A14; no backend change (the order detail carries the unit ids and masks;
  a JSON `GET /account/digital-codes` was not needed).

## Wallet (M-A15)

- **Route.** `/wallet` (`RouteAccess.signedIn`, above the tabs), from the Account tab's "Wallet"
  row. The row and the checkout wallet option are hidden when `features.wallet_enabled` is off
  (`walletEnabledProvider` in `features/wallet/wallet_providers.dart`; true before the launch check
  has a configuration, the server still decides whether the wallet can pay); opened anyway (a
  stale link), the screen says "Wallet isn't available" and loads nothing.
- **Screen** (`WalletController`, Figma 12.1 to 12.3): `GET /account/wallet` and the first page of
  `GET /account/wallet/transactions` together; the balance card shows the server's money
  (`balance.formatted`) with the status badge (Active, or Frozen with the warning banner and a
  sticky "Contact support" that opens `links.support`); the statement is newest first, 20 per page,
  `meta` paging (next page 600 px before the end, a failed page keeps the list with a retry bar, no
  retry loop), pull to refresh (the list stays; a failed refresh keeps it). Loading skeleton,
  empty ("No transactions yet"), error with request id and offline states. A sign-in, a sign-out
  and `CartRevision` (an order or a payment answer can move the balance) reload it. Read only: no
  top-up (BD3).
- **Statement lines.** Credit (arrow down, success tint) or debit (arrow up); the type (Refund,
  Credit from support, Payment, Adjustment by support, Reversal) with the order number when the
  line has an `order`; the staff or system `note` only on lines without an order (the note is not
  translated); the date (`AppDates.full`: local time, Arabic month names, Western digits); the
  signed amount and the balance after. A line with an `order` opens the M-A14 order detail
  (`/orders/:order`). Ledger amounts carry no currency (decimal strings, 6 dp): `walletAmountText`
  writes them with the wallet currency's symbol and exponent from `GET /app/config` (else the code
  and the balance's scale), through `MoneyFormatter`, truncating like the server, never through a
  floating-point number; a `+` only on an amount that shows above zero. Amounts and order numbers
  are LTR islands in Arabic.
- **Accessibility.** The balance card reads "Store credit balance $40.00, Active"; each line reads
  "type · order, amount, balance …, date" with "Opens the order" when it has one; rows are at least
  64dp, buttons 48dp.
- **Checkout** (M-A13 follow-up): when the payment options list the wallet, the controller reads
  `GET /account/wallet` once per quote and the option reads "Store credit · balance $40.00" (07.2).
  A failed read only hides the balance. The payment-options contract is unchanged.
- **Backend (M-A15).** Statement lines carry `order: { public_id, order_number } | null` (the
  customer's own order behind a purchase or a refund); `apple-app-site-association` lists the app's
  link paths. No package was added.

## Biometric unlock (owner decision 6)

Package: **`biometric_signature` 13.3.0** (pinned exactly; MIT; `github.com/chamodanethra/biometric_signature`).
Why: it is the maintained plugin (13.3.0 published 2026-10-24, pigeon-typed API, SwiftPM and CocoaPods,
AGP 9 support) that creates **hardware-backed ECDSA P-256 keys** in the Android Keystore (StrongBox when
present) and the iOS Secure Enclave, gated by a **strong** biometric prompt (`setUserAuthenticationRequired`,
`.biometryCurrentSet`), **invalidated when biometrics change**, and signs with SHA256withECDSA returning a
base64 DER signature and a base64 DER SubjectPublicKeyInfo public key, exactly what ADR-040 /
`API_DOCUMENTATION.md` 1g verify. `local_auth` was **not** added: a boolean prompt cannot prove anything
to the server, and unlocking by signing with the same key (below) also gives the "biometrics changed ->
password" fallback the owner asked for. No other package was added in M-A10.

Native requirements it brings: `MainActivity` extends `FlutterFragmentActivity` (androidx BiometricPrompt),
`USE_BIOMETRIC` in the manifest, `NSFaceIDUsageDescription` in `ios/Runner/Info.plist`; compileSdk 36
(Flutter default) is above its floor of 35.

Flow (`features/auth/domain/usecases/biometric_unlock.dart`, all tested with fakes of the key store and the
server):
- **Offer**: once per install after the first sign-in, when a strong biometric is enrolled
  (`prefs.biometric_offer_shown`). "Turn on" creates the key (prompt), `POST /mobile/auth/biometric-key`;
  on 423 the password sheet asks for the password, `confirm-password`, then registers once more. Any failure
  deletes the new local key. The flag `auth.biometric_enabled` lives in secure storage and is wiped on
  sign-out together with the local key (the server key dies with the sign-in).
- **App lock**: with unlock on, the app locks after **5 minutes** in the background (`AppLifecycleListener`)
  and at a cold start; `AppLockGate` covers the navigator (state kept). Unlock signs a fresh random nonce
  with the key, which the platform releases only after a successful strong biometric prompt; a changed
  enrolment (`keyInvalidated`) turns unlock off and asks for the password (`confirm-password`). It gates
  opening the app only.
- **Recent-auth re-auth** for code reveal (M-A14): `confirmRecentAuthWithBiometricProvider` runs
  challenge -> sign -> `confirm-biometric`; `BiometricCheck.usePassword` tells the caller to show the
  password sheet (not enrolled, key unknown to the server, biometrics changed, `biometric_locked`).
- **Turn off**: `BiometricUnlockSettings.disable()` (`DELETE /mobile/auth/biometric-key`, delete key, clear
  flag). The switch (`BiometricUnlockTile`) sits on the home account card until the Security screen (M-A16).
- Not verified here: the prompts on real devices (no emulator with biometrics in this environment) and the
  iOS build (no macOS host). Key attestation is not sent (ADR-040 outstanding).

## Conventions

- State and DI: Riverpod with code generation. `riverpod_lint` runs through `dart analyze` (analysis
  server plugin); `flutter analyze` does not load plugins, so CI runs both.
- Money is never a `double`: `Money.fromJson` rejects JSON numbers, keeps the server string
  (`amountText`) for round trips and formats from the decimal string.
- Colours, sizes and type come only from `core/theme/tokens`; user-facing text only from the ARB files.
- Only directional geometry (`EdgeInsetsDirectional`, `AlignmentDirectional`,
  `BorderRadiusDirectional`). Logos, amounts and codes are LTR islands.
- Tokens are never logged; debug logging goes through `RedactingLogInterceptor`.

## Data layer conventions (M-A7)

- **Paths**: data sources call the stateless twins under `/api/v1/mobile` (base URL `.../api/v1`, so
  paths start with `/mobile/`). Every response goes through `decodeEnvelope` (`core/network/api_decoding.dart`),
  which turns any decode error (missing key, wrong type, money as a number) into a `DecodingFailure`.
- **Repositories** return `Result<T>` through `guardRequest`; failures are the typed `Failure`s of roadmap
  2.3 (`Failure` implements `Exception`). Entities never carry JSON.
- **Money**: `Money` (Decimal, `amountText` kept) through `MoneyConverter`/`NullableMoneyConverter`;
  wallet ledger lines stay decimal strings. A test and `tool/ci.sh` fail on any `double` in
  `lib/features`, `lib/shared/data` and `lib/shared/domain`.
- **Tokens**: `AuthRepository` writes the token pair to `SecureStore` on register, login and the 2FA
  challenge, and wipes secure storage on logout. The auth interceptor refreshes through
  `MobileTokenRefresher` (`POST /mobile/auth/refresh` on a separate client without the auth
  interceptor), single flight; a refused or reused refresh token (401 `refresh_token_invalid`) clears the
  tokens and signs out; a transport error keeps the session. `CartTokenInterceptor` captures the
  `X-Cart-Token` response header and sends it back; the cart repository drops it after a signed-in call
  (the server adopted the guest cart). `GuestOrderTokenInterceptor` attaches the per-order
  `X-Guest-Order-Token` (secure storage key `guest_order.token.<order>`) to the order routes; the checkout
  repository stores every token the server issues (place-order, OTP verify, delivery link).
- **Place order** (`checkout/domain/usecases/place_order.dart`): one ULID key per body (FNV-1a hash of the
  canonical JSON), stored with the hash in the pending checkout (secure storage) before sending, so retries
  and restarts re-send the same key and body; a changed body gets a new key; `meta.duplicate` routes to
  the existing order; `next: null` calls retry-payment once; `pendingOrder()` resumes after a restart.
- **Payment resolution** (`payments/domain/usecases/resolve_payment.dart`): capture (PayPal), nothing
  (wallet) or verify, then status after 2 s, 3 s, 5 s and every 10 s up to 2 minutes; only
  `payment_status: paid` from the server is "paid".
- **Reveal** (`code_reveal/domain/usecases/reveal_code.dart`): 423 -> password callback ->
  confirm-password -> one retry; 429 and every other failure are returned, never retried. Reveal requests
  are `sensitive` and `noRetry`; the revealed code is a plain class with a redacted `toString`, held only
  by the code screen's auto-disposed controller (M-A14; every code-reveal provider is auto-disposed) and
  never written to storage. M-A14 added `confirmRecentAuth` (biometric, then password) and
  `deviceIntegrity` (`X-Device-Integrity`) to `RevealCode`.
- **Fixtures**: `test/fixtures/api` is a committed copy of `docs/api-fixtures`, so Flutter CI needs no PHP.
  After a backend contract change: `cd backend && UPDATE_API_FIXTURES=1 php artisan test --compact
  tests/Feature/MobileContract`, then `bash application/tool/sync_fixtures.sh`. `tool/ci.sh` runs
  `sync_fixtures.sh --check` when `../docs/api-fixtures` exists. `test/helpers/fixtures.dart` loads a
  fixture and `test/helpers/repo_harness.dart` serves it through the real API client, checking the
  recorded method, path and query.
- `build.yaml` sets `field_rename: snake` and `explicit_to_json` for json_serializable.

## Generated code

Generated files are **committed** so analysis works on a clean checkout without code generation:
`*.g.dart`, `*.freezed.dart` and `lib/core/l10n/generated/`. After changing an annotated class or an
ARB file:

```sh
flutter gen-l10n
dart run build_runner build
```

(`--delete-conflicting-outputs` was removed from the current `build_runner`; it now overwrites by
default.) `tool/ci.sh` regenerates and fails when the committed output is stale.

## Tests and CI

```sh
flutter test                      # unit, widget and golden tests
flutter test --update-goldens test/goldens   # after an intended visual change
bash tool/ci.sh                   # pub get, generated-code check, fixture sync check, no-double check,
                                  # format, both analyzers, tests + coverage
bash tool/sync_fixtures.sh        # copy docs/api-fixtures into test/fixtures/api
```

Goldens are rendered with the bundled fonts on Linux; regenerate them on the CI platform.

## Tests (M-A15 additions)

`test/features/wallet/wallet_controller_test.dart` (balance and first page together; paging through
`meta` and the last page; a failed next page kept with no retry loop, then retried; one page at a
time; pull to refresh keeps the list and reloads the balance; a failed refresh keeps the list; a
failed balance or statement until retry; empty; `wallet_enabled` off loads nothing and comes back
when turned on; signed out loads nothing, then signing in loads; `CartRevision` reloads; a late page
from before a reload is dropped; the currency from the configuration or the fallback; support link;
statement amounts: sign, exponent truncation, KWD, malformed; checkout: the balance in the wallet
option, the option dropped with `wallet_enabled` off, no balance call without a wallet option, a
failed balance only hides it), `wallet_page_test.dart` (in `en` and `ar`: Account entry to the
wallet and back, types with order numbers, signed amounts as LTR islands, a linked line opens the
order detail, an unlinked line does not, loading skeleton, empty, frozen with "Contact support" and
without a support link, server error with request id and retry, offline and retry, paging by scroll
with a failed page and retry, pull to refresh, `wallet_enabled` off hides the entry and the screen
says unavailable, signed out asks to sign in, screen-reader labels with tap-target and label
guidelines, 360 wide with a long balance, checkout balance and hidden option) and goldens in LTR
and RTL (`test/goldens/m_a15/`, 11 states: 12.1 with the next-page skeleton, 12.2, 12.3, loading,
error, offline, next page failed, unavailable, 360x800, the Account entry, the 07.2 wallet option).
`test/helpers/wallet_fakes.dart` holds the fake wallet repository (per-page answers and gates) and
the fixture helpers; the harness wires it. `account_repositories_test.dart` and the fixture contract
test cover the new `order` field and the `wallet/wallet_frozen` fixture.

## Tests (M-A14 additions)

`test/features/orders/reveal_controller_test.dart` (unit lookup; reveal with `X-Device-Integrity`;
423 -> biometric success; 423 -> biometric fails -> password; no biometric -> password; wrong
password keeps the sheet; closing the sheet; 429 with the retry time and no retry; 403 and 404;
unverified email; auto-hide at 60 s on the test clock; hide on pause and cover on inactive; iOS
capture; Hide; `setSecure` on and off with the provider's life; copy; the real Android guard clears
after 60 s; https-only redemption link; the header on the wire),
`orders_controllers_test.dart` (paging through `meta`, failed next page, status filter, retry,
signed out, reload on `CartRevision`; retry payment through `PaymentFlow`; refund eligibility; guest
link -> email -> code -> order, `otp_invalid`, expired link, OTP already done, no link; paste-link
validation; the account and guest app links), `orders_pages_test.dart` (every state in `en` and
`ar`: list, paging by scroll, chips, empty, offline, signed out, detail and unit to code, refund
placeholder, retry payment, not found, "View order" from a paid result; code screen through the
re-auth sheet, copy, hide, wrong password, denied, rate limit, device warning, 60 s and background
hide, capture, screen-reader labels and the announcement on request only, 360 wide, provider
disposed and nothing stored after leaving; the guest flow end to end; tap-target and label
guidelines), `test/features/code_reveal/reveal_safety_test.dart` (M-A7 checks plus: the screen
controller is auto-disposed, no logging, storage, analytics or file APIs in the reveal screen, the
state prints no code, the header is logged without the code), `test/core/security/security_test.dart`
(`setSecure` reference counting on Android and iOS, the capture stream, the clipboard clear on
Android and iOS without reading, the clear on resume) and goldens in LTR and RTL
(`test/goldens/m_a14/`, 22 states incl. the 19.4 code reveal at 360x800, canary codes only).
`test/helpers/reveal_fakes.dart` holds the fake reveal repository, screen security and clipboard
guard; the harness wires them and the extended orders and guest checkout fakes.

## Tests (M-A13 additions)

`test/features/commerce/cart_checkout_controllers_test.dart` (cart load, guest without token,
quantity, remove, 422 quantity, coupon applied/refused/removed, blocked line, revision reload;
checkout mode and web redirect; the checkout guard redirect and return; checkout country from the
profile, options display versus charge, disabled options, terms, place order body and PayPal launch,
key reuse across a retry and across a restart, `next: null`, provider unavailable, duplicate,
`price_changed` with a new key, `cart_empty`, `idempotency_in_progress`, 401
`guest_checkout_disabled`, item errors), `payment_flow_test.dart` (wallet, PayPal and Paymob launch,
browser failure, capture on return, a return link never marks paid, the polling schedule and
2-minute end with the fake clock, early paid, failed attempt, final answers, cancel link, retry,
`order_expired`, attempts exhausted, Stripe not launched, close, resume from the pending checkout at
launch, app links: return, cancel, foreign host, queued during launch, signed out),
`commerce_pages_test.dart` (06.1 to 06.5, offline, guest sign-in, web mode, 07.2 to 07.7, pay with
PayPal to 07.6, 08.1 to 08.4, close, tap-target and label guidelines, 360 wide; `en` and `ar`) and
goldens in LTR and RTL (`test/goldens/m_a13/`, 18 states incl. the 19.3 checkout at 360x800).
`test/helpers/checkout_fakes.dart` holds the checkout, payments and orders fakes and the fake link
source; the harness wires them, with `PlaceOrder` on the real secure pending store and
`ResolvePayment` on the test clock.

## Tests (M-A12 additions)

`test/features/product/product_rules_test.dart` (delivery-field matrix for every field type, variant
options, ratings, description HTML), `product_controllers_test.dart` (loading, selection, quantity,
add to cart success, local checks and 422 mapping incl. the `cart/add_item_validation_failed`
fixture, cart count, reviews paging), `product_pages_test.dart` (every state in `en` and `ar`, add
and View cart with the tab badge, number and select fields, reviews page, deep-link location, 360
wide, tap-target and label guidelines) and goldens in LTR and RTL (`test/goldens/m_a12/`, including
the 19.2 360x800 check and the cart badge). `test/helpers/product_fakes.dart` holds fake product
and cart repositories answering with the contract fixtures; the harness wires them by default.

## Tests (M-A11 additions)

`test/features/storefront/storefront_controllers_test.dart` (controllers, pagination, currency
invalidation, search history, link targets), `hero_carousel_test.dart` (auto-advance, touch, swipe,
focus, reduce motion, inactive tab, scrolled away, single slide, RTL), `storefront_pages_test.dart`
(every page in `en` and `ar`, infinite scroll, load-more retry, filters, sort, search, currency
switch, tabs, tap-target and label guidelines) and goldens of the key states in LTR and RTL
(`test/goldens/m_a11/`). `test/helpers/catalog_fakes.dart` holds fake home, catalog and search
repositories answering with the contract fixtures and an image loader that records URLs; the harness
(`pumpHarness(arrange: ...)`) wires them by default.

## Tests (M-A10 additions)

Controller tests (`test/features/auth/auth_controllers_test.dart`, `test/features/launch/launch_controllers_test.dart`),
biometric use cases and the plugin adapter with fakes (`biometric_unlock_test.dart`), the biometric endpoints against
the new contract fixtures (`biometric_repository_test.dart`), widget tests of every page in `en` and `ar`
(`auth_pages_test.dart`, `launch_pages_test.dart`), tap-target and label guidelines (`accessibility_test.dart`) and
goldens of the key states in LTR and RTL (`test/goldens/m_a10/`). `test/helpers/pump_app.dart` `buildHarness` /
`pumpHarness` wire every fake (config, auth, account, key store, biometric server, native bridge, clock).

## Screen and clipboard protection

See "Orders and code reveal (M-A14)" above. Everything goes through the app's own channel
`shop.boxcards.app/security` (`SecurityChannel.kt`, `AppDelegate.swift`); no third-party plugin.

## Brand assets and icons

`assets/brand/logo-master.png` and `mark-master.png` are copies of
`backend/resources/assets/images/brand/` (never recoloured, stretched or mirrored). Launcher icons were
generated from `mark-master.png` with ImageMagick on an ink-950 `#07080B` background:

- Android legacy `mipmap-*/ic_launcher.png` (48 dp, mark at 80%), adaptive
  `mipmap-*/ic_launcher_foreground.png` (108 dp, mark at 60% inside the safe zone) with
  `@color/ic_launcher_background`;
- iOS `AppIcon.appiconset` (mark at 80%, no alpha).

```sh
convert -size 192x192 xc:'#07080B' \( assets/brand/mark-master.png -resize 153x153 \) \
  -gravity center -composite PNG32:android/app/src/main/res/mipmap-xxxhdpi/ic_launcher.png
```

## Fonts (SIL Open Font License 1.1)

Downloaded from the official Google Fonts repository (`github.com/google/fonts`, branch `main`,
commit `5bb28733cec313b63fbd415b574b5ae35c8aa8b8`) on 2026-11-10. Each family's `OFL.txt` sits next to
its files.

| Family | Source URL | Bundled as |
|---|---|---|
| Changa (variable, wght 200-800) | https://github.com/google/fonts/blob/main/ofl/changa/Changa%5Bwght%5D.ttf | `assets/fonts/changa/Changa-Variable.ttf` |
| Changa licence | https://github.com/google/fonts/blob/main/ofl/changa/OFL.txt | `assets/fonts/changa/OFL.txt` |
| Readex Pro (variable, HEXP + wght) | https://github.com/google/fonts/blob/main/ofl/readexpro/ReadexPro%5BHEXP,wght%5D.ttf | `assets/fonts/readex_pro/ReadexPro-Variable.ttf` |
| Readex Pro licence | https://github.com/google/fonts/blob/main/ofl/readexpro/OFL.txt | `assets/fonts/readex_pro/OFL.txt` |
| IBM Plex Mono Regular | https://github.com/google/fonts/blob/main/ofl/ibmplexmono/IBMPlexMono-Regular.ttf | `assets/fonts/ibm_plex_mono/IBMPlexMono-Regular.ttf` |
| IBM Plex Mono Medium | https://github.com/google/fonts/blob/main/ofl/ibmplexmono/IBMPlexMono-Medium.ttf | `assets/fonts/ibm_plex_mono/IBMPlexMono-Medium.ttf` |
| IBM Plex Mono licence | https://github.com/google/fonts/blob/main/ofl/ibmplexmono/OFL.txt | `assets/fonts/ibm_plex_mono/OFL.txt` |

Variable files were renamed only (brackets removed); their bytes are unchanged. Weights are selected
with `FontVariation('wght', …)` in `core/theme/tokens/typography.dart`.

SHA-256:

```
7c4f7a14d4b70ac8816ea8df3a0b127aee4c5f5af7239aca2afecc84ddc7f4d3  Changa-Variable.ttf
268bba7e1e8f3b14d798b3fb0e40ebaa3fc39308c9ac0020e2faf6df181cc30e  ReadexPro-Variable.ttf
6a3412f058c7d8dfd9170c41e85ade48e5156ecb89356110ca57a0a27734af46  IBMPlexMono-Regular.ttf
a9b4c49bb299e05b5f6c481e7fb5e78943d2793249a0c8874ab574a2d1ea6755  IBMPlexMono-Medium.ttf
```
