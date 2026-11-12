# Manual test checklist: storefront and admin (M19)

The manual checks for Step 12 ("Run automated and manual tests"). Each step has an id, what to do and the expected result. The results of the M19 run are at the end, with the environment, the defects found and the observations.

Rules for running it:

- Use the testing database `gaming-store_testing` only, never the app database `gaming-store`, and never edit `backend/.env`. Pass every setting as an environment override (see "Environment").
- Keep payments in mock mode: no provider credential is real, and outbound HTTP from the server must go nowhere (a closed local proxy). No real money.
- Record PASS, FAIL or BLOCKED with a note for every step. Take a screenshot only when a step fails.

## Preview data

Built for the run on top of `php artisan migrate:fresh --seed` (reference data and the Super Admin from `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD`, the test values in `phpunit.xml`), with the project's own test fixtures (`backend/tests/Support`):

- Rates: EGP 48.5 and SAR 3.75 per USD, both currencies active.
- Platform `steam`, category `gift-cards`; products `steam-wallet-20` (variants $10, $20, $50), `steam-wallet-50`, `playstation-store-25`, `mobile-legends-250-diamonds` (required numeric Player ID), and `retired-card-5` (soft-deleted, for 410). Search documents in English and Arabic.
- Gateways: wallet (internal) plus Stripe, PayPal and Paymob active in sandbox mode with the fixture test credentials and the seeded eligibility rules (Paymob: Egypt, EGP, matching IP country).
- Customers: Sara (US, verified, wallet credited $100, one delivered order with a fixture code, one paid undelivered order, a ticket with a staff reply and an internal note, inbox entries), Omar (country EG), and another buyer with an approved review.
- Staff: the seeded Super Admin (no 2FA yet) and, for step A15b, a second Super Admin with 2FA.
- Analytics: a GTM container id, so the consent banner shows.

## Storefront (English and Arabic)

