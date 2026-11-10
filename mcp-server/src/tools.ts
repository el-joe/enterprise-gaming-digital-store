import { z } from 'zod';

/**
 * Tool catalogue (MCP_SERVER.md section 4). Each tool maps to exactly one Laravel route; inputs
 * are validated here with zod and again by a Laravel FormRequest. There is no generic request,
 * query or SQL tool.
 */

export type Scope =
  | 'products:read'
  | 'orders:read'
  | 'analytics:read'
  | 'suppliers:read'
  | 'marketing:read'
  | 'operations:read'
  | 'products:write'
  | 'coupons:write'
  | 'catalog:sync'
  | 'orders:retry';

export type Shape = Record<string, z.ZodType>;

export interface ToolDefinition {
  name: string;
  title: string;
  description: string;
  scope: Scope;
  write: boolean;
  input: Shape;
  /** Laravel path below /api/v1/mcp/ and the query or body built from validated arguments. */
  route: (args: Record<string, unknown>) => { method: 'GET' | 'POST'; path: string; params: Record<string, unknown> };
}

const identifier = (description: string, max = 80) =>
  z.string().regex(new RegExp(`^[A-Za-z0-9][A-Za-z0-9_.-]{0,${max - 1}}$`), 'Letters, digits, dot, dash or underscore only.').describe(description);

const date = (description: string) => z.string().regex(/^\d{4}-\d{2}-\d{2}$/, 'Use YYYY-MM-DD.').describe(description);

const money = (description: string) =>
  z.string().regex(/^\d{1,14}(\.\d{1,6})?$/, 'A decimal string such as "12.50"; never a JSON number.').describe(description);

const page = z.number().int().min(1).max(10000).optional().describe('Page number, default 1.');
const perPage = z.number().int().min(1).max(100).optional().describe('Items per page, default 20, max 100.');
const reason = z.string().min(5).max(1000).describe('Why this change is needed; stored in the audit log.');
const range = {
  from: date('First day of the window (UTC, inclusive).'),
  to: date('Last day of the window (UTC, inclusive). At most 366 days after `from`.'),
};
const limit = z.number().int().min(1).max(100).optional().describe('How many rows, default 10.');
const groupBy = z.enum(['day', 'week', 'month', 'none']).optional().describe('Period grouping, default day.');

const segment = (value: unknown): string => encodeURIComponent(String(value));

const get = (path: string, pick: string[] = []) => (args: Record<string, unknown>) => ({
  method: 'GET' as const,
  path,
  params: Object.fromEntries(pick.filter((key) => args[key] !== undefined).map((key) => [key, args[key]])),
});

const post = (path: string) => (args: Record<string, unknown>) => ({ method: 'POST' as const, path, params: args });

