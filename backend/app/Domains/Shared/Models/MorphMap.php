<?php

namespace App\Domains\Shared\Models;

use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Affiliates\Models\AffiliatePayout;
use App\Domains\Affiliates\Models\AffiliateReferral;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Analytics\Models\DailySalesSummary;
use App\Domains\Analytics\Models\SearchConsoleMetric;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Audit\Models\SecurityEvent;
use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Automation\Models\AutomationCustomerMessage;
use App\Domains\Automation\Models\AutomationLog;
use App\Domains\Automation\Models\AutomationOutbox;
use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Automation\Models\ScheduledTaskRun;
use App\Domains\Catalog\Models\AttributeDefinition;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Models\ProductCountryRestriction;
use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Models\ProductRelation;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Region;
use App\Domains\Catalog\Models\SearchDocument;
use App\Domains\Catalog\Models\SearchQuery;
use App\Domains\Catalog\Models\WishlistItem;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\BlogCategory;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\FaqGroup;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\HomepageSectionItem;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Models\MenuItem;
use App\Domains\Cms\Models\NewsletterSubscriber;
use App\Domains\Cms\Models\Page;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Customers\Models\CustomerConsent;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Identity\Models\MobileBiometricChallenge;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\MobileLoginChallenge;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\OtpChallenge;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Models\McpAuditLog;
use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Notifications\Models\MobilePushToken;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Orders\Models\GuestOrderToken;
use App\Domains\Orders\Models\IdempotencyKey;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Models\OrderNote;
use App\Domains\Orders\Models\RiskAssessment;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentDispute;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use App\Domains\Payments\Models\PaymentGatewayFeeRule;
use App\Domains\Payments\Models\PaymentMethod;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\ReconciliationRun;
use App\Domains\Payments\Models\Refund;
use App\Domains\Pricing\Models\PricingOverrideLog;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;
use App\Domains\Reviews\Models\Review;
use App\Domains\Seo\Models\NotFoundLog;
use App\Domains\Seo\Models\Redirect;
use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Seo\Models\SeoTemplate;
use App\Domains\Settings\Models\AdminTableState;
use App\Domains\Settings\Models\ExportJob;
use App\Domains\Settings\Models\SavedFilter;
use App\Domains\Settings\Models\Setting;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierApiLog;
use App\Domains\Suppliers\Models\SupplierBalanceSnapshot;
use App\Domains\Suppliers\Models\SupplierCredential;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use App\Domains\Suppliers\Models\SupplierImageCandidate;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use App\Domains\Suppliers\Models\SupplierSyncRun;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Models\SupplierWebhook;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketAttachment;
use App\Domains\Support\Models\SupportTicketMessage;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use Spatie\Permission\Models\Role;

/**
 * Polymorphic type aliases, so the database never stores PHP class names.
 */
