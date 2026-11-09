# Design Tokens (M1)

Figma file: https://www.figma.com/design/JkUUSR3WGBYC9ewy2qRFQO

Source of truth is the Figma file: variable collections `Primitives`, `Theme` (modes `Dark`, `Admin Light`), `Layout`; text and effect styles. Names below map 1:1 to Tailwind 4 `@theme` keys (`color/surface/raised` is `--color-surface-raised`, utility `bg-surface-raised`). Working brand: PLAYVAULT (replaceable before launch).

## Fonts (Google Fonts, Latin + Arabic where noted)

| Role | Family | Weights used | Scripts |
|---|---|---|---|
| Display, prices | Changa | 500 600 700 800 | Latin, Arabic |
| Body, UI, labels | Readex Pro | 400 500 600 | Latin, Arabic |
| Codes, SKUs, data | IBM Plex Mono | 400 500 | Latin |

Arabic rules: no letter spacing, no uppercase, line height about +15% from 20px up.

## Core colours

Ink (surfaces and text): 950 `#07080B`, 900 `#0C0E13`, 850 `#12151C`, 800 `#191D26`, 700 `#252A35`, 600 `#383F4D`, 500 `#5A6374`, 400 `#8791A3`, 300 `#B3BBC9`, 200 `#D7DBE3`, 100 `#ECEEF2`, 50 `#F6F7F9`.

Brand: volt-500 `#C8FF2E` (primary CTA), volt-300 `#E4FF85`, volt-600 `#A6DB10`, volt-800 `#4A6404`, volt-900 `#222E05`; ember-500 `#FF5A2B`, ember-400 `#FF7A52`, ember-900 `#3B1408`; ultra-400 `#7C7EFF`, ultra-500 `#5A50FF`, ultra-900 `#1B1754`; aqua-500 `#12C4AB`; flux-500 `#EB2FA8`; sun-500 `#F5A800`.

Status: green-400 `#4EE39B`, sun-400 `#FFC53D`, red-400 `#FF6B7A`, red-500 `#F23D52`, blue-400 `#5CB2FF`.

## Semantic tokens (Dark / Admin Light)

| Token | Dark | Admin Light |
|---|---|---|
| `surface-canvas` | ink-950 | ink-50 |
| `surface-base` | ink-900 | white |
| `surface-raised` | ink-850 | white |
| `surface-overlay` | ink-800 | white |
| `surface-sunken` | ink-950 | ink-100 |
| `line-subtle` / `line-default` / `line-strong` | ink-800 / 700 / 600 | ink-100 / 200 / 300 |
| `content-primary` / `secondary` / `muted` | ink-50 / 300 / 400 | ink-900 / 600 / 500 |
| `content-on-accent` | ink-950 | ink-950 |
| `accent-primary` / `-hover` / `-tint` / `-text` | volt-500 / 300 / 900 / 500 | volt-500 / 600 / 300 / 800 |
| `accent-ember` / `-tint` / `-text` | ember-500 / 900 / 400 | ember-500 / red-100 / ember-600 |
| `accent-ultra` / `focus-ring` | ultra-400 | ultra-500 |
| `status-success` `-warning` `-danger` `-info` | green-400, sun-400, red-400, blue-400 | -700 steps |
| `*-tint` for status | `-900` steps | `-100` steps (warning: sun-400) |
| `field-*` (platform tiles) | volt/ember/ultra/aqua/flux/sun-900, ink-800 | same |

## Spacing and radius

Spacing base 4px (`--spacing: 0.25rem`). Scale used: 0.5, 1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10, 12, 16, 20, 24.

Radius: `xs` 4, `sm` 6, `md` 10, `lg` 14, `xl` 20, `key` 28, `full`.

Key-cut silhouette (brand signature, mirrors automatically): `rounded-ss-sm rounded-se-[28px] rounded-ee-sm rounded-es-sm`. Buttons: `rounded-ss-sm rounded-se-[14px] rounded-ee-sm rounded-es-sm`.

## Type scale (font-size/line-height px)

display-hero 72/76, display-xl 56/60, display-lg 40/46, display-md 32/38, display-sm 26/32 (Changa); heading-lg 22/30, heading-md 18/26, heading-sm 16/24; body-lg 16/26, body-md 14/22, body-sm 13/20, body-caption 12/16; label-lg 16/24, label-md 14/20, label-sm 12/16 (Readex Pro); overline 12/16 Changa +12% uppercase; price-xl 44/48, price-lg 28/32, price-md 20/26, price-sm 16/20 (Changa); mono-lg 20/28, mono-md 13/20, mono-sm 12/16.

## Effects

shadow-card `0 8px 24px rgb(0 0 0/.35)`, shadow-raised `0 16px 40px rgb(0 0 0/.45)`, shadow-overlay `0 32px 80px rgb(0 0 0/.6)`, glow-volt `0 0 28px rgb(200 255 46/.28)`, glow-ember `0 0 28px rgb(255 90 43/.3)`.

## Layout

Breakpoints: sm 375, md 768, lg 1280 (design canvas 1440). Margins 16 / 32 / 80; grid 4 / 8 / 12 columns; gutters 12 / 16 / 24; content max width 1280.

## Implementation notes

RTL: `dir="rtl"`, logical utilities only; do not mirror logos, currency values, SKUs, digital codes (full rules on the Foundations page). Motion: fast 120ms, base 200ms, slow 360ms, `cubic-bezier(.2,.8,.2,1)`, honour `prefers-reduced-motion`. Chart.js defaults: series order volt, ultra, ember, aqua; grid ink-700; 2px lines; always a legend at 2+ series; one y axis. Palette validator: CVD and contrast pass on `#12151C`; bright volt and aqua fall outside the suggested lightness band, a deliberate brand decision offset by legends and labels.

BD8: platforms use a plain-text tile and an empty `Logo Slot` until rights are confirmed. Reviews in frames are flagged "Sample content".
