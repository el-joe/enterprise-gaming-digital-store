# Database Design

Target: MySQL 8.0, InnoDB, `utf8mb4_0900_ai_ci`. This document is the implementation source for all migrations. Business rationale is in `PROJECT_ARCHITECTURE.md`.

## 0. Conventions

### Column shorthand

| Shorthand | MySQL type | Laravel |
|---|---|---|
| `fk:x` | `BIGINT UNSIGNED`, FK to `x.id`. `ON DELETE RESTRICT` unless suffixed: `cascade` or `null` | `foreignId()->constrained()` |
| `ulid` | `CHAR(26)` | `ulid()` |
| `money` | `DECIMAL(20,6)` | `decimal(20,6)` |
| `ccy` | `CHAR(3)`, FK to `currencies.code`, RESTRICT | `char(3)` + manual `foreign` |
| `rate` | `DECIMAL(24,12)` | `decimal(24,12)` |
| `pct` | `DECIMAL(7,4)` | `decimal(7,4)` |
| `country` | `CHAR(2)`, FK to `countries.code`, RESTRICT | |
| `enum` | `VARCHAR(32)`, PHP backed enum, never MySQL `ENUM` (ADR-005) | `string(32)` |
| `str(n)` | `VARCHAR(n)` | |
| `bool` | `TINYINT(1)` NOT NULL | `boolean()` |
| `enc` | `TEXT`, Laravel `encrypted` cast (AES via `APP_KEY`) unless noted | |
| `ts` | `TIMESTAMP` | `timestamp()` |
| `json` | `JSON` | `json()` |

### Implicit columns

Unless stated, every table has `id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY`, `created_at`, `updated_at` (`ts`, null). They are omitted from column lists.

- `[sd]`: plus `deleted_at` (soft delete).
- `[append]`: only `created_at`, never updated.
- Nullability is `NN` (not null) or `Y` (nullable). Defaults are in Notes.

### Rules

1. No FK to `users` may cascade from financial tables. Users are soft-deleted or anonymized.
2. Financial and audit tables are never hard-deleted by application code.
3. Status columns are written only by their state machine or service (`PROJECT_ARCHITECTURE.md`).
4. An index is listed with the query it serves. Do not add indexes beyond these without a query to justify them; FK columns get an index automatically from MySQL (noted only when part of a composite).
5. Large text bodies that are only displayed go in `TEXT`, not `JSON`.

---

## 1. Identity and customers

### users (extends skeleton table by new migration) [sd]

Existing columns stay: `id`, `name`, `email` (unique), `email_verified_at`, `password`, `remember_token`, timestamps.

| Added column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| type | enum | NN | `customer`, `staff`; default `customer` |
| status | enum | NN | `active`, `suspended`, `closed`; default `active` |
| phone | str(32) | Y | |
| locale | str(8) | Y | |
| display_currency | ccy | Y | |
| country_code | country | Y | added after `countries` migration |
| two_factor_secret | enc | Y | staff TOTP |
| two_factor_confirmed_at | ts | Y | |
| two_factor_recovery_codes | enc | Y | |
| last_login_at | ts | Y | |
| last_login_ip | str(45) | Y | |

Indexes: `idx_users_type_status` (type, status): admin customer list filter. `idx_users_created` (created_at): dashboard new-customer counts.

### Spatie permission tables

`permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`: exactly as published by `spatie/laravel-permission`. Teams disabled. Permission seed in `SECURITY.md` section 4.

### personal_access_tokens

Standard Sanctum table. `tokenable` is a `ServiceAccount` (or staff user for testing). Abilities store scopes.

### service_accounts [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| name | str(120) | NN | |
| kind | enum | NN | `mcp`, `n8n` |
| is_read_only | bool | NN | default true |
| allowed_ips | json | Y | CIDR list; null means any |
| rate_limit_per_minute | int unsigned | NN | default 60 |
| hmac_secret | enc | Y | n8n request signing |
| last_used_at | ts | Y | |
| disabled_at | ts | Y | |

Unique: `uq_service_accounts_name` (name).

### customer_profiles

| Column | Type | Null | Notes |
|---|---|---|---|
| user_id | fk:users cascade | NN | unique |
| risk_level | enum | NN | `low`, `medium`, `high`, `blocked`; default `low` |
| risk_notes | text | Y | |
| orders_count | int unsigned | NN | default 0, denormalized |
| lifetime_value_base | money | NN | default 0 |
| first_order_at | ts | Y | |
| last_order_at | ts | Y | |

Indexes: `idx_cp_risk` (risk_level): risk filter. `idx_cp_last_order` (last_order_at): reactivation cohort.

### customer_notes

| Column | Type | Null | Notes |
|---|---|---|---|
| user_id | fk:users cascade | NN | |
| author_id | fk:users | NN | staff |
| body | text | NN | |

### customer_consents [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| user_id | fk:users null | Y | |
| email | str(190) | Y | guest or newsletter |
| channel | enum | NN | `email_marketing`, `sms_marketing`, `analytics` |
| granted | bool | NN | |
| source | str(64) | NN | page or flow |
| ip | str(45) | Y | |

Index: `idx_cc_user_channel` (user_id, channel, created_at): latest consent per channel, used before any marketing send. `idx_cc_email_channel` (email, channel, created_at): same for guests.

### notification_preferences

| Column | Type | Null | Notes |
|---|---|---|---|
| user_id | fk:users cascade | NN | |
| category | enum | NN | `orders`, `support`, `marketing`, `security` |
| channel | enum | NN | `mail`, `database` |
| enabled | bool | NN | default true |

Unique: `uq_np` (user_id, category, channel).

### otp_challenges

| Column | Type | Null | Notes |
|---|---|---|---|
| purpose | enum | NN | `guest_order_access`, `email_verification`, `sensitive_action` |
| identifier_hash | char(64) | NN | HMAC of email |
| code_hash | char(64) | NN | |
| attempts | tinyint unsigned | NN | default 0 |
| expires_at | ts | NN | |
| consumed_at | ts | Y | |

Index: `idx_otp_lookup` (purpose, identifier_hash, expires_at): verification lookup.

### `notifications`, `sessions`, `password_reset_tokens`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

Laravel standard. Only `notifications` needs a new migration.

---

## 2. Reference

### countries

Primary key is `code`; no surrogate id.

| Column | Type | Null | Notes |
|---|---|---|---|
| code | char(2) | NN | PK, ISO 3166-1 alpha-2 |
| name | str(120) | NN | |
| dial_code | str(8) | Y | |
| default_currency | char(3) | Y | no FK to break the load cycle; validated by seeder |
| is_active | bool | NN | default true |

### currencies

Primary key is `code`.

| Column | Type | Null | Notes |
|---|---|---|---|
| code | char(3) | NN | PK, ISO 4217 |
| name | str(80) | NN | |
| symbol | str(8) | NN | |
| exponent | tinyint unsigned | NN | ISO minor digits |
| rounding_rule | json | Y | e.g. `{"mode":"step","step":"0.05"}` or `{"mode":"ends_with","value":"0.99"}` |
| markup_pct | pct | NN | default 0; applied to provider rate |
| is_active | bool | NN | selectable for display |
| sort_order | smallint | NN | default 0 |

Index: `idx_cur_active` (is_active, sort_order): currency switcher.

### regions

Commercial grouping (US, UK, EU, MENA, Global).

| Column | Type | Null | Notes |
|---|---|---|---|
| code | str(16) | NN | unique |
| name | str(80) | NN | |
| is_global | bool | NN | default false |

### region_country

No id, no timestamps. PK (region_id, country_code). `region_id` fk:regions cascade; `country_code` country.

---

## 3. Catalog

### gaming_platforms [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| name | str(120) | NN | |
| slug | str(140) | NN | unique |
| description | text | Y | |
| logo_media_id | fk:media null | Y | FK added after media |
| banner_media_id | fk:media null | Y | |
| popularity_score | int unsigned | NN | default 0 |
| is_active | bool | NN | |
| is_featured | bool | NN | default false |
| sort_order | int | NN | default 0 |

Indexes: `idx_gp_active_featured` (is_active, is_featured, sort_order): homepage and `/gaming` listing.

### categories [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| parent_id | fk:categories null | Y | |
| name | str(120) | NN | |
| slug | str(140) | NN | unique. Reserved-word validation against top-level routes. |
| description | text | Y | |
| banner_media_id | fk:media null | Y | |
| is_active | bool | NN | |
| sort_order | int | NN | default 0 |

Index: `idx_cat_parent` (parent_id, is_active, sort_order): tree rendering.

### products [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| gaming_platform_id | fk:gaming_platforms | NN | |
| primary_category_id | fk:categories | NN | |
| name | str(190) | NN | |
| slug | str(210) | NN | unique |
| sku | str(64) | NN | unique |
| short_description | str(500) | Y | |
| description | mediumtext | Y | sanitized HTML |
| instructions | text | Y | |
| terms | text | Y | |
| delivery_type | enum | NN | `instant_code`, `direct_topup`, `account_recharge`, `api_delivery`, `manual`, `subscription_activation` |
| delivery_time_minutes | int unsigned | Y | |
| max_quantity_per_order | tinyint unsigned | NN | default 10 |
| status | enum | NN | `draft`, `active`, `inactive`; default `draft` |
| is_featured | bool | NN | default false |
| is_trending | bool | NN | default false |
| rating_avg | decimal(3,2) | NN | default 0 |
| rating_count | int unsigned | NN | default 0 |
| sales_count | int unsigned | NN | default 0 |
| published_at | ts | Y | |

