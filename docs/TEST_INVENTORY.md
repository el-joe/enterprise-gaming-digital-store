# Test inventory

Generated 2026-11-11 by `python3 tools/test_inventory.py` from the test sources. No test was run to produce it. Regenerate before the final run.

Owner instruction (2026-11-11): tests are written with each milestone but are run only once, after every milestone is finished. The commands for that single run are at the end of this file.

Last measured results, before the no-run rule: backend 2,842 passed (M-A15, commit `c7ac8f6`); app 1,268 passed (M-A16, commit `0bd6b36`). Counts below are declared cases. They expand at run time: Pest datasets run once per row, and many app tests are declared inside loops over locales (en, ar) and directions (LTR, RTL), so both runs report more tests than are declared here.

## Backend (Laravel, Pest)

115 files, 1419 declared test cases (202 with datasets, which run once per dataset row).

| Area | Files | Declared cases |
|---|---:|---:|
| `Feature` | 1 | 1 |
| `Feature/Account` | 5 | 53 |
| `Feature/Admin` | 12 | 150 |
| `Feature/Affiliates` | 1 | 12 |
| `Feature/Automation` | 8 | 72 |
| `Feature/Catalog` | 5 | 56 |
| `Feature/Cms` | 1 | 21 |
| `Feature/Currency` | 3 | 70 |
| `Feature/Foundation` | 7 | 64 |
| `Feature/Fulfillment` | 5 | 52 |
| `Feature/Mcp` | 5 | 55 |
| `Feature/Mobile` | 9 | 157 |
| `Feature/MobileContract` | 6 | 48 |
| `Feature/Orders` | 7 | 87 |
| `Feature/Payments` | 6 | 85 |
| `Feature/Pricing` | 2 | 83 |
| `Feature/Promotions` | 3 | 27 |
| `Feature/Reviews` | 1 | 10 |
| `Feature/Schema` | 3 | 28 |
| `Feature/Seo` | 7 | 70 |
| `Feature/Storefront` | 6 | 95 |
| `Feature/Suppliers` | 2 | 34 |
| `Feature/Support` | 1 | 14 |
| `Feature/Wallet` | 3 | 39 |
| `Unit` | 1 | 1 |
| `Unit/Foundation` | 2 | 20 |
| `Unit/Pricing` | 1 | 9 |
| `Unit/Shared` | 2 | 6 |

### `Feature`

<details><summary><code>backend/tests/Feature/ExampleTest.php</code> (1)</summary>

  - the application returns a successful response

</details>

### `Feature/Account`

<details><summary><code>backend/tests/Feature/Account/AccountM13RoutesTest.php</code> (9)</summary>

- **edit a review**
  - updates the customer's review and sends it back to moderation, taking it out of the rating _(dataset)_
  - validates the rating
  - is 404 for an unknown review and needs a session
- **sign out everywhere else**
  - needs the current password
  - revokes every other app sign-in and browser session, keeps the caller, and rotates remember-me
  - keeps the calling app sign-in
- **wishlist**
  - adds, lists as priced cards and removes, idempotently _(dataset)_
  - refuses unknown or unlisted products and hides a saved product once it is unlisted
  - needs a signed-in customer

</details>

<details><summary><code>backend/tests/Feature/Account/AccountNotificationsTest.php</code> (11)</summary>

- **database channel of the customer notifications**
  - writes the inbox and sends the email for delivery, partial delivery, failure and support replies
  - respects the preferences: no inbox row when the inbox is off, and the order email is always sent
  - sends guests email only: nothing is written to the inbox for an on-demand notifiable
  - never puts a code or PIN in the inbox payloads or responses (canary) _(dataset)_
- **inbox**
  - lists newest first with localized text, a structured link and pagination, and filters unread _(dataset)_
  - marks everything read _(dataset)_
  - answers 404 when reading another customer's notification or an unknown id _(dataset)_
- **notification preferences**
  - shows the category x channel matrix with defaults and mandatory cells _(dataset)_
  - saves changes, audits them and reads them back _(dataset)_
  - refuses to switch off a mandatory email and saves nothing of that request _(dataset)_
  - validates the cells _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Account/AccountOrdersTest.php</code> (12)</summary>

- **order history**
  - lists only the customer's orders, newest first, with pagination and Money totals _(dataset)_
  - filters by status and by placement date range _(dataset)_
  - refuses an unknown status, a malformed date and a range that ends before it starts _(dataset)_
  - returns an absolute thumbnail URL and keeps the query count fixed _(dataset)_
- **order detail**
  - shows items, units with reveal eligibility, payments, refunds and the timeline, never the code _(dataset)_
  - answers 404 for another customer's order, a guest order and an unknown id _(dataset)_
  - feeds the reveal: the unit id from the detail reveals after recent authentication _(dataset)_
- **guest order detail**
  - opens with the placement token and reports the reveal proofs it holds
  - reports both proofs for the token issued after the link and the one-time code
  - answers 404 to no token, a link-only token, another order's token and an expired token
  - never shows a customer's order on the guest route, even to its owner
  - opens for the browser session that placed or verified the order, and for no other

</details>

<details><summary><code>backend/tests/Feature/Account/AccountProfileSecurityTest.php</code> (13)</summary>

- **profile**
  - shows the profile _(dataset)_
  - updates name, locale, display currency, country and phone, and audits without the phone number _(dataset)_
  - changes only the fields sent and clears a field sent as null _(dataset)_
  - validates every field _(dataset)_
  - localizes the next web request with the saved locale; the app keeps Accept-Language
- **password change**
  - needs the current password and a new, different, policy-compliant password _(dataset)_
  - keeps the caller signed in and signs out every other device and browser _(dataset)_
  - signs every device out after a password reset by email
- **devices**
  - lists mobile sign-ins with masked IPs and marks the caller _(dataset)_
  - signs another device out and audits it _(dataset)_
  - refuses to revoke the calling device
  - answers 404 for another customer's device and an unknown id _(dataset)_
  - lists and signs out browser sessions when sessions are stored in the database

</details>

<details><summary><code>backend/tests/Feature/Account/EmailLinksTest.php</code> (8)</summary>

- **email verification without a session**
  - verifies from a mail client and redirects to the configured landing
  - redirects to the site root with the status when no landing is configured
  - answers JSON clients with the envelope, without a session or token
  - refuses a tampered signature, a hash of another address and an address changed since the email
  - verifies the user of the link even when another user is signed in, and signs nobody in
  - resends the verification email to a bearer client
- **password reset link**
  - redirects to the configured page or app link with the token and email encoded
  - keeps the JSON echo when no landing is configured

</details>

### `Feature/Admin`

<details><summary><code>backend/tests/Feature/Admin/AdminAccessTest.php</code> (19)</summary>

- **sign-in and two-step verification**
  - shows the admin sign-in page in English and Arabic with a strict CSP _(dataset)_
  - sends a guest from any admin page to the admin sign-in
  - refuses customers on admin pages
  - sends staff without two-step verification to set it up, and shows the setup page
  - signs out staff whose session never passed the challenge
  - keeps the JSON admin API answering 403 two_factor_required
  - shows the challenge page only while a sign-in waits for its code
  - serves the setup QR code only for a pending enrolment, never cached
  - sends a verified staff member from the sign-in page to the admin
  - switches the admin language and returns to the admin page
- **permission-based navigation**
  - lists only the sections the staff member may open _(dataset)_
  - shows the M15 sections to holders of their permissions
  - shows each seeded role its own navigation _(dataset)_
  - opens the first permitted screen from the admin home
  - shows only the payment tabs the staff member may open
  - badges the orders entry with the orders that need a person
- **route denial**
  - answers 403 on every page without its permission and 200 with it _(dataset)_
  - answers 403 on every table endpoint without the table permission _(dataset)_
  - answers 403 on every action endpoint without its permission _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminAutomationPagesTest.php</code> (8)</summary>

- **pages and permissions**
  - guards each screen by its own permission and renders it with CSP _(dataset)_
  - serves each table to its permission only _(dataset)_
- **AI review**
  - approves once and rejects only with a note, audited, escaping the draft
- **MCP approvals and tokens**
  - offers approve and reject only for live pending actions
  - issues an MCP token shown only in the issue response, then lists it without the token
  - issues n8n credentials whose token and secret never appear again, and switches workflows
- **scheduled and console tasks**
  - prunes expired export files and keeps the rows
  - exports and erases a customer from the console

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminCatalogPagesTest.php</code> (12)</summary>

- **products**
  - guards the product pages by permission
  - shows the full editor with variants, delivery fields and status to catalog managers, in Arabic too
  - lists products server-side by public id, with bulk actions only for the permissions held
  - refuses bulk product actions at the route without catalog.publish or catalog.manage
  - saves delivery field rows sent as a list of objects
- **supplier mappings**
  - maps many variants at once, each audited
  - saves nothing when any line is refused and names the lines
  - rolls back every line when a later mapping breaks a rule (one primary per variant)
  - switches selected mappings on and off with mappings.manage only
- **media library**
  - uploads a validated image with content.media and refuses other files
- **exchange rates**
  - shows rates to pricing.view without actions and to currency.manage with them
  - refuses the rates page without a pricing or currency permission

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminCustomerAndSupportPagesTest.php</code> (14)</summary>

- **customers**
  - guards the customer pages and masks contact details without customers.view_pii
  - never lists staff as customers and 404s their detail page
  - renders the detail page in Arabic with the wallet ledger and actions for managers
  - blocks a customer with a reason, signs them out and audits; unblocks
  - refuses customer actions on staff accounts
  - sets the risk level and adds a note, both audited
  - revokes a biometric key of the customer (OD-1) and refuses another customer's key
  - answers 404 for an unknown device
  - exports personal data without staff notes or internal ticket notes, audited
  - erases a customer only with the id typed again and an empty wallet
- **support**
  - shows internal notes to staff only on the thread, marked staff only
  - hides the reply box without support.reply and the status controls without support.manage
  - lists refund requests and opens one with its units for approval
  - moderates reviews from the queue with row actions behind catalog.manage

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminM15RouteDenialTest.php</code> (2)</summary>

  - answers 403 on every new M15 action route without its permission _(dataset)_
  - answers 403 on every new M15 page without its permission _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminOrderPagesTest.php</code> (13)</summary>

- **order detail page**
  - shows units, payments, snapshot and the append-only timeline, with the email masked
  - shows the full email to holders of customers.view_pii and renders in Arabic
  - offers each action only to holders of its permission
  - never shows a code, a provider secret or a delivery value
- **order actions**
  - approves a held order with a reason, audited
  - needs a reason to approve or reject, shown on the reason field
  - rejects a paid held order by refunding its undelivered units, audited
  - refuses to reject a paid order without refunds.create and changes nothing
  - cancels an unpaid held order on reject
  - refuses to reject while a unit still has an open supplier attempt
  - refuses to reject an order that is not in review
  - runs the supplier check for one order and for selected orders, audited per order
  - refunds chosen units from the order page endpoint, audited with the note

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminResourceScreensTest.php</code> (16)</summary>

- **pages and permissions**
  - answers 403 on the list, create and edit pages without the view permission _(dataset)_
  - renders the list and a read-only edit page with only the view permission _(dataset)_
  - renders the create and edit forms in English and Arabic for staff who may manage _(dataset)_
  - serves each screen table and refuses it without the table permission _(dataset)_
  - lists the new sections in the sidebar per permission and opens the first permitted tab
  - escapes record values in table cells
- **writes through the admin API**
  - creates a platform from the form fields and audits it, with envelope errors beside fields
  - updates a country display currency (OD-1) with currency.manage only, audited
  - lists an affiliate commission with an approve action for pending ones
- **translations (content_translations write endpoint)**
  - saves Arabic text for a product, removes empty fields and audits field names only
  - refuses the base locale, unknown fields and staff without the type permission
  - shows saved translations on the edit page in an RTL field
- **SEO fields**
  - saves SEO per locale with seo.manage and removes an empty row
  - refuses types without SEO fields
- **menu items panel**
  - shows the items of a menu with their edit forms
  - shows the fixed gateway charge currencies on a currency page, without a form for them

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminSettingsTest.php</code> (11)</summary>

- **settings pages**
  - renders every group read-only for settings.view, in English and Arabic _(dataset)_
  - 404s an unknown group
  - shows guest checkout read-only from configuration
- **saving**
  - saves a group with its permission, typed and audited, with envelope errors
  - needs settings.manage_critical for critical groups
  - edits the mobile app config with its own validation (force update, web checkout redirect)
- **write-only secrets**
  - stores a secret encrypted, shows only configured, and never renders or audits the value
  - refuses an unknown secret key and secret rules
- **payment eligibility rules and public key**
  - creates, edits and deletes rules with payments.manage, audited
  - sets the Stripe publishable key, refuses secret keys and never renders the secret key
  - offers supplier creation on the suppliers settings to suppliers.manage only

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminSupplierAndPaymentPagesTest.php</code> (17)</summary>

- **suppliers**
  - renders the capability matrix across adapters with the current supplier, and the health block
  - shows credentials only as configured, with the replace form only for suppliers.credentials
  - replaces supplier credentials through the write-only endpoint, audited without values
  - shows supplier settings errors beside the fields from the envelope
  - shows the supplier list with summary cards
- **payments**
  - lists gateways with mode, charge currency and credentials state, never values
  - shows the gateway page with eligibility rules and the OD-1 charge currency
  - replaces gateway credentials, checking the Stripe key against the mode, audited by field name only
  - refuses to activate a gateway without credentials for its mode, beside the field
  - switches an eligibility rule on and off, audited
  - runs the open-payment reconciliation on demand, audited
  - resolves reconciliation differences one by one and in bulk, with a note
  - renders each payments tab with its table _(dataset)_
- **dashboard**
  - shows KPIs, charts and blocks the staff member may see
  - leaves out profit, balances, gateways and orders without their permissions
  - renders the dashboard in Arabic
  - has the same admin translation keys in English and Arabic

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminTableExportAndPreferencesTest.php</code> (10)</summary>

- **exports**
  - queues an export, writes the filtered rows as CSV and audits request and download
  - exports only the selected rows
  - writes a valid XLSX workbook
  - refuses invalid export requests in the envelope
  - lets only the requester download, while they hold the permission and before expiry
  - marks an export failed without leaving a file when writing breaks
- **layout and saved filters**
  - saves the column layout per staff member, keeping only known columns, audited
  - stores, replaces and deletes saved filters, validated against the table
  - never shows or deletes another staff member's saved filters
  - caps saved filters per table

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminTablesTest.php</code> (16)</summary>

- **orders table**
  - pages on the server and reports total and filtered counts
  - searches by order number prefix and filters by status, gateway and date
  - treats search text as a literal prefix, never as a pattern
  - sorts by an allow-listed column and ignores client column names
  - returns only the declared columns, escaped, with the email masked without customers.view_pii
  - shows and searches the full email with customers.view_pii
  - rejects filter values outside the declared options in the envelope
  - rejects a page length that is not offered and more than three sort instructions
  - runs the same number of queries for one row and for a full page
- **supplier tables**
  - lists suppliers with credentials as configured or not, never the values
  - scopes logs and mappings to the supplier given as context, which is required _(dataset)_
  - runs a constant number of queries for supplier pages _(dataset)_
- **payment tables**
  - pages, searches and filters each payment table on the server _(dataset)_
  - never returns webhook payloads or headers
  - runs a constant number of queries per payment table _(dataset)_
  - runs a constant number of queries for webhooks and reconciliation _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Admin/AdminUsersAndRolesTest.php</code> (12)</summary>

- **pages**
  - guards the staff and role pages
  - lists staff only, with their 2FA state
  - shows a read-only own account
- **invites and roles**
  - invites staff with a reset link, roles and audit; the invitee must set up 2FA
  - refuses granting a role with permissions the actor does not hold
  - refuses any change to the actor's own account
  - lets only a Super Admin give the Super Admin role and explicit permissions
  - suspends staff with a reason and resets their 2FA, audited
  - answers 404 for customers on staff routes
- **permission matrix**
  - saves a role column with roles.manage and refuses E permissions, the Super Admin role and own roles
  - creates and deletes custom roles but never seeded ones
  - refuses the role routes without roles.manage

</details>

### `Feature/Affiliates`

<details><summary><code>backend/tests/Feature/Affiliates/AffiliateTest.php</code> (12)</summary>

- **clicks and attribution**
  - records a click against a hashed token kept in an HTTP-only cookie
  - answers the same for an unknown or suspended code and records nothing _(dataset)_
  - attributes an order placed from the same browser to the last click
  - never attributes a click outside the window or an affiliate buying for themselves
- **commission**
  - accrues a pending commission once when an attributed order completes
  - accrues nothing for an order without a referral or for a suspended affiliate
  - reverses the commission when the order is refunded in full
  - reduces the commission to what the customer kept on a partial refund
  - leaves a paid-out commission unchanged on refund and alerts finance
- **admin**
  - manages affiliates with write-only payout details
  - lists an affiliate's commissions and approves a pending one once
  - needs marketing.affiliates

</details>

### `Feature/Automation`

<details><summary><code>backend/tests/Feature/Automation/AutomationAuthTest.php</code> (17)</summary>

  - accepts a correctly signed request and records the call
  - rejects a request without a token with the 401 envelope and records it
  - rejects tokens that are not live n8n service-account tokens _(dataset)_
  - rejects a request whose signature headers are missing or malformed _(dataset)_
  - rejects a wrong or tampered signature _(dataset)_
  - binds the signature to method, path, query, workflow and execution key
  - rejects a stale or future timestamp even when correctly signed _(dataset)_
  - accepts a timestamp at the edge of the five-minute window
  - rejects a replayed request with the same nonce
  - does not burn a nonce on a request with a bad signature
  - refuses unknown and disabled workflows
  - requires the exact scope for each route group _(dataset)_
  - grants nothing for a wildcard or foreign ability
  - enforces the account IP allow-list
  - rate limits per account, records the refusal and leaves other accounts alone
  - ignores a staff session on automation routes
  - stores only the redacted query, never the body, in the call row