export const TOOLS: ToolDefinition[] = [
  // Products
  {
    name: 'list_products',
    title: 'List products',
    description: 'List catalog products, newest first, with platform, category and active variant count.',
    scope: 'products:read',
    write: false,
    input: {
      status: z.enum(['draft', 'active', 'inactive']).optional().describe('Product status.'),
      platform: z.string().regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/).max(140).optional().describe('Gaming platform slug.'),
      category: z.string().regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/).max(140).optional().describe('Primary category slug.'),
      page,
      per_page: perPage,
    },
    route: get('products', ['status', 'platform', 'category', 'page', 'per_page']),
  },
  {
    name: 'get_product',
    title: 'Get product',
    description: 'One product by public id or SKU, with its variants and base prices.',
    scope: 'products:read',
    write: false,
    input: { id_or_sku: identifier('Product public id (ULID) or product SKU.') },
    route: (args) => ({ method: 'GET', path: `products/${segment(args.id_or_sku)}`, params: {} }),
  },
  {
    name: 'search_products',
    title: 'Search products',
    description: 'Search products by name, product SKU or variant SKU.',
    scope: 'products:read',
    write: false,
    input: { query: z.string().min(2).max(100).describe('Text to search for.'), page, per_page: perPage },
    route: get('products/search', ['query', 'page', 'per_page']),
  },
  {
    name: 'get_product_pricing',
    title: 'Get variant pricing',
    description: 'The price the store would charge for a variant now, from the pricing engine, with cost, margin floor and margin.',
    scope: 'products:read',
    write: false,
    input: {
      variant_sku: identifier('Variant SKU.'),
      country: z.string().regex(/^[A-Za-z]{2}$/).optional().describe('ISO country code.'),
      currency: z.string().regex(/^[A-Za-z]{3}$/).optional().describe('ISO currency code; default is the base currency.'),
    },
    route: (args) => ({ method: 'GET', path: `variants/${segment(args.variant_sku)}/pricing`, params: { country: args.country, currency: args.currency } }),
  },
  {
    name: 'check_product_availability',
    title: 'Check variant availability',
    description: 'Whether a variant is sellable, its supplier mappings and the count of internal stock codes (counts only).',
    scope: 'products:read',
    write: false,
    input: { variant_sku: identifier('Variant SKU.') },
    route: (args) => ({ method: 'GET', path: `variants/${segment(args.variant_sku)}/availability`, params: {} }),
  },

  // Orders
  {
    name: 'list_orders',
    title: 'List orders',
    description: 'Orders, newest first. Customer emails are masked.',
    scope: 'orders:read',
    write: false,
    input: {
      status: z
        .enum(['pending_payment', 'payment_processing', 'paid', 'processing', 'supplier_pending', 'pending_verification', 'manual_review', 'completed', 'partially_fulfilled', 'failed', 'cancelled', 'refund_pending', 'partially_refunded', 'refunded'])
        .optional()
        .describe('Order status.'),
      from: date('Created on or after this day.').optional(),
      to: date('Created on or before this day.').optional(),
      page,
      per_page: perPage,
    },
    route: get('orders', ['status', 'from', 'to', 'page', 'per_page']),
  },
  {
    name: 'get_order_details',
    title: 'Get order details',
    description: 'Items, units and supplier attempt statuses of one order. Customer identifiers are masked; codes and delivery values are never returned.',
    scope: 'orders:read',
    write: false,
    input: { order_number: identifier('Order number.', 24) },
    route: (args) => ({ method: 'GET', path: `orders/${segment(args.order_number)}`, params: {} }),
  },
  {
    name: 'get_order_status',
    title: 'Get order status',
    description: 'Current status of one order.',
    scope: 'orders:read',
    write: false,
    input: { order_number: identifier('Order number.', 24) },
    route: (args) => ({ method: 'GET', path: `orders/${segment(args.order_number)}/status`, params: {} }),
  },
  {
    name: 'search_customer_orders',
    title: 'Search customer orders',
    description: 'Orders for one exact customer email or one exact order number (not both). No partial matching.',
    scope: 'orders:read',
    write: false,
    input: {
      email: z.email().max(190).optional().describe('Exact customer email.'),
      order_number: identifier('Exact order number.', 24).optional(),
      page,
      per_page: perPage,
    },
    route: get('orders/search', ['email', 'order_number', 'page', 'per_page']),
  },

  // Analytics
  {
    name: 'get_sales_summary',
    title: 'Sales summary',
    description: 'Orders, units, revenue and refunds from recognised order financials (settled orders), by period.',
    scope: 'analytics:read',
    write: false,
    input: { ...range, group_by: groupBy },
    route: get('analytics/sales-summary', ['from', 'to', 'group_by']),
  },
  {
    name: 'get_profit_summary',
    title: 'Profit summary',
    description: 'Revenue, supplier cost, gateway fees, FX and other costs, gross and net profit, by period.',
    scope: 'analytics:read',
    write: false,
    input: { ...range, group_by: groupBy },
    route: get('analytics/profit-summary', ['from', 'to', 'group_by']),
  },
  {
    name: 'get_top_selling_products',
    title: 'Top selling products',
    description: 'Products ranked by recognised revenue.',
    scope: 'analytics:read',
    write: false,
    input: { ...range, limit },
    route: get('analytics/top-selling-products', ['from', 'to', 'limit']),
  },
  {
    name: 'get_supplier_performance',
    title: 'Supplier performance',
    description: 'Supplier attempts by outcome, success rate, time to success and recognised supplier cost.',
    scope: 'analytics:read',
    write: false,
    input: range,
    route: get('analytics/supplier-performance', ['from', 'to']),
  },
  {
    name: 'get_failed_orders',
    title: 'Failed orders',
    description: 'Failed and partially fulfilled orders created in the window.',
    scope: 'analytics:read',
    write: false,
    input: { ...range, page, per_page: perPage },
    route: get('analytics/failed-orders', ['from', 'to', 'page', 'per_page']),
  },
  {
    name: 'get_conversion_metrics',
    title: 'Conversion metrics',
    description: 'Checkout-to-payment rate from orders. Visitor conversion is reported as unavailable until GA4 (M18) collects sessions.',
    scope: 'analytics:read',
    write: false,
    input: range,
    route: get('analytics/conversion-metrics', ['from', 'to']),
  },

  // Inventory and suppliers
  {
    name: 'get_supplier_balances',
    title: 'Supplier balances',
    description: 'Last cached balance of each supplier and whether it is below its threshold. No live supplier call.',
    scope: 'suppliers:read',
    write: false,
    input: {},
    route: get('suppliers/balances'),
  },
  {
    name: 'get_supplier_status',
    title: 'Supplier status',
    description: 'Status, mode, health, circuit breaker and last health check of suppliers.',
    scope: 'suppliers:read',
    write: false,
    input: { supplier: z.string().min(1).max(120).optional().describe('Supplier id, name or adapter key.') },
    route: get('suppliers/status', ['supplier']),
  },
  {
    name: 'get_supplier_sync_status',
    title: 'Supplier sync status',
    description: 'Latest catalog and pricing sync run of suppliers.',
    scope: 'suppliers:read',
    write: false,
    input: { supplier: z.string().min(1).max(120).optional().describe('Supplier id, name or adapter key.') },
    route: get('suppliers/sync-status', ['supplier']),
  },
  {
    name: 'get_low_margin_products',
    title: 'Low margin products',
    description: 'Active variants whose margin over cost is below the threshold, lowest first; variants with unknown cost are counted separately.',
    scope: 'suppliers:read',
    write: false,
    input: {
      threshold_pct: z.string().regex(/^\d{1,3}(\.\d{1,4})?$/).optional().describe('Margin threshold in percent as a decimal string, default "10".'),
      page,
      per_page: perPage,
    },
    route: get('inventory/low-margin-products', ['threshold_pct', 'page', 'per_page']),
  },

  // Marketing
  {
    name: 'get_best_selling_products',
    title: 'Best selling products',
    description: 'Products ranked by units sold on paid orders.',
    scope: 'marketing:read',
    write: false,
    input: { ...range, limit },
    route: get('marketing/best-selling-products', ['from', 'to', 'limit']),
  },
  {
    name: 'get_underperforming_products',
    title: 'Underperforming products',
    description: 'Active products with the fewest units sold on paid orders, including none.',
    scope: 'marketing:read',
    write: false,
    input: { ...range, limit },
    route: get('marketing/underperforming-products', ['from', 'to', 'limit']),
  },
  {
    name: 'get_coupon_performance',
    title: 'Coupon performance',
    description: 'Redemptions on paid orders, discount given and order revenue per coupon.',
    scope: 'marketing:read',
    write: false,
    input: { ...range, code: z.string().regex(/^[A-Za-z0-9_-]{1,64}$/).optional().describe('One coupon code.') },
    route: get('marketing/coupon-performance', ['from', 'to', 'code']),
  },
  {
    name: 'get_customer_purchase_trends',
    title: 'Customer purchase trends',
    description: 'Aggregates only: paid orders, revenue, average order value, guest and repeat shares per period, top countries.',
    scope: 'marketing:read',
    write: false,
    input: { ...range, group_by: groupBy },
    route: get('marketing/customer-purchase-trends', ['from', 'to', 'group_by']),
  },

  // Operations
  {
    name: 'get_pending_orders',
    title: 'Pending orders',
    description: 'Paid or in-fulfilment orders whose status has not changed for at least the given minutes.',
    scope: 'operations:read',
    write: false,
    input: { older_than_minutes: z.number().int().min(0).max(43200).optional().describe('Default 30.'), page, per_page: perPage },
    route: get('operations/pending-orders', ['older_than_minutes', 'page', 'per_page']),
  },
  {
    name: 'get_failed_transactions',
    title: 'Failed payment transactions',
    description: 'Failed, expired or cancelled payment attempts with gateway and failure code.',
    scope: 'operations:read',
    write: false,
    input: { ...range, page, per_page: perPage },
    route: get('operations/failed-transactions', ['from', 'to', 'page', 'per_page']),
  },
  {
    name: 'get_pending_reconciliation',
    title: 'Pending reconciliation',
    description: 'Open reconciliation items and the count of unresolved supplier attempts.',
    scope: 'operations:read',
    write: false,
    input: { page, per_page: perPage },
    route: get('operations/pending-reconciliation', ['page', 'per_page']),
  },
  {
    name: 'get_system_health',
    title: 'System health',
    description: 'Database, queue, suppliers, exchange rates and fulfilment backlog. Scheduler heartbeats are reported unavailable until M17.',
    scope: 'operations:read',
    write: false,
    input: {},
    route: get('operations/system-health'),
  },

  // Administrative actions: each creates a pending action; nothing changes until it is approved.
  {
    name: 'update_product_price',
    title: 'Update variant base price',
    description: 'Request a new explicit base-currency price for a variant. Needs approval; more than 10% change or a price at the margin floor needs admin approval.',
    scope: 'products:write',
    write: true,
    input: { variant_sku: identifier('Variant SKU.'), new_price_base: money('New price in the base currency.'), reason },
    route: post('actions/update-product-price'),
  },
  {
    name: 'activate_product',
    title: 'Publish product',
    description: 'Request publication of a product. Refused at execution if customers could not buy it.',
    scope: 'products:write',
    write: true,
    input: { product_sku: identifier('Product SKU.', 64), reason },
    route: post('actions/activate-product'),
  },
  {
    name: 'deactivate_product',
    title: 'Unpublish product',
    description: 'Request that a product is unpublished.',
    scope: 'products:write',
    write: true,
    input: { product_sku: identifier('Product SKU.', 64), reason },
    route: post('actions/deactivate-product'),
  },
  {
    name: 'create_coupon',
    title: 'Create coupon',
    description: 'Request a coupon (one use per buyer, never below the margin floor). Above the configured value cap it needs admin approval.',
    scope: 'coupons:write',
    write: true,
    input: {
      code: z.string().regex(/^[A-Za-z0-9_-]{3,64}$/).describe('Coupon code; stored upper case.'),
      type: z.enum(['percent', 'fixed']).describe('Percent off, or a fixed amount in the base currency.'),
      value: money('Percent (at most 100) or amount.'),
      starts_at: date('First valid day.'),
      ends_at: date('Last valid day; after starts_at.'),
      usage_limit: z.number().int().min(1).max(100000).describe('Total uses allowed.'),
      reason,
    },
    route: post('actions/create-coupon'),
  },
  {
    name: 'trigger_catalog_sync',
    title: 'Trigger catalog sync',
    description: 'Request a catalog sync for one active supplier.',
    scope: 'catalog:sync',
    write: true,
    input: { supplier: z.string().min(1).max(120).describe('Supplier id, name or adapter key.'), reason },
    route: post('actions/trigger-catalog-sync'),
  },
  {
    name: 'retry_verified_failed_order',
    title: 'Retry verified-failed unit',
    description: 'Request a retry of one order unit whose latest supplier attempt is verified as not accepted. Never for pending_verification. Always needs admin approval.',
    scope: 'orders:retry',
    write: true,
    input: {
      order_number: identifier('Order number.', 24),
      unit_public_id: z.string().regex(/^[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}$/).describe('Unit id (ULID) from get_order_details.'),
      reason,
    },
    route: post('actions/retry-verified-failed-order'),
  },
];

export const ACTION_STATUS_TOOL: ToolDefinition = {
  name: 'get_action_status',
  title: 'Get action status',
  description: 'Status and result of a pending action requested by this account.',
  scope: 'products:write',
  write: true,
  input: { action_id: z.string().regex(/^[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}$/).describe('action_id returned by a write tool.') },
  route: (args) => ({ method: 'GET', path: `actions/${segment(args.action_id)}`, params: {} }),
};