| Id | Steps | Expected result |
|---|---|---|
| S39a | Clear cookies. Open `/`. Watch network requests | The analytics consent banner is shown. No request to `googletagmanager.com` before a choice |
| S01 | Look at the English home page and the console | `lang=en`, `dir=ltr`; hero carousel with pause/play; prices formatted by the server; no console errors or CSP violations |
| S39b | Click "Decline" on the banner, reload | Banner gone and stays gone after reload; still no tag request |
| S02 | Open `/ar` | `lang=ar`, `dir=rtl`, Arabic copy; no console errors |
| S03 | Open `/gaming`, then `/gaming/steam` | Platforms listed; the platform page lists its products |
| S04 | Open `/gift-cards`; sort by price ascending | Category products, self canonical; the sorted page loads (cheapest first) |
| S05 | Search `steam`; then a nonsense term | Both Steam cards found, page `noindex,follow`; empty state for the nonsense term |
| S06 | Search `ستيم` on `/ar/search` | Steam products found; page RTL |
| S07 | Open `steam-wallet-20`; pick the $50 variant | Three variants with server prices; the shown price follows the choice; JSON-LD includes `Product` |
| S08 | Open the top-up; click "Add to bag" with an empty Player ID; then enter `123456789` and add | Error "Player ID is required." beside the field; with a value the item is added and the bag badge counts 1 |
| S09 | Add `steam-wallet-20`; open `/cart` | Toast "Added to your bag."; both lines with server totals; page `noindex` |
| S10 | On the bag page press + on the Steam line, then remove the top-up | Line, totals **and the header bag badge** update without a reload; the removed line disappears |
| S11 | As a guest open `/checkout` | Redirect to `/login`; the form returns to `/checkout` after sign-in (owner decision 4) |
| S12 | Sign in with a wrong password | Generic error "These credentials do not match our records." |
| S13 | Sign in as Sara | Back on `/checkout`; the guest bag is kept |
| S14 | Choose country US | Stripe, PayPal and wallet offered, each "You will be charged $… USD"; Paymob not offered |
| S15 | Switch the display currency to EGP on checkout; pick Stripe | Display amounts in EGP; the charge box says Stripe charges the USD amount (owner decision 1: only Paymob charges EGP) |
| S16 | Sign out; sign in as Omar (profile EG) with request header `CF-IPCountry: EG`; add a product; checkout with country EG | Prices in EGP while browsing; Paymob offered and charging EGP; Stripe and PayPal not offered to customers declared in Egypt. Do not place (it would call Paymob) |
| S17 | As Sara, back in USD, pay with Stripe (provider unreachable) | Order created unpaid, no live call; the order page says the payment is not confirmed and offers "Try payment again" |
| S18 | Add the $10 variant and pay with the wallet | Paid at once with no provider; order page "Payment received" |
| S19 | Open the return page of the unpaid Stripe order | "Confirming your payment…"; never shown as paid on return alone |
| S19b | Mock the provider success for that order (apply a verified succeeded state server side, as the faked webhook does in the tests), then open the return page | The return page confirms and lands on the paid order |
| S20 | Open the order page | "Display total" and "Actual payment amount"; `Cache-Control: no-store, private` |
| S21 | Open `/account` | "Welcome back, Sara …", figures, recent orders; `noindex` |
| S22 | Orders list; status filter; open the delivered order | Own orders only; filter works; detail with timeline and payment; no code in the HTML |
| S23 | Reveal the code: continue, enter a wrong password, then the right one; wait | Password step (423); "The password is incorrect." beside the field; code and countdown shown; nothing in local or session storage; hidden and wiped after 60 s |
| S24 | Reveal again; then hide the tab | No password asked inside the window; hiding the tab closes and wipes the code at once |
| S25 | Open `/account/codes` | Codes masked with a reveal button; no full code in the HTML |
| S26 | Profile: change name and display currency to EGP, save; open a product with and without the `currency` cookie; revert | Saved; with the switcher's cookie the cookie wins (documented precedence); without it prices show in EGP |
| S27 | Security: turn on two-step (password, QR and key, a wrong code, then a TOTP); sign out and in; turn it off | Wrong code refused; recovery codes shown once; the next sign-in asks for the code; turning off needs the password |
| S28 | Security: devices | This browser session listed with masked IP and agent |
| S29 | Open `/account/wallet` | Balance $90.00; statement shows the $100 credit and the $10 checkout debit with its order number |
| S30 | Reviews: rate the delivered item 5 and submit | Review listed as pending moderation |
| S31 | Open the ticket; reply; open a new ticket | Staff reply shown, internal note never; reply appended; new ticket created and opened |
| S32 | On the paid undelivered order send a refund request | Request accepted; the page shows the pending request instead of the form |
| S33 | Notifications: inbox, "mark all read", preferences: change one switch and save | Delivery and support-reply entries; mandatory cells locked; "Preferences saved." |
| S34 | Save a product to the wishlist, open the wishlist, remove it | Saved (`aria-pressed=true`), listed, removed |
| S35 | Product page: follow the Arabic link | `/ar/products/...`, RTL, price kept LTR, Arabic button |
| S36 | Signed in, open the Arabic account pages, bag and checkout | All RTL, no overflow, no console errors |
| S37 | Open an unknown path in English and Arabic | Designed 404 in each language |
| S38 | Open the deleted product (EN and AR) | 410 Gone with the designed page |
| S40 | Fetch `/sitemap.xml`, a product child sitemap and `/robots.txt` | Index of child sitemaps; products with en/ar/x-default alternates, removed product left out; robots references the index and disallows `/account`, `/checkout` |
| S41 | Signed out, load the public pages in EN and AR at 1280, 768 and 375 px | No console errors, exceptions, CSP violations, failed requests or horizontal overflow |
| S42 | Signed in, at 375 px open home, product, bag, checkout, account (EN) and home, product (AR) | No horizontal overflow |
| S43 | Sign out from the account menu | `/account` then redirects to sign-in |

