# External Integrations

Research date: 2026-11-10. Every fact below was read from the cited official source on that date unless marked.

**Markers.** `UNVERIFIED`: could not be confirmed from official documentation; must be proven against the provider's sandbox or support before relying on it. `SECONDARY`: from a non-official source only. Rule 17 of the spec applies: nothing is assumed to exist because it would be useful.

**Status of every integration in this file: awaiting credentials / sandbox verification.** No credentials exist. Adapters ship complete with mocked tests.

---

## Summary of findings that change the plan

| # | Finding | Effect |
|---|---|---|
| 1 | Reloadly Gift Cards has no player-ID direct top-up. The Airtime API is mobile numbers only. | Spec's direct game recharge needs another supplier or manual fulfilment |
| 2 | Reloadly has no cancel or refund endpoint for gift cards or top-ups | `cancelOrder` and `refundOrder` are declared unsupported |
| 3 | Reloadly's `/orders` default limit is USD 100 per request; raising it requires 2FA and an IP whitelist; live only | Production needs a static egress IP and portal setup |
| 4 | Reloadly: no documented idempotency, but transaction reports filter by `customIdentifier` | Ambiguous outcomes can be verified by reference |
| 5 | Stripe's global availability page does not list Egypt or Saudi Arabia for merchants; UAE is listed | Business decision on the Stripe entity |
| 6 | PayPal's currency list excludes EGP, SAR and AED | These can only be display currencies on PayPal |
| 7 | Paymob documents no idempotency mechanism and no callback retry policy | Local guards and polling are mandatory |
| 8 | Google removed FAQ rich results (no longer shown since May 2026) | FAQ schema off by default |

---

## 1. Reloadly

Sources (official): OpenAPI specs loaded by the docs site: `https://docs.reloadly.com/assets/api/gift-cards.json`, `https://docs.reloadly.com/assets/api/airtime.json`. Guides: `https://developers.reloadly.com/gift-cards/order-giftcard`, `/gift-cards/quickstart`, `/gift-cards/error-codes`, `/gift-cards/transactions`, `/developer-tools/sandbox`, `/developer-tools/webhooks/security`, `/developer-tools/webhooks/events`. Support: `https://support.reloadly.com/reloadly-webhook`.

### 1.1 Base URLs and authentication

| | Sandbox | Production |
|---|---|---|
| Gift Cards API | `https://giftcards-sandbox.reloadly.com` | `https://giftcards.reloadly.com` |
| Airtime API | `https://topups-sandbox.reloadly.com` | `https://topups.reloadly.com` |
| Auth (both) | `https://auth.reloadly.com` | `https://auth.reloadly.com` |

`POST /oauth/token`, JSON body: `client_id`, `client_secret`, `grant_type=client_credentials`, `audience` (the product base URL for the environment).

- Tokens are per product: a Gift Card token cannot call Airtime.
- Production tokens last 60 days, test tokens 24 hours. Cache by `expires_in`, and refresh once on `401 INVALID_TOKEN` (or expired) before giving up. A token refresh is a safe retry because the 401 means the order did not run.
- Calls require `Accept: application/com.reloadly.giftcards-v1+json` (`-v2+json` for the v2 redeem-code response). Airtime uses `application/com.reloadly.topups-v1+json`.

### 1.2 Gift Card endpoints we use

| Purpose | Method and path | Notes |
|---|---|---|
| Balance | `GET /accounts/balance` | `balance`, `currencyCode`, `currencyName`, `updatedAt` |
| Categories | `GET /product-categories` | |
| Countries | `GET /countries`, `GET /countries/{code}` | |
| Products | `GET /products?size=&page=&productName=&countryCode=&productCategoryId=&includeRange=&includeFixed=&global=` | Paged |
| Product details | `GET /products/{id}` | |
| By country | `GET /countries/{code}/products` | |
| Redeem instructions | `GET /redeem-instructions`, `GET /products/{id}/redeem-instructions` | `concise`, `verbose` |
| Discounts | `GET /discounts`, `GET /products/{id}/discounts` | |
| FX | `GET /fx-rate?currencyCode=&amount=` | Sender-currency cost for range products |
| Place order | `POST /orders` | Below |
| Redeem code | `GET /orders/transactions/{transactionId}/cards` | Below |
| Transactions | `GET /reports/transactions?size=&page=&customIdentifier=&startDate=&endDate=` | **Lookup by reference** |
| Transaction | `GET /reports/transactions/{id}` | |