</details>

<details><summary><code>backend/tests/Feature/Automation/AutomationCredentialTest.php</code> (6)</summary>

  - issues a working credential from the console with default scopes only
  - issues through the admin endpoint and the credential signs real requests
  - lists credentials without token values or secrets and revokes one
  - denies the admin endpoint without service_accounts.manage
  - refuses to turn an MCP account into an automation account
  - enables and disables workflows from the console

</details>

<details><summary><code>backend/tests/Feature/Automation/AutomationCustomerMessagingTest.php</code> (9)</summary>

  - prepares an order message only for a customer who opted in to SMS order updates
  - never prepares a message for a guest, a disabled preference, a missing phone or a closed account _(dataset)_
  - refuses an event of another type and reports one already sent
  - includes only customers whose latest SMS marketing consent is a grant
  - excludes carts outside the abandonment window, empty carts and carts already reminded
  - applies the same consent rule to review, reactivation and recommendation segments _(dataset)_
  - lets each workflow read only its own segment
  - logs a message idempotently and never overwrites a sent one
  - refuses a log for another workflow, an unknown customer or a bad status _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Automation/AutomationExecutionTest.php</code> (10)</summary>

  - starts an execution once and reports the second start as a duplicate
  - still refuses a duplicate after the execution succeeded
  - lets a failed execution be restarted and counts the attempt
  - keeps executions of different keys and workflows apart
  - refuses to finish an execution that is not running
  - validates the execution request _(dataset)_
  - records an alert from the error workflow and closes the failed execution
  - queues a task only when n8n is its driver, once per execution
  - refuses a task for an execution that is not running or another workflow
  - closes the newest running execution when the error workflow cannot name the key

</details>

<details><summary><code>backend/tests/Feature/Automation/AutomationOutboxTest.php</code> (11)</summary>

  - writes nothing when the outbox is disabled
  - writes subscribed order events with identifiers only
  - writes no event for transitions n8n does not subscribe to
  - writes the event in the transaction of the state change
  - writes a ticket event for a public staff reply but not an internal note
  - keeps events pending when delivery is not configured
  - delivers a signed event that n8n can verify
  - retries a failed delivery with backoff and records why
  - marks an event failed after the last retry and reports it to F08
  - treats a connection failure as retryable
  - skips events another runner has leased

</details>

<details><summary><code>backend/tests/Feature/Automation/AutomationReportsTest.php</code> (10)</summary>

  - reports yesterday's sales and profit from order_item_financials
  - validates report windows _(dataset)_
  - returns unavailable with a reason where the store holds no data _(dataset)_
  - flags failed and stalled orders for A03 and A04
  - uses the supplier threshold for warnings and a fraction of it for critical alerts
  - reports no alert and no invented figures on an empty store _(dataset)_
  - serves every weekly and financial report on an empty store _(dataset)_
  - gives the AI workflows public product context without costs or customers
  - stores AI output as a pending_review suggestion and publishes nothing
  - lets each workflow create only its own suggestion types

</details>

<details><summary><code>backend/tests/Feature/Automation/N8nWorkflowValidationTest.php</code> (2)</summary>

  - validates every n8n workflow and its documentation
  - covers exactly the seeded workflow inventory

</details>

<details><summary><code>backend/tests/Feature/Automation/ScheduledTaskHeartbeatTest.php</code> (7)</summary>

  - names every scheduled command by its artisan name
  - records a completed heartbeat with its duration through the scheduler events
  - records a non-zero exit code as a failed run
  - records an exception by class only, never its message
  - skips tasks handed to n8n in the scheduler and runs them otherwise
  - reports the scheduler as ok when every task ran since it was last due
  - reports late, failing and never-run tasks and excludes tasks n8n drives

</details>

### `Feature/Catalog`

<details><summary><code>backend/tests/Feature/Catalog/AdminCrudPermissionsTest.php</code> (9)</summary>

  - requires authentication _(dataset)_
  - denies staff without the view or manage permission _(dataset)_
  - lets a view-only user read but not write when the permissions differ _(dataset)_
  - creates, reads, updates and deletes with the manage permission, leaving an audit trail _(dataset)_
  - returns field errors in the envelope for invalid input
  - reorders by the position of each id
  - refuses to delete a category or platform that still has products _(dataset)_
  - scopes nested variants to their product
  - builds no N+1 on the admin product list

</details>

<details><summary><code>backend/tests/Feature/Catalog/ProductAdminTest.php</code> (14)</summary>

  - creates a product as a draft regardless of what the client sends
  - sanitizes the description
  - syncs categories, regions, delivery fields and restrictions
  - refuses a duplicate sku or slug, including one held by a deleted product
  - needs catalog.publish to publish
  - refuses to publish without an active variant
  - refuses to publish a top-up with no required delivery field
  - publishes, sets the publication date, audits and activates the search document
  - applies bulk actions and reports individual failures
  - requires catalog.manage for bulk delete
- **variants**
  - marks a new variant dirty for pricing
  - rejects money sent as a JSON number
  - requires a denomination on fixed variants and a range on custom ones _(dataset)_
  - rejects a duplicate denomination when the region is null

</details>

<details><summary><code>backend/tests/Feature/Catalog/SearchTest.php</code> (12)</summary>

- **with committed data, because InnoDB fulltext only reads committed rows**
  - finds a product by a word in its name
  - finds a product by its platform
  - finds a product by its game attribute
  - survives a term that is only boolean operators
  - finds by title prefix, product sku and variant sku
  - escapes LIKE wildcards
  - hides inactive products
  - keeps a search document for each launch locale
  - rejects too short a term
  - records a search only when committed and counts repeats
  - returns popular terms that found something
  - throttles the search endpoints

</details>

<details><summary><code>backend/tests/Feature/Catalog/SlugAndDeliveryFieldTest.php</code> (14)</summary>

- **category slugs**
  - rejects reserved words _(dataset)_
  - reserves the first segment of any registered route automatically
  - rejects malformed slugs _(dataset)_
  - accepts an ordinary slug
  - rejects a category nested under its own descendant
- **delivery field definitions**
  - rejects a regex that does not compile
  - requires options for a select
  - refuses to delete a definition in use
- **customer delivery values**
  - accepts and trims a valid value
  - requires a required field
  - skips an absent optional field
  - applies the pattern to the whole value _(dataset)_
  - validates each input type _(dataset)_
  - rejects fields the product does not take and non-string values

</details>

<details><summary><code>backend/tests/Feature/Catalog/StorefrontListingTest.php</code> (7)</summary>

  - lists only active products with a priced active variant
  - filters by platform, category, secondary category, region and delivery type
  - filters by price and sorts
  - rejects an unknown sort and a float price
  - prices the listing in the display currency, flagging a stale rate
  - interprets filter prices in the display currency
  - runs a bounded query count that does not grow with rows

</details>

### `Feature/Cms`

<details><summary><code>backend/tests/Feature/Cms/CmsTest.php</code> (21)</summary>

- **HTML sanitizer**
  - removes dangerous markup _(dataset)_
  - keeps Arabic text and safe links
  - adds noopener to a new-tab link
- **pages and posts**
  - sanitizes a page and stamps publication
  - clears the publication date when a page returns to draft
  - needs a future time to schedule a post
- **menus**
  - rejects a script URL
  - rejects a link to a record that does not exist
  - needs exactly one of url or linked record
  - refuses a parent from another menu and a cycle
  - replaces homepage section items in order and rejects unknown item types
  - validates banner urls and windows
- **redirects**
  - rejects an off-site target, a self redirect and a chain
  - stores a normalized hash and drops the target on 410
  - returns a conflict for a duplicate source
- **media**
  - re-encodes an upload to WebP under a random name
  - rejects svg, non-images and oversized files _(dataset)_
  - rejects a php payload named like an image
  - updates details but not the stored path
  - refuses to delete media in use
  - needs content.media

</details>

### `Feature/Currency`

<details><summary><code>backend/tests/Feature/Currency/DisplayCurrencyTest.php</code> (10)</summary>

- **the resolver**
  - uses the GeoIP country currency when it is active with a fresh rate
  - falls back to base when the country currency is stale, inactive, unknown or missing a rate _(dataset)_
  - falls back to base with no country at all (null GeoIP driver, guest)
  - prefers the signed-in customer profile country over GeoIP
  - lets an explicit choice win over the country _(dataset)_
  - decides once per request
- **over HTTP**
  - prices the public storefront for web and app by GeoIP country, without redirecting, and varies the cache on it _(dataset)_
  - does not vary the cache on a GeoIP header that is not used
  - prices a signed-in customer cart in the profile country currency, on the web session and the mobile bearer _(dataset)_
  - refuses an X-Currency that is not an active currency, as before

</details>

<details><summary><code>backend/tests/Feature/Currency/ExchangeRateTest.php</code> (53)</summary>

- **fetching**
  - stores a first rate with markup and appends history
  - does not switch a currency on when a rate arrives
  - treats a repeat of the same provider timestamp as a duplicate
  - applies an ordinary change
  - leaves rates untouched and reports failure on a provider timeout
  - reports a currency the provider did not return
  - never stores a rate for the base currency
  - is scheduled hourly
- **spike guard**
  - holds a change above the threshold and keeps the old rate
  - allows a change exactly at the threshold
  - holds a spike downward too
  - does not refresh freshness while a rate is held, so it eventually goes stale
  - clears the hold when the provider returns to normal
- **decisions**
  - needs currency.manage
  - approves a held rate with a reason, and audits it
  - requires a reason
  - refuses a duplicate approval
  - rejects a held rate and keeps the old one
- **manual override**
  - needs currency.manage
  - replaces the provider rate and ignores markup, with audit and history
  - requires a positive decimal string rate _(dataset)_
  - can supply the first rate for a currency
  - takes precedence over later provider fetches, while the provider rate keeps updating
  - is not treated as stale while active
  - expires by itself and restores the provider rate with history
  - can be cleared with a reason
  - removes a rate that only ever had a manual source when cleared
  - refuses an override on the base currency
- **staleness and missing rates**
  - marks a rate stale past the maximum age and blocks checkout in it
  - accepts a rate just inside the maximum age
  - notices staleness even when the rate table is cached
  - still converts for display on a stale rate, flagged
  - throws for a missing rate
  - falls back to base at checkout for stale, missing and inactive currencies
  - uses a fresh active currency at checkout
  - never treats the base currency as stale or missing
- **conversion rounding**
  - rounds to the currency precision, half up _(dataset)_
  - applies a step rule
  - applies an ends-with rule, never below the converted amount _(dataset)_
  - rejects a rounding rule finer than the precision
  - computes display and payment identically when the currency matches
  - computes display and payment independently from the same base when they differ
- **admin currencies**
  - refuses to activate a currency with no rate
  - refuses to activate a currency with a stale rate, even with a loosely typed flag
  - activates a currency once a real rate exists
  - refuses to deactivate the base currency
  - recomputes the effective rate when markup changes, with history
  - lists history and rates for pricing.view
  - fetches on demand and reports a provider failure in the envelope
- **storefront currency selection**
  - sets a usable currency and falls back for an unusable one
  - lists only active currencies
  - has no exchange rate literals in application code
  - seeds no exchange rates

</details>

<details><summary><code>backend/tests/Feature/Currency/OpenExchangeRatesProviderTest.php</code> (7)</summary>

  - parses rates from the raw body without losing precision
  - sends the app id and symbols to the documented endpoint
  - prefers the encrypted setting over the environment for credentials
  - expands exponent notation and ignores symbols not requested
  - skips invalid rates
  - fails cleanly, without leaking the credential _(dataset)_
  - refuses to call the provider without credentials

</details>

### `Feature/Foundation`

<details><summary><code>backend/tests/Feature/Foundation/AuditAndSettingsTest.php</code> (9)</summary>

  - writes an audit row with the actor and request id
  - records system as the actor when nobody is signed in
  - redacts secrets from audit snapshots
  - refuses to update or delete audit rows
  - round-trips each setting type _(dataset)_
  - returns the default for a missing setting
  - rejects floats
  - reads the new value after an update, despite the cache
  - encrypts secret settings at rest and never audits their value

</details>

<details><summary><code>backend/tests/Feature/Foundation/AuthFlowTest.php</code> (18)</summary>

  - registers a customer, signs them in and sends verification
  - cannot register as staff through mass assignment
  - rejects a short password and a duplicate email
  - rejects a password found in a breach
  - signs in with valid credentials and records the login
  - rejects a wrong password with the same error as an unknown email
  - refuses suspended accounts
  - denies a suspended account holding a live session on account routes
  - audits staff logins but not customer logins
  - signs out
  - verifies email through a signed link
  - rejects a verification link with a bad signature or wrong hash
  - resends the verification email
  - responds identically to a reset request whether or not the email exists
  - expires reset tokens after thirty minutes
  - resets a password with a valid token
  - rejects an invalid reset token
  - applies the stricter staff policy on reset once the token is valid

</details>

<details><summary><code>backend/tests/Feature/Foundation/BrandAssetsTest.php</code> (4)</summary>

  - ships every logo, mark and icon file the brand config points to
  - renders the logo as a picture with avif before webp and fixed dimensions
  - renders the square mark lazily and wraps light surfaces in a dark chip
  - puts the brand logo and name in transactional email

</details>

<details><summary><code>backend/tests/Feature/Foundation/JsonEnvelopeTest.php</code> (9)</summary>

  - renders 401 for guests
  - renders 403 for a customer on an admin route
  - renders 404 for an unknown route
  - renders 422 with Laravel dot notation errors
  - renders 429 with Retry-After and records a security event
  - renders 500 without leaking the exception, even in debug mode
  - renders domain exceptions with their own code
  - renders 419 when the CSRF token has expired
  - echoes a well formed request id and replaces a malformed one

</details>

<details><summary><code>backend/tests/Feature/Foundation/LogRedactionTest.php</code> (4)</summary>

  - keeps canary values out of the log file _(dataset)_
  - redacts through the default stack channel
  - redacts nested context
  - leaves ordinary diagnostic keys readable _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Foundation/RoleAccessTest.php</code> (6)</summary>

  - seeds exactly the documented permissions
  - reaches only the permissions granted to each role _(dataset)_
  - does not give Super Admin the explicit-grant permissions
  - denies a staff member with no role any permission route
  - never exposes permissions to a customer holding a staff role
  - lists the signed-in staff permissions

</details>

<details><summary><code>backend/tests/Feature/Foundation/TwoFactorTest.php</code> (14)</summary>

  - denies staff without two-factor from admin routes
  - denies a session that never passed the challenge even when enrolled
  - lets staff enrol and reach admin in the same session
  - requires the current password to enrol
  - rejects enrolment confirmation with a wrong code
  - does not sign in until the challenge passes
  - rejects a replayed TOTP code
  - accepts a recovery code exactly once
  - expires a pending challenge after five minutes
  - throttles challenge attempts
  - refuses to let staff disable two-factor
  - lets a customer disable two-factor with the current password
  - refuses to start enrolment when already enabled
  - ends an admin session after thirty idle minutes

</details>

### `Feature/Fulfillment`

<details><summary><code>backend/tests/Feature/Fulfillment/CodeCanaryTest.php</code> (1)</summary>

  - keeps the canary code out of logs, tables, job payloads, mail, resources and errors

</details>

<details><summary><code>backend/tests/Feature/Fulfillment/DigitalCodeTest.php</code> (18)</summary>

- **vault and keyring**
  - encrypts code, PIN and URL separately and masks all but the last four
  - produces different ciphertext per key version and rotation re-encrypts every row
  - rejects a duplicate code regardless of case, spaces and hyphens
  - refuses to store a code without a configured keyring
  - refuses to serialize code material
- **stock**
  - imports codes, rejecting duplicates by position without echoing them
  - requires the import permission
  - allocates with SKIP LOCKED: a code locked by another connection is passed over
- **customer reveal**
  - reveals to the owner after a recent password confirmation, uncached and audited
  - confirms the password through the account API
  - denies and audits every failed attempt _(dataset)_
  - rate limits reveals to 10 per hour
- **guest reveal**
  - needs both the signed link and the email one-time code in the session
  - refuses a tampered or expired link
  - refuses a guest whose email was never verified
  - marks the session verified when the one-time code is accepted
- **staff reveal**
  - needs the explicit permission and a reason, and is audited
  - sends delivery emails with a link and never the code

</details>

<details><summary><code>backend/tests/Feature/Fulfillment/ManualFulfilmentTest.php</code> (7)</summary>

  - lists, shows, claims and completes a top-up task, completing the order
  - refuses another staff member finishing a task claimed by someone else
  - rejects a task and fails the unit towards refund
  - keeps the order in review when the rejecting staff member cannot release it
  - requires the manual fulfilment permission
  - lets staff approve a risk-held order, which then fulfils its units
  - shows the order timeline to staff with orders.view

</details>

<details><summary><code>backend/tests/Feature/Fulfillment/SupplierConcurrencyTest.php</code> (6)</summary>

  - has the unit queued and committed before the race
  - lets exactly one of two simultaneous workers submit the unit _(dataset)_
  - refuses a second active attempt from another connection while the first is active
  - recovers a worker killed after step 1: no supplier call, pending verification, never resubmitted
  - recovers a worker killed after the supplier accepted: the lookup settles it, one order only
  - makes a worker wait on the unit row lock held by another connection

