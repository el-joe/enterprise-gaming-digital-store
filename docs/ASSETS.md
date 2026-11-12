# Visual Assets (M2)

Status: complete for the M2 list. Hero banners (priority 1) are the Canva files delivered earlier. Category, platform and promotional banners, empty states and social templates are original vector art built in Figma (see "Original vector set" below). Nothing on the list is outstanding except owner sign-off on the Canva licence note.

Canva, no longer used for new work: from 2026-11-10, at the owner's request, new assets are made without Canva. They are hand-built vector art in Figma, with no Canva and no AI image generation. The existing Canva heroes stay in use as delivered, so the hero rows, production notes and licence note below still apply to them. The Canva folder is not a source for anything new.

Earlier history: Canva returned `quota_exceeded` ("User has exceeded their credit quota") partway through the hero batch, so only priority 1 (hero banners) was delivered from Canva.

Canva folder: [PLAYVAULT Assets](https://www.canva.com/folder/FAHad74IjHc)

## Delivered files

Paths are relative to `backend/resources/assets/images/`. All files are textless WebP, encoded at quality 84.

| Path | Dimensions | Size | Placement | Origin | Licence note | Canva design |
|---|---|---|---|---|---|---|
| `hero/hero-01-keycut-desktop-2400x1000.webp` | 2400x1000 | 61 KB | Homepage hero, concept 1 (key-cut slabs), desktop | Canva AI generation (create-design), cropped | Licence confirmation needed, see below | [DAHad-N3Sdo](https://canva.link/der2j81xgc99m31) |
| `hero/hero-01-keycut-mobile-1080x1350.webp` | 1080x1350 | 52 KB | Homepage hero, concept 1, mobile | Crop of the concept 1 desktop export | Licence confirmation needed | [DAHad-N3Sdo](https://canva.link/der2j81xgc99m31) |
| `hero/hero-02-vault-dial-desktop-2400x1000.webp` | 2400x1000 | 82 KB | Homepage hero, concept 2 (vault dial), desktop | Canva AI generation (create-design), retouched, cropped | Licence confirmation needed | [DAHadzik5Gw](https://canva.link/43tbb0pwm77kf9b) |
| `hero/hero-02-vault-dial-mobile-1080x1350.webp` | 1080x1350 | 89 KB | Homepage hero, concept 2, mobile | Crop of the retouched concept 2 desktop export | Licence confirmation needed | [DAHadzik5Gw](https://canva.link/43tbb0pwm77kf9b) |
| `hero/hero-03-glass-cards-desktop-2400x1000.webp` | 2400x1000 | 66 KB | Homepage hero, concept 3 (glass cards), desktop | Canva AI generation (create-design), cropped | Licence confirmation needed | [DAHad800P5U](https://canva.link/kwgm4jjx54qz3i9) |
| `hero/hero-03-glass-cards-mobile-1080x1350.webp` | 1080x1350 | 73 KB | Homepage hero, concept 3, mobile | Crop of the concept 3 desktop export | Licence confirmation needed | [DAHad800P5U](https://canva.link/kwgm4jjx54qz3i9) |

### Production notes

- Concept 2 desktop was retouched after export: a stray smudge above and left of the dial and jagged shapes along the floor were faded into the background. The source design in Canva still contains them.
- Mobile files are crops of the desktop exports, because the two dedicated portrait designs could not be exported (see Missing Assets). They have no calm area for overlaid copy, so the storefront needs a scrim or an art-on-top layout as in the Figma mobile frame.
- Canva AI output is soft at 1:1 on close inspection, most visibly on concept 3. Acceptable at hero scale.
- All three concepts carry no baked-in text, so they work for English and Arabic without a second version.
- Concept 2 is an original vault dial following the Figma hero art. No prompt named a brand, and no brand logos, characters, controllers or consoles appear.

### Licence confirmation before commercial use

Every delivered file is Canva AI output; no Canva library element was used. Before launch, confirm that the Canva plan on this account permits commercial use of AI-generated output and check Canva's current terms on AI content, including ownership and non-exclusivity. Comparable images may be generated for other users.

### Rejected or unused in Canva

These remain in the folder and are not in the project.

| Item | Reason |
|---|---|
| [DAHad3QnC8A](https://www.canva.com/d/BTz9FIZ4QoXH_Ls), concept 1 resized to 1080x1350 | Rejected. Canva's resize duplicated the slab composition awkwardly. |
| `MAHad6XC-zM`, generated test image for concept 1 | Unused. Superseded by the concept 1 design. |
| [DAHad4QmPfA](https://canva.link/n9vm2q7b8b3jfge), concept 1 portrait | Export refused, see below. |
| [DAHad_yXE2Q](https://canva.link/v7az58rmcnhjq1m), concept 2 portrait | Export refused, see below. |

## Original vector set (2026-11-10, no Canva)

Source: Figma file `JkUUSR3WGBYC9ewy2qRFQO`, page **Assets — Vector set** (`57:13`). Every item is hand-built vector art, made from paths, gradients, dashed strokes and background blur. It reuses the key-cut slab, the vault dial (as in `Art / Vault Dial`) and the token palette. No raster input, no Canva and no AI image generation went into it. There are no platform or publisher logos and no characters, so BD8 is respected. The social templates use the boxcards.shop logo as supplied (the owner's brand exception). These are original works for this project, so no third-party licence is needed.

Paths are relative to `backend/resources/assets/images/`. Raster banners were exported from Figma as PNG masters at 1x and converted with PHP GD (`imageavif` quality 60, speed 6; `imagewebp` quality 84). The PNG masters are not kept in the repo; re-export them from the Figma node when needed. Banners carry no text. The art sits on the inline-end side and leaves a calm, faded area on the inline-start side for overlaid copy. Each banner therefore also has an `-rtl` file, a horizontal mirror that puts the art on the left for Arabic. Serve the `-rtl` file when `dir="rtl"`.

### Category banners (1600x600)

| Path (each in `.avif` and `.webp`) | AVIF / WebP | Placement | Origin |
|---|---|---|---|
| `category/gift-cards-1600x600`, `category/gift-cards-rtl-1600x600` | 8 / 19 KB | Gift cards category header | Original vector, Figma node 61:61 (fanned key-cut cards with a ribbon, volt/aqua) |
| `category/game-top-ups-1600x600`, `category/game-top-ups-rtl-1600x600` | 10 / 26 KB | Game top-ups category header | Original vector, Figma node 61:164 (rising token stacks, charge gauge, ember/sun) |
| `category/subscriptions-1600x600`, `category/subscriptions-rtl-1600x600` | 6 / 14 KB | Subscriptions category header | Original vector, Figma node 61:214 (renewal loop around a calendar slab, ultra/flux) |
| `category/gaming-wallets-1600x600`, `category/gaming-wallets-rtl-1600x600` | 6 / 12 KB | Gaming wallets category header | Original vector, Figma node 61:267 (key-cut wallet with cards and tokens, aqua/blue) |

### Generic platform banners (1600x480)

Logo-free and textless, for any platform page. The dashed key-cut square in the dial centre marks where the storefront can overlay the platform's `Logo Slot` or plain-text tile once rights are confirmed (BD8).

| Path (each in `.avif` and `.webp`, plus `-rtl`) | AVIF / WebP | Palette | Origin |
|---|---|---|---|
| `platform/01-volt-1600x480` | 9 / 19 KB | volt-500 to aqua-500 | Original vector, Figma node 61:313 |
| `platform/02-ember-1600x480` | 9 / 20 KB | ember-500 to sun-500 | Original vector, Figma node 61:359 |
| `platform/03-ultra-1600x480` | 8 / 18 KB | ultra-400 to blue-400 | Original vector, Figma node 61:405 |
| `platform/04-aqua-1600x480` | 9 / 19 KB | aqua-500 to volt-500 | Original vector, Figma node 61:451 |
| `platform/05-flux-1600x480` | 8 / 17 KB | flux-500 to ultra-400 | Original vector, Figma node 61:497 |
| `platform/06-sun-1600x480` | 9 / 20 KB | sun-500 to ember-400 | Original vector, Figma node 61:543 |

### Promotional banners (1200x600, textless)

| Path (each in `.avif` and `.webp`, plus `-rtl`) | AVIF / WebP | Placement | Origin |
|---|---|---|---|
| `promo/01-flash-1200x600` | 5 / 11 KB | Flash sale or limited-time promo | Original vector, Figma node 61:580 (bolt with speed lines, ember/sun) |
| `promo/02-bundle-1200x600` | 8 / 17 KB | Bundle or best-value promo | Original vector, Figma node 61:629 (stacked key-cut cards on a ray burst, volt/aqua) |
| `promo/03-new-drop-1200x600` | 6 / 15 KB | New arrivals or weekly drop | Original vector, Figma node 61:670 (glass card, orbs and confetti chips, ultra/flux) |

### Empty-state illustrations (SVG, 240x200)

The SVGs are optimised: no ids, no raster, no clip paths, `viewBox="0 0 240 200"` set, coordinates rounded and `aria-hidden="true"`. They are themeable: the line work and backdrop use `currentColor`, so set `color` (for example `text-content-muted`). The single accent is volt-500 `#C8FF2E`. They are checked on ink-900 and on ink-50. None of them depends on direction, so they need no RTL variant.

| Path | Size | Placement | Origin |
|---|---|---|---|
| `empty-states/empty-cart-240x200.svg` | 1.9 KB | Cart drawer and cart page, empty | Original vector, Figma node 62:32 |
| `empty-states/no-search-results-240x200.svg` | 1.7 KB | Search and catalog filters, no results | Original vector, Figma node 62:42 |
| `empty-states/no-orders-240x200.svg` | 1.6 KB | Account orders, empty | Original vector, Figma node 62:51 |
| `empty-states/empty-wishlist-240x200.svg` | 1.9 KB | Account wishlist, empty | Original vector, Figma node 62:59 |
| `empty-states/no-tickets-240x200.svg` | 2.1 KB | Account support tickets, empty | Original vector, Figma node 62:71 |
| `empty-states/not-found-404-240x200.svg` | 1.7 KB | 404 page | Original vector, Figma node 62:83 |
| `empty-states/no-notifications-240x200.svg` | 1.8 KB | Account and app notifications, empty | Original vector, Figma node 62:93 |
| `empty-states/no-reviews-240x200.svg` | 1.4 KB | Account My reviews, product reviews, admin moderation queue, empty | Original vector, Figma node 62:101 |

A preview row on ink-900 (`62:102`) sits next to them in Figma. It is not exported.

### Social templates (Figma frames, one PNG example each)

These are editable Figma frames with live text in Changa (overline, headline) and Readex Pro (body, CTA), the `Logo / boxcards.shop` instance (Latin or Arabic variant, as supplied) and vector background art. The Arabic frames are RTL: right-aligned, no letter spacing, line height about +15%, logo on the inline-start side, and the key-cut corner of the CTA mirrored to the top left. The story layout keeps the logo below the top 250 px and the CTA above the bottom 250 px. To make a new post, duplicate a frame in Figma and edit the text.

| Example export | Size | Figma frame |
|---|---|---|
| `social/square-en-1080x1080.webp` | 50 KB | Social / Square 1080x1080 / EN, node 63:13 |
| `social/square-ar-1080x1080.webp` | 51 KB | Social / Square 1080x1080 / AR (RTL), node 63:42 |
| `social/story-en-1080x1920.webp` | 67 KB | Social / Story 1080x1920 / EN, node 63:71 |
| `social/story-ar-1080x1920.webp` | 63 KB | Social / Story 1080x1920 / AR (RTL), node 63:100 |

Copy: EN "INSTANT DIGITAL DELIVERY / Every game. One vault. / Gift cards, wallet codes and game top-ups, delivered in minutes. / Shop now at boxcards.shop". AR "تسليم رقمي فوري / كل لعبة. خزنة واحدة. / بطاقات هدايا وأكواد محافظ وشحن ألعاب، تصلك خلال دقائق. / تسوّق الآن على boxcards.shop". The logo includes third-party platform symbols, which the owner accepted for the brand mark (trademark clearance pending, see the roadmap brand note).

## Supplier product images (owner decision 12, 2026-11-12)

- **Source:** the supplier's own product image from the synced catalog. For Reloadly, this is `logoUrls[0]` (the card image), else `brand.logoUrl`. All of these are on `https://cdn.reloadly.com`. This overrides BD8 for supplier-provided images only; the owner holds the rights under the reseller agreement. No other third-party logos are fetched.
- **Processing:** downloaded by a queued job (HTTPS, allow-listed host, no redirects, 10 s, 5 MB, raster only, no SVG), decoded and re-encoded with GD (metadata stripped), and resized to 960 and 480 px wide (aspect ratio kept, no upscaling).
- **Storage:** `public` disk, `media/supplier-products/{supplier_product_id}/{hash16}.avif|.webp` (960) and `{hash16}-480.avif|.webp`, with a `media` row whose `source` is `supplier`. The files are replaced when the supplier's URL changes.
- **Use:** the primary product image when no staff image exists. It is the background of the storefront card and the product page tile (`<picture>`, AVIF then WebP), and it appears in the API `image` and `images` payloads (the Flutter `ProductTile`) and as a thumbnail in the admin supplier catalog. Without an image, the generated dial art stays.
- **Admin uploads:** the same optimizer (AVIF and WebP at 480, 960 and 1920 px, at most 5 MB, no SVG).

## Missing Assets

| Priority | Asset | Status |
|---|---|---|
| 1 | Dedicated portrait hero art for concepts 1 and 2 | Kept as delivered crops (owner decision 2026-11-10). |
| 1 | Dedicated portrait hero art for concept 3 | Kept as delivered crops (owner decision 2026-11-10). |
| 2 | Category banners: gift cards, game top-ups, subscriptions, gaming wallets | Done. Original vector, see "Original vector set". |
| 3 | Generic platform banner set (6 or more palette variations) | Done. 6 palettes, plus RTL mirrors. |
| 4 | Promotional banners (3, textless) | Done. |
| 5 | Empty states: empty cart, no search results, no orders, empty wishlist, no tickets, 404, plus no notifications and no reviews | Done. Themeable SVG. |
| 6 | Marketing and campaign graphics, social templates (square and story, English and Arabic) | Social templates done as Figma frames with PNG examples. Campaign-specific graphics need owner input on campaigns, offers and dates; duplicate the social frames when a campaign is defined. |
| 7 | Default Open Graph share image | Done: `og-default.jpg` 1200x630 (see the favicon and logo row). |
| 7 | Favicon and logo exports (boxcards.shop) | Done. AVIF + WebP logo at 320/640/1280w and mark at 64/128/256 in `backend/public/images/brand`; `favicon.ico`, `favicon-96`, `icon-180/192/512`, `site.webmanifest`, `logo-email.png` (baked on a dark chip), `og-default.jpg` 1200x630; masters in `backend/resources/assets/images/brand`; Blade components `<x-brand.logo variant="full\|mark" surface="dark\|light" :height="36" eager />` and `<x-brand.head-icons />`; brand config in `config/brand.php`. |

Also not produced from spec Phase 3: product promotional visuals, which were not in the M2 task list.

## Canva limits observed

- AI generation: `quota_exceeded` after 5 accepted design jobs and 1 image job in this session. Exports and folder moves kept working for the designs noted above.
- Resize: Canva reported 1 trial use remaining after one resize.