Indexes:
- `idx_p_listing` (status, gaming_platform_id, primary_category_id, sales_count): category and platform listing sorted by popularity.
- `idx_p_featured` (status, is_featured, sales_count): homepage sections.
- `idx_p_recent` (status, published_at): recently added.

### category_product

No id, no timestamps. PK (category_id, product_id). Both fk cascade. Secondary index (product_id, category_id): reverse lookup.

### product_variants [sd]

A purchasable SKU: denomination plus region.

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| product_id | fk:products cascade | NN | |
| region_id | fk:regions | Y | null means not region-specific |
| sku | str(80) | NN | unique |
| title | str(190) | NN | |
| denomination_value | money | Y | face value |
| denomination_currency | ccy | Y | |
| is_custom_amount | bool | NN | default false; range products |
| custom_min | money | Y | |
| custom_max | money | Y | |
| manual_cost_base | money | Y | used if no supplier cost |
| price_base | money | Y | computed listing price |
| compare_at_base | money | Y | original price |
| availability | enum | NN | `in_stock`, `limited`, `out_of_stock`; default `out_of_stock` |
| pricing_dirty_at | ts | Y | set when a recalculation is needed |
| is_active | bool | NN | |
| sort_order | int | NN | default 0 |

Indexes:
- `idx_pv_product` (product_id, is_active, sort_order): product page variants.
- `idx_pv_price` (is_active, availability, price_base): price filter and sort.
- `idx_pv_dirty` (pricing_dirty_at): `pricing:recalculate-dirty`.
- `idx_pv_region` (region_id, is_active): region filter.

Unique: `uq_pv_product_region_denomination` (product_id, region_id, denomination_value, denomination_currency). Note that a NULL region allows repeats, so `ProductVariantService` also validates.

### product_regions

Spec entity: products sold into which regions, for filtering and platform pages. PK (product_id, region_id), no id. Both fk cascade. Secondary (region_id, product_id).

### product_country_restrictions

| Column | Type | Null | Notes |
|---|---|---|---|
| product_id | fk:products cascade | NN | |
| country_code | country | NN | |
| mode | enum | NN | `allow`, `deny` |

Unique: `uq_pcr` (product_id, country_code).

### product_images

| Column | Type | Null | Notes |
|---|---|---|---|
| product_id | fk:products cascade | NN | |
| variant_id | fk:product_variants cascade | Y | |
| media_id | fk:media | NN | |
| alt | str(190) | Y | |
| sort_order | int | NN | default 0 |

Index: `idx_pi_product` (product_id, sort_order).

### attribute_definitions

| Column | Type | Null | Notes |
|---|---|---|---|
| code | str(64) | NN | unique |
| name | str(120) | NN | |
| type | enum | NN | `text`, `number`, `boolean`, `select` |
| options | json | Y | |
| is_filterable | bool | NN | default false |

### product_attribute_values

| Column | Type | Null | Notes |
|---|---|---|---|
| product_id | fk:products cascade | NN | |
| attribute_definition_id | fk:attribute_definitions cascade | NN | |
| value | str(255) | NN | |

Unique: `uq_pav` (product_id, attribute_definition_id). Index: `idx_pav_filter` (attribute_definition_id, value, product_id): facet filter.

### delivery_field_definitions

Reusable field library (Player ID, Server ID, Region, Username, Account identifier).

| Column | Type | Null | Notes |
|---|---|---|---|
| code | str(64) | NN | unique |
| label | str(120) | NN | |
| input_type | enum | NN | `text`, `number`, `select`, `email` |
| validation_regex | str(255) | Y | |
| options | json | Y | |
| help_text | str(255) | Y | |

### product_delivery_fields

| Column | Type | Null | Notes |
|---|---|---|---|
| product_id | fk:products cascade | NN | |
| delivery_field_definition_id | fk:delivery_field_definitions | NN | |
| is_required | bool | NN | default true |
| sort_order | int | NN | default 0 |

Unique: `uq_pdf` (product_id, delivery_field_definition_id).

### product_relations

| Column | Type | Null | Notes |
|---|---|---|---|
| product_id | fk:products cascade | NN | |
| related_product_id | fk:products cascade | NN | |
| type | enum | NN | `related`, `cross_sell`, `upsell` |
| sort_order | int | NN | default 0 |

Unique: `uq_pr` (product_id, related_product_id, type).

### wishlist_items

| Column | Type | Null | Notes |
|---|---|---|---|
| user_id | fk:users cascade | NN | |
| product_id | fk:products cascade | NN | |

Unique: `uq_wi` (user_id, product_id).

### content_translations

| Column | Type | Null | Notes |
|---|---|---|---|
| translatable_type | str(64) | NN | morph map alias |
| translatable_id | bigint unsigned | NN | |
| locale | str(8) | NN | |
| field | str(64) | NN | |
| value | mediumtext | NN | |

Unique: `uq_ct` (translatable_type, translatable_id, locale, field): eager-load and upsert.

### search_documents

Denormalized search index, one row per entity per locale.

| Column | Type | Null | Notes |
|---|---|---|---|
| searchable_type | str(64) | NN | |
| searchable_id | bigint unsigned | NN | |
| locale | str(8) | NN | |
| title | str(255) | NN | |
| keywords | text | NN | game, platform, SKU, aliases |
| boost | int | NN | default 0 |
| is_active | bool | NN | |

Unique: `uq_sd` (searchable_type, searchable_id, locale). FULLTEXT: `ft_sd` (title, keywords): instant search. Index: `idx_sd_title` (locale, is_active, title): prefix suggestions.

### search_queries

| Column | Type | Null | Notes |
|---|---|---|---|
| term | str(190) | NN | normalized |
| locale | str(8) | NN | |
| hits | int unsigned | NN | default 1 |
| results_count | int unsigned | NN | default 0 |
| last_searched_at | ts | NN | |

Unique: `uq_sq` (term, locale). Index: `idx_sq_popular` (locale, hits): popular searches. Search history for logged-in users is kept client-side per spec "where appropriate".

---

## 4. Suppliers

### suppliers [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| name | str(120) | NN | |
| adapter_key | str(64) | NN | registry key |
| status | enum | NN | `active`, `inactive`; default `inactive` |
| mode | enum | NN | `sandbox`, `production`; default `sandbox` |
| base_url_override | str(255) | Y | |
| priority | smallint unsigned | NN | default 100 |
| fallback_enabled | bool | NN | default false |
| fallback_supplier_id | fk:suppliers null | Y | admin preference; selection still ranks mappings |
| max_fallback_attempts | tinyint unsigned | NN | default 2 |
| connect_timeout_ms | int unsigned | NN | default 5000 |
| request_timeout_ms | int unsigned | NN | default 30000 |
| retry_policy | json | Y | pre-send failures only |
| verification_deadline_minutes | int unsigned | NN | default 120 |
| per_request_cap_base | money | Y | e.g. Reloadly default cap |
| low_balance_threshold | money | Y | |
| balance_currency | ccy | Y | |
| last_balance | money | Y | cached |
| last_balance_at | ts | Y | |
| health | enum | NN | `healthy`, `degraded`, `down`, `unknown`; default `unknown` |
| circuit_open_until | ts | Y | |

Unique: `uq_suppliers_name` (name). Index: `idx_sup_status` (status, priority): selection.

### supplier_credentials

| Column | Type | Null | Notes |
|---|---|---|---|
| supplier_id | fk:suppliers cascade | NN | |
| mode | enum | NN | |
| credentials | enc | NN | JSON encrypted; never serialized to any Resource |
| webhook_secret | enc | Y | |
| rotated_at | ts | Y | |

Unique: `uq_sc` (supplier_id, mode).

### supplier_products

Supplier catalog as synced, before mapping.

| Column | Type | Null | Notes |
|---|---|---|---|
| supplier_id | fk:suppliers cascade | NN | |
| external_id | str(128) | NN | |
| name | str(255) | NN | |
| brand_name | str(190) | Y | |
| country_code | str(2) | Y | raw, no FK (suppliers can send unknown codes) |
| category_name | str(190) | Y | |
| denomination_type | enum | Y | `fixed`, `range` |
| currency | str(3) | Y | recipient currency, raw |
| fixed_denominations | json | Y | |
| min_denomination | money | Y | |
| max_denomination | money | Y | |
| sender_currency | str(3) | Y | |
| cost_map | json | Y | recipient to sender amounts; for `range` products `{"rate": recipient-to-sender rate}` (M8) |
| discount_pct | pct | Y | |
| fee_fixed | money | Y | |
| fee_pct | pct | Y | |
| supports_preorder | bool | NN | default false |
| requires_user_id | bool | NN | default false |
| availability | enum | NN | `available`, `unavailable`, `unknown`; default `unknown` |
| raw | json | Y | last synced payload |
| last_seen_at | ts | Y | |
| removed_at | ts | Y | no longer in supplier feed |

Unique: `uq_spd` (supplier_id, external_id): sync upsert. Indexes: `idx_spd_name` (supplier_id, brand_name): mapping search. `idx_spd_removed` (supplier_id, removed_at): "needs attention".

### supplier_product_prices [append]

History of supplier cost for cost analysis. Since M8 this holds the **effective** cost (sender amount plus fees) written by catalog and pricing sync, and it is the only synced cost the pricing engine and supplier selection read. A row is appended when the cost changes, or when the latest is older than half of `suppliers.max_price_age_minutes`.