</details>

<details><summary><code>backend/tests/Feature/Fulfillment/SupplierFulfilmentTest.php</code> (20)</summary>

  - fulfils a paid order: risk gate, one purchase, code in the vault, order completed
  - holds a risky paid order for review and calls no supplier
  - never fulfils an unpaid order, even with a queued unit
  - sends a read timeout to pending verification with no second POST, then verifies by reference only
  - recovers a worker that died between the supplier call and recording the outcome
  - treats a redelivered submit job as a no-op
- **fallback**
  - falls back to the next supplier only after a definitive refusal
  - never falls back after an ambiguous outcome _(dataset)_
  - holds the unit for review when the next supplier would sell below margin
  - fails the unit when fallback is disabled, and tells the customer
  - holds the unit for review at the verification deadline and keeps the active lock
  - retries only the GET when fetching the code fails after a purchase
  - never sends an order above the per-request cap
  - skips a supplier whose cached balance does not cover the cost
  - settles a multi-unit order as partially fulfilled when one unit is refused, then moves it to refund_pending (M10)
  - treats a duplicate supplier transaction id as ambiguous
  - lets staff confirm verified not accepted, then retries with the same client reference
  - refuses to mark a resolved attempt or retry a unit whose attempt is still active
  - lets staff force a reconciliation lookup that settles the order
  - routes direct top-ups to staff tasks (BD2)

</details>

### `Feature/Mcp`

<details><summary><code>backend/tests/Feature/Mcp/McpAuthTest.php</code> (10)</summary>

  - rejects a call without a token with the 401 envelope and audits it
  - rejects tokens that are not live MCP service-account tokens _(dataset)_
  - rejects a deleted account and a revoked token
  - ignores a staff session on MCP routes
  - requires the exact scope of each read tool _(dataset)_
  - grants nothing to a wildcard or unprefixed ability
  - enforces the account IP allow-list
  - writes one audit row per call with account, token, outcome and redacted arguments
  - marks MCP responses as not cacheable and records last use
  - describes the calling account for the MCP server start-up, without secrets

</details>

<details><summary><code>backend/tests/Feature/Mcp/McpCanaryTest.php</code> (1)</summary>

  - keeps codes, credentials, secrets, delivery values and raw customer data out of every MCP response

</details>

<details><summary><code>backend/tests/Feature/Mcp/McpReadToolsTest.php</code> (13)</summary>

  - lists, finds and searches products with variant prices as decimal strings
  - prices a variant with the pricing engine and reports availability and stock counts only
  - masks customer identifiers in order lists, details and searches
  - searches customer orders by exact identifiers only
  - summarises sales and profit from order_item_financials within the window only
  - reports conversion as unavailable where GA4 data does not exist, without inventing numbers
  - reports supplier performance, balances, status and sync runs without credentials
  - ranks best sellers and underperformers and reports coupon performance and trends as aggregates
  - lists stalled orders, failed payments, open reconciliation and system health
  - lists failed orders in the window
  - finds low-margin variants and counts those with unknown cost
  - runs a constant number of queries for order and product lists
  - never lists a delivered unit code or its mask in order details

</details>

<details><summary><code>backend/tests/Feature/Mcp/McpTokenAndLimitsTest.php</code> (5)</summary>

  - issues a token once through the admin endpoint and lets it be listed and revoked
  - refuses write scopes without allow_writes and needs service_accounts.manage
  - lists and revokes tokens from the command line without printing token values in the list
  - limits each account to its rate_limit_per_minute and audits the refusals
  - has no MCP route for refunds, codes, credentials, settings, customers or queries

</details>

<details><summary><code>backend/tests/Feature/Mcp/McpWriteActionsTest.php</code> (26)</summary>

- **read-only by default**
  - refuses writes from a read-only account even when the token holds the write scope
  - refuses each write tool without its scope _(dataset)_
  - issues read-only tokens unless writes are allowed explicitly
- **pending action and confirmation**
  - creates a pending action and changes nothing until it is confirmed
  - accepts a confirmation token once only
  - refuses confirmation after expiry and marks the action expired
  - expires due actions on schedule
  - accepts confirmation only from the token that requested the action
  - rejects a wrong confirmation token without consuming the right one
  - gives no confirmation token without actions:confirm, and refuses MCP confirmation of tier 2
  - refuses a second identical pending action
  - fails an action whose stored arguments were tampered with
  - reports action status to the owning account with a write scope only
  - validates write arguments before storing anything
- **admin approval**
  - lets staff with mcp_actions.approve and the tool permission approve, and runs it as them
  - needs the permission to see the queue, and rejects with a note
  - fails an approved action whose requesting token was revoked
- **write tools keep the business rules of the existing services**
  - refuses to publish a product customers could not buy
  - deactivates a product and refuses a no-op
  - sets a small price change through the margin guard as tier 1
  - makes a large change or a price below the floor tier 2, and the margin guard still decides
  - creates a coupon marked as created by MCP, capped and within margin
  - queues a catalog sync recorded as triggered by the MCP account
- **retry_verified_failed_order**
  - refuses a unit whose latest attempt is not verified as not accepted _(dataset)_
  - retries a verified-rejected unit after staff approval, through the state machine _(dataset)_
  - re-checks at execution and blocks a unit that moved to pending_verification after the request

</details>

### `Feature/Mobile`

<details><summary><code>backend/tests/Feature/Mobile/AppConfigTest.php</code> (14)</summary>

- **content**
  - returns versions, flags, locales, currencies, media base and links on both paths _(dataset)_
  - reads saved settings over the config defaults, without a deploy
  - publishes guest checkout only while the server allows it (owner decision 4) _(dataset)_
  - switches the web checkout redirect on without a deploy and publishes its URL (owner decision 3)
  - publishes the forgot-password page the app opens, settable without a deploy (M-A10)
  - turns maintenance on and off with a localized message
- **force update**
  - requires an update strictly below the minimum and offers one below the latest _(dataset)_
  - refuses a platform or version it cannot compare _(dataset)_
- **payment gateways**
  - publishes null for Stripe until a publishable key is configured, then the key
  - refuses a secret, a key of the other mode, or a mode without credentials _(dataset)_
  - publishes null for a stored value that does not match the gateway mode
  - lists active gateways only, with no public field for the wallet
  - never contains a secret value or a secret field name
  - stays within its query budget with a cold cache

</details>

<details><summary><code>backend/tests/Feature/Mobile/BiometricAuthTest.php</code> (13)</summary>

- **registration**
  - needs a recent password confirmation on this device
  - registers an Android SPKI key or an iOS raw point, one live key per device, and audits it _(dataset)_
  - refuses a key that is not ECDSA P-256 _(dataset)_
  - cannot be done with a biometric confirmation, so one key can never enrol another
  - needs a customer bearer token
  - rate limits challenge and confirm requests per customer
- **confirmation**
  - satisfies the reveal re-auth without the password and audits it
  - uses every challenge once
  - refuses an expired challenge
  - binds a challenge to the device and token that asked for it
  - refuses a signature from another key, and revokes the key after five failures
  - stops working when turned off, or after a password change _(dataset)_
  - stops working when the device is signed out from another device

</details>

<details><summary><code>backend/tests/Feature/Mobile/CatalogDiscoveryTest.php</code> (23)</summary>

- **product detail**
  - returns the product page on both bases with absolute images, fields, rating and SEO _(dataset)_
  - localizes text and field labels in Arabic, sanitizes translated HTML and falls back to English
  - returns 404 for a draft, inactive, deleted or unknown product _(dataset)_
  - prices the product in the X-Currency currency as the listing does
  - sends short public cache headers and answers 304 to a matching ETag
  - stays within its query budget however many images and fields the product has
- **delivery field definitions**
  - publishes exactly the specs the cart validator enforces
  - accepts values built from the published definitions and refuses values that break them
- **absolute image URLs on the listing**
  - keeps the stored path and adds an absolute URL from the disk or the configured CDN
- **platforms and categories**
  - lists active platforms in order with listed-product counts and only uploaded images _(dataset)_
  - lists active categories with parents first, hides children of inactive parents and counts secondary products
  - localizes platform and category names in Arabic
  - stays within its query budget whatever the number of platforms and categories
- **countries**
  - lists the active countries, localized, on both bases _(dataset)_
  - lists exactly the countries checkout validation accepts
- **home**
  - returns active sections in order with manual items resolved and hidden items dropped _(dataset)_
  - fills automatic sections from the listing rules
  - shows featured approved reviews of active products only
  - shows only active banners inside their schedule, with images and structured links
  - maps stored web links to app targets _(dataset)_
  - prices carousel products exactly as the listing in another currency
  - localizes section, banner and product text in Arabic
  - stays within its query budget however many items the sections hold

</details>

<details><summary><code>backend/tests/Feature/Mobile/MobileAccountTest.php</code> (15)</summary>

- **account routes with a bearer token**
  - reads the wallet, reviews and refund requests _(dataset)_
  - opens, reads and answers a ticket and hides other customers' tickets
  - enrols two-factor authentication without a session
  - needs a bearer token: no token, a web session and a bearer token on the web path are refused
- **reveal with token-scoped recent authentication**
  - asks for the password, then reveals within the window
  - keeps the confirmation on the token that confirmed: another device must confirm itself
  - carries the confirmation over a refresh of the same sign-in, without extending the window
  - stores the X-Device-Integrity verdict in the reveal audit and never blocks on it (owner decision 5) _(dataset)_
  - records the verdict on a refused reveal too
  - still refuses another customer's unit with 404
- **who may use the customer routes**
  - refuses a customer token without the customer ability on account and checkout routes _(dataset)_
  - never treats a bad bearer token as a guest
  - refuses staff tokens and service-account tokens even with the customer ability
  - refuses a staff web session on the mobile account routes
  - answers 403 to a suspended account on account and checkout routes

</details>

<details><summary><code>backend/tests/Feature/Mobile/MobileAuthTest.php</code> (17)</summary>

  - registers a customer and returns a token pair without a session
  - requires a device name to register and to sign in
  - signs a customer in with a customer token and a refresh token
  - refuses a wrong password with the same error as an unknown email
  - refuses staff accounts with the generic sign-in error
  - refuses staff accounts with two-factor enabled before any challenge
  - refuses suspended and closed accounts like the web login _(dataset)_
  - returns a challenge instead of tokens when two-factor is enabled
  - completes the two-factor challenge with a code or a recovery code _(dataset)_
  - rejects a wrong code and spends the challenge after the attempt limit
  - rejects an expired or unknown challenge
  - rejects a challenge of an account suspended after the password step
  - audits the first sign-in from each device name only
  - throttles sign-in attempts per email and IP with the auth limiter
  - throttles registration with the auth limiter
  - throttles two-factor attempts per challenge with the otp limiter
  - keeps the web login on the session and issues no tokens

</details>

<details><summary><code>backend/tests/Feature/Mobile/MobileCheckoutTest.php</code> (17)</summary>

- **guest place-order**
  - places an order with headers only and returns a guest order token stored as a hash
  - replays the same key and body without a second order and hands out a token again
  - records the request locale on the order
  - refuses the same key with a different body
  - returns no guest token for a signed-in customer, whose orders open by account
- **guest checkout switch (owner decision 4, OD-1)**
  - refuses a guest place-order on the app route with 401 guest_checkout_disabled by default, creating nothing
- **guest order token scope**
  - verifies a payment server side for the holder of the token
  - refuses a token of another order, an expired token and a customer order
  - prunes expired tokens on schedule
  - keeps the session list for the browser and ignores the header there
- **stateless guest OTP and reveal**
  - needs the signed link and the email code on the token, as the session does
  - does not reveal with the email code alone
  - refuses a tampered or expired link and a token of another order
  - refuses an expired token with both proofs
  - still refuses a wrong code
- **limiters without a session**
  - counts the checkout limiter per cart token, not per request
  - counts the checkout limiter per customer for bearer clients

</details>

<details><summary><code>backend/tests/Feature/Mobile/MobilePaymentReturnTest.php</code> (25)</summary>

- **PayPal return and cancel URLs**
  - sends the web return page as return_url and cancel_url for a web attempt
  - sends the configured web cancel page when one is set
  - sends the app return and cancel links for an app attempt
  - builds app links on APP_URL even when the current URL root is another host
  - refuses an app template that is not a path on the store domain and sends nothing _(dataset)_
- **Paymob redirection_url**
  - sends the return link of the channel _(dataset)_
- **Stripe next.return_url**
  - publishes the app return link for an app attempt and nothing for a web attempt
  - publishes the configured app scheme when one is set
  - does not change the PaymentIntent create request
- **the client channel at place-order and retry-payment**
  - defaults to app on the mobile group and returns the server return URL
  - defaults to web on the session group, with no next.return_url
  - honours an explicit client
  - ignores a return URL sent by the client
  - rejects a client that is not a known channel _(dataset)_
  - keeps the app channel on a retry from the app, and lets the retry name another channel
  - rejects an unknown client on retry-payment
- **the app return links in a browser**
  - redirects to the web return page, dropping every query parameter
  - redirects the cancel link to the web cancel page
  - never changes the order or the payment and calls no provider, even with success=true
  - is not found for an unknown order or a malformed id _(dataset)_
- **app link association files**
  - serves an empty assetlinks.json when no fingerprint is configured
  - declares every configured package with every valid fingerprint
  - serves an apple-app-site-association with no apps when unconfigured
  - lists the configured app ids for every path the app handles, as the Android intent filter does
  - never claims the whole site, so web pages the app does not handle stay in the browser

</details>

<details><summary><code>backend/tests/Feature/Mobile/MobileStorefrontTest.php</code> (15)</summary>

- **guest cart by header**
  - issues a cart token on the first add, returns it once and stores only its hash, without a cookie
  - keeps empty JSON objects as objects in the response that issues the token
  - updates, removes and applies a coupon with the header only
  - never shows or changes a cart to a client without its token
  - needs no CSRF token and ignores the browser cookie
- **cart adoption on mobile sign-in**
  - moves the guest cart to the customer who presents its token with a bearer token
  - keeps the customer cart when they already have one
- **display currency header**
  - prices the listing, the variants and the cart in the X-Currency currency
  - overrides the currency cookie
  - refuses a currency that is malformed, unknown or inactive _(dataset)_
- **locale**
  - returns Arabic names from content_translations and falls back to the English base column
  - translates variant titles and cart lines
  - adds one translation query to a listing page in Arabic and none in English
  - localizes validation and domain messages but never the codes
  - prefers the saved locale of a signed-in web customer over the header

</details>

<details><summary><code>backend/tests/Feature/Mobile/MobileTokenTest.php</code> (18)</summary>

  - returns the signed-in customer from me
  - refuses me without a token, with a session, or with an unknown token
  - rejects an expired access token
  - rejects a customer token without the exact customer ability _(dataset)_
  - rejects the token of an account suspended after sign-in
  - rotates the refresh token and replaces the access token
  - revokes the whole family and audits when a used refresh token is presented again
  - refuses an unknown or expired refresh token
  - refuses to refresh for an account suspended after sign-in
  - throttles refresh attempts per token
  - signs out the current device only
  - signs out every device
  - refuses logout without a customer token
  - never accepts a customer token on MCP, automation or admin routes
  - never accepts a service-account token on mobile routes, whatever its abilities _(dataset)_
  - keeps MCP tokens working beside customer tokens
  - prunes expired refresh tokens, challenges and customer access tokens only
  - schedules the prune hourly on one server

</details>

### `Feature/MobileContract`

<details><summary><code>backend/tests/Feature/MobileContract/AccountContractTest.php</code> (15)</summary>

- **orders and reveal**
  - lists and shows orders
  - asks for recent authentication, confirms the password and reveals the canary
- **wallet, reviews, tickets and refund requests**
  - reads the wallet and its statement
  - reads a frozen wallet
  - reads an empty wallet
  - lists eligible items, submits a review and refuses a second one
  - refuses a review from an unverified email
  - opens, reads and answers a ticket, and refuses a closed one
  - requests a refund and refuses a second open request
- **notifications**
  - lists, counts and marks the inbox, and reads and updates preferences
- **profile, password, devices and verification**
  - reads and updates the profile
  - changes the password
  - lists devices, revokes another and refuses the current one
  - resends the verification email
- **two-factor security**
  - enrols, confirms and disables two-factor authentication

</details>

<details><summary><code>backend/tests/Feature/MobileContract/AuthContractTest.php</code> (7)</summary>

- **auth**
  - registers
  - signs in with and without a second factor
  - refuses wrong credentials and then rate limits
  - refreshes, detects reuse and reads the signed-in user
  - signs out this device and every device
  - enrols a biometric key and confirms recent auth with it (M-A10, API_DOCUMENTATION.md 1g)
- **app config**
  - returns the public configuration with an update verdict

</details>

<details><summary><code>backend/tests/Feature/MobileContract/CartCheckoutContractTest.php</code> (10)</summary>

- **cart**
  - runs a guest cart with the X-Cart-Token header
  - refuses an unknown variant and a missing variant
  - keeps a signed-in customer cart by account
- **guest checkout disabled**
  - refuses a guest place-order while guest checkout is off (owner decision 4)
- **checkout**
  - validates and lists payment options
  - refuses an empty cart
  - places a guest order, replays it, refuses a changed body and reports a duplicate
  - refuses a changed price and a missing consent
  - starts no attempt when the provider is down, so next is null
  - places a customer order without a guest order token

