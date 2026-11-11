# SEO Strategy

Status: built in M18 (see `PROGRESS.md` M18 for what was built and the deviations). A Lighthouse SEO score is a technical quality signal and does not guarantee rankings; no document or report in this project may claim otherwise.

## 1. Rendering

All indexable pages are server-rendered Blade. JavaScript enhances only. Prices in the initial HTML are in the default display currency, so crawlers see complete content.

## 2. URLs

| Page | URL | Canonical | Index |
|---|---|---|---|
| Home | `/` | self | yes |
| Platforms | `/gaming` | self | yes |
| Platform | `/gaming/{slug}` | self | yes |
| Category | `/{slug}`: `/gift-cards`, `/game-top-ups`, `/subscriptions`, `/gaming-wallets` | self | yes |
| Product | `/products/{slug}` | self | yes |
| Blog | `/blog`, `/blog/{slug}` | self | yes |
| Page | `/pages/{slug}` | self | yes |
| Search | `/search` | - | `noindex,follow` |
| Cart, checkout, account, admin, order status | various | - | `noindex`, blocked in robots where safe |

Category slugs are validated against reserved top-level routes. Languages use a prefix: default language unprefixed, other locales at `/{locale}/...`.

**Variants do not get their own indexable URLs.** Denomination and region are selected on the product page. Query strings such as `?region=us` canonicalize to the product. Reason: Steam 5, 10, 20... would otherwise be near-duplicates at large scale.

Currency is not in the URL; it is a cookie preference and must not create duplicate URLs.

Filter pages: `?platform=`, `?sort=` canonical to the unfiltered category, `noindex,follow` when more than one filter is active. Paginated pages are self-canonical, with unique titles ("Page 2").

## 3. Metadata

`SeoResolver` precedence: `seo_metadata` row, then `seo_templates` for the page type and locale, then generated default. Template tokens: `{name}`, `{platform}`, `{category}`, `{region}`, `{min_price}`, `{site}`.

Title 60 chars or less, description 160 or less (guidance, warnings shown in admin). Open Graph and Twitter Cards on all public pages, with fallback image from settings.

## 4. Structured data

JSON-LD generated server-side by `Seo\StructuredData` builders; never hand-written in Blade.

| Page | Types |
|---|---|
| All | `Organization`, `WebSite` with `SearchAction` |
| Product | `Product` + `Offer` or `AggregateOffer`, `BreadcrumbList`, `AggregateRating` only when genuine approved reviews exist |
| Category, Platform | `BreadcrumbList`, `CollectionPage` |
| Blog | `Article`, `BreadcrumbList` |

Product requirements per Google: `name`, and `offers` with `price`; include `priceCurrency`, `availability`, and `priceValidUntil`. `Offer` uses the page's rendered price and currency. Review markup must correspond to visible reviews.

**FAQ schema:** Google's documentation states that FAQ rich results are no longer shown in Search (notice May 2026; docs removed June 2026). The builder exists because the spec lists it "where eligible", but is **off by default** behind a setting. FAQ content remains valuable on the page.

## 5. Sitemaps and robots

`seo:generate-sitemaps` writes static gzip files: an index `/sitemap.xml` with children by type (`products-1.xml`, `platforms.xml`, `categories.xml`, `blog.xml`, `pages.xml`), max 50,000 URLs per file, `lastmod` from content updates. Inactive and `noindex` items are excluded.

`robots.txt` allows public content, disallows `/admin`, `/checkout`, `/cart`, `/account`, `/api`, `/search`, and references the sitemap index. Do not block CSS and JS.

## 6. Hreflang

Generated for supported languages when a translated version exists, with `x-default`. Launch set is a business decision (recommended: `en`, `ar`). Region-qualified hreflang (`en-gb`) is only used if content genuinely differs by region, which is not the case initially since variants live on a single page.

## 7. Redirects, 404 and 410

`redirects` is checked in middleware after a route miss, cached in Redis. A slug change automatically creates a 301. Chains are collapsed on save. Soft-deleted products return 410 when permanently removed, or 301 to the platform when a replacement exists. Inactive-but-returning products stay at 200 with `OutOfStock` availability. A custom 404 logs to `not_found_logs` and gives search and popular links.

## 8. Internal linking

Breadcrumbs on every page. Product to platform, category, related products. Platform to products and blog posts. Footer and header menus are manageable. Blog posts link to products.

## 9. Content

Unique descriptions for platforms and categories. Supplier-provided text is not reused verbatim where it would be duplicate content. AI-drafted content (workflows C07 to C09) goes through human review. Do not imply official affiliation or misuse brand marks.

## 10. Images

`<picture>` with AVIF or WebP and fallback, width and height attributes, `loading="lazy"` below the fold, `fetchpriority="high"` for the LCP image, responsive `srcset`, alt text required for catalog media.

## 11. Performance targets

Lighthouse Performance 90+ and SEO 100 where achievable, measured on mobile profile. LCP under 2.5 s, INP under 200 ms, CLS under 0.1.

GTM and GA can lower Lighthouse scores. They load after consent and idle, so that a measured score reflects real behaviour rather than a stripped test build. Do not sacrifice security or accessibility to raise scores.

## 12. Measurement

GA4 and GTM per `INTEGRATIONS.md` sections 6 and 7. Events: `view_item_list`, `select_item`, `view_item`, `add_to_cart`, `remove_from_cart`, `begin_checkout`, `add_payment_info` (browser); `purchase`, `refund` (server only, deduplicated by order). Search Console reporting: clicks, impressions, CTR, average position, top queries, top landing pages. Admin settings: GA4 Measurement ID, GTM Container ID, Search Console verification, conversion tracking toggles. Private credentials never reach the frontend.