Product fields used: `productId`, `productName`, `global`, `status`, `supportsPreOrder`, `senderFee`, `senderFeePercentage`, `discountPercentage`, `denominationType` (`FIXED` or `RANGE`), `recipientCurrencyCode`, `recipientCurrencyToSenderCurrencyExchangeRate`, `senderCurrencyCode`, `minRecipientDenomination`, `maxRecipientDenomination` (the spec's sample spells it `maxrecipientDenomination`; parse both), `fixedRecipientDenominations`, `fixedSenderDenominations`, `fixedRecipientToSenderDenominationsMap`, `logoUrls`, `brand`, `category`, `country`, `redeemInstruction`, `additionalRequirements.userIdRequired`.

### 1.3 Place order

`POST /orders`. Required: `productId` (int), `quantity` (int), `unitPrice` (number, must be a listed fixed denomination or within the range, in recipient currency), `senderName` (string). Optional: `customIdentifier`, `preOrder` (bool), `recipientEmail`, `recipientPhoneDetails`, `productAdditionalRequirements.userId`.

Our usage: `quantity=1`, `customIdentifier = supplier_transactions.client_reference`, **`recipientEmail` omitted** (the spec states that if absent Reloadly sends no email), `senderName` = store name from settings.

Response fields: `transactionId`, `amount`, `discount`, `currencyCode`, `fee`, `smsFee`, `totalFee`, `recipientEmail`, `customIdentifier`, `status`, `transactionCreatedTime`, `preOrdered`, `product{...}`, `balanceInfo{oldBalance,newBalance,cost,currencyCode,currencyName,updatedAt}`. The purchase response does **not** include the code.

Amounts have up to 5 decimal places in official samples. Cost basis for profit is `balanceInfo.cost`.

### 1.4 Deliverables

`GET /orders/transactions/{id}/cards`. v2 returns `cardNumber`, `pinCode`, `redemptionUrl`; v1 returns `cardNumber`, `pinCode`.

The official schema types these as integer. Treat as strings and decode with `JSON_BIGINT_AS_STRING` or digits and leading zeros can be lost. The shape for `quantity > 1` is `UNVERIFIED` (official sample is a single object); not relevant at `quantity=1` but the parser must accept an array too.

### 1.5 Statuses and error classification

Transaction statuses (official): `SUCCESSFUL`; `PENDING` (queued); `PROCESSING`; `REFUNDED` (attempt failed, funds instantly reversed); `FAILED` (could not be purchased, funds **not yet** reversed).

| Observation | Outcome kind |
|---|---|
| HTTP 200, `SUCCESSFUL` | `Fulfilled` |
| HTTP 200, `PENDING` or `PROCESSING` | `InProgress` |
| `REFUNDED` | `Rejected` |
| `FAILED` | `Rejected`, set `cost_reversal_pending` |
| Connection refused, DNS, TLS, connect timeout | `Rejected` (not sent) |
| 401 | `Rejected` after a single token refresh |
| 400 with `errorCode: null` (official description: incorrect body parameter) | `Rejected`, alert operator |
| `INSUFFICIENT_BALANCE`, `PRODUCT_NOT_FOUND`, `PRICE_IS_NOT_PERMITTED`, `WRONG_PRODUCT_PRICE`, `PRE_ORDER_IS_NOT_ALLOWED`, `DIFFERENT_CURRENCIES`, `ACTION_NOT_ALLOWED`, `TEMPORARY_OUT_OF_STOCK`, `RESERVED` | `Rejected` |
| `PENDING_OR_IN_PROGRESS`, `SET_ORDER_PROCESS_FACED_A_PROBLEM`, `COST_CALCULATION_PROBLEM`, `ORDER_IS_NOT_READY`, `CARD_IS_NOT_READY`, `DIGITAL_ROLLBACK`, `DUPLICATE_CARD`, `IMPORT_CARD_ERROR`, `NO_ACCESS_TO_API` | `Ambiguous` |
| Read timeout, connection reset, 5xx, malformed body, 429, any unknown code | `Ambiguous` |

The docs state what each code means but not whether funds were taken. The `Rejected` list is a judgment from those descriptions and must be re-checked in sandbox and with Reloadly support before go-live. `UNVERIFIED`.

### 1.6 Capability declaration

| Capability | Level | Basis |
|---|---|---|
| ListProducts, ProductDetails, Countries, Pricing, Balance, CreateOrder, OrderStatus, FetchDeliverables, TransactionHistory | `full` | Official OpenAPI |
| LookupByReference | `full` | `customIdentifier` filter on `/reports/transactions` |
| Availability | `advisory` | Only product `status` and `CARD_IS_NOT_READY` / `TEMPORARY_OUT_OF_STOCK` at order time. No stock endpoint. |
| CancelOrder, RefundOrder | `none` | No such operation in the OpenAPI spec |
| ClientIdempotency | `unverified` | `customIdentifier` is described as a "unique reference". The airtime spec says it cannot be reused; the gift card spec has no such sentence, and the behaviour on reuse is undocumented. Do not rely on it as a guarantee. |
| Webhooks | `unverified` | Dashboard webhooks exist (section 1.8). Gift card events are unconfirmed. |
| PerRequestAmountCap | `full` | USD 100 default |

Delivery types: `instant_code`, `api_delivery`. Products with `additionalRequirements.userIdRequired=true` accept `productAdditionalRequirements.userId`; the sample applies to "certain products" and is not a general game top-up mechanism.

### 1.7 Limits and account requirements

- Default limit **USD 100 (or equivalent) per request** to `/orders`. To raise: enable 2FA on the portal and whitelist at least one IP (Security, Manage Whitelist). Takes effect within 2 hours; live mode only, cannot be tested in sandbox.
- Rate limits: not documented in the OpenAPI spec. `UNVERIFIED`. Treat 429 as `Ambiguous` for orders and back off for reads.
- Sandbox test wallet is pre-funded at signup.

### 1.8 Webhooks

Configured in the dashboard (Developers, Webhooks); endpoint, service and events are selected there; a webhook signature secret is shown on that screen. Delivery is at-least-once, up to 10 attempts on non-2xx. Holding the connection longer than 3 seconds makes Reloadly treat the message as consumed (support article), so respond immediately. Docs recommend an IP allow-list, whose values were not rendered on the page.

| Item | Status |
|---|---|
| Signature header name and algorithm | `UNVERIFIED` (code samples are not available without an interactive session) |
| Event names | `airtime_transaction.status` only in a 2022 blog post. Full list `UNVERIFIED` |
| Gift card events | `UNVERIFIED` |

Design consequence: Reloadly webhooks, if enabled later, only schedule an immediate status poll. They are not a source of truth, and polling is the primary path.

### 1.9 Airtime API

`POST /topups` (sync), `POST /topups-async` (returns `transactionId`), `GET /topups/{id}/status`, `GET /topups/reports/transactions` (filter by `customIdentifier`), `GET /operators/auto-detect/phone/{phone}/countries/{iso}`. Body: `operatorId`, `amount`, `useLocalAmount`, `customIdentifier` (documented: cannot be reused), `recipientPhone{countryCode,number}`, `senderPhone`. Statuses: `SUCCESSFUL`, `PROCESSING`, `REFUNDED`, `FAILED`.

Mobile airtime and data only, not game accounts. Not in initial scope; an adapter can be added under the same contract if mobile top-ups are wanted.

### 1.10a Adapter status (M8)

**Built: `App\Domains\Suppliers\Adapters\Reloadly\ReloadlyGiftCardAdapter`. Tested with `Http::fake` only; awaiting sandbox verification. It has never called the live API and no credentials exist.**

| Operation | Endpoint used | State |
|---|---|---|
| Token | `POST https://auth.reloadly.com/oauth/token` | Implemented. Cached encrypted until 5 minutes before `expires_in`; one refresh on 401 |
| Balance, test connection | `GET /accounts/balance` | Implemented |
| Products, details, pricing, advisory availability | `GET /products`, `GET /products/{id}` | Implemented |
| Countries | `GET /countries` | Implemented (`isoName`, `name`, `currencyCode`; field names `UNVERIFIED`) |
| Create order | `POST /orders` | Implemented: `quantity=1`, `customIdentifier`, `senderName`, `preOrder=false`, `productAdditionalRequirements.userId` from a `user_id` delivery field, no `recipientEmail` |
| Order status | `GET /reports/transactions/{id}` | Implemented; response shape assumed to be the transaction object (`UNVERIFIED`) |
| Lookup by reference | `GET /reports/transactions?customIdentifier=` | Implemented; dates not sent. Only a well-formed empty page counts as not found; two matches are ambiguous |
| Deliverables | `GET /orders/transactions/{id}/cards` (v2 Accept header for `redemptionUrl`) | Implemented; body never logged; single object or list accepted |
| Transaction report | `GET /reports/transactions?startDate=&endDate=` | Implemented for reconciliation only; date format `Y-m-d H:i:s` is `UNVERIFIED` and the run only reports |
| Cancel, refund | none | Declared `none`; calling them throws `CapabilityNotSupported` |
| Webhooks | none | Declared `unverified`; no route is registered |
| Client idempotency | `customIdentifier` | Declared `unverified`; never relied on as a guarantee |

Assumptions to check in the sandbox (`UNVERIFIED`): paged responses use `content` and `last` (or `totalPages`); product `status` values beyond `ACTIVE`/`INACTIVE`; effective cost = sender amount + `senderFee` + `senderFeePercentage`, with `discountPercentage` **not** subtracted until confirmed (`suppliers.apply_discount_to_cost`, default false); the error-code split in 1.5; the HTTP status that carries each `errorCode` (codes are read from any non-2xx below 500). JSON is decoded with every number kept as its literal text, which covers `cardNumber` typed as an integer.

### 1.10 Not supported

Cancel or refund orders; stock level query; player-ID game top-up as a general mechanism; a documented idempotency guarantee on `POST /orders`.

---

## 2. Stripe

Sources: `https://docs.stripe.com/api/payment_intents/create`, `/webhooks`, `/api/idempotent_requests`, `/currencies`, `/refunds`, `/api/events/types`, `https://stripe.com/global`.

| Item | Value |
|---|---|
| Base URL | `https://api.stripe.com` (same for test and live; the key selects the mode) |
| Auth | Secret key, HTTP Basic or Bearer. Keys `sk_test_`, `sk_live_`. |
| Version | Send `Stripe-Version`. Doc examples show `2026-10-28.endive` at research date. Pin in config. |

**Create:** `POST /v1/payment_intents`, form-encoded. Fields used: `amount` (positive integer, smallest unit), `currency` (lowercase), `automatic_payment_methods[enabled]=true`, `metadata[order_public_id]`, `metadata[payment_public_id]`, `receipt_email`, `description`, `statement_descriptor_suffix` (cards, not `statement_descriptor`). `capture_method` default is `automatic_async`. Returns `client_secret` for Stripe.js Elements.

**Verify:** `GET /v1/payment_intents/{id}`; check `status`, `amount_received`, `currency`. Fee retrieval through `expand[]=latest_charge.balance_transaction` is `UNVERIFIED` here (not on the pages read); confirm in sandbox.

**Statuses:** `requires_payment_method`, `requires_confirmation`, `requires_action`, `processing`, `requires_capture`, `canceled`, `succeeded`.

**Idempotency:** `Idempotency-Key` header, up to 255 characters, all v1 `POST`s. The saved response, including 500s, is replayed. Keys may be pruned after at least 24 hours; a reused key with different parameters errors; a concurrent conflict is not saved and is retryable.

**Webhooks:**
- Signature in `Stripe-Signature`: `t=<ts>,v1=<hex>`. `HMAC-SHA256` of `{t}.{raw body}` with the `whsec_` secret. Ignore schemes other than `v1`. Library default tolerance 5 minutes. Rotation can produce multiple `v1` values for up to 24 hours. Raw body required.
- Live retries up to 3 days with exponential backoff; sandbox retries 3 times over a few hours.
- No ordering guarantee. Dedupe on `event.id`. Separate Event objects can be emitted for one occurrence: also compare `data.object.id` + `event.type`.
- Use **snapshot** payloads. Stripe recommends thin for new integrations, but snapshot avoids an extra call for the fields we re-verify anyway, and the handler re-reads state regardless.
- Must be publicly reachable HTTPS (TLS 1.2+). Up to 16 event destinations. Stripe publishes source IPs (`https://docs.stripe.com/ips`).

Events subscribed: `payment_intent.succeeded`, `payment_intent.processing`, `payment_intent.payment_failed`, `payment_intent.canceled`, `payment_intent.requires_action`, `charge.refunded`, `refund.created`, `refund.updated`, `refund.failed`, `charge.dispute.created`, `charge.dispute.updated`, `charge.dispute.closed`, `charge.dispute.funds_withdrawn`, `charge.dispute.funds_reinstated`.

**Refunds:** `POST /v1/refunds` with `payment_intent`, optional `amount`. Statuses observed: `pending`, `requires_action`, `succeeded`, `failed`, `canceled`. Original fees are not returned. Requires sufficient balance (else card refunds wait as pending; others fail). Can fail up to 30 days later (`refund.failed`).

**Currencies and minimums:** Amount is in the smallest unit. Minimums include 0.50 USD, 0.50 EUR, 0.30 GBP, 2.00 AED; charges converted to a settlement currency must meet that currency's minimum. EGP and SAR do not appear on the minimum list. Whether they are presentment currencies for a given account is `UNVERIFIED`, so they stay disabled in `payment_gateway_currencies` until confirmed on the live account. Special cases: ISK and UGX are sent with a `00` suffix; HUF and TWD have payout-only quirks.

**Availability:** `stripe.com/global` lists UAE as a supported business country, and does not list Egypt or Saudi Arabia. Merchant entity is a business decision (see Roadmap).

**Not supported / restrictions:** Payment methods depend on the account; `automatic_payment_methods` returns what is enabled. Do not hardcode a method list.

---

**Return URL (M-A6).** The PaymentIntent is confirmed by the client (Stripe.js or PaymentSheet), which supplies its own `return_url`; the create request is unchanged. For `client=app` attempts the server publishes the value to use as `next.return_url`. Verified (`https://docs.stripe.com/payments/accept-a-payment?payment-ui=mobile&platform=ios`, section "Set up a return URL", read 2026-11-10): the iOS SDK needs a **custom URL scheme** for redirect-based methods and does not support universal links, so `PAYMENTS_STRIPE_APP_RETURN_URL` must be set to the app's scheme; without it the HTTPS app link is published, which is not expected to work on iOS. Whether the Android SDK reads `returnURL` at all is `UNVERIFIED` here.

**Adapter status (M10):** `StripePaymentGateway` built; mocked tests only (SDK on a faked transport); awaiting sandbox verification and BD1. Fees are estimated from fee rules because the balance-transaction expansion is `UNVERIFIED`. ISK, UGX, HUF and TWD are refused until their special cases are verified.

---

## 3. PayPal Orders v2

Sources: `https://developer.paypal.com/api/rest/requests/`, `/api/rest/authentication/`, `https://developer.paypal.com/api/orders/v2/schema.json`, `/docs/api/payments/v2/`, `/api/payments/v2/captures-refund`, `/api/rest/webhooks/rest/`, `/api/rest/webhooks/event-names/`, `/docs/reports/reference/paypal-supported-currencies/`.

| Item | Sandbox | Live |
|---|---|---|
| Base | `https://api-m.sandbox.paypal.com` | `https://api-m.paypal.com` |

**Auth:** `POST /v1/oauth2/token`, Basic (client ID, secret), `grant_type=client_credentials`. Cache for `expires_in`.

| Purpose | Endpoint |
|---|---|
| Create | `POST /v2/checkout/orders` |
| Read | `GET /v2/checkout/orders/{id}` |
| Capture | `POST /v2/checkout/orders/{id}/capture` |
| Capture read | `GET /v2/payments/captures/{id}` |
| Refund | `POST /v2/payments/captures/{id}/refund` |
| Webhook verify | `POST /v1/notifications/verify-webhook-signature` |

**Create body:** `intent=CAPTURE`; `purchase_units[0]` (schema allows up to 10; we use 1): `reference_id` (max 256), `custom_id` (max 255) = `payment.public_id`, `invoice_id` (max 127) = `{order_number}-{attempt}` (PayPal can reject duplicate invoice IDs per account, so the attempt number must be included), `amount{currency_code,value}`, `description`. `Prefer: return=representation` for the full resource.

**Return and cancel URLs (M-A6).** Sent as `payment_source.paypal.experience_context.return_url` ("The URL where the customer will be redirected upon approving a payment") and `cancel_url` (upon cancelling), both `format: uri`. Verified against the official Orders v2 schema (`https://developer.paypal.com/api/orders/v2/schema.json`, version 2.36, `paypal_wallet_experience_context`, read 2026-11-10); the same schema marks the top-level `application_context` fields deprecated in favour of `experience_context`. `UNVERIFIED`: which approval link rel the create response carries when `payment_source.paypal` is sent (`payer-action` is expected; the adapter accepts `approve` or `payer-action`), whether the order status is then `CREATED` or `PAYER_ACTION_REQUIRED` (both map to requires-action), and which query parameters PayPal appends on return (ignored). Values: web = the return page (`PAYMENTS_RETURN_URL`) and `PAYMENTS_CANCEL_URL` (else the return page); app = `{APP_URL}/app/payments/return/{order}?attempt={payment}` and `/app/payments/cancel/...`.

**Statuses.** Order: `CREATED`, `SAVED`, `APPROVED`, `VOIDED`, `COMPLETED`, `PAYER_ACTION_REQUIRED`. Capture: `COMPLETED`, `DECLINED`, `PARTIALLY_REFUNDED`, `PENDING`, `REFUNDED`, `FAILED`.

**Critical rule (from the schema):** `COMPLETED` on an order does not prove funds were captured. Always check `purchase_units[].payments.captures[].status`, and fulfil only on capture `COMPLETED`.

**Fees:** `captures[].seller_receivable_breakdown` has `gross_amount`, `paypal_fee`, `net_amount`, `exchange_rate`, `receivable_amount`.

**Idempotency:** `PayPal-Request-Id` (1 to 10,000 chars). Makes `POST`s safe to retry after timeout or 500 for as long as the server keeps it. Refunds retention: up to 45 days. Orders create and capture retention: `UNVERIFIED`.

**Webhook verification.** Headers: `paypal-transmission-id`, `paypal-transmission-time`, `paypal-cert-url`, `paypal-transmission-sig`. Two supported methods:
- Postback: `POST /v1/notifications/verify-webhook-signature` with `transmission_id`, `transmission_time`, `cert_url`, `auth_algo` (SHA256withRSA), `transmission_sig`, `webhook_id`, `webhook_event` (passed exactly as received; re-serializing can fail).
- Offline: verify signature over `transmissionId|timeStamp|webhookId|crc32` where `crc32` is the decimal CRC32 of the raw body, using the certificate's public key. Docs recommend offline.

Decision at M0: offline, with the `cert_url` host allow-listed to PayPal domains and certificates cached. **Changed at M10 (ADR-028): postback through `verify-webhook-signature`**, with the raw body passed unmodified. Postback does not work for mock events.

Retries: up to 25 times over 3 days on non-2xx.

**Events subscribed:** `CHECKOUT.ORDER.APPROVED` (drives recovery capture), `CHECKOUT.PAYMENT-APPROVAL.REVERSED`, `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.PENDING`, `PAYMENT.CAPTURE.DECLINED`, `PAYMENT.CAPTURE.REFUNDED`, `PAYMENT.CAPTURE.REVERSED`, `PAYMENT.REFUND.PENDING`, `PAYMENT.REFUND.FAILED`, `CUSTOMER.DISPUTE.CREATED`, `CUSTOMER.DISPUTE.UPDATED`, `CUSTOMER.DISPUTE.RESOLVED`.

`CHECKOUT.ORDER.COMPLETED` is documented for marketplaces and platforms only and is not used.

**Refund body:** `amount{currency_code,value}` (omit for full), `invoice_id` (127), `custom_id` (127), `note_to_payer` (255). Refund status values beyond `COMPLETED`: read from `refund_status` in the schema during implementation.

**Currencies:** AUD, CAD, CZK, DKK, EUR, HKD, ILS, MXN, NZD, NOK, PHP, PLN, GBP, SGD, SEK, CHF, THB, USD. In-country only: BRL, CNY, MYR. No decimals: HUF, JPY, TWD. **Not on the list: EGP, SAR, AED.** A customer viewing EGP who selects PayPal is charged in the configured PayPal currency, normally USD.

Merchant eligibility of an Egyptian entity is `UNVERIFIED` and must be confirmed with PayPal.

**Not supported:** Charging EGP, SAR or AED.

---

**Adapter status (M10):** `PayPalPaymentGateway` built; mocked tests only; awaiting sandbox verification. `UNVERIFIED` and handled conservatively: capture-refund `up` link parsing, dispute status mapping, create and capture key retention (re-send capped at 12 hours). The provider fee is read from `seller_receivable_breakdown.paypal_fee`.

---

## 4. Paymob (Egypt)

Sources: `https://developers.paymob.com/paymob-docs/developers/intention-apis/create-intention`, `/developers/intention-apis/overview`, `/developers/webhook-callbacks-and-hmac/overview`, `/developers/webhook-callbacks-and-hmac/transaction-callbacks`, `/developers/webhook-callbacks-and-hmac/hmac/hmac-transaction-callback`, `/developers/manage-payment-apis/void`, `/need-help/faqs`. Official Postman collections: `https://github.com/PaymobAccept/API-Postman-Collections` (Intention APIs, Refund and Void and Capture APIs, Transaction Inquiry API).

| Item | Value |
|---|---|
| Egypt base URL | `https://accept.paymob.com` (test and live are the same; keys and integration IDs select the mode) |
| Other regions | `ksa.paymob.com`, `uae.paymob.com`, `oman.paymob.com`. Accounts are region-specific, with no global account. |
| Credentials | Secret key, public key, integration IDs (one per method), HMAC secret, API key (inquiry only) |

| Purpose | Request | Auth |
|---|---|---|
| Create intention | `POST /v1/intention/` | `Authorization: Token {secret_key}` |
| Update intention | `PUT /v1/intention/{client_secret}` | Same |
| Checkout | Redirect to `/unifiedcheckout/?publicKey={public}&clientSecret={client_secret}` | n/a |
| Refund | `POST /api/acceptance/void_refund/refund`, body `transaction_id`, `amount_cents` | `Authorization: Token {secret_key}` |
| Void | `POST /api/acceptance/void_refund/void`, body `transaction_id` | Same |
| Capture | `POST /api/acceptance/capture`, body `transaction_id`, `amount_cents` | Same |
| Auth token | `POST /api/auth/tokens`, body `api_key`; 60-minute token | none |
| Inquire | `POST /api/ecommerce/orders/transaction_inquiry`, body `order_id` or `merchant_order_id`; returns only the **most recent** transaction | Bearer token |
| Retrieve | `GET /api/acceptance/transactions/{id}` | Bearer token |

**Create body:** `amount` (cents; must equal the sum of items), `currency` (must match the integration ID), `payment_methods` (integration IDs matching the key's mode), `items[]{name,amount,description,quantity}`, `billing_data` (first name, last name, phone, email mandatory), `extras`, `special_reference`, `expiration`, `notification_url`, `redirection_url` (card and wallet only).

Response: `id`, `intention_order_id`, `client_secret`, `payment_keys`, `status`, `special_reference`, `extras`, `confirmed`, `created`.

**Callbacks** (per integration ID, or `notification_url`). Sent only when a transaction succeeds or is declined.

| | Processed | Response |
|---|---|---|
| Direction | Server POST, JSON | Browser GET, query params |
| Use | **Source of truth** | Display only. Docs explicitly say do not update order status from it. |

**HMAC (official field order):** concatenate these values and compute `HMAC-SHA512` with the HMAC secret, compare in lowercase hex (sample):

`amount_cents`, `created_at`, `currency`, `error_occured`, `has_parent_transaction`, `obj.id` (POST) or `id` (GET), `integration_id`, `is_3d_secure`, `is_auth`, `is_capture`, `is_refunded`, `is_standalone_payment`, `is_voided`, `order.id` (POST) or `order_id`/`order` (GET), `owner`, `pending`, `source_data.pan`, `source_data.sub_type`, `source_data.type`, `success`.

Implementation traps: booleans appear as lowercase `true` or `false` (PHP string-casts `true` to `"1"`); `error_occured` is spelled that way. Where POST callbacks carry the `hmac` value (query string versus body) is not stated in the page text and is `UNVERIFIED`; check the query string first, then the body, and pin down in sandbox. Compare with `hash_equals`.

Fields to read: `obj.id`, `obj.success`, `obj.pending`, `obj.amount_cents`, `obj.currency`, `obj.order.id`, `obj.order.merchant_order_id`, `obj.is_refunded`, `obj.is_voided`, `obj.is_capture`, `obj.refunded_amount_cents`, `obj.captured_amount`, `obj.source_data`, `obj.merchant_commission` (seen in sample callback; meaning as gateway fee `UNVERIFIED`).

**Refunds:** partial supported (repeat until full). A refund requires a successful parent transaction and cannot exceed it. Kiosk payments cannot be refunded. May fail on insufficient balance. **Void:** card only, same business day, before settlement. Callbacks arrive on the parent with flags.

**Idempotency:** none documented. `special_reference` is described as unique; enforcement behaviour on duplicates is `UNVERIFIED`. Duplicate protection is local.

**Callback retries:** not documented. `UNVERIFIED`. Polling with `transaction_inquiry` is therefore mandatory.

**Currency and methods:** Currency must match the integration ID. Which methods apply (cards, wallets, kiosk, BNPL) depends on the merchant account. PayMe works in live mode only.

**Not supported:** Multi-region accounts; idempotency keys; automatic fee reporting (use `payment_gateway_fee_rules`).

---

**Return URL (M-A6).** `redirection_url` is set per channel: the web return page for `client=web`, the app link `{APP_URL}/app/payments/return/{order}?attempt={payment}` for `client=app`. Verified as a create-intention field from the create-intention page above ("card and wallet only"); the query parameters Paymob appends on the browser return are the transaction response callback and are display only (the docs say not to update order status from it), so the app link ignores them and the app calls `verify-payment`. `UNVERIFIED`: the behaviour of `redirection_url` for methods other than card and wallet (kiosk, BNPL), and whether Paymob follows an HTTPS app link into an installed app from Unified Checkout (to prove in sandbox on devices).

**Adapter status (M10):** `PaymobPaymentGateway` built; mocked tests only; awaiting sandbox verification. EGP only. `UNVERIFIED` and handled conservatively: `hmac` location (query string, then body), empty-string for missing HMAC fields, `NA` placeholders for missing billing name and phone, `special_reference` uniqueness, `merchant_commission` as fee (not used). A timed-out refund is never re-sent; it stays `unknown` until the transaction's `refunded_amount_cents` shows it, or fails for staff after 24 hours.

---

## 5. Exchange rates: recommendation

**Open Exchange Rates**, through the `ExchangeRateProvider` contract so it can be swapped.

Sources: `https://docs.openexchangerates.org/docs/latest-json`, `https://openexchangerates.org/faq/`.

| Item | Value |
|---|---|
| Endpoint | `GET https://openexchangerates.org/api/latest.json?app_id=&symbols=` |
| Auth | `app_id` query parameter. Never log the full URL. |
| Response | `timestamp`, `base`, `rates{CODE: number}` |
| Base | Default USD; `base=` is paid plans only, which is not needed while our base is USD |
| Update frequency | Hourly (about 1 to 2 minutes past the hour); every 10 minutes on Unlimited |
| Sandbox | None. Test with mocks. |

Why: USD base matches our base currency, simple contract, hourly updates suffice for retail pricing with markup.

**Status: adapter built (`App\Domains\Currency\Adapters\OpenExchangeRatesProvider`), tested with `Http::fake` only, awaiting credentials. It has never called the live API.** `app_id` is read from the encrypted setting `currency.oxr_app_id`, falling back to `OPEN_EXCHANGE_RATES_APP_ID`. Timeouts: connect 5 s, total 15 s. Rates are read from the raw body as decimal text, since `json_decode` would make them floats; provider errors carry no URL because the URL contains the `app_id`.

Caveats: OXR says the rates are not for precise forex trades and gives no accuracy warranty. Hence markup, spike guard and manual overrides. EGP, SAR and AED availability is `UNVERIFIED` until `currencies.json` is checked with a key. Plan request quotas are `UNVERIFIED`.

---

## 6. Google Analytics 4 Measurement Protocol

Sources: `https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference`, `/sending-events`, `/validating-events`, `/ga4/reference/events`.

| Item | Value |
|---|---|
| Endpoint | `POST https://www.google-analytics.com/mp/collect?api_secret=&measurement_id=` (EU: `region1.google-analytics.com`) |
| Validate | `/debug/mp/collect`, returns `validationMessages` |
| Body | `client_id` (must match the site's GA tag ID), `user_id`, `timestamp_micros` (backdate up to 72 hours), `consent`, `events[]{name,params}` |
| Limits | 25 events per request, 25 params per event, 130 kB, names up to 40 chars, values up to 100 chars |
| Behaviour | Returns 2xx even for malformed data; validate with the debug endpoint in non-production |

`purchase` requires `transaction_id`, `items`, `value`, `currency`; `transaction_id` is what deduplicates purchases. `refund` requires `transaction_id`, `currency`, `value`.

Browser-side events via gtag/GTM: `view_item_list`, `select_item`, `view_item`, `add_to_cart`, `remove_from_cart`, `begin_checkout`, `add_payment_info`. **`purchase` and `refund` are server-only** (Measurement Protocol) from verified order data, deduplicated by `analytics_event_dispatches`. `api_secret` stays server-side. No PII in events; `user_id` is a hash.

GTM: container ID setting; loaded only after consent where required.

---

## 7. Google Search Console API

Sources: `https://developers.google.com/webmaster-tools/v1/searchanalytics/query`, `/v1/how-tos/authorizing`.

`POST https://www.googleapis.com/webmasters/v3/sites/{siteUrl}/searchAnalytics/query` where `siteUrl` is URL-encoded, such as `sc-domain:example.com`.

Auth: OAuth 2.0 only, scope `https://www.googleapis.com/auth/webmasters.readonly`. Service account usage is not described on the authorization page and is `UNVERIFIED`. Plan: OAuth web-flow consent by an admin once; refresh token encrypted in settings.

Body: `startDate`, `endDate` (PT time zone), `dimensions` (`query`, `page`, `date`, ...), `type` (`web`), `rowLimit` (max 25,000), `startRow`, `dataState` (`final`).

Response: `rows[]{keys[],clicks,impressions,ctr,position}`. The API returns top rows and does not guarantee all data. Reporting screens must say so.

Stored daily in `search_console_metrics` for clicks, impressions, CTR, average position, top queries and top landing pages.

Verification: site verification meta tag from settings.