</details>

<details><summary><code>backend/tests/Feature/MobileContract/FixtureSafetyContractTest.php</code> (4)</summary>

  - has fixtures for every area the app uses
  - holds no secret key material, provider secrets or bearer values
  - reveals only the canary code
  - sends money as strings, never as JSON numbers

</details>

<details><summary><code>backend/tests/Feature/MobileContract/PaymentsContractTest.php</code> (6)</summary>

- **next shapes from retry-payment**
  - returns a Stripe client secret with the app return URL
  - returns a PayPal approve URL, then captures
  - returns a Paymob redirect URL
  - returns the wallet shape when the balance paid at placement
- **verification**
  - verifies a Stripe payment server side and refuses capture on a non-PayPal order
- **guest order access and reveal**
  - opens the order with the signed link and the email code, then reveals the canary

</details>

<details><summary><code>backend/tests/Feature/MobileContract/StorefrontContractTest.php</code> (6)</summary>

- **home**
  - returns sections and banners in English and Arabic
- **catalog**
  - lists products, platforms, categories, countries and currencies _(dataset)_
  - filters the listing and prices it in the X-Currency currency
- **product**
  - returns the detail, variants and reviews in English and Arabic _(dataset)_
  - answers 404 for an unknown product
- **search**
  - suggests and lists popular terms

</details>

### `Feature/Orders`

<details><summary><code>backend/tests/Feature/Orders/CartTest.php</code> (15)</summary>

  - lets a guest fill a cart identified by an http-only cookie
  - returns no cart to a visitor without one
  - merges a repeated add into one line and enforces the per-product limit
  - updates and removes only items in the owner's own cart
  - refuses products that cannot be sold _(dataset)_
  - refuses a custom amount in the request
- **delivery fields**
  - requires and validates a Player ID when adding to the cart _(dataset)_
  - stores accepted values encrypted and shows them to the owner
  - keeps different Player IDs for the same variant on separate lines
- **coupons**
  - refuses every code under the fail-closed coupon binding
  - applies a code accepted by the coupon contract and removes it again
  - lets a customer who signs in keep the cart they filled as a guest
  - marks lines not sold in the chosen country and refuses suspended accounts
  - needs a CSRF token for cart writes outside tests
  - prices the cart in the same number of queries however many lines it has

</details>

<details><summary><code>backend/tests/Feature/Orders/CheckoutConcurrencyTest.php</code> (5)</summary>

  - handles all three Idempotency-Key cases across processes while the first request holds the cart
  - creates one order when two processes submit the same cart at the same moment with different keys
  - creates one order when two processes race with the same key
  - lets the unique placement index stop two guest carts with the same email and contents
  - leaves no partial order when a process dies mid-transaction, and the key can be retried once stale

</details>

<details><summary><code>backend/tests/Feature/Orders/CheckoutTest.php</code> (29)</summary>

  - places a pending_payment order with items, units and an event, and converts the cart
- **snapshots**
  - populates every currency snapshot field once, from the rate in force
  - charges a different payment currency from the base total with a fresh rate
  - never changes a placed order when rates, prices and settings change later
- **stale rates over HTTP**
  - still shows the cart with a may-vary flag but not as ready
  - blocks checkout validation and placement in that currency
  - allows checkout once the customer switches to the base currency
  - blocks a payment currency whose rate is stale even when display is fresh
- **refusals that change nothing**
  - refuses placement with the fail-closed resolver bound
  - refuses when the total or currency differs from what the customer saw _(dataset)_
  - validates the request _(dataset)_
  - refuses an empty cart
  - rolls back everything when the transaction fails late, and the same key can retry
- **idempotency**
  - replays the stored response for the same key and body
  - treats the body as the same regardless of key order
  - refuses the same key with a different body
  - scopes keys to their owner
- **duplicate placement**
  - does not place the same cart twice under a new key
  - returns the open order when the same contents are placed again from a new cart
  - allows the same contents again once the earlier order is closed
  - treats two guest carts with the same email and contents as one placement
- **guest orders**
  - places a guest order and shows its status only to the session that placed it
  - revalidates delivery values at checkout and stores them encrypted on the order
- **cart-level coupon allocation (ADR-025)**
  - splits a fixed discount over units in whole cents that sum to the discount exactly
  - limits a unit to its margin floor and moves the rest of the discount to units with room
  - forfeits what no unit can absorb and says so
  - lets a coupon marked allow_below_margin cross the floor
  - refuses checkout when the applied coupon no longer validates
  - expires an unpaid placed order and releases nothing it did not reserve

</details>

<details><summary><code>backend/tests/Feature/Orders/GuestOrderAccessTest.php</code> (8)</summary>

  - emails a six digit code to the guest email of the order and stores only its hash
  - answers the same way and sends nothing when the details do not match _(dataset)_
  - verifies the guest email with the right code and opens the order to this session
  - refuses a wrong code and counts the attempt
  - locks a code after five wrong attempts
  - refuses an expired code and a code replaced by a newer one
  - caps how many codes one order can send per hour
  - rate limits the code endpoints

</details>

<details><summary><code>backend/tests/Feature/Orders/IdempotencyStoreTest.php</code> (5)</summary>

  - reports a key that is still being processed and one reused with another body
  - replays a completed response
  - takes over an abandoned claim and fences out the original holder
  - releases a key so the same request can retry
  - keeps owners apart and forgets keys after retention

</details>

<details><summary><code>backend/tests/Feature/Orders/OrderLifecycleTest.php</code> (14)</summary>

- **aggregator**
  - derives the order status from units in the documented precedence _(dataset)_
  - refuses to derive a status without units
  - applies the derived status through the state machine
  - does nothing when the order already has the derived status
  - throws when the derived move is not in the table
- **expiry**
  - cancels unpaid orders past their window and leaves the rest
  - is a no-op the second time
- **with another connection holding the order**
  - skips an order locked by a payment in progress and cancels it on the next run
- **risk**
  - sends a clean paid order to processing and stores the assessment
  - holds a risky paid order for manual review with the signals that fired
  - marks a score at the block threshold as block and still only holds it
  - counts velocity, failed attempts and card reuse
  - assesses an order once
  - refuses to run on an unpaid order

</details>

<details><summary><code>backend/tests/Feature/Orders/OrderStateMachineTest.php</code> (11)</summary>

  - performs every row of the transition table and records an event _(dataset)_
  - refuses every transition the table does not list, changing nothing _(dataset)_
  - refuses an actor the row does not name _(dataset)_
  - refuses staff rows to staff without the permission and to customers _(dataset)_
  - never moves an order without a verified payment into paid or fulfilment (rule 9) _(dataset)_
  - lets a customer cancel an unpaid order but never mark one paid
  - logs an alert for an illegal transition
  - stamps paid_at and completed_at and clears the placement hash on closed statuses
  - releases the coupon reservation when an unpaid order is cancelled or fails _(dataset)_
  - refuses a status change saved through the model
  - has no code outside the state machine that writes order status

</details>

### `Feature/Payments`

<details><summary><code>backend/tests/Feature/Payments/EligibilityAndCheckoutTest.php</code> (18)</summary>

- **default deny**
  - offers nothing while the seeded gateways are inactive
  - refuses an active gateway that has no matching allow rule
  - refuses a gateway without credentials for its mode, without a merchant currency, or excluded for a product _(dataset)_
  - never offers the wallet to a customer without a wallet balance
- **seeded country and currency rules**
  - offers Paymob only to a customer declared and located in Egypt paying in EGP
  - does not offer Paymob outside Egypt, from a non-Egyptian or unknown IP, or for a non-EGP display currency _(dataset)_
  - offers Paymob for any display currency in Egypt and always charges EGP _(dataset)_
  - places a Paymob order from a USD display, charging EGP once at the snapshot rate
  - still refuses Paymob at place-order outside Egypt, whatever the client sends
  - charges Stripe in USD even when the merchant account also enables the display currency
  - offers Stripe and PayPal internationally but not to customers declared in Egypt
  - charges PayPal in its default currency when the customer browses in EGP, SAR or AED, and says so _(dataset)_
  - lists a gateway as unavailable when its payment currency would push a unit below the margin floor
- **place-order with the real resolver**
  - places the order, snapshots the payment currency and IP country, and creates the Stripe attempt
  - returns the order with a null next when the first attempt cannot start, and lets the customer retry
  - refuses to retry with another gateway or once the order is paid
- **GeoIP**
  - resolves nothing with the default null driver
  - reads a two-letter country from the trusted header and ignores unknown or malformed values _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Payments/PayPalPaymentTest.php</code> (14)</summary>

- **webhook verification**
  - verifies through verify-webhook-signature with the raw event, then marks paid on capture COMPLETED
  - rejects an event PayPal does not verify
  - rejects an event with missing transmission headers without calling PayPal
  - answers 503 when the verification API cannot be reached, so PayPal retries
  - stores a duplicate delivery once
- **capture**
  - does not mark paid when the order is COMPLETED but its capture is PENDING
  - captures an approved order server side when CHECKOUT.ORDER.APPROVED arrives (recovery capture)
  - captures from the capture endpoint for the order's own session only
  - marks a DECLINED capture failed
- **ordering and timeouts**
  - ignores a DECLINED event that arrives after the capture completed, because the re-read says COMPLETED
  - changes nothing when the order read times out during webhook processing
- **attempts and currencies**
  - creates the order with PayPal-Request-Id, the invoice id and a decimal amount
  - never sends EGP, SAR or AED to PayPal, even if the merchant list wrongly enables them _(dataset)_
  - refunds against the capture with PayPal-Request-Id

</details>

<details><summary><code>backend/tests/Feature/Payments/PaymentConcurrencyTest.php</code> (2)</summary>

  - applies success once when a webhook and the return-page status check race on the same payment _(dataset)_
  - stores one row when the same signed event is delivered twice at the same moment

</details>

<details><summary><code>backend/tests/Feature/Payments/PaymobPaymentTest.php</code> (12)</summary>

- **HMAC**
  - concatenates the 20 official fields in order, with booleans as true and false
  - accepts a valid callback, re-reads the transaction and marks the order paid
  - accepts the hmac from the body when it is not in the query string
  - rejects a wrong hmac, a tampered field and a missing hmac _(dataset)_
  - stores a repeated callback once, using the state hash as event id
  - sends a callback whose re-read amount differs to manual_review
  - treats a declined transaction as a failed attempt
- **ordering and timeouts**
  - keeps the order paid when a declined callback for the same order arrives late and the re-read still says success
  - changes nothing when the transaction read times out
- **intention**
  - creates an intention in EGP piastres with the integration id and returns the Unified Checkout URL
- **refunds**
  - sends a partial refund in piastres against the transaction and allows only one in flight
  - resolves a timed-out refund by re-reading refunded_amount_cents, never by sending it again

</details>

<details><summary><code>backend/tests/Feature/Payments/RefundTest.php</code> (12)</summary>

- **automatic refunds of undelivered units (BD4 default)**
  - refunds only the failed unit of a partially fulfilled order and ends partially_refunded
  - refunds the whole payment when every unit failed and ends refunded
  - sends a refund the provider refuses to manual_review and releases the units for a new refund
  - leaves a timed-out refund unknown, then resolves it by re-reading the provider, never by a second create
- **staff refunds (BD4 admin discretion)**
  - refunds delivered units in two partial refunds that add up to the payment
  - refuses to refund the same unit twice
  - refuses a refund once nothing remains on the payment
  - denies staff without refunds.create and changes nothing
  - validates the request and refuses a unit of another order
- **order_item_financials**
  - is written on completion with revenue, supplier cost, estimated fee, FX cost and net
  - books no supplier cost for a unit whose supplier attempt FAILED (cost reversal pending) and records the refund
  - is written once per item however many settling transitions happen

</details>

<details><summary><code>backend/tests/Feature/Payments/StripePaymentTest.php</code> (27)</summary>

- **webhook signatures**
  - accepts a valid signature, re-reads the intent and marks the order paid, which starts fulfilment
  - rejects a wrong, a stale and a missing signature without storing or applying anything _(dataset)_
  - rejects a valid signature over a different body (raw body is what is signed)
- **idempotency and ordering**
  - stores a duplicate delivery once and processes it once
  - marks paid once when two distinct events describe the same success
  - ignores a failure that arrives after success, because the re-read still says succeeded
  - never lets a failed state overwrite a settled payment, even when applied directly
  - handles a refund event that arrives before the success event: the payment is re-verified first
- **verification rules**
  - sends an amount mismatch to manual_review and never marks it paid
  - sends a currency mismatch to manual_review
  - trusts the re-read, not the webhook body: a body claiming success while the provider says processing
  - sends a late payment on a cancelled order to manual_review, never to fulfilment
  - records a second successful payment as duplicate_capture and refunds it in full
  - marks a declined payment failed and keeps the order open for another attempt
  - fails the order when the last permitted attempt fails
  - changes nothing when the provider times out during verification, and recovers on reconciliation
  - queues the GA4 purchase event once, however many times success is applied
- **browser return**
  - does not mark paid on return alone: without a provider success the order stays unpaid
  - marks paid on return only because the server-side check confirmed it, with no webhook at all
  - hides another session's guest order
- **payment attempts**
  - creates the intent with the snapshot amount in minor units and the pay_create idempotency key
  - expires an attempt whose create timed out, without retrying blindly
  - refuses a key that does not match the gateway mode
- **secrets and limits**
  - keeps the secret key, the signing secret and the signature out of logs and stored webhook headers
  - rate limits the webhook endpoint
- **disputes and reconciliation**
  - records a dispute and flags the order without changing its status
  - records a daily mismatch when the provider no longer agrees with a settled payment, and changes nothing

</details>

### `Feature/Pricing`

<details><summary><code>backend/tests/Feature/Pricing/MarginAndRecalculationTest.php</code> (27)</summary>

- **explicit prices below the floor**
  - accepts a price at or above the floor without authorization
  - refuses a below-floor price from staff without the override permission
  - requires a reason even with the permission
  - accepts an authorized override and records who, why and the floor
  - refuses a price whose margin cannot be verified
  - checks a foreign currency price in base
  - reports a missing rate rather than guessing
  - guards updates too
  - rejects a duplicate price with no start date
- **pricing rules**
  - refuses the allow policy without the override permission
  - needs a reason for the allow policy and audits it
  - needs authorization to change an existing rule to allow
  - accepts floor and block with no extra permission _(dataset)_
  - validates the scope target, percentages and dynamic conditions _(dataset)_
  - needs pricing.manage to write and pricing.view to read
  - marks only the variants in the rule scope dirty
  - marks every variant dirty for a global rule and on delete
- **recalculation**
  - stores the listing price and clears the mark
  - is safe to run twice
  - clears the listing price when a variant cannot be sold
  - runs from the scheduled command through the queue
  - leaves a variant dirty if it was marked again after being read
  - moves the mark forward even twice in one second
  - marks rules, prices and promotions that started or ended since the last sweep
  - marks variants whose supplier cost currency rate changed
- **with another connection holding a row lock**
  - skips the locked row and picks it up on the next run
  - never touches historical order prices when rates and prices change

</details>

<details><summary><code>backend/tests/Feature/Pricing/PricingEngineTest.php</code> (56)</summary>

- **step 1: cost**
  - uses the manual cost when there is no supplier mapping
  - uses the latest supplier cost of the primary mapping over the manual cost
  - prefers a mapping cost override
  - ignores secondary and inactive mappings
  - converts a foreign cost at the rate without markup
  - is not purchasable with no cost
  - is not purchasable when the cost currency rate is stale or missing
  - does not price custom amount variants
- **step 2: explicit price**
  - beats any markup rule
  - country and currency beats currency beats country
  - does not apply currency markup to a converted explicit price
  - charges exactly the explicit amount, rounded only to precision
  - honours the validity window
  - skips the margin guard: a deliberate explicit price below cost is sold as set
  - works without a known cost
- **step 3: markup rule precedence**
  - takes the most specific scope, one level at a time
  - does not stack rules
  - matches a rule on a secondary category
  - applies fixed and percent together
  - within a scope prefers country and currency, then country, then currency, then neither
  - ignores rules for another country or currency
  - breaks ties by lower priority number, then newest
  - ignores inactive and out-of-window rules
  - does not let a margin-only rule shadow a broader markup
  - reports cost only when no rule matches
- **step 4: dynamic rules**
  - applies when conditions hold, at its own scope position
  - is skipped when conditions fail, falling through to the next rule
  - evaluates a supplier cost band _(dataset)_
  - fails closed on an unknown condition or none _(dataset)_
- **step 5: promotion**
  - applies the best single promotion
  - stacks only promotions marked stackable
  - does not combine a best non-stackable promotion with stackable ones
  - supports fixed price promotions and targets by category and platform
  - does not apply expired or future promotions
  - applies to explicit prices but never below the margin floor
- **step 6: coupon**
  - applies after the promotion
  - caps at the maximum discount and supports fixed coupons
  - stops at the margin floor unless the coupon allows going below
  - stops at the floor on an explicit price too
- **step 7: margin guard**
  - floor raises the price to the floor
  - block makes it not purchasable
  - allow sells below the floor
  - defaults to block at break-even when nothing is configured
  - allows exactly the floor
  - accounts for gateway fees and conversion cost, using the most expensive gateway
  - requires both a percent and a fixed margin when both are set
  - uses the most specific margin, ignoring platform scope, and inherits the policy
  - blocks when no price can reach the margin