## Admin panel

| Id | Steps | Expected result |
|---|---|---|
| A01 | As a guest open `/admin/orders`; read the admin CSP | Redirect to `/admin/login`; no Google sources in the admin CSP |
| A02 | Signed in as a customer open `/admin/dashboard` | 403 |
| A03 | Sign in as the Super Admin with a wrong password, then the right one | Wrong password refused; first sign-in goes to two-step setup; QR and key, TOTP confirmed, recovery codes shown; admin opens |
| A04 | Sign out; sign in again; enter a wrong code, then a fresh TOTP | Challenge asked; wrong code refused; the fresh code opens the dashboard |
| A05 | Dashboard | KPIs and blocks render; no console errors |
| A18 | Look at the theme | Dark theme (`data-theme=dark`) |
| A06 | Orders DataTable: search by order-number prefix, filter by status, sort by Total, export CSV | Server-side paging; 1 row for the prefix; only matching statuses; sorted ascending; the CSV downloads with the rows |
| A07 | Open a held order; approve without a reason, then with a reason | Units, payments, snapshot and timeline; no code; actions offered per permission; the reason is required; with it the order leaves review (audited) |
| A07b | On an order with a refundable unit, refund it to store credit with reason and note | Refund recorded; the customer's wallet is credited; no provider call |
| A08 | Products list: search; open the top-up editor | Matching rows; editor with variants, delivery fields and status |
| A09 | Customers list; open Sara | Staff never listed; wallet and ledger on the customer page |
| A10 | Support tickets: open the ticket; refund requests list | The internal note is shown to staff, marked staff-only; the customer's refund request listed |
| A11 | Reviews: approve the pending review; open the product | Approved from the row; shown on the product page |
| A12 | Payments: gateway list and a gateway page, as the Super Admin without explicit grants | Mode, charge currency (Paymob EGP, others USD) and "configured" only; no replace form without `payments.credentials`; no stored value in the HTML |
| A15 | Own staff page; try to grant oneself explicit permissions or roles (API); try to edit the Super Admin role and to give a role an explicit permission | Own page read-only; every attempt refused (403) |
| A15b | Sign in as a second Super Admin (2FA) and grant the four explicit-grant permissions to the first; sign back in as the first | Granted with a confirmation; the first admin now holds them |
| A12b | Replace the PayPal credentials through the write-only form | Saved; only the public `client_id` is shown back (labelled "not a secret"); `client_secret` and `webhook_id` never appear; the inputs come back empty |
| A13 | Suppliers list; a supplier page; replace its credentials | Capability matrix and health; credentials saved and never rendered |
| A14 | Settings: open every group; save the analytics secret | Every group renders; the secret is stored encrypted and not rendered back |
| A16 | MCP tokens: issue a token; reload | Shown once; after reload listed without the value |
| A17 | n8n credentials: issue; reload | Token and HMAC secret shown once; after reload listed without either |
| A20 | Open every admin screen in English at 1280 px | 200; each DataTable loads rows or its empty state; no console errors, CSP violations or overflow |
| A19 | Switch to العربية and open the same screens; switch back | RTL; tables load; no console errors |
| A21 | Sign out from the top menu | Back on `/admin/login`; admin pages redirect again |
| A22 | Load `/admin/login` six times within a minute from one IP | The sign-in page keeps loading (only sign-in attempts are throttled) |

## Results: M19 run

