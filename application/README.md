# boxcards.shop customer app

Flutter app for boxcards.shop (Android and iOS). The plan, architecture and milestones live in
`../docs/MOBILE_APP_ROADMAP.md`; this README covers running and maintaining the code.

Status: **M-A10 auth and onboarding UI** (2026-11-11) on the M-A7 data layers and the M-A1 foundation.
The app starts on the splash (app config check: force update, maintenance, offline), runs onboarding
(language, display currency) once, and has sign in, two-step verification, register, verify email,
forgot password (web page), sign out, the rooted-device warning and biometric unlock (owner decision 6).
Home is still the foundation placeholder with an account card; catalog screens arrive with M-A11.

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
  core/security/                 FLAG_SECURE screen guard, clipboard guard (Android channel)
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

`presentation/` (controllers, pages, widgets) exists for `auth` and the new `launch` feature (splash,
force update, maintenance, onboarding, rooted-device sheet) since M-A10; the other features get theirs
in M-A11 onwards. `features/auth/auth_launch.dart` is the auth feature's launch hook (reload the user,
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
  are `sensitive` and `noRetry`; the revealed code is a plain class with a redacted `toString`, held by no
  provider (all code-reveal providers are auto-disposed) and never written to storage.
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

## Tests (M-A10 additions)

Controller tests (`test/features/auth/auth_controllers_test.dart`, `test/features/launch/launch_controllers_test.dart`),
biometric use cases and the plugin adapter with fakes (`biometric_unlock_test.dart`), the biometric endpoints against
the new contract fixtures (`biometric_repository_test.dart`), widget tests of every page in `en` and `ar`
(`auth_pages_test.dart`, `launch_pages_test.dart`), tap-target and label guidelines (`accessibility_test.dart`) and
goldens of the key states in LTR and RTL (`test/goldens/m_a10/`). `test/helpers/pump_app.dart` `buildHarness` /
`pumpHarness` wire every fake (config, auth, account, key store, biometric server, native bridge, clock).

## Screen and clipboard protection

`SecureScreen` sets Android `FLAG_SECURE` through the app's own method channel
(`android/app/src/main/kotlin/shop/boxcards/app/SecurityChannel.kt`, channel
`shop.boxcards.app/security`) instead of a third-party plugin. `ClipboardGuard` marks copied codes as
sensitive on Android 13+ and clears them after 60 s if the clipboard still holds the app's clip.
iOS cannot block screenshots; iOS capture detection and app-switcher obscuring arrive with the code
reveal milestone (M-A14), and until then both helpers are no-ops on iOS.

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