| Column | Type | Null | Notes |
|---|---|---|---|
| supplier_product_id | fk:supplier_products cascade | NN | |
| denomination | money | NN | |
| cost | money | NN | |
| cost_currency | str(3) | NN | |
| captured_at | ts | NN | |

Index: `idx_spp` (supplier_product_id, denomination, captured_at).

### supplier_product_mappings

| Column | Type | Null | Notes |
|---|---|---|---|
| product_variant_id | fk:product_variants cascade | NN | |
| supplier_id | fk:suppliers | NN | |
| supplier_product_id | fk:supplier_products | NN | |
| role | enum | NN | `primary`, `secondary`, `other` |
| priority | smallint unsigned | NN | default 100 |
| supplier_unit_price | money | NN | value sent as Reloadly `unitPrice` |
| supplier_unit_currency | str(3) | NN | |
| override_cost | money | Y | supplier-specific pricing |
| override_cost_currency | ccy | Y | |
| allowed_countries | json | Y | regional restriction |
| denied_countries | json | Y | |
| is_active | bool | NN | default true |
| availability | enum | NN | default `unknown` |
| last_verified_at | ts | Y | |

Unique: `uq_spm` (product_variant_id, supplier_id). Index: `idx_spm_select` (product_variant_id, is_active, priority): supplier selection. `idx_spm_supplier` (supplier_id, is_active): supplier product list. Only one `primary` per variant is enforced in the mapping service.

### supplier_transactions

Each row is one attempt to purchase one unit. See exactly-once gate.

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| order_item_unit_id | fk:order_item_units | NN | RESTRICT required, see note |
| supplier_id | fk:suppliers | NN | |
| supplier_product_mapping_id | fk:supplier_product_mappings null | Y | |
| attempt_no | tinyint unsigned | NN | |
| client_reference | str(100) | NN | stable per unit+supplier: `u{unit.public_id}s{supplier_id}` |
| external_transaction_id | str(128) | Y | |
| status | enum | NN | `submitting`, `accepted`, `pending_verification`, `succeeded`, `rejected`, `not_accepted_verified` |
| outcome_reason | str(128) | Y | classified reason code |
| supplier_error_code | str(64) | Y | |
| is_fallback | bool | NN | default false |
| code_retrieval_status | enum | NN | `not_applicable`, `pending`, `retrieved`, `failed`; default `not_applicable` |
| requested_amount | money | NN | |
| requested_currency | str(3) | NN | |
| actual_cost | money | Y | supplier-reported |
| actual_cost_currency | str(3) | Y | |
| cost_reversal_pending | bool | NN | default false; supplier status FAILED |
| submitted_at | ts | Y | |
| completed_at | ts | Y | |
| next_verification_at | ts | Y | |
| verification_attempts | smallint unsigned | NN | default 0 |
| verification_deadline_at | ts | Y | |
| resolved_by | fk:users null | Y | admin, for `not_accepted_verified` |
| resolution_note | text | Y | mandatory when `resolved_by` set |
| active_unit_key | bigint unsigned | Y | **generated**, see below |

Generated column:

```sql
active_unit_key BIGINT UNSIGNED GENERATED ALWAYS AS (
  CASE WHEN status IN ('submitting','accepted','pending_verification','succeeded')
       THEN order_item_unit_id END
) STORED
```

MySQL forbids `CASCADE` or `SET NULL` on the FK base column of a stored generated column, so `order_item_unit_id` must be RESTRICT.

Unique:
- `uq_st_active_unit` (active_unit_key): **at most one active attempt per unit**.
- `uq_st_reference` (supplier_id, client_reference, attempt_no): one row per attempt. The same reference may be reused only after `not_accepted_verified`.
- `uq_st_external` (supplier_id, external_transaction_id): duplicate detection on supplier ID (NULLs permitted).

Indexes:
- `idx_st_verify` (status, next_verification_at): `suppliers:verify-pending`.
- `idx_st_stale` (status, submitted_at): `suppliers:sweep-stale-submissions`.
- `idx_st_unit` (order_item_unit_id, attempt_no): unit history.
- `idx_st_supplier_time` (supplier_id, submitted_at): supplier performance reports.

### supplier_api_logs [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| supplier_id | fk:suppliers cascade | NN | |
| supplier_transaction_id | fk:supplier_transactions null | Y | |
| operation | str(64) | NN | |
| method | str(8) | NN | |
| url | str(500) | NN | no secrets in query |
| http_status | smallint unsigned | Y | |
| duration_ms | int unsigned | Y | |
| succeeded | bool | NN | |
| error_class | str(120) | Y | |
| request_redacted | json | Y | |
| response_redacted | json | Y | **never set for deliverable endpoints** |

Indexes: `idx_sal_supplier` (supplier_id, created_at): admin logs. `idx_sal_failed` (supplier_id, succeeded, created_at): failed-request view and health. Retention in `SECURITY.md`.

### supplier_sync_runs

Spec "Supplier Sync Logs".

| Column | Type | Null | Notes |
|---|---|---|---|
| supplier_id | fk:suppliers cascade | NN | |
| type | enum | NN | `catalog`, `pricing`, `balance` |
| triggered_by | str(64) | NN | `scheduler`, `n8n`, `admin:{id}`, `mcp:{id}` |
| status | enum | NN | `running`, `completed`, `failed` |
| items_seen | int unsigned | NN | default 0 |
| items_created | int unsigned | NN | default 0 |
| items_updated | int unsigned | NN | default 0 |
| items_failed | int unsigned | NN | default 0 |
| error | text | Y | |
| started_at | ts | NN | |
| finished_at | ts | Y | |
| window_key | str(64) | Y | duplicate-run protection |

Unique: `uq_ssr_window` (supplier_id, type, window_key). Index: `idx_ssr` (supplier_id, type, started_at): sync history and last-sync status.

### supplier_balance_snapshots [append]

Columns: supplier_id fk cascade NN, balance money NN, currency str(3) NN, captured_at ts NN. Index: `idx_sbs` (supplier_id, captured_at): balance chart.

### supplier_health_checks [append]

Columns: supplier_id fk cascade NN, reachable bool NN, latency_ms int unsigned Y, error str(255) Y. Index: `idx_shc` (supplier_id, created_at): health charts, F07 workflow.

### supplier_webhooks

| Column | Type | Null | Notes |
|---|---|---|---|
| supplier_id | fk:suppliers | NN | |
| provider_event_id | str(128) | NN | or computed hash |
| signature_valid | bool | Y | null when mechanism unverified |
| payload | enc | NN | |
| status | enum | NN | `received`, `processed`, `ignored`, `failed` |
| processed_at | ts | Y | |

Unique: `uq_sw` (supplier_id, provider_event_id).

---

## 5. Currency and pricing

### exchange_rates

Current rate per quote currency; base is `settings.currency.base`.

| Column | Type | Null | Notes |
|---|---|---|---|
| base_currency | ccy | NN | |
| quote_currency | ccy | NN | |
| provider_rate | rate | Y | |
| manual_rate | rate | Y | |
| is_manual_active | bool | NN | default false |
| manual_expires_at | ts | Y | |
| effective_rate | rate | NN | provider rate with markup, or manual |
| source | str(64) | NN | |
| fetched_at | ts | NN | |
| pending_rate | rate | Y | held by spike guard |
| pending_since | ts | Y | provider timestamp of the held rate |

Unique: `uq_er` (base_currency, quote_currency).

### exchange_rate_history [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| base_currency | str(3) | NN | |
| quote_currency | str(3) | NN | |
| provider_rate | rate | Y | |
| effective_rate | rate | NN | |
| source | str(64) | NN | |
| is_manual | bool | NN | |
| changed_by | fk:users null | Y | |
| captured_at | ts | NN | |

Index: `idx_erh` (quote_currency, captured_at): charts, audit. Referenced by orders.

### pricing_rules [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| name | str(120) | NN | |
| scope | enum | NN | `global`, `category`, `platform`, `product`, `variant` |
| scope_id | bigint unsigned | Y | polymorphic by scope; null for global |
| kind | enum | NN | `markup`, `dynamic` |
| country_code | country | Y | |
| currency | ccy | Y | |
| fixed_markup | money | Y | in base |
| percent_markup | pct | Y | |
| min_margin_pct | pct | Y | |
| min_margin_fixed | money | Y | |
| below_margin_policy | enum | Y | `floor`, `block`, `allow`; inherits when null |
| conditions | json | Y | dynamic rules |
| priority | int | NN | default 100, lower wins |
| starts_at | ts | Y | |
| ends_at | ts | Y | |
| is_active | bool | NN | default true |

Index: `idx_prl_scope` (is_active, scope, scope_id, country_code, currency, priority): precedence resolution.

### variant_prices

Explicit prices that bypass markup and conversion.

| Column | Type | Null | Notes |
|---|---|---|---|
| product_variant_id | fk:product_variants cascade | NN | |
| country_code | str(2) | NN | `*` for any; FK not used for this reason |
| currency | ccy | NN | |
| price | money | NN | |
| compare_at | money | Y | |
| starts_at | ts | Y | |
| ends_at | ts | Y | |

Unique: `uq_vp` (product_variant_id, country_code, currency, starts_at). Index: `idx_vp_lookup` (product_variant_id, currency, country_code): checkout lookup.

### pricing_overrides_log [append]

Columns: actor_id fk:users NN, product_variant_id fk NN, previous_price_base money Y, new_price_base money NN, floor_price_base money Y, reason text NN.
Index: (product_variant_id, created_at).

---

