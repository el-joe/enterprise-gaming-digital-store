<?php

namespace App\Domains\Suppliers\Enums;

/**
 * Operations an adapter may support (architecture 7.1). Each adapter declares a level for every
 * case; callers check the declaration before calling.
 */
enum SupplierCapability: string
{
    case ListProducts = 'list_products';
    case ProductDetails = 'product_details';
    case Countries = 'countries';
    case Pricing = 'pricing';
    case Availability = 'availability';
    case Balance = 'balance';
    case CreateOrder = 'create_order';
    case OrderStatus = 'order_status';
    case LookupByReference = 'lookup_by_reference';
    case FetchDeliverables = 'fetch_deliverables';
    case CancelOrder = 'cancel_order';
    case RefundOrder = 'refund_order';
    case TransactionHistory = 'transaction_history';
    case Webhooks = 'webhooks';
    case ClientIdempotency = 'client_idempotency';
    case PerRequestAmountCap = 'per_request_amount_cap';
}