| | |
|---|---|
| Date | 2026-11-12 |
| Code | commit `2c7e2e9` plus the uncommitted D1 fix (`backend/resources/js/modules/cart.js`, assets rebuilt with `npm run build`) |
| Server | `php artisan serve --host=127.0.0.1 --port=8123 --no-reload` with overrides: `DB_DATABASE=gaming-store_testing`, `APP_ENV=local`, `APP_DEBUG=false`, `APP_URL=http://127.0.0.1:8123`, `LARAVEL_STORAGE_PATH` in a scratch folder (own views, sessions, sitemap and logs), `CACHE_STORE=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=log`, `LOG_CHANNEL=stderr`, `AUTOMATION_OUTBOX_ENABLED=false`, `FCM_ENABLED=false`, `GEOIP_DRIVER=header`, the digital-code test keys from `phpunit.xml`, and `HTTP(S)_PROXY=http://127.0.0.1:9` (closed port) so no server-side request can leave the machine. The database name was checked before every destructive command |
| Browser | Google Chrome 157.0.8103.50, headless (`--headless=new`), driven over the DevTools protocol from Node 20 (no packages), external hosts resolved to nothing (`--host-resolver-rules`), 1280x900 unless stated |
| Payments | Mock mode only: fixture test credentials, outbound HTTP blocked; the one "paid by provider" case (S19b) was a server-side mocked verification. No real money, no live call |
| Totals | **70 steps: 68 PASS, 1 FAIL, 1 BLOCKED**. S10 failed on the first run (defect D1, fixed, re-run PASS) |