## 6. Orders

### carts

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| user_id | fk:users null | Y | |
| session_token | char(64) | Y | guest cookie (hash) |
| email | str(190) | Y | for abandoned-cart with consent only |
| display_currency | ccy | NN | |
| country_code | country | Y | |
| coupon_id | fk:coupons null | Y | |
| status | enum | NN | `active`, `converted`, `abandoned`; default `active` |
| abandoned_at | ts | Y | |
| reminder_sent_at | ts | Y | |
| last_activity_at | ts | NN | |

Indexes: `idx_c_user` (user_id, status): load cart. `idx_c_session` (session_token, status): guest cart. `idx_c_abandon` (status, last_activity_at): abandoned detection.

### cart_items

| Column | Type | Null | Notes |
|---|---|---|---|
| cart_id | fk:carts cascade | NN | |
| product_variant_id | fk:product_variants cascade | NN | |
| quantity | tinyint unsigned | NN | |
| custom_amount | money | Y | |
| delivery_values | enc | Y | JSON |

Unique: `uq_ci` (cart_id, product_variant_id, custom_amount).

### idempotency_keys

| Column | Type | Null | Notes |
|---|---|---|---|
| scope | str(64) | NN | e.g. `checkout.place_order` |
| key | str(128) | NN | |
| owner_hash | char(64) | NN | user or session |
| request_hash | char(64) | NN | |
| response_status | smallint unsigned | Y | |
| response_body | json | Y | no sensitive fields |
| locked_at | ts | Y | |
| expires_at | ts | NN | |

Unique: `uq_ik` (scope, key, owner_hash). Index: `idx_ik_expiry` (expires_at): prune.

Behaviour (M7, `IdempotencyStore`): the claim row is committed on its own before the work starts, with `locked_at` as a fencing token; `response_status` and `response_body` are written inside the work's transaction, so they exist exactly when the work committed. A claim with no response older than 120 s is treated as abandoned and taken over by compare-and-set on `locked_at`. A failure that changed nothing deletes the claim. `owner_hash` is SHA-256 of `user:{id}` or `guest:{sha256(cart token)}`. Rows are kept 24 hours; `orders:prune-idempotency-keys` deletes them daily. MySQL's JSON type does not keep key order, so a replay returns the same content, not the same bytes.

### orders

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique, used in URLs |
| order_number | str(24) | NN | unique, displayed |
| user_id | fk:users null | Y | null for guest |
| guest_email | str(190) | Y | |
| guest_email_verified_at | ts | Y | |
| contact_email | str(190) | NN | |
| contact_phone | str(32) | Y | |
| customer_name | str(120) | Y | |
| status | enum | NN | section 6 of architecture |
| status_changed_at | ts | NN | |
| country_code | country | NN | declared |
| ip_country | str(2) | Y | |
| ip_address | str(45) | Y | |
| user_agent | str(255) | Y | |
| locale | str(8) | NN | |
| ga_client_id | str(64) | Y | for GA4 purchase event |
| base_currency | ccy | NN | |
| subtotal_base | money | NN | |
| discount_base | money | NN | default 0 |
| tax_base | money | NN | default 0, ADR-022 |
| total_base | money | NN | |
| display_currency | ccy | NN | |
| display_rate | rate | NN | |
| display_total | money | NN | |
| payment_currency | ccy | Y | set when a gateway is chosen |
| payment_rate | rate | Y | |
| payment_total | money | Y | **final charged amount**, frozen at paid |
| payment_gateway_id | fk:payment_gateways | Y | |
| exchange_rate_history_id | fk:exchange_rate_history null | Y | |
| rate_source | str(64) | Y | |
| rate_fetched_at | ts | Y | |
| coupon_id | fk:coupons null | Y | |
| coupon_code | str(64) | Y | snapshot |
| affiliate_referral_id | fk:affiliate_referrals null | Y | FK added later |
| primary_payment_id | fk:payments null | Y | **unique**, FK added later |
| risk_score | smallint | Y | |
| has_open_dispute | bool | NN | default false |
| paid_at | ts | Y | |
| completed_at | ts | Y | |
| expires_at | ts | Y | |
| placement_hash | char(64) | Y | duplicate placement detection |

Unique: `uq_o_public` (public_id), `uq_o_number` (order_number), `uq_o_primary_payment` (primary_payment_id), `uq_o_placement` (placement_hash).

Indexes:
- `idx_o_user` (user_id, created_at): customer order history.
- `idx_o_guest` (guest_email, created_at): guest lookup, `search_customer_orders`.
- `idx_o_status` (status, status_changed_at): operational queues, failed and pending monitors.
- `idx_o_expire` (status, expires_at): `orders:expire-unpaid`.
- `idx_o_paid` (paid_at, payment_gateway_id): revenue reports.
- `idx_o_country` (country_code, paid_at): profit by country.
- `idx_o_payment_currency` (payment_currency, paid_at): profit by currency.
- `idx_o_ip` (ip_address, created_at): risk engine IP velocity (added in M7).

`placement_hash` = HMAC-SHA256 (app key) of the buyer (`user:{id}`, or the normalized guest email), the sorted cart lines (variant, quantity, custom amount, hash of delivery values) and the coupon code, with a 10-minute time bucket. Placement looks up the current and the previous bucket, so a double submit across a bucket edge is caught; the unique index stops two carts racing in the same bucket. Cleared when the order enters a closed status (`completed`, `partially_refunded`, `refunded`, `cancelled`, `failed`), so the same contents can be bought again.

### order_items

| Column | Type | Null | Notes |
|---|---|---|---|
| order_id | fk:orders | NN | |
| product_id | fk:products | NN | |
| product_variant_id | fk:product_variants | NN | |
| name_snapshot | str(255) | NN | |
| sku_snapshot | str(80) | NN | |
| delivery_type | enum | NN | snapshot |
| quantity | tinyint unsigned | NN | |
| unit_price_base | money | NN | original base price |
| compare_at_base | money | Y | |
| discount_base | money | NN | default 0 |
| line_total_base | money | NN | |
| estimated_unit_cost_base | money | Y | |
| custom_amount | money | Y | |
| pricing_breakdown | json | NN | engine breakdown (rule IDs, markup, promotion, cost, floor, rate, history id), display amounts, coupon reference, and `units`: one entry per unit with `unit_public_id`, `gross_base`, `discount_base`, `net_base`, `net_display` (ADR-025) |

Indexes: `idx_oi_order` (order_id). `idx_oi_product` (product_id, created_at): best sellers. `idx_oi_variant` (product_variant_id).

### order_item_delivery_values

| Column | Type | Null | Notes |
|---|---|---|---|
| order_item_id | fk:order_items cascade | NN | |
| field_code | str(64) | NN | |
| field_label | str(120) | NN | snapshot |
| value | enc | NN | |

Unique: `uq_oidv` (order_item_id, field_code).

### order_item_units

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| order_id | fk:orders | NN | |
| order_item_id | fk:order_items | NN | |
| status | enum | NN | section 6.3 of architecture |
| selection_snapshot | json | Y | ranked candidates |
| delivered_at | ts | Y | |
| first_revealed_at | ts | Y | gates refund eligibility |
| refunded_at | ts | Y | |

Indexes: `idx_oiu_order` (order_id, status): aggregate status. `idx_oiu_status` (status, updated_at): stuck-unit monitoring.

### order_events [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| order_id | fk:orders | NN | |
| order_item_unit_id | fk:order_item_units null | Y | |
| type | str(64) | NN | |
| from_status | enum | Y | |
| to_status | enum | Y | |
| actor_type | enum | NN | `system`, `customer`, `staff`, `webhook`, `n8n`, `mcp` |
| actor_id | bigint unsigned | Y | |
| message | str(500) | Y | |
| metadata | json | Y | never codes or PII beyond IDs |

Index: `idx_oe_order` (order_id, id): timeline.

### order_notes

Columns: order_id fk cascade NN, author_id fk:users NN, body text NN.

### manual_fulfillment_tasks

| Column | Type | Null | Notes |
|---|---|---|---|
| order_item_unit_id | fk:order_item_units | NN | unique |
| status | enum | NN | `open`, `claimed`, `done`, `rejected` |
| assigned_to | fk:users null | Y | |
| claimed_at | ts | Y | |
| completed_at | ts | Y | |
| completion_note | text | Y | |

Index: `idx_mft` (status, created_at): queue.

### digital_codes

Both delivered codes and internal stock.

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| source | enum | NN | `supplier`, `stock` |
| order_item_unit_id | fk:order_item_units null | Y | unique once assigned |
| supplier_transaction_id | fk:supplier_transactions null | Y | |
| product_variant_id | fk:product_variants | Y | for stock |
| stock_status | enum | Y | `available`, `reserved`, `delivered`, `void`; stock only |
| code_ciphertext | mediumtext | NN | AES-256-GCM via code keyring |
| pin_ciphertext | text | Y | |
| redemption_url_ciphertext | text | Y | |
| key_version | smallint unsigned | NN | |
| code_hash | char(64) | NN | HMAC blind index |
| masked_code | str(32) | NN | last 4 chars |
| expires_at | ts | Y | |
| imported_by | fk:users null | Y | |
| import_batch | str(64) | Y | |

Unique: `uq_dc_hash` (code_hash): duplicate code detection. `uq_dc_unit` (order_item_unit_id).

Indexes: `idx_dc_stock` (product_variant_id, stock_status, id): `SELECT ... FOR UPDATE SKIP LOCKED` allocation. `idx_dc_key` (key_version): rotation.