- **step 8: conversion**
  - converts at the effective rate with markup and applies currency rounding
  - reports a missing rate rather than inventing one
  - shows a stale rate for display but blocks it for checkout
  - carries the history row for the order snapshot
  - prices many variants with the same number of queries as few
  - exposes a price simulation without changing anything
  - serves variant offers in the display currency without leaking cost
  - hides variant offers of an unpublished product

</details>

### `Feature/Promotions`

<details><summary><code>backend/tests/Feature/Promotions/CouponConcurrencyTest.php</code> (3)</summary>

  - lets only one of two simultaneous orders take the last use of a coupon _(dataset)_
  - lets one customer take a once-per-customer coupon only once across simultaneous orders _(dataset)_
  - grants exactly two uses of a limit of two when three buyers race

</details>

<details><summary><code>backend/tests/Feature/Promotions/CouponTest.php</code> (21)</summary>

  - is bound as the real coupon contract, with the fail-closed implementation still available
- **validity**
  - applies a valid code, ignoring case, and discounts the cart
  - applies a percentage capped by max_discount_base
  - refuses a code that cannot be used now _(dataset)_
  - refuses unknown and deleted codes alike
  - refuses a customer who already holds their per-user uses, by account or by email
  - does not count a released use against the buyer
  - limits first-order coupons to buyers without a paid order
  - applies a restricted coupon to the eligible lines only and refuses it when none match
  - converts a fixed coupon set in another currency to the base currency with the stored rate
- **reservation, release and redemption**
  - reserves one use at placement and redeems it when the order is paid
  - refuses placement when the last use was taken after the coupon was applied, and creates nothing
  - refuses a second order by the same customer beyond the per-user limit
  - releases the use when the unpaid order expires, so it can be used again
  - releases the use when an unpaid order fails or is cancelled from review, and only once _(dataset)_
  - never releases a use once the order was paid
  - has a database check that stops used_count passing the global limit
- **admin**
  - creates, lists, updates and deletes coupons with marketing.coupons, audited
  - validates coupon input _(dataset)_
  - refuses to lower the global limit below the uses already held
  - needs marketing.coupons

</details>

<details><summary><code>backend/tests/Feature/Promotions/PromotionAdminTest.php</code> (3)</summary>

  - creates a promotion with targets, replaces them on update and marks prices for recalculation
  - validates promotion input _(dataset)_
  - needs marketing.promotions

</details>

### `Feature/Reviews`

<details><summary><code>backend/tests/Feature/Reviews/ReviewTest.php</code> (10)</summary>

- **verified purchase**
  - accepts a review of a delivered item from the buyer, pending moderation, as plain text
  - refuses an item the customer did not buy, did not receive, or has not paid for _(dataset)_
  - refuses a second review of the same item
  - requires a verified email and a signed-in customer
  - validates the rating _(dataset)_
- **moderation and publication**
  - publishes only approved reviews and keeps the product rating to approved ones
  - recomputes the rating when an approved review is rejected or deleted
  - features approved reviews only and requires a reason to reject
  - needs catalog.manage to moderate
  - hides the reviews of a product that is not active

</details>

### `Feature/Schema`

<details><summary><code>backend/tests/Feature/Schema/ModelFactoryTest.php</code> (1)</summary>

  - creates a valid row and resolves every relation _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Schema/SchemaAcceptanceTest.php</code> (17)</summary>

  - has every table in section 17 of the database design _(dataset)_
  - creates exactly the tables in the design
  - rejects a second active attempt for a unit _(dataset)_
  - allows a new attempt after not_accepted_verified, then locks again
  - allows a new attempt after a definitive rejection
  - allows active attempts on different units
  - rejects a duplicate attempt number for one reference
  - cannot delete a unit that has a supplier attempt
  - allows one order per primary payment
  - allows many orders with no primary payment yet
  - enforces the primary payment foreign key
  - stores one webhook per provider event per gateway
  - allows the same event id on a different gateway
  - rejects a duplicate digital code hash
  - uses decimal 20,6 for money and never a float column
  - keeps order status and totals out of mass assignment
  - never serializes digital code ciphertext

</details>

<details><summary><code>backend/tests/Feature/Schema/SeederTest.php</code> (10)</summary>

  - seeds reference data, no rates, no users and no orders
  - can be seeded twice without duplicates
  - seeds gateways inactive with no credentials
  - restricts Paymob to Egypt, in EGP, with a matching IP
  - keeps Reloadly inactive and the built-in fulfilment paths active
  - fails clearly when super admin credentials are missing _(dataset)_
  - rejects a weak super admin password
  - creates a verified staff Super Admin from configuration
  - refuses to seed the demo catalog in production
  - seeds the demo catalog outside production

</details>

### `Feature/Seo`

<details><summary><code>backend/tests/Feature/Seo/AnalyticsTest.php</code> (9)</summary>

  - loads no tag, no consent banner and no Google source while analytics is not configured
  - hands the public GTM id to the page, behind the consent banner, with the exact Google sources in the CSP
  - never adds Google sources to the admin CSP
  - pushes view_item with the rendered price on the product page and begin_checkout on checkout
  - sends no browser events while they are switched off, and the add_to_cart data stays on the buttons
  - refuses purchase and refund as browser events on the server _(dataset)_
  - has no browser code path that can emit purchase
  - never renders purchase on any page of a paid order, and the server sends it exactly once
  - saves and validates the analytics settings, and prints the Search Console verification tag

</details>

<details><summary><code>backend/tests/Feature/Seo/CanonicalRulesTest.php</code> (12)</summary>

  - canonicalizes product query strings to the clean product URL with matching hreflang
  - canonicalizes one filter or a sort to the unfiltered category and keeps it indexable _(dataset)_
  - marks two or more filters noindex,follow without canonical or hreflang
  - keeps paginated listings self-canonical with the page in hreflang and a "Page N" title
  - marks an empty (thin) listing noindex
  - keeps search noindex,follow with no canonical or hreflang
  - never puts the display currency in the URL
  - applies an admin robots value, canonical override and title, then templates
  - removes a trailing slash with a 301, keeping the query string
  - sends upper-case storefront paths to their lower-case URL with a 301
  - never normalises JSON or admin paths
  - has no country segment: a country code path is an ordinary 404 _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Seo/MeasurementProtocolTest.php</code> (7)</summary>

  - sends purchase once with the documented body, then does nothing on any later run
  - uses the EU collection host when configured
  - sends a pseudonymous client id with ad consent denied when the browser gave none
  - stays pending without an attempt while awaiting credentials or switched off
  - counts a failed attempt for a retry and fails the row after the attempt limit
  - retries through the queued job with backoff
  - queues every pending row from the scheduler command

</details>

<details><summary><code>backend/tests/Feature/Seo/RedirectRuntimeTest.php</code> (13)</summary>

  - sends an old path to its target with 301 or 302 and counts the hit _(dataset)_
  - keeps the query string when the target has none
  - answers 410 Gone with the designed page for a removed URL, in the language of the path
  - ignores inactive redirects and never redirects a page that exists
  - applies a change to a cached redirect at once
  - follows a chain that slipped through to its final target in one redirect
  - never sends a loop, or a chain longer than the hop limit, to the browser
  - makes a chain that ends in a removed URL gone
  - collapses chains when an admin saves a redirect
  - answers 410 for a permanently removed product and 404 for an unknown one
  - creates 301s in every language when a slug changes, without loops when it changes back
  - logs storefront misses with a hit count, without the query string, and shows popular searches
  - does not log JSON misses or file probes

</details>

<details><summary><code>backend/tests/Feature/Seo/SearchConsoleTest.php</code> (11)</summary>

- **OAuth connection**
  - sends the admin to Google with the read-only scope, offline access, state and PKCE
  - asks for the client id and secret before connecting
  - stores the refresh token encrypted and never shows it
  - refuses a callback with a wrong, missing or reused state, or a denied consent
  - needs settings.manage_critical to connect or disconnect, and seo.manage for the report
  - revokes and deletes the token on disconnect
- **reporting**
  - fetches totals, queries and pages into search_console_metrics, idempotently
  - does nothing while awaiting credentials
  - reports an expired consent as an error without a token in the message
  - shows clicks, impressions, CTR, position, top queries and pages, in English and Arabic
  - says the report awaits credentials when nothing was fetched

</details>

<details><summary><code>backend/tests/Feature/Seo/SitemapTest.php</code> (7)</summary>

  - serves an index of child sitemaps per type and language, generated on first request
  - lists each product with hreflang alternates for en, ar and x-default and its lastmod
  - excludes drafts, inactive and noindex pages and never lists private pages
  - splits a type into chunks of the configured size
  - replaces the previous set on regeneration and 404s unknown files
  - regenerates hourly from the scheduler
  - serves robots.txt that references the index and disallows private areas in every language

</details>

<details><summary><code>backend/tests/Feature/Seo/StructuredDataTest.php</code> (11)</summary>

  - describes the organization and the site search on every page, per language
  - marks up a product with one priced variant as a Product with an Offer at the rendered price
  - uses an AggregateOffer with the low and high price for several variants
  - prices the offer in the display currency the page uses
  - adds AggregateRating only when approved reviews exist
  - marks up the Arabic product page with Arabic names and Arabic URLs
  - never lets catalog text close the JSON-LD script
  - marks up a category as a CollectionPage listing its products, with breadcrumbs
  - marks up a blog post as a BlogPosting
  - lists visible blog posts only and hides drafts and future posts
  - adds FAQPage only while the FAQ schema setting is on

</details>

### `Feature/Storefront`

<details><summary><code>backend/tests/Feature/Storefront/AccountAuthorizationTest.php</code> (12)</summary>

- **pages**
  - is 404 for another customer's order detail, in both languages _(dataset)_
  - is 404 for a guest order in the account, even for the guest email's customer
  - is 404 for another customer's ticket, in both languages, without its messages _(dataset)_
  - never lists another customer's orders, codes, tickets, reviews, wishlist, wallet, notifications or devices
- **actions**
  - refuses to reveal another customer's code, even after the intruder confirmed their password
  - refuses to reply to another customer's ticket
  - refuses to edit another customer's review
  - refuses to sign out another customer's device
  - refuses to mark another customer's notification read
  - refuses a payment retry on another customer's order
  - refuses refund requests and tickets about another customer's order without creating anything
  - only ever changes the caller's own wishlist

</details>

<details><summary><code>backend/tests/Feature/Storefront/AccountPagesTest.php</code> (31)</summary>

- **access**
  - sends guests to the sign-in page of the page language _(dataset)_
  - serves every account page, never indexed, with the strict CSP and the account navigation _(dataset)_
  - refuses a suspended account on the account pages
- **dashboard**
  - greets the customer with order, wallet and sealed-code figures, the sealed notice and recent orders _(dataset)_
  - greets in English as the placeholder did
  - shows an empty state and a verification banner for a new unverified customer
  - shows no verification banner to a verified customer
- **orders**
  - lists only the customer's orders with server-formatted totals and links to the detail _(dataset)_
  - filters by status and period
  - sends an invalid filter back to the unfiltered page
  - shows the detail with timeline, payment amounts, the reveal component and a refund request form, never the code _(dataset)_
  - offers the payment retry on an unpaid order and no refund request
  - shows a pending refund request instead of the form
  - resolves the delivery email link to the account order page
- **digital codes**
  - lists delivered codes masked, with per-unit reveal URLs and no code in the page _(dataset)_
  - filters by state and searches by product or order number
- **wallet, wishlist and reviews**
  - shows the balance and the statement with server-formatted amounts _(dataset)_
  - shows saved products as cards and an empty state otherwise _(dataset)_
  - marks a saved product on the product page
  - offers items to review and lists the customer's reviews with an edit form _(dataset)_
- **support tickets**
  - lists tickets and shows a thread without internal notes, with the reply form _(dataset)_
  - opens the new-ticket form about one of the customer's orders only
  - resolves the ticket reply email link to the account ticket page
- **notifications, profile and security**
  - shows the inbox with account links and the preference matrix _(dataset)_
  - shows the profile with the saved country, language and display currency selected _(dataset)_
  - shows password change, two-factor enrolment, the reveal history and this device _(dataset)_
  - serves the QR code of a pending enrolment only, as uncached SVG without the secret in any page
  - offers turning two-factor off once it is on
  - lists the customer's reveal attempts, granted and denied
  - keeps the orders and ticket routes on ULIDs only
  - has the same account translation keys in English and Arabic

</details>

<details><summary><code>backend/tests/Feature/Storefront/AccountRevealTest.php</code> (6)</summary>

  - wires the same reveal component on every page that shows codes, with the 60 second hide and clipboard clear _(dataset)_
  - takes the hide time from configuration
  - asks for the password first (423), then reveals with no-store after confirm-password
  - shows a revealed unit as revealed afterwards, still without the code
  - never writes a revealed code to the log or the audit trail
  - keeps the reveal module free of browser storage and wired to auto-hide, tab hiding and clipboard clearing

</details>

<details><summary><code>backend/tests/Feature/Storefront/AuthPagesTest.php</code> (7)</summary>

  - renders the sign-in and registration pages in both languages, posting to the session routes _(dataset)_
  - sends a signed-in customer from sign-in and registration to the account page
  - requires sign-in for the account and verification pages and redirects guests to the localized sign-in page
  - shows the two-factor page only while a challenge is pending
  - shows the forgotten password page and the verification page for an unverified customer
  - renders the reset page for the email link in a browser and keeps the JSON echo for API clients
  - uses the language the shopper last browsed in for the reset page

</details>

<details><summary><code>backend/tests/Feature/Storefront/CheckoutPagesTest.php</code> (13)</summary>

- **bag page**
  - shows the empty state
  - shows the lines and server totals, and serves the cart fragment alone
- **checkout access (owner decision 4)**
  - sends a guest to sign in and back to checkout while guest checkout is off
  - shows the guest email field when guest checkout is switched on
  - hides the guest email field from a signed-in customer and sends an empty bag back to the bag page
  - refuses a browser guest at place-order while the flag is off, and accepts one when it is on
  - translates the refusal for Arabic shoppers
- **customer checkout end to end (mocked Stripe)**
  - goes from product page to a paid order page showing the display and payment amounts
  - offers to retry an unpaid order
- **order and guest access pages**
  - is 404 for anyone who neither owns nor opened the order
  - shows an unknown-order message on the payment return page without revealing anything
  - opens the guest order page from the email link, asks for the one-time code, then shows the order
  - keeps the JSON answer of the email link for API clients

</details>

<details><summary><code>backend/tests/Feature/Storefront/StorefrontPagesTest.php</code> (26)</summary>

- **home**
  - renders the sections with server prices, the hero carousel controls and a strict CSP
  - renders right to left in Arabic with translated copy and catalog names
  - shows prices in the display currency chosen with the currency cookie
  - confirms an email verification landing _(dataset)_
- **platforms**
  - lists active platforms with their product counts and BD8 neutral tiles
  - shows a platform with its products, in both languages _(dataset)_
  - is 404 with the storefront error page for an unknown platform
- **category listing**
  - lists the category products with filters, sort and a self canonical
  - filters by price in the display currency and marks multi-filter pages noindex
  - treats empty filters as none and sends an invalid filter back to the plain page
  - shows the empty state when nothing matches
  - is 404 for an unknown or inactive category, and never shadows reserved routes
- **product page**
  - renders variants with server prices, delivery fields, reviews and related products
  - renders the Arabic page right to left with prices left to right
  - is 404 for a draft product
- **search page**
  - finds products by title prefix, counts the term and is noindex
  - shows the empty search page without a term and an empty state without results
- **content pages**
  - serves the built-in legal pages in both languages
  - prefers a published CMS page and sanitizes its body
  - is 404 for an unknown or draft page
- **errors and switchers**
  - serves the English home at / and the Arabic home at /ar without any country redirect
  - builds every banner image the pages reference through Vite
  - renders the 404 page for unknown paths, in the language of the path
  - renders the 500 page without the storefront chrome
  - links each page to the same page in the other language
  - keeps the JSON API envelope for API paths

</details>

### `Feature/Suppliers`

<details><summary><code>backend/tests/Feature/Suppliers/ReloadlyAdapterTest.php</code> (19)</summary>

- **classification table (INTEGRATIONS.md 1.5)**
  - classifies each order response _(dataset)_
  - rejects every documented refusal code _(dataset)_
  - treats every documented uncertain code as ambiguous _(dataset)_
  - never sends the order a second time after a read timeout
- **authentication**
  - caches the token across calls
  - refreshes an expired token once and repeats the 401 call, which did not run
  - rejects after a second 401 without a third attempt
  - does not send the order when the token cannot be obtained
  - sends the documented token request and keeps the secret and token out of the logs
- **order request**
  - buys one unit with our reference, an exact unit price and no recipient email
  - records the supplier cost exactly from balanceInfo
- **deliverables**
  - keeps a 20-digit card number intact and never logs the body
  - accepts a list of cards
  - fails without exposing the body
- **lookups and reads**
  - finds an order by our reference
  - treats a lookup the supplier cannot answer clearly as ambiguous, not as not found _(dataset)_
  - parses products, prices and the balance as exact decimals
- **capabilities**
  - declares cancel and refund unsupported and refuses to call them
  - declares the documented USD 100 per-request cap

</details>

<details><summary><code>backend/tests/Feature/Suppliers/SupplierManagementTest.php</code> (15)</summary>

- **admin API**
  - requires supplier permissions
  - creates a supplier inactive by default and refuses an unknown adapter or an unsafe base URL
  - stores credentials encrypted and never returns them
  - shows the capability matrix, the balance and failed request logs
  - tests the connection and queues syncs
  - manages mappings with one primary per variant and a matching supplier product