| Id | Result | Notes |
|---|---|---|
| S39a | PASS | Banner shown; no `googletagmanager` request before a choice |
| S01 | PASS | EN LTR; 3 hero slides with pause/play; 12 server prices; no console errors or CSP violations |
| S39b | PASS | Decline hides it, kept after reload (`bc_consent=denied`), still no tag request |
| S02 | PASS | `lang=ar dir=rtl`, Arabic copy, clean console |
| S03 | PASS | `/gaming` and `/gaming/steam` list platforms and products |
| S04 | PASS | 4 products, canonical `/gift-cards`; `sort=price_asc` → 200, top-up ($5) first |
| S05 | PASS | Both Steam cards; `noindex,follow`; empty state for the nonsense term |
| S06 | PASS | `ستيم` finds the Steam products, RTL |
| S07 | PASS | Variants $20.00, $10.00, $50.00; price $20.00 → $50.00; JSON-LD Organization, WebSite, Product, BreadcrumbList |
| S08 | PASS | "Player ID is required." beside the field; added with 123456789, badge 1 |
| S09 | PASS | Both lines with server totals; `noindex,nofollow` |
| S10 | PASS (after fix) | **First run FAIL, defect D1**: the line and totals updated but the header badge stayed stale until a reload (screenshot `S10-FAIL-bag-badge-stale.png`: badge 4, "Subtotal (3 items)"). Fixed; re-run: badge 3 after +, 2 after removing the top-up, no reload |
| S11 | PASS | 302 to `/login`, form returns to `/checkout` |
| S12 | PASS | "These credentials do not match our records." |
| S13 | PASS | Back on `/checkout`, guest bag kept |
| S14 | PASS | Stripe, PayPal, Wallet: each "You will be charged $40.00 USD"; no Paymob |
| S15 | PASS | Display EGP1940.00; Stripe charges $40.00 USD; note "This payment method charges in USD. You will be charged $40.00." |
| S16 | PASS | Omar browses in EGP (EGP1212.50 for $25); only "Paymob · Card, charged EGP1212.50 EGP" offered; Stripe and PayPal not offered (declared in Egypt), wallet not offered (no balance). Not placed |
| S17 | PASS | Order GS-261112-7RGPZHB3HR created unpaid; server log `payments.first_attempt_not_started` (proxy refused the call); page offers "Try payment again" |
| S18 | PASS | Wallet paid $10.00, no provider; "Payment received." (order GS-261112-22XDRDJYTF) |
| S19 | PASS | "Confirming your payment… We are checking with the payment provider"; not marked paid |
| S19b | PASS | After the mocked verified success the return page lands on "Payment received". The risk engine then held the order in `manual_review` (an earlier failed attempt counts as a signal), as designed |
| S20 | PASS | "Display total" and "Actual payment amount" shown; `Cache-Control: no-store, private` |
| S21 | PASS | "Welcome back, Sara Preview", figures, recent orders; `noindex,nofollow` |
| S22 | PASS | 8 order links; status filter; detail without the code in the HTML |
| S23 | PASS | 423 → password step; "The password is incorrect."; code shown with "Hidden again in 60 s. This reveal is logged to your account."; no storage; hidden and wiped after ~61 s |
| S24 | PASS | No password inside the window; tab hide (simulated `visibilitychange` with `document.hidden`) closed and wiped the code at once |
| S25 | PASS | Masked code with reveal button; no full code in the HTML |
| S26 | PASS | Saved; with the `currency=USD` cookie the product stayed $20.00 (cookie wins: `DisplayCurrencyResolver` order header, cookie, profile, country); without the cookie EGP970.00; reverted. See observation O1 |
| S27 | PASS | QR and key; "The authentication code is invalid." for the wrong code; 8 recovery codes; next sign-in asked the code and accepted a fresh TOTP; turned off with the password |
| S28 | PASS | One browser session with masked IP (`127.0.0.x`) and agent, current marked |
| S29 | PASS | $90.00; statement "Used at checkout, Order GS-261112-22XDRDJYTF, −$10.00" and "Refund +$100.00". The order number is text, not a link |
| S30 | PASS | Review listed as pending moderation |
| S31 | PASS | Staff reply shown, `INTERNAL-NOTE-SECRET` absent from the HTML; reply appended; new ticket opened |
| S32 | PASS | Request sent; the form is replaced by the pending request |
| S33 | PASS | Both entries; mark all read; 10 editable and 2 locked mandatory cells; "Preferences saved." |
| S34 | PASS | Saved, listed, removed |
| S35 | PASS | `/ar/products/steam-wallet-20`, RTL, price LTR, "أضف إلى الحقيبة" |
| S36 | PASS | 9 Arabic pages RTL, no overflow, no console errors |
| S37 | PASS | "404 This vault is empty." and "404 هذه الخزنة فارغة." (RTL) |
| S38 | PASS | 410 "This page was removed." in EN and AR |
| S40 | PASS | Index with 10 children; `products-en` with en/ar/x-default, removed product absent; robots OK |
| S41 | PASS | 19 public pages × 3 widths, EN and AR: clean |
| S42 | PASS | No overflow at 375 px |
| S43 | PASS | Signed out; `/account` redirects |
| A01 | PASS | Redirect to `/admin/login`; no Google sources in the admin CSP |
| A02 | PASS | Customer → 403 |
| A03 | PASS | Wrong password refused; setup page; TOTP confirmed; 8 recovery codes; dashboard |
| A04 | PASS | Challenge; "The authentication code is invalid."; fresh TOTP opens the dashboard. (The first attempt hit defect D2, see A22) |
| A05 | PASS | KPIs and blocks render, no console errors. Revenue $0.00 and empty charts: no order reached `completed` in the preview data |
| A18 | PASS | `data-theme=dark`, body `rgb(7, 8, 11)` |
| A06 | PASS | 5 rows; prefix search → 1; `manual_review` filter → 2; sorted by Total ascending; `orders-20261112-084406.csv` downloaded with 5 rows |
| A07 | PASS | No code on the page; Approve, Reject, retry and "Units to refund" offered; "Write at least 5 characters." without a reason; with one the order went `manual_review` → `processing` (staff event). The unit has no supplier mapping in the preview data, so fulfilment put it back in review (system event), as designed |
| A07b | BLOCKED | Not exercised in the browser: the only orders that show the refund block are the two placed in this run, and their units are held in `manual_review`, so the unit checkbox is disabled by rule (checked); the fixture-made paid orders have no gateway snapshot and show no refund block. Covered by `AdminOrderPagesTest` "refunds chosen units from the order page endpoint" and `RefundTest` |
| A08 | PASS | 4 products; "Steam" → 2; editor with variants, Player ID and status |
| A09 | PASS | 3 customers, no staff; Sara's wallet $90.00 with ledger |
| A10 | PASS | Internal note shown to staff, marked staff-only; refund request #1 listed |
| A11 | PASS | Approved from the row; review shown on the product page |
| A12 | PASS | Mode, charge currency and "configured"; the Super Admin without the explicit grant sees no replace form (by design: `payments.credentials` is explicit-grant); no value in the HTML |
| A15 | PASS | Own page read-only; own permissions 403, own roles 403, Super Admin role 403, explicit permission on a role 403 |
| A15b | PASS | Second Super Admin granted `payments.credentials`, `suppliers.credentials`, `settings.manage_critical`, `service_accounts.manage` with confirmation |
| A12b | PASS | Only the public `client_id` rendered; secrets never |
| A13 | PASS | Capability matrix and health; Reloadly sandbox credentials saved (row in `supplier_credentials`), value never rendered |
| A14 | PASS | 12 groups render; `analytics.ga4_api_secret` stored encrypted (the plaintext is not in the table) and not rendered |
| A16 | PASS | 50-character token shown once; not in the page after reload |
| A17 | PASS | Token and HMAC secret shown once; neither in the page after reload |
| A20 | PASS | 46 screens: 200, tables loaded, clean console, no overflow |
| A19 | PASS | The same 46 screens in Arabic: RTL, clean; switched back |
| A21 | PASS | Signed out; admin pages redirect |
| A22 | **FAIL, since fixed** | Defect D2: the sixth load of `/admin/login` within a minute showed the 429 page (screenshot `A04-FAIL-admin-login-page-429.png`). Fixed after the run; covered by regression tests |

