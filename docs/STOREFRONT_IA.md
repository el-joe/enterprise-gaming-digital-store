# Storefront information architecture (v2)

Owner request (2026-11-12): customers should be able to see every product and the categories, the top-nav links (Gift Cards, Top-ups, Subscriptions, Platforms) are unclear and must go, the home page data must be controllable with a "See all" on every section, and there must be one product list page with side filters. Follow the pattern of international stores.

## Research basis
- Leading key and gift-card stores organise navigation by **category** (Games, Gaming eCards, eGift Cards, Mobile recharges and so on), with brands and platforms as filters or sub-menus inside them, not as separate top-level links. Eneba's menu is the most-cited clean example.
- Baymard and related filter research recommends:
  - a **left filter sidebar** for large catalogs where shoppers combine filters;
  - **always-visible applied filters**;
  - a **result count** next to every filter value;
  - **checkbox multi-select**;
  - **accordions** for many facets;
  - a filter for every attribute shown on the card.

## Structure
1. **Header:** logo · **All categories** button (mega menu) · large search · language and currency · wishlist · bag · account.
   - Remove the Gift Cards, Top-ups, Subscriptions and Platforms links.
   - A slim second row shows up to 8 **featured categories**, managed in the admin.
2. **Mega menu** (desktop panel, full-screen sheet on mobile):
   - categories from the database, each with a product count;
   - hovering a category shows its top brands (platforms) with logos, plus "See all in {category}".
3. **All products** `/products`, with an `/ar/products` twin:
   - the left sidebar has accordions with counts for:
     - Category;
     - Brand (platform) with search inside the facet;
     - Region or country;
     - Price range in the display currency;
     - Delivery type;
     - Availability (in stock).
   - The results area has applied-filter chips with "Clear all", the result count and Sort (best selling, newest, price low to high, price high to low).
   - Grid of product cards with images; pagination with page links, crawlable.
   - The filters live in the URL query, so pages can be shared and indexed; canonical and noindex rules follow `SEO_STRATEGY.md`.
   - Mobile: a "Filters" button opens a bottom sheet with an "Apply (N results)" button.
4. **Category pages** `/c/{slug}` and **brand pages** `/b/{slug}` use the same listing, pre-filtered, with a header (name, image, intro text).
   - Old URLs (`/gaming`, `/gaming/{slug}`, `/{category-slug}`) redirect with 301 through the existing redirects runtime.
5. **Home page:** every section is managed in Admin → Marketing → Homepage sections.
   - Section types:
     - hero banners;
     - category tiles;
     - brand logos;
     - product rail by rule (best sellers, new, on sale, by category, by brand, manual pick).
   - Each section has a title (EN and AR), a position, an on/off switch and an item limit.
   - **Each section has "See all"**, linking to `/products` with the matching filters (or the category or brand page).
6. **App parity:** the Flutter app uses the same API facets and the same section rules.

## Admin control
- **Homepage sections:** a drag-to-order list with a live preview link, a type-specific form, and the "See all" target computed automatically.
- **Featured categories** for the header row: a switch and an order field on each category.
- Categories and brands use their active images (supplier or staff) everywhere.