### digital_code_accesses [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| digital_code_id | fk:digital_codes | NN | |
| actor_type | enum | NN | `customer`, `guest`, `staff` |
| actor_id | bigint unsigned | Y | |
| purpose | enum | NN | `customer_reveal`, `support_verification`, `manual_delivery` |
| reason | str(500) | Y | mandatory for staff |
| granted | bool | NN | |
| denial_reason | str(64) | Y | |
| ip | str(45) | Y | |
| user_agent | str(255) | Y | |

Index: `idx_dca_code` (digital_code_id, created_at). `idx_dca_actor` (actor_type, actor_id, created_at): staff access review.

### risk_assessments

| Column | Type | Null | Notes |
|---|---|---|---|
| order_id | fk:orders cascade | NN | unique |
| score | smallint | NN | |
| decision | enum | NN | `pass`, `review`, `block` |
| signals | json | NN | |
| reviewed_by | fk:users null | Y | |
| reviewed_at | ts | Y | |
| review_note | text | Y | |

Index: `idx_ra_decision` (decision, created_at): fraud review queue.

### order_item_financials

Written at completion and corrected on refund.

| Column | Type | Null | Notes |
|---|---|---|---|
| order_item_id | fk:order_items | NN | unique |
| order_id | fk:orders | NN | |
| product_id | fk:products | NN | |
| supplier_id | fk:suppliers null | Y | |
| payment_gateway_id | fk:payment_gateways null | Y | |
| country_code | str(2) | NN | |
| currency | str(3) | NN | payment currency |
| revenue_base | money | NN | |
| supplier_cost_base | money | NN | |
| gateway_fee_base | money | NN | |
| gateway_fee_is_estimate | bool | NN | |
| fx_cost_base | money | NN | |
| other_cost_base | money | NN | |
| refunded_base | money | NN | default 0 |
| gross_profit_base | money | NN | revenue minus supplier cost |
| net_profit_base | money | NN | |
| recognized_at | ts | NN | |

Indexes: `idx_oif_recognized` (recognized_at). `idx_oif_product` (product_id, recognized_at): product profitability, loss-makers. `idx_oif_supplier` (supplier_id, recognized_at): margin by supplier. `idx_oif_gateway` (payment_gateway_id, recognized_at): profit by method. `idx_oif_country` (country_code, recognized_at). `idx_oif_currency` (currency, recognized_at).

---

## 7. Payments

### payment_gateways

| Column | Type | Null | Notes |
|---|---|---|---|
| key | str(32) | NN | unique: `stripe`, `paypal`, `paymob`, `wallet` |
| name | str(80) | NN | |
| adapter_key | str(64) | NN | |
| is_active | bool | NN | default false |
| mode | enum | NN | `sandbox`, `production` |
| default_currency | ccy | NN | |
| settings | json | Y | non-secret |
| sort_order | int | NN | default 0 |

### payment_gateway_credentials

Columns: payment_gateway_id fk cascade NN, mode enum NN, credentials enc NN, webhook_secret enc Y, rotated_at ts Y. Unique: `uq_pgc` (payment_gateway_id, mode).

### payment_gateway_currencies

Merchant-account capability; admin-maintained.

| Column | Type | Null | Notes |
|---|---|---|---|
| payment_gateway_id | fk:payment_gateways cascade | NN | |
| currency | ccy | NN | |
| minor_exponent | tinyint unsigned | NN | per gateway rule |
| min_amount | money | Y | |
| max_amount | money | Y | |
| is_enabled | bool | NN | |
| integration_ref | str(64) | Y | e.g. Paymob integration ID |

Unique: `uq_pgcur` (payment_gateway_id, currency).

### payment_methods

Methods offered within a gateway (card, wallet, kiosk...), used for display and Paymob integration IDs.

| Column | Type | Null | Notes |
|---|---|---|---|
| payment_gateway_id | fk:payment_gateways cascade | NN | |
| code | str(64) | NN | |
| label | str(120) | NN | |
| integration_ref | str(64) | Y | |
| supports_refund | bool | NN | |
| is_active | bool | NN | |

Unique: `uq_pm` (payment_gateway_id, code).

### payment_eligibility_rules

| Column | Type | Null | Notes |
|---|---|---|---|
| payment_gateway_id | fk:payment_gateways cascade | NN | |
| effect | enum | NN | `allow`, `deny` |
| countries | json | Y | declared country condition |
| ip_countries | json | Y | |
| require_ip_match | bool | NN | default false |
| currencies | json | Y | |
| min_amount_base | money | Y | |
| max_amount_base | money | Y | |
| allow_guest | bool | NN | default true |
| max_risk_level | enum | Y | |
| priority | int | NN | default 100 |
| is_active | bool | NN | default true |

Index: `idx_per` (payment_gateway_id, is_active, priority): evaluation.

### payment_gateway_product_exclusions

PK (payment_gateway_id, product_id), no id. Both fk cascade.

### payment_gateway_fee_rules

Columns: payment_gateway_id fk cascade NN, payment_method_code str(64) Y, country_code str(2) Y, currency str(3) Y, percent_fee pct NN default 0, fixed_fee money NN default 0, fixed_fee_currency str(3) Y, is_active bool NN. Index: `idx_pgfr` (payment_gateway_id, is_active).

### payments

One row per attempt.

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| order_id | fk:orders | NN | |
| payment_gateway_id | fk:payment_gateways | NN | |
| attempt_no | tinyint unsigned | NN | |
| status | enum | NN | `created`, `requires_action`, `processing`, `succeeded`, `failed`, `cancelled`, `expired`, `partially_refunded`, `refunded`, `duplicate_capture` |
| method_code | str(64) | Y | |
| provider_payment_id | str(128) | Y | `pi_`, PayPal order ID, Paymob intention ID |
| provider_order_id | str(128) | Y | Paymob `intention_order_id` |
| provider_capture_id | str(128) | Y | PayPal capture, Paymob transaction ID |
| provider_reference | str(128) | Y | `special_reference` |
| base_amount | money | NN | |
| base_currency | ccy | NN | |
| rate | rate | NN | |
| amount | money | NN | charged |
| currency | ccy | NN | gateway currency |
| fee_amount | money | Y | |
| fee_currency | str(3) | Y | |
| fee_is_estimate | bool | NN | default true |
| settled_amount | money | Y | |
| settled_currency | str(3) | Y | |
| failure_code | str(64) | Y | |
| failure_message | str(255) | Y | |
| card_country | str(2) | Y | |
| card_fingerprint_hash | char(64) | Y | |
| verified_at | ts | Y | last provider re-read |
| next_check_at | ts | Y | |
| expires_at | ts | Y | |

Unique: `uq_pay_attempt` (order_id, attempt_no). `uq_pay_provider` (payment_gateway_id, provider_payment_id): locate by webhook. `uq_pay_capture` (payment_gateway_id, provider_capture_id). Index `idx_pay_fingerprint` (card_fingerprint_hash, created_at): risk engine card velocity (added in M7).

Indexes: `idx_pay_reconcile` (status, next_check_at): `payments:reconcile-open`. `idx_pay_gw_time` (payment_gateway_id, status, created_at): gateway performance, failures. `idx_pay_order` (order_id, status).

### payment_transactions [append]

Spec entity: financial ledger of provider movements.

| Column | Type | Null | Notes |
|---|---|---|---|
| payment_id | fk:payments | NN | |
| type | enum | NN | `authorization`, `capture`, `refund`, `void`, `chargeback`, `chargeback_reversal`, `fee_adjustment` |
| status | enum | NN | `pending`, `succeeded`, `failed` |
| provider_txn_id | str(128) | Y | |
| amount | money | NN | |
| currency | str(3) | NN | |
| raw_redacted | json | Y | |

Unique: `uq_pt` (payment_id, type, provider_txn_id): duplicate ledger entry prevention. Index: `idx_pt_type` (type, created_at): reconciliation and refund monitors.

### payment_webhooks

| Column | Type | Null | Notes |
|---|---|---|---|
| payment_gateway_id | fk:payment_gateways | NN | |
| provider_event_id | str(128) | NN | Paymob hash, see architecture |
| event_type | str(96) | NN | |
| signature_valid | bool | NN | |
| headers | json | Y | allow-listed names only |
| payload | enc | NN | raw body |
| payment_id | fk:payments null | Y | |
| status | enum | NN | `received`, `processed`, `ignored`, `failed` |
| attempts | smallint unsigned | NN | default 0 |
| error | str(500) | Y | |
| processed_at | ts | Y | |

Unique: `uq_pwh` (payment_gateway_id, provider_event_id): **webhook idempotency**.

Indexes: `idx_pwh_status` (status, created_at): retry and F08 delivery failures. `idx_pwh_payment` (payment_id).

Rows with invalid signatures are not stored in this table; they increment a counter and are logged to `security_events`.

### refunds

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| order_id | fk:orders | NN | |
| payment_id | fk:payments null | Y | null for wallet-only |
| destination | enum | NN | `original_method`, `wallet` |
| status | enum | NN | `requested`, `pending`, `succeeded`, `failed`, `unknown` |
| reason | enum | NN | `undelivered`, `customer_request`, `fraud`, `duplicate`, `admin` |
| amount | money | NN | payment currency |
| currency | ccy | NN | |
| amount_base | money | NN | |
| provider_refund_id | str(128) | Y | |
| requested_by_type | enum | NN | `system`, `staff` |
| requested_by_id | bigint unsigned | Y | |
| approved_by | fk:users null | Y | |
| note | text | Y | |
| failure_reason | str(255) | Y | |
| completed_at | ts | Y | |