Screenshots of failures are kept outside the repository, in the session scratch folder (`shots/`).

## Defects

| Id | Severity | Status | Description | Fix |
|---|---|---|---|---|
| D1 | Minor (wrong count shown) | **Fixed, not committed** | On the bag page, changing a quantity or removing a line refreshed the bag fragment but not the header bag badge, which stayed stale until a reload. The badge sits outside the refreshed fragment and only `product.js` updated it | `backend/resources/js/modules/cart.js` updates every `[data-bag-count]` from the cart JSON returned by the change. Regression test `tests/Feature/Storefront/CheckoutPagesTest.php` "returns the cart lines on quantity change and remove, and the bag module updates the header badge from them" (file run alone: 14 passed). Re-tested in the browser (S10) |
| D2 | Minor (availability of the staff sign-in page) | **Fixed** | **Fixed 2026-11-12** (owner approved): the staff sign-in and two-step pages use a page limiter `admin-auth-pages` (60 per minute per IP); attempts stay limited on `POST /login` (`auth`, 5 per minute per email and IP) and `POST /two-factor-challenge` (`otp`). Regression tests in `tests/Feature/Admin/AdminAccessTest.php`. Original finding: `GET /admin/login` uses `throttle:auth` (5 per minute keyed on email and IP). A page load carries no email, so every load from one IP shares one bucket: the sixth load in a minute returns 429. Staff behind one office IP, or a sign-out followed by a sign-in with a redirect, can hit it. The storefront `/login` page uses the `storefront` limiter instead | Not changed: it is a security control. Suggested: throttle only the sign-in POST with `auth`, and give the page the `admin` or `storefront` limiter. Owner or security review to decide |

## Observations (not defects)

- O1: A display currency saved on the profile has no visible effect while a `currency` cookie from the switcher exists, because the cookie comes first (`DisplayCurrencyResolver`; OD-1 "a saved choice always wins"). Consider clearing or rewriting the cookie when the profile currency is saved.
- O2: Amounts in some currencies are printed with the code and no space or grouping (`EGP1940.00`); USD uses the symbol (`$40.00`). Matches the currency table's symbol; a display-format decision.
- O3: Gateway names in the preview data come from the test fixture (`Paypal`, not `PayPal`); the seeded names are not affected.
- O4: The wallet statement shows the order number as text; it is not a link to the order (the app links it).
