# boxcards.shop customer app

Flutter app for boxcards.shop (Android and iOS). The plan, architecture and milestones live in
`../docs/MOBILE_APP_ROADMAP.md`; this README covers running and maintaining the code.

Status: **M-A1 Flutter foundation** (2026-11-10). The app boots to a token-styled shell; there are no
feature screens yet (feature UI waits for the approved designs, M-A9).

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
  shared/widgets/                BrandLogo, AppButton
assets/brand/                    logo-master.png, mark-master.png (from backend/resources/assets/images/brand/)
assets/fonts/                    Changa, Readex Pro, IBM Plex Mono (OFL, licences next to the files)
test/                            core tests, helpers, goldens (en LTR and ar RTL)
tool/ci.sh                       CI gate
```

Feature folders (`lib/features/<feature>/{data,domain,presentation}`) are created by the milestone
that builds each feature.

## Conventions

- State and DI: Riverpod with code generation. `riverpod_lint` runs through `dart analyze` (analysis
  server plugin); `flutter analyze` does not load plugins, so CI runs both.
- Money is never a `double`: `Money.fromJson` rejects JSON numbers, keeps the server string
  (`amountText`) for round trips and formats from the decimal string.
- Colours, sizes and type come only from `core/theme/tokens`; user-facing text only from the ARB files.
- Only directional geometry (`EdgeInsetsDirectional`, `AlignmentDirectional`,
  `BorderRadiusDirectional`). Logos, amounts and codes are LTR islands.
- Tokens are never logged; debug logging goes through `RedactingLogInterceptor`.

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
bash tool/ci.sh                   # pub get, generated-code check, format, both analyzers, tests + coverage
```

Goldens are rendered with the bundled fonts on Linux; regenerate them on the CI platform.

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