Unique: `uq_ref_provider` (payment_id, provider_refund_id). Indexes: `idx_ref_status` (status, created_at): refund monitor, poll. `idx_ref_order` (order_id).

### refund_units

PK (refund_id, order_item_unit_id), no id, both fk RESTRICT. Each unit refunded at most once: unique `uq_ru_unit` (order_item_unit_id).

### payment_disputes

Columns: payment_id fk NN, provider_dispute_id str(128) NN, status enum NN (`open`,`won`,`lost`,`closed`), reason str(128) Y, amount money NN, currency str(3) NN, evidence_due_at ts Y, closed_at ts Y. Unique: `uq_pd` (payment_id, provider_dispute_id). Index: `idx_pd_status` (status, evidence_due_at).

### reconciliation_runs

Columns: kind enum NN (`payments`,`suppliers`), subject_key str(64) NN, window_start ts NN, window_end ts NN, status enum NN (`running`,`completed`,`failed`), checked int unsigned NN default 0, mismatches int unsigned NN default 0, started_at ts NN, finished_at ts Y. Unique: `uq_rr` (kind, subject_key, window_start, window_end). Index (kind, started_at).

### reconciliation_items

| Column | Type | Null | Notes |
|---|---|---|---|
| reconciliation_run_id | fk:reconciliation_runs cascade | NN | |
| type | enum | NN | `orphan_provider`, `missing_provider`, `status_mismatch`, `amount_mismatch` |
| reference_type | str(64) | Y | |
| reference_id | bigint unsigned | Y | |
| provider_reference | str(128) | Y | |
| details | json | NN | redacted |
| status | enum | NN | `open`, `resolved`, `ignored` |
| resolved_by | fk:users null | Y | |
| resolution_note | text | Y | |

Index: `idx_ri_status` (status, created_at): `get_pending_reconciliation`, admin queue.

---

## 8. Wallet

### customer_wallets

Columns: user_id fk:users NN unique, currency ccy NN (base currency), balance money NN default 0, status enum NN (`active`,`frozen`) default `active`.

### wallet_transactions [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| customer_wallet_id | fk:customer_wallets | NN | |
| type | enum | NN | `credit_refund`, `credit_admin`, `debit_purchase`, `debit_admin`, `reversal` |
| amount | money | NN | signed |
| balance_after | money | NN | |
| reference_type | str(64) | Y | |
| reference_id | bigint unsigned | Y | |
| idempotency_key | str(128) | NN | |
| note | str(255) | Y | |
| created_by | fk:users null | Y | |

Unique: `uq_wt_idem` (customer_wallet_id, idempotency_key). Index: `idx_wt_wallet` (customer_wallet_id, id): statement.

M11: `chk_cw_balance_nonneg` CHECK (`balance >= 0`) on `customer_wallets`. Keys used: `pay:{payment public id}` (wallet payment), `refund:{refund public id}` (wallet refund credit), `admin:{Idempotency-Key}` (staff adjustment).

---

## 9. Promotions and affiliates

### coupons [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| code | str(64) | NN | unique, normalized uppercase |
| type | enum | NN | `percent`, `fixed` |
| value | money | NN | percent stored in the same column as `12.500000` |
| currency | ccy | Y | fixed only |
| max_discount_base | money | Y | |
| min_order_base | money | Y | |
| usage_limit | int unsigned | Y | |
| usage_limit_per_user | int unsigned | Y | default 1 |
| used_count | int unsigned | NN | default 0 |
| applicable_to | json | Y | product, category, platform lists |
| stackable | bool | NN | default false |
| allow_below_margin | bool | NN | default false |
| first_order_only | bool | NN | default false |
| starts_at | ts | Y | |
| ends_at | ts | Y | |
| is_active | bool | NN | default true |
| created_by_type | enum | NN | `staff`, `mcp` |

Index: `idx_c_active` (is_active, ends_at).

### coupon_redemptions

| Column | Type | Null | Notes |
|---|---|---|---|
| coupon_id | fk:coupons | NN | |
| order_id | fk:orders | NN | |
| user_id | fk:users null | Y | |
| email_hash | char(64) | Y | guest limit |
| discount_base | money | NN | |
| status | enum | NN | `reserved`, `redeemed`, `released` |

Unique: `uq_cr` (coupon_id, order_id). Indexes: `idx_cr_user` (coupon_id, user_id, status): per-user limit. `idx_cr_perf` (coupon_id, status, created_at): coupon performance.

`used_count` is incremented with a row lock on the coupon at order placement; released on cancel, expiry or failure before payment; the redemption becomes `redeemed` on `paid`. M11: `chk_c_usage` CHECK (`usage_limit IS NULL OR used_count <= usage_limit`). `email_hash` is HMAC-SHA256 of the lower-cased email with `APP_KEY`, written for every redemption, so per-user limits match by account or email. `applicable_to` is `{ products?, categories?, platforms? }` (ids).

### promotions [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| name | str(120) | NN | |
| campaign | str(120) | Y | |
| type | enum | NN | `percent`, `fixed`, `fixed_price` |
| value | money | NN | |
| priority | int | NN | default 100 |
| stackable | bool | NN | default false |
| badge_text | str(40) | Y | |
| starts_at | ts | NN | |
| ends_at | ts | Y | |
| is_active | bool | NN | |

Index: `idx_pr_active` (is_active, starts_at, ends_at).

### promotion_targets

Columns: promotion_id fk cascade NN, target_type enum NN (`product`,`variant`,`category`,`platform`), target_id bigint unsigned NN. Unique: (promotion_id, target_type, target_id). Index (target_type, target_id): resolve applicable promotion.

### affiliates [sd]

Columns: user_id fk:users NN unique, code str(32) NN unique, status enum NN (`pending`,`active`,`suspended`), commission_pct pct NN, payout_details enc Y, notes text Y.

### affiliate_referrals

Columns: affiliate_id fk NN, visitor_token_hash char(64) NN, landing_url str(500) Y, order_id fk:orders null Y, status enum NN (`clicked`,`converted`,`rejected`), clicked_at ts NN, converted_at ts Y. Index (affiliate_id, status, clicked_at): dashboard; (visitor_token_hash, clicked_at): attribution. Unique: `uq_ar_order` (order_id).

### affiliate_commissions

Columns: affiliate_id fk NN, order_id fk NN, amount_base money NN, status enum NN (`pending`,`approved`,`paid`,`reversed`), payout_id fk:affiliate_payouts null Y. Unique: `uq_ac` (affiliate_id, order_id). Index (status, created_at).

### affiliate_payouts

Columns: affiliate_id fk NN, amount_base money NN, status enum NN (`pending`,`paid`,`failed`), reference str(128) Y, paid_at ts Y, created_by fk:users NN.

---

## 10. Reviews and support

### reviews [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| product_id | fk:products cascade | NN | |
| user_id | fk:users null | Y | |
| order_item_id | fk:order_items null | Y | verified purchase |
| rating | tinyint unsigned | NN | 1 to 5 |
| title | str(190) | Y | |
| body | text | Y | |
| status | enum | NN | `pending`, `approved`, `rejected` |
| is_featured | bool | NN | default false; homepage |
| moderated_by | fk:users null | Y | |

Unique: `uq_r_item` (user_id, order_item_id). Index: `idx_r_product` (product_id, status, created_at): product page. `idx_r_featured` (status, is_featured, created_at): homepage.

### support_tickets

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| ticket_number | str(24) | NN | unique |
| user_id | fk:users null | Y | |
| guest_email | str(190) | Y | |
| order_id | fk:orders null | Y | |
| category | enum | NN | `order`, `payment`, `refund`, `account`, `other` |
| subject | str(190) | NN | |
| status | enum | NN | `open`, `pending_customer`, `pending_staff`, `resolved`, `closed` |
| priority | enum | NN | `low`, `normal`, `high`, `urgent` |
| assigned_to | fk:users null | Y | |
| last_activity_at | ts | NN | |

Indexes: `idx_st_queue` (status, priority, last_activity_at): agent queue. `idx_st_user` (user_id, created_at). `idx_st_order` (order_id).

### support_ticket_messages

Columns: support_ticket_id fk cascade NN, author_type enum NN (`customer`,`staff`,`system`), author_id bigint unsigned Y, body text NN, is_internal bool NN default false. Index (support_ticket_id, id).

### support_ticket_attachments

Columns: support_ticket_message_id fk cascade NN, media_id fk:media NN.

### refund_requests

| Column | Type | Null | Notes |
|---|---|---|---|
| order_id | fk:orders | NN | |
| support_ticket_id | fk:support_tickets null | Y | |
| user_id | fk:users null | Y | |
| reason | text | NN | |
| status | enum | NN | `requested`, `approved`, `rejected`, `completed` |
| reviewed_by | fk:users null | Y | |
| refund_id | fk:refunds null | Y | |

Index: `idx_rr_status` (status, created_at).

---

## 11. Content, SEO and marketing

### media [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| disk | str(32) | NN | |
| path | str(500) | NN | |
| original_filename | str(255) | NN | |
| mime | str(100) | NN | |
| size_bytes | int unsigned | NN | |
| width | int unsigned | Y | |
| height | int unsigned | Y | |
| alt | str(190) | Y | |
| conversions | json | Y | WebP, AVIF derivative paths |
| folder | str(190) | Y | |
| source | enum | NN | `upload`, `canva`, `supplier` |
| license_note | str(255) | Y | brand rights record |
| uploaded_by | fk:users null | Y | |