- **catalog and price sync**
  - writes effective cost to supplier_product_prices, which the pricing engine then uses
  - marks products missing from a complete run as removed and refuses a duplicate window
  - writes a new price row only on change or when the last one is ageing
  - prices range denominations from the synced exchange rate
- **balances, health and reconciliation**
  - caches the balance with a snapshot and opens the circuit after repeated failures
  - reports orphans, cost differences and missing transactions without changing anything
- **margin floor in the payment currency (inherited from M7)**
  - adds the conversion cost when the payment currency is not the base currency
  - refuses placement when charging another currency would break the margin
  - activates a supplier through the guarded status field only

</details>

### `Feature/Support`

<details><summary><code>backend/tests/Feature/Support/SupportTicketTest.php</code> (14)</summary>

- **customer flow**
  - opens a ticket, lists and shows it, and replies
  - hides other customers' tickets and refuses someone else's order
  - refuses a reply on a closed ticket
- **internal notes are never visible to customers**
  - shows staff the note, marked internal
  - never returns the note to the customer, in the ticket or the list
  - filters notes in the customer resource itself, even when the relation holds them
  - emails the customer for a public reply only, with a link and no message text
- **staff flow**
  - lists the queue, assigns to an agent who can reply and changes status and priority, audited
  - runs the same number of queries for the agent queue whatever its length
  - enforces support permissions per action
  - records staff replies as staff authored
- **refund requests**
  - lets a customer ask once per paid order of their own
  - approves by refunding the chosen units through the refund flow, then completes once the refund succeeds
  - rejects with a note, and approval needs refunds.create too

</details>

### `Feature/Wallet`

<details><summary><code>backend/tests/Feature/Wallet/WalletConcurrencyTest.php</code> (3)</summary>

  - never overdraws when two debits race for a balance that covers one _(dataset)_
  - posts one entry when the same credit is sent twice at the same moment
  - pays exactly one of two orders racing for a balance that covers one _(dataset)_

</details>

<details><summary><code>backend/tests/Feature/Wallet/WalletLedgerTest.php</code> (20)</summary>

- **ledger**
  - keeps the balance equal to the sum of entries and records each balance_after
  - creates the wallet in the base currency on the first credit
  - posts a credit once however often the same key is sent
  - refuses a key reused for a different movement
  - refuses a debit beyond the balance and changes nothing
  - allows a debit of exactly the balance
  - refuses a debit from a customer who has no wallet
  - refuses debits from a frozen wallet but still accepts refund credits
  - refuses zero, negative and other-currency amounts _(dataset)_
  - has a database check that rejects a negative balance written around the ledger
  - keeps ledger rows append-only
  - detects a wallet whose balance no longer matches its ledger _(dataset)_
- **customer wallet routes**
  - shows the balance and statement of the signed-in customer only, with no top-up
  - shows an empty wallet before the first credit
  - requires a signed-in customer and offers no way to add money
- **staff adjustments**
  - credits and debits with a reason, once per Idempotency-Key, and audits it
  - refuses a debit beyond the balance
  - validates the body and the Idempotency-Key header _(dataset)_
  - needs customers.wallet_adjust to adjust and customers.view to read
  - freezes a wallet with a reason, which stops wallet payments

</details>

<details><summary><code>backend/tests/Feature/Wallet/WalletPaymentTest.php</code> (16)</summary>

- **eligibility**
  - offers the wallet only when the balance covers the order
  - never offers the wallet to an unverified customer or from a frozen wallet _(dataset)_
  - is never available to a guest, who has no account to hold credit
  - does not need gateway credentials, unlike provider gateways
- **paying from the wallet**
  - debits the wallet and marks the order paid in the same transaction, with no provider call
  - fails the attempt and leaves the order unpaid when the balance was spent after the options were shown
  - rolls the debit back when applying the payment fails, so there is no debit without paid
  - settles an attempt only once: a second settle is refused and debits nothing
  - reports the ledger as the provider state, so daily reconciliation matches
  - cannot be reached through the webhook route
- **statement links to the order**
  - shows the order behind a purchase debit and a refund credit, and none for a credit without one
  - never links an order that belongs to someone else
  - loads the linked orders of a page in a fixed number of queries
- **wallet refunds**
  - refunds a wallet payment back to the wallet through the M10 refund flow, once
  - lets staff refund a card payment to the wallet instead, without calling the provider
  - refuses a wallet refund for a guest order

</details>

### `Unit`

<details><summary><code>backend/tests/Unit/ExampleTest.php</code> (1)</summary>

  - that true is true

</details>

### `Unit/Foundation`

<details><summary><code>backend/tests/Unit/Foundation/ArchitectureTest.php</code> (5)</summary>

  - has no static Eloquent query calls in controllers
  - keeps repositories private to their domain _(dataset)_
  - stores enums as string backed PHP enums _(dataset)_
  - has no float types on value objects or DTOs _(dataset)_
  - does not cast to float in money code

</details>

<details><summary><code>backend/tests/Unit/Foundation/MoneyTest.php</code> (15)</summary>

  - normalizes amounts to six decimal places _(dataset)_
  - rejects floats and malformed amounts _(dataset)_
  - rejects amounts beyond the decimal 20,6 column range
  - rejects invalid currency codes
  - adds and subtracts without floating point drift
  - refuses to combine different currencies _(dataset)_
  - multiplies and divides with half up rounding
  - refuses to divide by zero or by a float _(dataset)_
  - rounds to the currency exponent half up _(dataset)_
  - converts with an explicit rate only
  - compares and reports sign
  - serializes as strings, never JSON numbers
  - applies a percentage
  - rejects a float percentage
  - validates country codes

</details>

### `Unit/Pricing`

<details><summary><code>backend/tests/Unit/Pricing/CartDiscountAllocatorTest.php</code> (9)</summary>

  - splits proportionally to unit price
  - gives the rounding remainder to the earliest of equal units, summing exactly
  - gives remainders to the largest fractional parts first
  - caps a unit at its margin room and moves the excess to units with room
  - forfeits what no unit has room for
  - gives nothing to units without weight or room
  - is deterministic and exact for awkward amounts
  - refuses slots in another currency
  - allocates in whole minor units when given a scale, rounding rooms down

</details>

### `Unit/Shared`

<details><summary><code>backend/tests/Unit/Shared/QrCodeSvgTest.php</code> (3)</summary>

  - encodes byte-mode level M symbols identical to the reference encoder _(dataset)_
  - draws the three finder patterns and renders an SVG with a quiet zone and no inline style
  - refuses data beyond version 20

</details>

<details><summary><code>backend/tests/Unit/Shared/SemanticVersionTest.php</code> (3)</summary>

  - orders versions by SemVer precedence, not as strings _(dataset)_
  - treats build metadata as equal
  - refuses what is not a semantic version _(dataset)_

</details>

## App (Flutter)

65 files, 786 declared test cases.

| Area | Files | Declared cases |
|---|---:|---:|
| `core/config` | 2 | 5 |
| `core/money` | 1 | 16 |
| `core/network` | 8 | 61 |
| `core/router` | 3 | 26 |
| `core/security` | 2 | 10 |
| `core/storage` | 2 | 7 |
| `core/theme` | 1 | 6 |
| `core/utils` | 2 | 6 |
| `features` | 5 | 53 |
| `features/account` | 2 | 38 |
| `features/auth` | 6 | 71 |
| `features/code_reveal` | 1 | 8 |
| `features/commerce` | 3 | 81 |
| `features/launch` | 2 | 20 |
| `features/orders` | 3 | 60 |
| `features/product` | 3 | 65 |
| `features/security` | 3 | 29 |
| `features/storefront` | 3 | 51 |
| `features/usecases` | 3 | 25 |
| `features/wallet` | 2 | 42 |
| `goldens` | 8 | 106 |

### `core/config`

<details><summary><code>application/test/core/config/app_env_test.dart</code> (3)</summary>

  - each env file matches its flavor and holds only known keys
  - env files contain no secret-looking keys or values
  - rejects a mismatched flavor, bad URL and cleartext outside dev

</details>

<details><summary><code>application/test/core/config/app_version_test.dart</code> (2)</summary>

  - appVersion is the pubspec build name (sent to GET /app/config)
  - platform names of the app config query

</details>

### `core/money`

<details><summary><code>application/test/core/money/money_test.dart</code> (16)</summary>

- **Money.fromJson**
  - accepts a decimal string amount
  - rejects a JSON integer amount
  - rejects a JSON double amount
  - rejects malformed strings, currencies and shapes
  - preserves 6 decimal places exactly
  - keeps the server string for round trips (trailing zeros included)
  - sums of 6 dp values are exact (no binary floating point)
- **MoneyFormatter**
  - formats by currency exponent like CurrencyConverter::present
  - cuts extra digits toward zero, as bcadd does
  - handles values beyond double precision
  - groups thousands when asked
  - Arabic-Indic digits option
  - negative zero after truncation prints without a sign
  - refuses a currency that does not match
- **CurrencyInfo.fromJson**
  - parses the storefront currency shape
  - rejects an invalid exponent

</details>

### `core/network`

<details><summary><code>application/test/core/network/api_envelope_test.dart</code> (6)</summary>

  - decodes data and pagination meta
  - accepts Laravel empty meta [] and missing meta
  - rejects error envelopes and non-objects
  - a numeric money amount inside data becomes a DecodingFailure
  - Result folds both ways
  - ApiError parses the error envelope and ignores other bodies

</details>

<details><summary><code>application/test/core/network/auth_interceptor_test.dart</code> (7)</summary>

  - adds the bearer token from secure storage
  - skips the token when the request opts out
  - concurrent 401s trigger one refresh and every request is replayed
  - a 401 arriving after a finished refresh reuses the new token
  - refused refresh clears tokens once and surfaces the 401
  - a replayed request that fails again is not refreshed twice
  - no stored refresh token signs nothing in and returns the 401

</details>

<details><summary><code>application/test/core/network/dio_client_test.dart</code> (2)</summary>

  - sends the standard headers on every request
  - omits X-Currency until a currency is chosen

</details>

<details><summary><code>application/test/core/network/error_mapper_test.dart</code> (20)</summary>

- **section 2.3 rows**
  - no connection, DNS and TLS -> NetworkFailure
  - timeout -> TimeoutFailure (retryable)
  - 401 unauthenticated -> UnauthenticatedFailure
  - 403 two_factor_required -> TwoFactorRequiredFailure
  - 403 email_unverified -> EmailUnverifiedFailure
  - 403 reveal_denied and forbidden -> ForbiddenFailure(code)
  - 404 -> NotFoundFailure
  - 409 codes -> ConflictFailure(code)
  - 423 recent_auth_required -> RecentAuthRequiredFailure
  - 422 validation_failed -> ValidationFailure(fieldErrors) by dot key
  - 422 domain codes -> DomainFailure(code, message, errors)
  - 429 -> RateLimitedFailure(retryAfter) from seconds
  - 429 Retry-After as an HTTP date
  - 423 biometric_locked -> BiometricLockedFailure (OD-1)
  - 503 maintenance -> MaintenanceFailure
  - 5xx -> ServerFailure(requestId), body ignored
- **outside the table**
  - decoding errors -> DecodingFailure
  - cancel -> CancelledFailure
  - 419 and 400 -> UnexpectedFailure with the code
  - unknown errors -> UnexpectedFailure

</details>

<details><summary><code>application/test/core/network/idempotency_interceptor_test.dart</code> (4)</summary>

  - place-order gets a ULID key that is reused on retry
  - a key stored with the pending checkout is sent as is
  - two separate place-order calls get different keys
  - other requests get no key

</details>

<details><summary><code>application/test/core/network/redaction_test.dart</code> (7)</summary>

- **Redactor**
  - masks the backend key list and the client additions
  - leaves ordinary keys alone
  - redacts nested maps and lists
  - masks sensitive query parameters
  - drops bodies of reveal, auth and payment traffic
- **RedactingLogInterceptor**
  - never writes secrets to the log
  - omits reveal bodies entirely

</details>

<details><summary><code>application/test/core/network/retry_interceptor_test.dart</code> (10)</summary>

  - retries a GET after a connection error
  - never retries a non-idempotent POST
  - never retries PATCH or DELETE without a key
  - retries place-order because it carries an Idempotency-Key
  - honours Retry-After on 429 for GET
  - gives up when Retry-After is longer than the cap
  - does not retry maintenance or client errors
  - stops after maxRetries
  - noRetry opts a GET out
  - isReplayable rules

</details>

<details><summary><code>application/test/core/network/token_headers_test.dart</code> (5)</summary>

- **X-Cart-Token**
  - is captured from the response header and sent back
  - a response without the header keeps the stored token
- **X-Guest-Order-Token**
  - is attached per order on every order-scoped route
  - is not sent for other orders or other routes
  - X-Currency and Accept-Language follow the app settings

</details>

### `core/router`

<details><summary><code>application/test/core/router/app_router_test.dart</code> (6)</summary>

  - boots to the home shell
  - maintenance state redirects and recovers
  - update required shows its notice
  - unknown location shows not found with a way home
  - switching to Arabic mirrors the shell
  - device locale picks Arabic when nothing is saved

</details>

<details><summary><code>application/test/core/router/deep_links_test.dart</code> (8)</summary>

  - payment return ignores provider query parameters
  - payment cancel is its own link; other app paths are not payments
  - signed guest access keeps the full signed URL
  - email verification needs the signature
  - password reset with optional email
  - rejects other hosts, schemes, ports and paths
  - product pages open the product (M-A12), with or without a locale prefix
  - host match is case-insensitive

</details>

<details><summary><code>application/test/core/router/guards_test.dart</code> (12)</summary>

  - public routes are open to everyone
  - protected routes send signed-out users to login with a return path
  - verified-email routes send unverified customers to verification
  - signed-in users leave login for the return path
  - return path cannot leave the app
  - maintenance wins over everything except update required
  - update required blocks every route
  - notice routes return home once the store is available
  - guest-only routes send a signed-in customer on (M-A10)
  - the biometric offer comes first when it is due
  - a return path never lands on a guest-only route
  - route table lookup

</details>

### `core/security`

<details><summary><code>application/test/core/security/native_bridge_test.dart</code> (3)</summary>

  - device integrity: ok, compromised, anything else unknown
  - device name is printable, at most 100 characters, with a fallback
  - opens https pages only

</details>

<details><summary><code>application/test/core/security/security_test.dart</code> (7)</summary>

- **ScreenSecurity**
  - Android: setSecure on for the first holder, off after the last
  - iOS: setSecure drives the privacy overlay; capture is read and streamed
  - other platforms do nothing
  - a missing native side never throws
- **ClipboardGuard**
  - Android copies a sensitive clip and clears it after 60 s
  - iOS writes an empty string after 60 s without reading the clipboard
  - a clear that fell due in the background runs on resume

</details>

### `core/storage`

<details><summary><code>application/test/core/storage/preferences_store_test.dart</code> (4)</summary>

  - stores only the allow-listed, non-sensitive keys
  - locale falls back to English and persists the choice
  - display currency is validated and persisted
  - onboarding flag

</details>

<details><summary><code>application/test/core/storage/secure_store_test.dart</code> (3)</summary>

  - token round trip
  - a lone access token is not a session
  - cart and guest order tokens are scoped and removable

</details>

### `core/theme`

<details><summary><code>application/test/core/theme/tokens_test.dart</code> (6)</summary>

  - key-cut corner sits top-end and mirrors in RTL
  - semantic dark colours resolve to the documented primitives
  - type scale matches the Figma text styles
  - Arabic rules drop letter spacing and add line height from 20 px
  - theme is dark first and exposes the extensions
  - reduced motion collapses durations

</details>

### `core/utils`