final class MorphMap
{
    /**
     * @return array<string, class-string>
     */
    public static function aliases(): array
    {
        return [
            'user' => User::class,
            'admin_table_state' => AdminTableState::class,
            'affiliate' => Affiliate::class,
            'affiliate_commission' => AffiliateCommission::class,
            'affiliate_payout' => AffiliatePayout::class,
            'affiliate_referral' => AffiliateReferral::class,
            'ai_suggestion' => AiSuggestion::class,
            'analytics_event_dispatch' => AnalyticsEventDispatch::class,
            'attribute_definition' => AttributeDefinition::class,
            'audit_log' => AuditLog::class,
            'automation_customer_message' => AutomationCustomerMessage::class,
            'automation_log' => AutomationLog::class,
            'automation_outbox' => AutomationOutbox::class,
            'automation_workflow' => AutomationWorkflow::class,
            'banner' => Banner::class,
            'blog_category' => BlogCategory::class,
            'blog_post' => BlogPost::class,
            'cart' => Cart::class,
            'cart_item' => CartItem::class,
            'category' => Category::class,
            'content_translation' => ContentTranslation::class,
            'country' => Country::class,
            'coupon' => Coupon::class,
            'coupon_redemption' => CouponRedemption::class,
            'currency' => Currency::class,
            'customer_consent' => CustomerConsent::class,
            'customer_note' => CustomerNote::class,
            'customer_profile' => CustomerProfile::class,
            'customer_wallet' => CustomerWallet::class,
            'daily_sales_summary' => DailySalesSummary::class,
            'delivery_field_definition' => DeliveryFieldDefinition::class,
            'digital_code' => DigitalCode::class,
            'digital_code_access' => DigitalCodeAccess::class,
            'exchange_rate' => ExchangeRate::class,
            'exchange_rate_history' => ExchangeRateHistory::class,
            'export_job' => ExportJob::class,
            'faq' => Faq::class,
            'faq_group' => FaqGroup::class,
            'gaming_platform' => GamingPlatform::class,
            'guest_order_token' => GuestOrderToken::class,
            'homepage_section' => HomepageSection::class,
            'homepage_section_item' => HomepageSectionItem::class,
            'idempotency_key' => IdempotencyKey::class,
            'manual_fulfillment_task' => ManualFulfillmentTask::class,
            'mcp_audit_log' => McpAuditLog::class,
            'mcp_pending_action' => McpPendingAction::class,
            'media' => Media::class,
            'menu' => Menu::class,
            'menu_item' => MenuItem::class,
            'mobile_biometric_challenge' => MobileBiometricChallenge::class,
            'mobile_biometric_key' => MobileBiometricKey::class,
            'mobile_login_challenge' => MobileLoginChallenge::class,
            'mobile_push_token' => MobilePushToken::class,
            'mobile_refresh_token' => MobileRefreshToken::class,
            'newsletter_subscriber' => NewsletterSubscriber::class,
            'not_found_log' => NotFoundLog::class,
            'notification_preference' => NotificationPreference::class,
            'notification_template' => NotificationTemplate::class,
            'order' => Order::class,
            'order_event' => OrderEvent::class,
            'order_item' => OrderItem::class,
            'order_item_delivery_value' => OrderItemDeliveryValue::class,
            'order_item_financial' => OrderItemFinancial::class,
            'order_item_unit' => OrderItemUnit::class,
            'order_note' => OrderNote::class,
            'otp_challenge' => OtpChallenge::class,
            'page' => Page::class,
            'payment' => Payment::class,
            'payment_dispute' => PaymentDispute::class,
            'payment_eligibility_rule' => PaymentEligibilityRule::class,
            'payment_gateway' => PaymentGateway::class,
            'payment_gateway_credential' => PaymentGatewayCredential::class,
            'payment_gateway_currency' => PaymentGatewayCurrency::class,
            'payment_gateway_fee_rule' => PaymentGatewayFeeRule::class,
            'payment_method' => PaymentMethod::class,
            'payment_transaction' => PaymentTransaction::class,
            'payment_webhook' => PaymentWebhook::class,
            'pricing_override_log' => PricingOverrideLog::class,
            'pricing_rule' => PricingRule::class,
            'product' => Product::class,
            'product_attribute_value' => ProductAttributeValue::class,
            'product_country_restriction' => ProductCountryRestriction::class,
            'product_delivery_field' => ProductDeliveryField::class,
            'product_image' => ProductImage::class,
            'product_relation' => ProductRelation::class,
            'product_variant' => ProductVariant::class,
            'promotion' => Promotion::class,
            'promotion_target' => PromotionTarget::class,
            'reconciliation_item' => ReconciliationItem::class,
            'role' => Role::class,
            'reconciliation_run' => ReconciliationRun::class,
            'redirect' => Redirect::class,
            'refund' => Refund::class,
            'refund_request' => RefundRequest::class,
            'region' => Region::class,
            'review' => Review::class,
            'risk_assessment' => RiskAssessment::class,
            'saved_filter' => SavedFilter::class,
            'scheduled_task_run' => ScheduledTaskRun::class,
            'search_console_metric' => SearchConsoleMetric::class,
            'search_document' => SearchDocument::class,
            'search_query' => SearchQuery::class,
            'security_event' => SecurityEvent::class,
            'seo_metadata' => SeoMetadata::class,
            'seo_template' => SeoTemplate::class,
            'service_account' => ServiceAccount::class,
            'setting' => Setting::class,
            'supplier' => Supplier::class,
            'supplier_api_log' => SupplierApiLog::class,
            'supplier_balance_snapshot' => SupplierBalanceSnapshot::class,
            'supplier_credential' => SupplierCredential::class,
            'supplier_health_check' => SupplierHealthCheck::class,
            'supplier_image_candidate' => SupplierImageCandidate::class,
            'supplier_product' => SupplierProduct::class,
            'supplier_product_mapping' => SupplierProductMapping::class,
            'supplier_product_price' => SupplierProductPrice::class,
            'supplier_sync_run' => SupplierSyncRun::class,
            'supplier_transaction' => SupplierTransaction::class,
            'supplier_webhook' => SupplierWebhook::class,
            'support_ticket' => SupportTicket::class,
            'support_ticket_attachment' => SupportTicketAttachment::class,
            'support_ticket_message' => SupportTicketMessage::class,
            'variant_price' => VariantPrice::class,
            'wallet_transaction' => WalletTransaction::class,
            'wishlist_item' => WishlistItem::class,
        ];
    }
}