Index: `idx_m_folder` (folder, created_at): media library.

### seo_metadata

| Column | Type | Null | Notes |
|---|---|---|---|
| seoable_type | str(64) | NN | |
| seoable_id | bigint unsigned | NN | |
| locale | str(8) | NN | |
| title | str(190) | Y | |
| description | str(320) | Y | |
| canonical_override | str(500) | Y | |
| robots | str(64) | Y | |
| og_image_media_id | fk:media null | Y | |
| schema_overrides | json | Y | |

Unique: `uq_sm` (seoable_type, seoable_id, locale).

### seo_templates

Columns: page_type enum NN (`home`,`platform`,`category`,`product`,`blog`,`page`), locale str(8) NN, title_template str(255) NN, description_template str(500) NN. Unique: `uq_st` (page_type, locale).

### redirects

| Column | Type | Null | Notes |
|---|---|---|---|
| source_path | str(500) | NN | |
| source_hash | char(64) | NN | |
| target_path | str(500) | Y | null with 410 |
| status_code | smallint unsigned | NN | 301, 302, 410 |
| hits | int unsigned | NN | default 0 |
| is_active | bool | NN | |

Unique: `uq_red_hash` (source_hash): request-time lookup, cached in Redis.

### not_found_logs

Columns: path_hash char(64) NN, path str(500) NN, hits int unsigned NN default 1, last_seen_at ts NN. Unique (path_hash). Index (hits): admin 404 report.

### pages [sd]

Columns: title str(190) NN, slug str(210) NN unique, body mediumtext NN, template str(64) Y, status enum NN (`draft`,`published`), published_at ts Y, author_id fk:users null Y.

### blog_categories

Columns: name str(120) NN, slug str(140) NN unique.

### blog_posts [sd]

| Column | Type | Null | Notes |
|---|---|---|---|
| blog_category_id | fk:blog_categories null | Y | |
| author_id | fk:users null | Y | |
| title | str(190) | NN | |
| slug | str(210) | NN | unique |
| excerpt | str(500) | Y | |
| body | mediumtext | NN | |
| cover_media_id | fk:media null | Y | |
| status | enum | NN | `draft`, `scheduled`, `published` |
| published_at | ts | Y | |

Index: `idx_bp_pub` (status, published_at): listing, sitemap.

### faq_groups

Columns: name str(120) NN, slug str(140) NN unique, sort_order int NN default 0.

### faqs

Columns: faq_group_id fk cascade NN, question str(500) NN, answer text NN, show_on_homepage bool NN default false, is_active bool NN, sort_order int NN default 0. Index (faq_group_id, is_active, sort_order).

### menus

Columns: location str(64) NN unique (`header`,`footer_1`,...), name str(120) NN.

### menu_items

Columns: menu_id fk cascade NN, parent_id fk:menu_items null Y, label str(120) NN, url str(500) Y, linkable_type str(64) Y, linkable_id bigint unsigned Y, target str(16) Y, sort_order int NN default 0, is_active bool NN. Index (menu_id, parent_id, sort_order).

### homepage_sections

| Column | Type | Null | Notes |
|---|---|---|---|
| key | str(64) | NN | unique: `hero`, `featured_platforms`, `trending_games`, `best_sellers`, `popular_gift_cards`, `special_offers`, `recommended`, `recently_added`, `promo_banners`, `customer_reviews`, `trust_indicators`, `faq`, `newsletter` |
| title | str(190) | Y | |
| subtitle | str(255) | Y | |
| source | enum | NN | `manual`, `auto` |
| settings | json | Y | item count, rule |
| is_active | bool | NN | |
| sort_order | int | NN | |

### homepage_section_items

Columns: homepage_section_id fk cascade NN, item_type str(64) NN, item_id bigint unsigned NN, sort_order int NN default 0. Unique (homepage_section_id, item_type, item_id).

### banners [sd]

Columns: placement enum NN (`hero`,`home_promo`,`category`,`platform`), title str(190) Y, subtitle str(255) Y, cta_label str(64) Y, cta_url str(500) Y, media_id fk:media NN, mobile_media_id fk:media null Y, starts_at ts Y, ends_at ts Y, is_active bool NN, sort_order int NN default 0. Index (placement, is_active, starts_at, ends_at).

### newsletter_subscribers

| Column | Type | Null | Notes |
|---|---|---|---|
| email | str(190) | NN | unique |
| user_id | fk:users null | Y | |
| status | enum | NN | `pending`, `subscribed`, `unsubscribed`, `bounced` |
| locale | str(8) | Y | |
| confirmation_token_hash | char(64) | Y | double opt-in |
| confirmed_at | ts | Y | |
| unsubscribed_at | ts | Y | |

Index: `idx_ns_status` (status, confirmed_at).

---

## 12. Settings, admin, audit, operations

### settings

| Column | Type | Null | Notes |
|---|---|---|---|
| group | str(64) | NN | |
| key | str(96) | NN | |
| value | mediumtext | Y | |
| type | enum | NN | `string`, `int`, `bool`, `json`, `decimal` |
| is_encrypted | bool | NN | |
| updated_by | fk:users null | Y | |

Unique: `uq_s` (group, key). Cached in Redis, cleared on save. Critical settings require `settings.manage_critical`.

### saved_filters

Columns: user_id fk cascade NN, table_key str(64) NN, name str(120) NN, payload json NN, is_default bool NN default false. Unique: `uq_sf` (user_id, table_key, name).

### export_jobs

Columns: user_id fk NN, table_key str(64) NN, filters json Y, status enum NN (`pending`,`running`,`completed`,`failed`), media_path str(500) Y, row_count int unsigned Y, expires_at ts Y, error str(255) Y. Index (user_id, created_at).

### audit_logs [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| actor_type | enum | NN | `staff`, `customer`, `system`, `n8n`, `mcp` |
| actor_id | bigint unsigned | Y | |
| action | str(96) | NN | `product.price.updated` |
| subject_type | str(64) | Y | |
| subject_id | bigint unsigned | Y | |
| before | json | Y | redacted |
| after | json | Y | redacted |
| reason | str(500) | Y | |
| ip | str(45) | Y | |
| user_agent | str(255) | Y | |
| request_id | char(26) | Y | |

Indexes: `idx_al_subject` (subject_type, subject_id, id): entity history. `idx_al_actor` (actor_type, actor_id, id). `idx_al_action` (action, id). A DB trigger is not used; the app user lacks `UPDATE, DELETE` on this table in production (see `DEPLOYMENT.md`).

### security_events [append]

Columns: type str(64) NN, severity enum NN (`info`,`warning`,`critical`), ip str(45) Y, user_id fk:users null Y, context json Y. Index (type, created_at); (severity, created_at).

### scheduled_task_runs

Columns: command str(120) NN, started_at ts NN, finished_at ts Y, status enum NN (`running`,`completed`,`failed`), duration_ms int unsigned Y, error str(500) Y. Index (command, started_at).

### notification_templates

Columns: key str(96) NN, locale str(8) NN, subject str(255) Y, body mediumtext NN, is_active bool NN. Unique (key, locale).

---

## 13. Analytics

### daily_sales_summaries

Rebuilt idempotently per date.

| Column | Type | Null | Notes |
|---|---|---|---|
| date | date | NN | |
| dimension | enum | NN | `total`, `product`, `platform`, `gateway`, `supplier`, `country`, `currency` |
| dimension_key | str(64) | NN | `-` for total |
| orders_count | int unsigned | NN | |
| units_count | int unsigned | NN | |
| revenue_base | money | NN | |
| refunds_base | money | NN | |
| supplier_cost_base | money | NN | |
| gateway_fee_base | money | NN | |
| gross_profit_base | money | NN | |
| net_profit_base | money | NN | |

Unique: `uq_dss` (date, dimension, dimension_key): upsert. Index: `idx_dss_dim` (dimension, date): trend charts.

### analytics_event_dispatches

| Column | Type | Null | Notes |
|---|---|---|---|
| event_name | enum | NN | `purchase`, `refund` |
| order_id | fk:orders | NN | |
| dedupe_key | str(128) | NN | `purchase:{order_number}`, `refund:{refund.public_id}` |
| payload | json | NN | no PII |
| status | enum | NN | `pending`, `sent`, `failed` |
| attempts | tinyint unsigned | NN | default 0 |
| sent_at | ts | Y | |

Unique: `uq_aed` (dedupe_key): **purchase deduplication**. Index: `idx_aed_status` (status, created_at).

### search_console_metrics

Columns: date date NN, dimension enum NN (`query`,`page`,`total`), key_hash char(64) NN, key_value str(1000) NN, clicks int unsigned NN, impressions int unsigned NN, ctr decimal(7,6) NN, position decimal(7,3) NN. Unique: `uq_scm` (date, dimension, key_hash). Index: (dimension, date, clicks): top queries and landing pages.

---

## 14. Automation and MCP

### automation_workflows

Columns: workflow_id str(16) NN unique (`A01`...), name str(190) NN, category str(32) NN, is_enabled bool NN, schedule str(64) Y, last_success_at ts Y, last_failure_at ts Y.

### automation_logs

Spec "Automation Logs" and n8n execution logs.

| Column | Type | Null | Notes |
|---|---|---|---|
| workflow_id | str(16) | NN | |
| execution_key | str(128) | NN | n8n execution ID or schedule window |
| status | enum | NN | `started`, `success`, `failed`, `skipped_duplicate` |
| started_at | ts | NN | |
| finished_at | ts | Y | |
| summary | json | Y | |
| error | text | Y | |