<details><summary><code>application/test/core/utils/qr_code_test.dart</code> (3)</summary>

  - matches the backend encoder: ${data.length} bytes, mask ${mask ??
  - draws the three finder patterns
  - refuses data beyond version 20

</details>

<details><summary><code>application/test/core/utils/ulid_test.dart</code> (3)</summary>

  - produces 26 Crockford base32 characters
  - encodes the timestamp in the first 10 characters
  - ids are unique

</details>

### `features`

<details><summary><code>application/test/features/account_repositories_test.dart</code> (20)</summary>

- **AuthRepository**
  - register stores the token pair in secure storage
  - register maps field errors
  - login signs in, or returns the two-factor challenge without storing tokens
  - login maps wrong credentials and the rate limit with Retry-After
  - me reads the user; an unknown token is unauthenticated
  - refresh rotates the stored pair
  - a refused (reused) refresh token signs out
  - logout and logout-all revoke, then wipe secure storage
- **CodeRevealRepository**
  - confirms the password and reveals the canary
  - maps every reveal failure and never retries
  - a guest reveal needs both proofs on the stored token
- **WalletRepository**
  - reads the wallet and the statement
- **ReviewsRepository**
  - lists eligible items, submits and maps review errors
- **SupportRepository**
  - opens, reads and answers tickets, and maps their errors
  - requests a refund and refuses a second open request
- **NotificationsRepository**
  - reads and marks the inbox and the preferences
- **AccountRepository**
  - profile, password, devices and verification
  - sign out other devices sends the current password once
  - a null field update clears the field
- **SecurityRepository**
  - enrols, confirms and disables two-factor authentication

</details>

<details><summary><code>application/test/features/commerce_repositories_test.dart</code> (17)</summary>

- **CartRepository**
  - a guest cart: token captured, sent back, items, coupon
  - maps cart errors
  - a signed-in customer drops the adopted guest cart token
- **CheckoutRepository**
  - validates, lists payment options and maps their errors
  - places a guest order, stores its guest token and sends the key
  - reads a replay, a duplicate and next: null
  - a customer order carries no guest token
  - maps place-order errors
  - reads the status with the stored guest token
  - opens the delivery link, sends and verifies the email code
- **PaymentsRepository**
  - models every next type from retry-payment
  - reads the wallet next from place-order
  - verifies, captures and reads the status; only the server says paid
  - maps payment errors
- **OrdersRepository**
  - lists and filters orders
  - shows an order with units and never a code
  - shows a guest order with its access proofs

</details>

<details><summary><code>application/test/features/fixture_contract_test.dart</code> (2)</summary>

  - the app copy holds every recorded fixture
  - every success fixture has a decoder and every decoder a fixture
- **success fixtures decode into their DTOs**
- **error fixtures are error envelopes that map to a typed failure**

</details>

<details><summary><code>application/test/features/no_double_test.dart</code> (1)</summary>

  - no feature or shared source uses double

</details>

<details><summary><code>application/test/features/storefront_repositories_test.dart</code> (13)</summary>

- **AppConfigRepository**
  - reads the public configuration and the update verdict
  - reads the Arabic configuration without an update verdict
  - maps a bad version to a validation failure
- **HomeRepository**
  - reads sections and banners ($locale)
- **CatalogRepository**
  - lists products, platforms, categories and countries ($locale)
  - prices the listing in the X-Currency currency
  - lists the active display currencies
  - maps an inactive X-Currency to a validation failure
  - maps an unknown sort to a validation failure
- **ProductRepository**
  - reads the detail, variants and reviews ($locale)
  - maps an unknown product to not found
- **SearchRepository**
  - suggests and lists popular terms
  - maps a short query to a validation failure

</details>

### `features/account`

<details><summary><code>application/test/features/account/account_controllers_test.dart</code> (19)</summary>

- **account profile**
  - loads when signed in and keeps the signed-in user in step
  - is null signed out and cleared on sign-out
  - a failed load stays failed until reload; the header falls back to the user
  - initials: Latin, Arabic apart, one word, blank
- **profile form**
  - sends only the fields that changed
  - a blank phone clears a saved one
  - checks the name before sending
  - a 422 keeps the server messages beside their fields
  - loads the countries, and the chosen country wins
  - resend: 60 s cooldown, Retry-After on 429
- **language**
  - signed in: saved on the profile first, then applied
  - a refused save keeps the language
  - signed out: saved on the device only
- **change password**
  - checks empty fields and the confirmation first
  - a 422 shows the server message beside the field and keeps no password
  - success revokes biometric unlock locally (the server already did) and says so
  - success without biometric unlock leaves the keys alone
- **biometric switch and sign out**
  - turning biometric unlock off deletes the server key and the local key
  - sign out everywhere revokes every sign-in and cleans up locally

</details>

<details><summary><code>application/test/features/account/account_pages_test.dart</code> (19)</summary>

- **account hub ($lang)**
  - signed in: header, rows and the wallet balance
  - placeholders for notifications and support (M-A17)
  - wallet_enabled off hides the wallet row
  - an unverified email shows its badge
  - signed out: sign in, register, guest orders and settings
  - sign out from the hub
  - 360 wide with a long name
  - screen-reader labels and tap targets
- **settings ($lang)**
  - language, currency, legal links, version and sign out
  - signed out: no notifications and no sign out
  - the language sheet saves on the profile and switches
  - signed in, the display currency is saved on the profile
  - a refused language save keeps the sheet with the error
- **profile ($lang)**
  - fields, read-only email with badge, save
  - server errors show beside their fields
  - unverified: banner and resend with cooldown
  - loading, then a failure with retry
- **change password ($lang)**
  - mismatch, wrong current password, then success
  - with biometric unlock on: turned off and the note shows

</details>

### `features/auth`

<details><summary><code>application/test/features/auth/accessibility_test.dart</code> (1)</summary>

  - ${route.name} meets tap-target and label guidelines ($lang)

</details>

<details><summary><code>application/test/features/auth/auth_controllers_test.dart</code> (29)</summary>

- **LoginController**
  - checks the fields locally before calling the server
  - signs in with the device name and records the user
  - keeps the two-factor challenge in memory only
  - server field errors go beside their fields
  - 429 blocks submits until Retry-After has passed
  - a 429 without Retry-After waits 60 seconds
- **TwoFactorController**
  - completes with a code and clears the challenge
  - sends a recovery code as recovery_code
  - an incomplete code is caught locally
  - two_factor_invalid is shown under the field
  - two_factor_expired and the local expiry end the attempt
  - an expired challenge is not sent
- **RegisterController**
  - needs the terms and matching passwords
  - registers, signs in and leaves the email unverified
  - maps every 422 field error
- **VerifyEmailController**
  - starts in cooldown, resends after it and restarts it
  - a 429 waits for Retry-After
  - re-reads the user to see the verification
- **ForgotPasswordController**
  - opens links.forgot_password from the app config
  - reports a missing link or a browser that did not open
- **SignOutController**
  - revokes the sign-in, wipes storage and deletes the local key
- **BiometricOfferController**
  - is due once on a device with biometrics, until shown
  - is never due without biometrics
  - accept enrols, turns unlock on and marks the offer shown
- **AppLock**
  - locks after 5 minutes in the background, not before
  - never locks without biometric unlock or when signed out
  - locks a cold start and unlocks with biometrics
  - changed biometrics fall back to the password
  - a cancelled prompt stays locked

</details>

<details><summary><code>application/test/features/auth/auth_pages_test.dart</code> (15)</summary>

- **sign in ($lang)**
  - signs in and returns to the protected location
  - server field errors appear beside the field
  - local checks before any request
  - 429 shows the countdown and disables sign-in until it ends
  - forgot password opens the web reset page
- **two-step verification ($lang)**
  - a full code signs in
  - an invalid code is shown under the boxes
  - recovery code mode and the expired challenge
  - the countdown ends the challenge
- **register and verify email ($lang)**
  - server errors: summary and field messages
  - terms are required; success opens the verify prompt
- **biometric unlock ($lang)**
  - offered once after the first sign-in, then turned on
  - not now continues without asking again
  - locks at launch; password fallback unlocks
  - sign out asks first, then signs out ($lang)

</details>

<details><summary><code>application/test/features/auth/biometric_repository_test.dart</code> (4)</summary>

  - registers the key; 423 until the password was confirmed
  - issues a challenge; 404 when this device has no key
  - confirms a signature; bad signatures and used challenges are domain failures
  - deletes the key

</details>

<details><summary><code>application/test/features/auth/biometric_unlock_test.dart</code> (19)</summary>

- **EnrolBiometricUnlock**
  - creates the key, registers its public key and turns unlock on
  - 423 asks for the password, confirms it and registers again once
  - a wrong password is shown on the next prompt
  - a cancelled password sheet deletes the new key
  - a refused registration deletes the key and reports the failure
  - a cancelled prompt or missing biometrics never reach the server
- **UnlockWithBiometric**
  - signs a fresh nonce: the platform releases the key only after biometrics
  - changed biometrics turn unlock off and fall back to the password
  - cancel keeps unlock on; lock-out uses the password
  - unlock off means the password
- **ConfirmRecentAuthWithBiometric (M-A14 re-auth)**
  - signs the server challenge and confirms it
  - not enrolled: use the password without calling the server
  - 404 biometric_not_registered turns unlock off locally
  - changed biometrics revoke the key on both sides
  - 423 biometric_locked: the server revoked the key; use the password
  - a cancelled prompt and a bad signature are reported
- **PluginBiometricKeyStore**
  - creates a strong-biometric ECDSA P-256 key invalidated by enrolment changes
  - maps platform error codes
  - a thrown platform error is a failure, never an exception

</details>

<details><summary><code>application/test/features/auth/token_lifecycle_test.dart</code> (3)</summary>

  - concurrent 401s refresh once and every request is replayed
  - a reused refresh token (401 refresh_token_invalid) signs out once
  - a refresh that cannot reach the server keeps the session

</details>

### `features/code_reveal`

<details><summary><code>application/test/features/code_reveal/reveal_safety_test.dart</code> (8)</summary>

  - the redacting log drops reveal request and response bodies
  - a reveal through the providers writes nothing to storage and leaves no provider alive
  - no code reveal provider is kept alive or exposes a revealed code
  - a revealed code never prints its value
- **M-A14 reveal screen**
  - the screen controller is auto-disposed (never keep-alive)
  - nothing in the reveal screen logs, stores, caches or reports
  - the screen state never prints the code
  - a reveal sends X-Device-Integrity, and the log keeps no code

</details>

### `features/commerce`

<details><summary><code>application/test/features/commerce/cart_checkout_controllers_test.dart</code> (33)</summary>

- **cart controller**
  - loads the cart and counts its units on the badge
  - a guest without a cart token has an empty cart and no call
  - a guest with a cart token reads it
  - offline and server failures are kept until retry
  - quantity change calls PATCH and the badge follows the returned cart
  - remove calls DELETE; the last line removed shows the empty cart
  - a refused quantity shows errors.quantity beside its line
  - another write failure is reported once for the snackbar
  - coupon applied, refused (coupon_invalid) and removed
  - a blocked line disables checkout and shows the pricing notice
  - an order placed elsewhere (CartRevision) reloads the cart
- **checkout mode (owner decision 3)**
  - in-app by default, web redirect, disabled
  - web redirect opens links.web_checkout in the system browser
  - the in-app checkout does not load while web redirect is on
- **checkout guard (owner decision 4)**
  - a guest is sent to sign in with the way back
  - after signing in the customer comes back to checkout
  - the payment screen is signed-in only too
- **checkout controller**
  - starts with the profile country, quotes it and picks the first payable option
  - a profile country that checkout does not sell to is not preselected
  - options keep display and charge apart and disabled ones cannot be chosen
  - nothing is placed until the terms are accepted
  - place order sends the shown total, opens PayPal and goes to the payment screen
  - a retry after a network error reuses the idempotency key
  - a restart re-sends the same key with the same body
  - next: null calls retry-payment once with the same order
  - a failed retry-payment shows "payment provider unavailable"
  - a duplicate goes to the existing order and asks the server
  - price_changed re-quotes, shows old and new totals and places with a new key
  - cart_empty goes back to the cart
  - an empty cart at validate goes back to the cart
  - idempotency_in_progress keeps the key and says so
  - 401 guest_checkout_disabled signs out (the guard asks to sign in)
  - an item error in validation_failed points back to the cart

</details>

<details><summary><code>application/test/features/commerce/commerce_pages_test.dart</code> (26)</summary>

- **cart ($lang)**
  - 06.1 lines, applied coupon and server totals
  - quantity and remove call the cart and update the count
  - 06.2 blocked line, refused coupon, checkout disabled
  - 06.3 empty cart browses the store
  - 06.4 loading skeleton
  - 06.5 error with request id, then retry
  - offline
  - a guest is asked to sign in and comes back to checkout
  - web checkout mode opens boxcards.shop (owner decision 3)
- **checkout ($lang)**
  - 07.2 country, options, charge notice and the charged amount
  - pay with PayPal opens the provider and shows 07.6
  - 07.3 unavailable options keep their reason
  - 07.4 country sheet: search and choose
  - 07.5 price changed: both totals, back to the cart
  - 07.7 payment options failure and retry
  - idempotency in progress is explained
  - a guest opening checkout is sent to sign in
  - web checkout mode shows the boxcards.shop state
- **payment results ($lang)**
  - 08.1 paid
  - 08.2 still processing, check again
  - 08.3 failed (cancel link) and retry payment
  - 08.4 expired, back to the cart
  - close forgets a final result and goes home
- **accessibility ($lang)**
  - $location meets tap-target and label guidelines
  - payment result meets tap-target and label guidelines
  - 19.3 checkout at 360x800 has no overflow ($lang)

</details>

<details><summary><code>application/test/features/commerce/payment_flow_test.dart</code> (22)</summary>

- **payment flow**
  - wallet: no provider page; paid only once the status says so
  - PayPal opens the approve page and waits; nothing is checked yet
  - a page that cannot be opened is reported and can be reopened
  - the PayPal return link captures on the server and paid comes from it
  - a return link never marks paid by itself (server says unpaid)
  - polling: 2 s, 3 s, 5 s then every 10 s, "still processing" after 2 minutes
  - polling ends early on paid
  - a failed latest attempt after polling offers retry
  - final answers: cancelled order is expired, failed order is failed
  - the cancel link reads the status once and offers retry
  - retry payment starts a new attempt and opens its page
  - retry: order_expired is expired, attempts exhausted is final
  - Stripe (no native SDK in this app) is not launched
  - closing a result forgets the pending checkout
  - a transport failure while confirming keeps the screen and reports it
- **resume after a restart**
  - the launch resumes the pending order at its payment screen
  - signed out or nothing placed: no resume
- **app links**
  - a payment return link verifies with the server and opens the screen
  - a link that arrives during the launch check waits for it
  - another host or scheme is ignored
  - the cancel link is handled as a cancel
  - signed out, a return link asks to sign in and verifies nothing

</details>

### `features/launch`

<details><summary><code>application/test/features/launch/launch_controllers_test.dart</code> (12)</summary>

- **StartupController**
  - sends the platform and version and continues home
  - a first launch continues to onboarding
  - update.required forces the update screen
  - maintenance from the config or a 503 shows maintenance
  - no connection is offline; other failures keep their request id
  - a signed-in launch reloads the user and locks when unlock is on
  - a signed-out launch on a biometric device makes the offer due
- **IntegrityWarningController**
  - warns once on a compromised device
  - never warns on a clean or unknown device
- **OnboardingController**
  - applies the language at once
  - saves the chosen currency and completes onboarding
  - skip keeps the server currency

</details>

<details><summary><code>application/test/features/launch/launch_pages_test.dart</code> (8)</summary>

- **launch pages ($lang)**
  - splash offline offers a retry that continues home
  - splash failure shows the mapped message and reference
  - force update shows the versions and opens the store
  - maintenance shows the server message, retries and recovers
  - onboarding: language, then currency, then home
  - onboarding can be skipped without a currency
  - rooted device: the warning sheet shows once, never blocks
  - status banners are live regions

</details>

### `features/orders`

<details><summary><code>application/test/features/orders/orders_controllers_test.dart</code> (18)</summary>

- **order list**
  - pages through meta: appends, stops at the last page
  - a failed next page keeps the list and is not retried by itself
  - a status chip restarts at page 1 with the filter
  - first page failure, offline, then retry
  - signed out shows no list and loads nothing; a placement reloads
- **order detail**
  - retry payment is offered for an unpaid order whose attempt ended,
  - a paid order offers a refund request, never a retry
  - 404 stays a failure
- **guest order access**
  - link -> email -> code -> order
  - otp_invalid stays on the code step with the server text
  - an expired link is 11.5
  - a verified link with the OTP already done opens the order
  - without a link, a stored token is tried; none means "open the email"
  - the paste page accepts only a signed guest link on the store host
- **app links (M-A14)**
  - the parser knows the customer and guest delivery links
  - a customer order link opens the order detail
  - signed out, the order link asks to sign in first
  - a guest delivery link opens guest access with the link

</details>

<details><summary><code>application/test/features/orders/orders_pages_test.dart</code> (22)</summary>

- **orders tab ($lang)**
  - lists orders and opens the detail
  - loads the next page near the end
  - a status chip filters
  - empty
  - offline, then retry
  - signed out: sign in and guest access
- **order detail ($lang)**
  - a unit opens its code screen; refund opens the placeholder
  - retry payment goes through the payment flow
  - not found
  - "View order" on a paid result lands on the detail
- **code reveal ($lang)**
  - masked by default; reveal -> password sheet -> code; copy; hide
  - wrong password keeps the sheet with the error
  - reveal_denied, rate limit and the device warning
  - auto-hides after 60 s and on background
  - iOS capture hides the code
  - screen readers: labelled buttons; the code is announced only
  - the 360 wide screen fits
  - leaving drops the code: provider disposed, nothing stored
- **guest access ($lang)**
  - delivery link -> email -> code -> order -> guest reveal
  - expired link
  - paste a link from the Account tab
- **accessibility ($lang)**
  - $location meets tap-target and label guidelines

</details>

<details><summary><code>application/test/features/orders/reveal_controller_test.dart</code> (20)</summary>

- **loading the unit**
  - finds the unit in the order detail; never a code
  - an unknown unit is not found
- **reveal and re-auth**
  - masked by default; a reveal sends X-Device-Integrity and shows the
  - 423 -> biometric success: confirm-biometric, then one retry
  - 423 -> biometric fails -> password sheet -> confirm-password ->
  - without biometric unlock the password sheet comes straight away
  - wrong password: the sheet stays open with the error; no reveal
  - closing the sheet goes back to masked without a retry
  - 429: rate limited with the retry time; never retried
  - 403 reveal_denied and 404 have their own states
  - an unverified email is flagged before any reveal
- **hiding**
  - auto-hides at 60 s on the clock and drops the code
  - hides on lifecycle pause and covers while inactive
  - iOS capture hides the code and blocks reveal until it stops
  - Hide drops the code at once
- **screen protection**
  - setSecure on while the controller lives, off once it is disposed
- **copy and links**
  - copy hands the code to the clipboard guard
  - the clipboard is cleared 60 s after a copy (real guard, Android)
  - the redemption link opens only over https
  - X-Device-Integrity reaches the wire on the reveal request

</details>

### `features/product`

<details><summary><code>application/test/features/product/product_controllers_test.dart</code> (23)</summary>

- **loading**
  - loads the detail and the variants together and selects the first offer
  - a 404 or a failed variants call fails the page with its typed failure
  - a display currency change reloads the product
  - nothing to buy: unavailable, nothing selected, add disabled
  - price_may_vary of the product or of the selected variant
- **variant selection**
  - selects amounts of the region; blocked amounts are refused
  - switching region keeps the denomination
- **quantity**
  - stays between 1 and the per-order limit
- **delivery fields**
  - local checks run only after the first add attempt, then as the customer types
  - number fields store Western digits; unknown keys are ignored
- **add to cart**
  - success: sends the variant, quantity and trimmed values; updates the cart badge
  - 422 validation_failed: delivery_values.{key} beside the field
  - 422 fixture (quantity and variant_id): quantity beside the stepper, the rest in the banner
  - 422 item_unavailable (out of stock) shows the server text
  - per-order limit counting the cart (422 quantity)
  - offline: the typed failure for the banner, nothing added
  - a second tap while adding is ignored
- **cart badge count**
  - a guest without a cart token is not asked for a cart
  - a guest with a cart token, and a signed-in customer, read the cart
  - a failed read keeps the last count
- **reviews**
  - first page with the rating summary, then pages until the last
  - a failed next page keeps the items and retries only when asked
  - a failed first page fails, retry loads

</details>

<details><summary><code>application/test/features/product/product_pages_test.dart</code> (22)</summary>

- **product page ($lang)**
  - 05.1: art, title, region, amounts, field and server prices
  - 05.2: description (HTML as text), instructions, terms, reviews
  - 05.5: skeleton while loading
  - server error with the request id, then retry
  - offline: banner and retry
  - 05.6: not found, back to the store
  - unavailable: every option blocked, reasons shown, add disabled
  - 05.3: price may vary notice
  - region switch shows that region's amounts and "few left"
  - quantity stepper: price × n, limit note, buttons disable
  - local validation: an empty required field blocks the request
  - 422: the server's delivery_values error beside the field
  - 422 item_unavailable: banner with the server text
  - 05.4: added, snackbar, View cart opens the Cart tab with the badge
  - number fields: digit keyboard, Western digits
  - select fields open a sheet of options
  - See all reviews opens the paged list (infinite scroll)
  - reviews: empty and failed
  - deep link location /products/:slug opens the page directly
  - 360 wide: nothing overflows
  - meets tap-target and label guidelines
  - reviews page meets tap-target and label guidelines

</details>

<details><summary><code>application/test/features/product/product_rules_test.dart</code> (20)</summary>

- **delivery field validation mirrors DeliveryFieldValidator**
  - ${field.type} ${field.key} "${value.length > 20 ?
  - the type check runs before the pattern, as on the server
  - a pattern Dart cannot compile is left to the server
  - PHP trim keeps Unicode spaces the server keeps
  - validates every field and sends only trimmed, known, non-empty values
  - Arabic-Indic and Persian digits become Western digits
- **variant options**
  - the fixture: US 10 USD in stock, GL 25 USD limited
  - blocked variants carry their reason
  - starts in the first region that has something to buy
  - nothing to buy: first region, no variant
  - changing region keeps the denomination when it can be bought there
  - regions without a name and variants without a region
  - quantity limit: the product limit, never above the request limit 10
- **ratings on decimal strings**
  - labels drop trailing zeros
  - stars round down with a half from .5
- **product description HTML as text**
  - the fixture description: one paragraph with a bold run
  - headings, lists, line breaks and entities
  - scripts, styles, images and links never become anything but text
  - table cells are joined on one line
  - plain text without tags is one paragraph

</details>

### `features/security`

<details><summary><code>application/test/features/security/security_controllers_test.dart</code> (14)</summary>

- **turn on two-step verification**
  - enable, confirm, recovery codes once, then finish
  - the state never prints the key or the codes
  - a wrong password stays on the password step
  - a wrong code keeps the key and says so
  - already on (409) marks the account and shows no key
  - leaving the screen turns screen protection off and drops the key
- **turn off two-step verification**
  - needs the password; success marks the account
- **devices**
  - this device first, then the others by last use
  - signing a device out drops it; this device is never offered
  - a device already gone (404) is dropped and the list reloaded
  - another failure keeps the device
  - sign out of all other devices needs the current password
  - a failed load stays failed until reload
  - signed out loads nothing

</details>

<details><summary><code>application/test/features/security/security_pages_test.dart</code> (11)</summary>

- **security ($lang)**
  - two-step off, password, biometric switch, devices, note
  - the biometric switch off deletes the server and local key
  - turn on: password, key and QR, code, recovery codes
  - a wrong code keeps the key with the server message
  - turn off asks for the password
  - sign out everywhere
  - screen-reader labels and tap targets
- **devices ($lang)**
  - current first; sign a device out after confirming
  - sign out of all other devices with the password
  - error with retry
  - screen-reader labels and tap targets

</details>

<details><summary><code>application/test/features/security/security_safety_test.dart</code> (4)</summary>

  - nothing in the security screens logs, stores, caches or reports
  - the enrolment controller is auto-disposed and the providers are not kept alive
  - the enrolment and confirmation are logged without the key or the codes
  - a full enrolment and a password change write nothing to storage

</details>

### `features/storefront`

<details><summary><code>application/test/features/storefront/hero_carousel_test.dart</code> (9)</summary>

  - auto-advances every 5 seconds and wraps around
  - advances in RTL too (pages follow the reading direction)
  - pauses while touched and restarts a full interval after release
  - a swipe changes the page and does not resume during the drag
  - pauses while a slide has keyboard or accessibility focus
  - never auto-advances with reduce motion on
  - stops when its tab or route is inactive
  - stops when scrolled off-screen and resumes when back
  - a single banner never moves; slides open their link

</details>

<details><summary><code>application/test/features/storefront/storefront_controllers_test.dart</code> (20)</summary>

- **home**
  - loads the server page, fails with a typed failure and retries
  - visible sections drop unknown items and empty sections
- **currency change invalidates priced data**
  - home, listing and the X-Currency choice reload from page 1
  - signed-in customers save the currency on the profile first
  - options come from the currencies endpoint, else the app config
- **listing pagination**
  - pages follow meta until the last page
  - a second load-more while one is running is ignored
  - a failed page keeps the items and retries the same page
  - filters restart at page 1 with decimal-string bounds
  - first-page failure and empty results
  - price bounds are validated as decimals
  - listing title uses translated names, then the slug
  - categories group under their parents
- **search**
  - suggestions are debounced; short queries send nothing
  - submit commits the term and records it in the local history
  - history is newest first, deduplicated, capped, removable and clearable
  - a corrupt stored history reads as empty
- **link targets (LinkTargetResolver)**
  - map to app locations or the browser
  - unsafe or unknown targets lead nowhere
  - suggestions open the product, the listing or a text search

</details>

<details><summary><code>application/test/features/storefront/storefront_pages_test.dart</code> (22)</summary>

- **home ($lang)**
  - shows the API banners, sections and server prices
  - loading shows the skeleton
  - server error shows the request id and retries
  - offline shows the banner and retry
  - the hero opens its link target (product)
  - a search promo banner opens search with the term
  - currency switcher reloads prices in the new currency
  - tabs switch and keep their pages
- **browse and platforms ($lang)**
  - browse lists categories with children and platforms
  - platforms grid opens the platform listing
  - taxonomy failure shows the error state
- **listing ($lang)**
  - infinite scroll loads the next pages
  - a failed page shows retry and keeps the products
  - filters apply, empty state clears them
  - invalid price bounds stay in the sheet
  - sort sheet re-queries from page 1
  - empty catalog and first-page error
- **search ($lang)**
  - suggestions, submit, history and clear
  - a suggestion opens its listing; popular terms search
  - history terms can be removed one by one
  - no results
- **accessibility ($lang)**
  - $location meets tap-target and label guidelines

</details>

### `features/usecases`

<details><summary><code>application/test/features/usecases/place_order_test.dart</code> (10)</summary>

  - reuses the key on a retry with the same body
  - a changed body gets a new key
  - a body change after a network failure also gets a new key
  - the same key survives a restart through the pending checkout
  - stores the order on the pending checkout to resume at its status
  - meta.duplicate routes to the existing order without paying
  - next: null calls retry-payment once
  - a replay with next: null whose retry fails reports the order without a payment
  - idempotency_in_progress keeps the key; idempotency_mismatch drops it
  - the body hash ignores key order and changes with any value

</details>

<details><summary><code>application/test/features/usecases/resolve_payment_test.dart</code> (7)</summary>

  - polls after 2 s, 3 s, 5 s, then every 10 s for 2 minutes, then still processing
  - stops as soon as the server says paid
  - is_final without payment is finished, never paid
  - never reports paid when the order status says paid but payment_status does not
  - a paid verification needs no polling
  - PayPal captures, the wallet polls without a confirmation call, a resume verifies
  - transient failures keep polling; a 404 stops

</details>

<details><summary><code>application/test/features/usecases/reveal_code_test.dart</code> (8)</summary>

  - 423: asks for the password, confirms, then retries the reveal once
  - a second 423 after confirming is returned, not retried again
  - a cancelled password sheet stops without confirming
  - a wrong password returns the confirmation failure and does not reveal
  - never retries on 429 or any other failure, and never asks for the password
- **confirmRecentAuth (M-A14: biometric, then the password sheet)**
  - 423: confirms once and retries once, sending the integrity header
  - a step that is not confirmed returns the 423 without retrying
  - 429 is returned at once and the step is never asked

</details>

### `features/wallet`

<details><summary><code>application/test/features/wallet/wallet_controller_test.dart</code> (24)</summary>

- **wallet controller**
  - loads the balance and the first page together
  - pages through meta: appends, stops at the last page
  - a failed next page keeps the statement and is not retried by itself
  - one page loads at a time
  - refresh reloads page 1 and the balance and keeps the list while it runs
  - a failed refresh keeps what was shown
  - a failed balance or statement fails the screen until retry
  - an empty wallet is loaded and empty
  - wallet_enabled off loads nothing and says it is not available
  - signed out loads nothing; signing in loads it
  - a placed order or payment answer (CartRevision) reloads the balance
  - a late answer from before a reload is dropped
  - the wallet currency comes from the app configuration
  - without a configured currency the code and the balance scale are used
  - contact support opens the configured https link only
  - the wallet screen is for signed-in customers only
- **statement amounts**
  - signed credit and debit at the currency exponent, never rounded up
  - a malformed amount is shown as sent, with the code
  - credit or debit follows the sign of the amount
- **checkout wallet option (M-A13 follow-up)**
  - shows the wallet balance from GET /account/wallet
  - wallet_enabled off drops the wallet option and reads no balance
  - no wallet option, no balance call
  - a failed balance only hides the balance
  - before the launch check the wallet counts as enabled

</details>

<details><summary><code>application/test/features/wallet/wallet_page_test.dart</code> (18)</summary>

- **wallet ($lang)**
  - the Account tab opens the wallet
  - statement: type, order, signed amounts and balance after
  - a line with an order opens the M-A14 order detail
  - a line without an order is not a button
  - loading shows the skeleton, announced once
  - empty: zero balance and no transactions (12.3)
  - frozen: banner, badge and contact support (12.2)
  - frozen without a support link has no footer
  - server error shows the request id; Try again reloads
  - offline shows the banner; Retry reloads
  - scrolling loads the next page; a failed page has a retry bar
  - pull to refresh reloads the balance
  - wallet_enabled off: no Account entry; the screen is unavailable
  - signed out, the wallet asks to sign in first
  - screen reader labels and 48dp targets
  - fits 360 wide with a long balance
  - checkout shows the wallet balance in the wallet option
  - checkout hides the wallet option when wallet_enabled is off

</details>

### `goldens`

<details><summary><code>application/test/goldens/account_golden_test.dart</code> (15)</summary>

  - 17.1 account hub ($lang)
  - 17.1 account hub, signed out ($lang)
  - 17.1 account hub at 360x800 ($lang)
  - 17.2 profile with a phone error ($lang)
  - 17.2 profile, email not verified ($lang)
  - 17.3 settings ($lang)
  - 17.4 sign out confirm ($lang)
  - 18.1 security ($lang)
  - 18.2 turn on two-step verification ($lang)
  - 18.3 recovery codes ($lang)
  - 18.4 turn off two-step verification ($lang)
  - 18.5 devices ($lang)
  - 18.6 sign a device out ($lang)
  - 18.7 change password, wrong current password ($lang)
  - devices, loading failed ($lang)

</details>

<details><summary><code>application/test/goldens/auth_golden_test.dart</code> (13)</summary>

  - 01.2 splash offline ($dir)
  - 01.3 force update ($dir)
  - 01.4 maintenance ($dir)
  - 01.5 onboarding language ($dir)
  - 01.6 onboarding currency ($dir)
  - 02.2 login rate limited with field error ($dir)
  - 02.3 two-factor code ($dir)
  - 02.6 register errors ($dir)
  - 02.7 verify email ($dir)
  - 02.8 forgot password ($dir)
  - biometric offer ($dir)
  - app lock, password fallback ($dir)
  - rooted device sheet ($dir)

</details>

<details><summary><code>application/test/goldens/commerce_golden_test.dart</code> (18)</summary>

  - 06.1 cart ($dir)
  - 06.2 blocked line and coupon error ($dir)
  - 06.3 empty cart ($dir)
  - 06.4 cart loading ($dir)
  - 06.5 cart error ($dir)
  - 06.1 web checkout mode ($dir)
  - 07.2 checkout ($dir)
  - 07.2 checkout, summary ($dir)
  - 07.3 payment notices ($dir)
  - 07.4 country sheet ($dir)
  - 07.5 price changed ($dir)
  - 07.6 payment in progress ($dir)
  - 07.7 payment options error ($dir)
  - 08.1 paid ($dir)
  - 08.2 processing ($dir)
  - 08.3 failed ($dir)
  - 08.4 expired ($dir)
  - 19.3 checkout at 360x800 ($dir)

</details>

<details><summary><code>application/test/goldens/orders_golden_test.dart</code> (22)</summary>

  - 09.1 orders ($dir)
  - 09.2 orders empty ($dir)
  - 09.3 orders loading ($dir)
  - 09.3 orders error ($dir)
  - orders signed out ($dir)
  - 09.4 order detail ($dir)
  - 09.5 partial refund and timeline ($dir)
  - order awaiting payment with retry ($dir)
  - 10.1 code masked ($dir)
  - 10.2 re-auth sheet ($dir)
  - 10.3 wrong password ($dir)
  - 10.4 code revealed and copied ($dir)
  - 10.5 hidden during capture ($dir)
  - 10.6 denied and device warning ($dir)
  - 10.6 rate limited ($dir)
  - 19.4 code revealed at 360x800 ($dir)
  - 11.1 guest verify email ($dir)
  - 11.2 guest enter code ($dir)
  - 11.3 guest code error ($dir)
  - 11.4 guest order ($dir)
  - 11.5 guest link expired ($dir)
  - guest paste link ($dir)

</details>

<details><summary><code>application/test/goldens/product_golden_test.dart</code> (11)</summary>

  - 05.1 product ($dir)
  - 05.2 details and reviews ($dir)
  - 05.3 price may vary and field errors ($dir)
  - 05.3 nothing can be bought ($dir)
  - 05.4 added to cart ($dir)
  - 05.5 loading ($dir)
  - 05.6 not found ($dir)
  - product offline ($dir)
  - reviews list ($dir)
  - bottom nav cart count ($dir)
  - 19.2 product at 360x800 ($dir)

</details>

<details><summary><code>application/test/goldens/shell_golden_test.dart</code> (2)</summary>

  - token shell home ($direction)
  - token shell notice ($direction)

</details>

<details><summary><code>application/test/goldens/storefront_golden_test.dart</code> (14)</summary>

  - 03.1 home ($dir)
  - 03.1 home, featured ($dir)
  - 03.2 home loading ($dir)
  - 03.3 home error ($dir)
  - 03.4 home offline ($dir)
  - 03.5 currency sheet ($dir)
  - 04.1 platforms ($dir)
  - 04.2 listing ($dir)
  - 04.3 filters sheet ($dir)
  - 04.4 listing empty ($dir)
  - 04.5 listing load-more error ($dir)
  - 04.6 search ($dir)
  - 04.7 search no results ($dir)
  - 04.8 browse ($dir)

</details>

<details><summary><code>application/test/goldens/wallet_golden_test.dart</code> (11)</summary>

- **M-A15 goldens ($dir)**
  - 12.1 wallet with statement and next page
  - 12.2 wallet frozen
  - 12.3 wallet empty
  - wallet loading
  - wallet server error
  - wallet offline
  - wallet next page failed
  - wallet not available (wallet_enabled off)
  - wallet at 360x800
  - account tab with the wallet entry
  - 07.2 checkout wallet option with balance

</details>

## App golden images

236 committed reference images under `application/test/goldens/`. Goldens written after the no-run rule have no image yet; the final run creates them with `--update-goldens`, and each new image must be reviewed before it is committed.

## The single final run

Run in this order, each on its own; never with `--parallel` (the suites share the `gaming-store_testing` database).

```bash
cd /var/www/enterprise-gaming-digital-store/backend && vendor/bin/pint --test && php artisan test --compact
```

```bash
cd /var/www/enterprise-gaming-digital-store/application && PATH=/home/joe/flutter/bin:$PATH flutter test --update-goldens test/goldens
```

```bash
cd /var/www/enterprise-gaming-digital-store/application && PATH=/home/joe/flutter/bin:$PATH bash tool/ci.sh
```