Unique: `uq_alog` (workflow_id, execution_key): **duplicate execution protection**. Index: `idx_alog` (workflow_id, started_at), (status, started_at).

### automation_outbox

| Column | Type | Null | Notes |
|---|---|---|---|
| event_id | ulid | NN | unique, n8n dedupe key |
| event_type | str(96) | NN | |
| payload | json | NN | no codes or secrets |
| status | enum | NN | `pending`, `delivered`, `failed` |
| attempts | smallint unsigned | NN | default 0 |
| next_attempt_at | ts | Y | |
| delivered_at | ts | Y | |

Index: `idx_ao_pending` (status, next_attempt_at): `automation:flush-outbox`.

### ai_suggestions

Human-review queue for section C workflows.

| Column | Type | Null | Notes |
|---|---|---|---|
| workflow_id | str(16) | NN | |
| type | enum | NN | `product`, `campaign`, `bundle`, `coupon`, `blog_idea`, `seo_description`, `marketing_copy`, `social_post`, `pricing` |
| subject_type | str(64) | Y | |
| subject_id | bigint unsigned | Y | |
| content | json | NN | |
| status | enum | NN | `pending_review`, `approved`, `rejected` |
| reviewed_by | fk:users null | Y | |
| reviewed_at | ts | Y | |
| review_note | text | Y | |

Index: `idx_ais` (status, type, created_at): review queue. Approved does not auto-publish; it creates a draft.

### mcp_pending_actions

| Column | Type | Null | Notes |
|---|---|---|---|
| public_id | ulid | NN | unique |
| service_account_id | fk:service_accounts | NN | |
| tool | str(64) | NN | |
| arguments | json | NN | |
| arguments_hash | char(64) | NN | |
| risk_tier | tinyint unsigned | NN | 1 or 2 |
| summary | str(1000) | NN | human-readable |
| status | enum | NN | `pending`, `approved`, `rejected`, `executed`, `failed`, `expired` |
| approved_via | enum | Y | `elicitation`, `admin_panel` |
| approved_by | fk:users null | Y | |
| expires_at | ts | NN | |
| executed_at | ts | Y | |
| result | json | Y | |

Index: `idx_mpa` (status, expires_at): admin queue, expiry. `idx_mpa_dedupe` (service_account_id, arguments_hash, status): the service rejects a second `pending` action with the same arguments. This is an index, not a unique key, because `status` changes over the row's life.

### mcp_audit_logs [append]

| Column | Type | Null | Notes |
|---|---|---|---|
| service_account_id | fk:service_accounts null | Y | |
| token_id | bigint unsigned | Y | |
| tool | str(64) | NN | |
| arguments_redacted | json | Y | |
| outcome | enum | NN | `ok`, `denied`, `invalid`, `error`, `confirmation_required`, `rate_limited` |
| http_status | smallint unsigned | Y | |
| duration_ms | int unsigned | Y | |
| ip | str(45) | Y | |

Indexes: `idx_mal_account` (service_account_id, created_at). `idx_mal_tool` (tool, outcome, created_at).

---

## 15. Migration ordering

Timestamps are assigned at creation in this order. Each group depends only on earlier groups. The existing skeleton migrations run first and are not altered.

| Group | Tables |
|---|---|
| G0 (exists) | `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` |
| G1 Reference | `currencies`, `countries`, `regions`, `region_country` |
| G2 Identity | alter `users` (adds columns and FKs to `countries`, `currencies`), Spatie tables, `service_accounts`, `personal_access_tokens`, `notifications`, `customer_profiles`, `customer_notes`, `customer_consents`, `notification_preferences`, `otp_challenges` |
| G3 Media and settings | `media`, `settings`, `saved_filters`, `export_jobs`, `audit_logs`, `security_events`, `scheduled_task_runs`, `notification_templates` |
| G4 Catalog | `gaming_platforms`, `categories`, `products`, `category_product`, `product_variants`, `product_regions`, `product_country_restrictions`, `product_images`, `attribute_definitions`, `product_attribute_values`, `delivery_field_definitions`, `product_delivery_fields`, `product_relations`, `wishlist_items`, `content_translations`, `search_documents`, `search_queries` |
| G5 CMS and SEO | `seo_metadata`, `seo_templates`, `redirects`, `not_found_logs`, `pages`, `blog_categories`, `blog_posts`, `faq_groups`, `faqs`, `menus`, `menu_items`, `homepage_sections`, `homepage_section_items`, `banners`, `newsletter_subscribers` |
| G6 Currency and pricing | `exchange_rates`, `exchange_rate_history`, `pricing_rules`, `variant_prices`, `pricing_overrides_log` |
| G7 Suppliers (non-transactional) | `suppliers`, `supplier_credentials`, `supplier_products`, `supplier_product_prices`, `supplier_product_mappings`, `supplier_sync_runs`, `supplier_balance_snapshots`, `supplier_health_checks`, `supplier_webhooks` |
| G8 Payment setup | `payment_gateways`, `payment_gateway_credentials`, `payment_gateway_currencies`, `payment_methods`, `payment_eligibility_rules`, `payment_gateway_product_exclusions`, `payment_gateway_fee_rules` |
| G9 Promotions | `coupons`, `promotions`, `promotion_targets` |
| G10 Cart and orders | `carts`, `cart_items`, `idempotency_keys`, `orders` (without `primary_payment_id` FK and `affiliate_referral_id` FK), `order_items`, `order_item_delivery_values`, `order_item_units`, `order_events`, `order_notes`, `manual_fulfillment_tasks`, `risk_assessments` |
| G11 Fulfilment and supplier transactions | `supplier_transactions`, `supplier_api_logs`, `digital_codes`, `digital_code_accesses` |
| G12 Payments | `payments`, `payment_transactions`, `payment_webhooks`, `refunds`, `refund_units`, `payment_disputes`, `reconciliation_runs`, `reconciliation_items` |
| G13 Back-references | add FK `orders.primary_payment_id` to `payments`; `coupon_redemptions`; `order_item_financials` |
| G14 Wallet | `customer_wallets`, `wallet_transactions` |
| G15 Affiliates | `affiliates`, `affiliate_referrals`, `affiliate_payouts`, `affiliate_commissions`; add FK `orders.affiliate_referral_id` |
| G16 Reviews and support | `reviews`, `support_tickets`, `support_ticket_messages`, `support_ticket_attachments`, `refund_requests` |
| G17 Analytics | `daily_sales_summaries`, `analytics_event_dispatches`, `search_console_metrics` |
| G18 Automation and MCP | `automation_workflows`, `automation_logs`, `automation_outbox`, `ai_suggestions`, `mcp_pending_actions`, `mcp_audit_logs` |

Order of operations for circular or late references:

- `gaming_platforms.logo_media_id` and similar reference `media`, created in G3 ahead of G4, so no back-reference is needed.
- `orders.primary_payment_id` and `orders.affiliate_referral_id` are created as plain indexed columns in G10, with FKs added in G13 and G15.
- `orders.coupon_id` refers to G9, already present.

## 16. Seeders

Required seeders (idempotent; values an admin can edit are written only on first run):

`CurrencySeeder` (no rates), `CountrySeeder`, `RegionSeeder`, `RolePermissionSeeder`, `SettingsSeeder` (including `pricing.default_below_margin_policy`, `default_min_margin_pct`, `default_min_margin_fixed`, `default_gateway_fee_pct`, `default_gateway_fee_fixed`, `conversion_cost_pct`; `orders.quote_ttl_minutes`, `orders.max_payment_attempts`; `security.risk_review_score` 40, `security.risk_block_score` 80, `security.risk_first_order_amount_base` 100), `PaymentGatewaySeeder` (inactive, no credentials, with default eligibility rules), `SupplierSeeder` (Reloadly inactive, internal stock, manual), `DeliveryFieldDefinitionSeeder`, `HomepageSectionSeeder`, `SeoTemplateSeeder`, `AutomationWorkflowSeeder`, `SuperAdminSeeder` (reads credentials from environment, refuses to run if they are empty). `SuperAdminSeeder` and `DemoCatalogSeeder` are run explicitly with `--class` and are not called by `DatabaseSeeder`, so `migrate:fresh --seed` needs no credentials. Sample catalog data is in `DemoCatalogSeeder`, which refuses to run when `APP_ENV=production`.

## 17. Coverage of spec entities

| Spec entity | Table |
|---|---|
| Users, Roles, Permissions | `users`, Spatie tables |
| Gaming Platforms, Categories, Products, Product Variants, Product Regions | as named |
| Suppliers, Supplier Products, Supplier Product Mappings, Supplier Transactions | as named |
| Supplier Sync Logs | `supplier_sync_runs` |
| Currencies, Exchange Rates, Pricing Rules | as named, plus `exchange_rate_history` |
| Payment Gateways, Payments, Payment Transactions, Payment Webhooks | as named |
| Orders, Order Items, Order Events | as named, plus `order_item_units` |
| Digital Codes | `digital_codes` |
| Customer Wallets, Wallet Transactions | as named |
| Coupons, Promotions, Reviews, Support Tickets | as named |
| Wishlists | `wishlist_items` |
| Notifications | `notifications` |
| SEO Metadata, Blog Posts, Audit Logs | `seo_metadata`, `blog_posts`, `audit_logs` |
| Automation Logs | `automation_logs` |
