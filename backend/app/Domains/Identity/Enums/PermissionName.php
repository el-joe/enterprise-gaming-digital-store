<?php

namespace App\Domains\Identity\Enums;

enum PermissionName: string
{
    case DashboardView = 'dashboard.view';
    case ReportsSales = 'reports.sales';
    case ReportsProfit = 'reports.profit';
    case CatalogView = 'catalog.view';
    case CatalogManage = 'catalog.manage';
    case CatalogPublish = 'catalog.publish';
    case SuppliersView = 'suppliers.view';
    case SuppliersManage = 'suppliers.manage';
    case SuppliersCredentials = 'suppliers.credentials';
    case SuppliersSync = 'suppliers.sync';
    case SuppliersLogs = 'suppliers.logs';
    case MappingsManage = 'mappings.manage';
    case OrdersView = 'orders.view';
    case OrdersReview = 'orders.review';
    case OrdersFulfillManual = 'orders.fulfill_manual';
    case OrdersReconcile = 'orders.reconcile';
    case OrdersFraudReview = 'orders.fraud_review';
    case DigitalCodesImport = 'digital_codes.import';
    case DigitalCodesReveal = 'digital_codes.reveal';
    case RefundsView = 'refunds.view';
    case RefundsCreate = 'refunds.create';
    case RefundsApprove = 'refunds.approve';
    case CustomersView = 'customers.view';
    case CustomersViewPii = 'customers.view_pii';
    case CustomersManage = 'customers.manage';
    case CustomersWalletAdjust = 'customers.wallet_adjust';
    case PaymentsView = 'payments.view';
    case PaymentsManage = 'payments.manage';
    case PaymentsCredentials = 'payments.credentials';
    case PaymentsWebhookLogs = 'payments.webhook_logs';
    case PricingView = 'pricing.view';
    case PricingManage = 'pricing.manage';
    case PricingOverrideMargin = 'pricing.override_margin';
    case CurrencyManage = 'currency.manage';
    case MarketingCoupons = 'marketing.coupons';
    case MarketingPromotions = 'marketing.promotions';
    case MarketingAffiliates = 'marketing.affiliates';
    case MarketingBanners = 'marketing.banners';
    case ContentPages = 'content.pages';
    case ContentBlog = 'content.blog';
    case ContentFaq = 'content.faq';
    case ContentMenus = 'content.menus';
    case ContentHomepage = 'content.homepage';
    case ContentMedia = 'content.media';
    case SeoManage = 'seo.manage';
    case SeoRedirects = 'seo.redirects';
    case SupportView = 'support.view';
    case SupportReply = 'support.reply';
    case SupportManage = 'support.manage';
    case SupportRefundRequests = 'support.refund_requests';
    case UsersManage = 'users.manage';
    case RolesManage = 'roles.manage';
    case SettingsView = 'settings.view';
    case SettingsManage = 'settings.manage';
    case SettingsManageCritical = 'settings.manage_critical';
    case ServiceAccountsManage = 'service_accounts.manage';
    case McpActionsApprove = 'mcp_actions.approve';
    case AiSuggestionsReview = 'ai_suggestions.review';
    case AuditView = 'audit.view';

    /**
     * Whether another Super Admin must grant this explicitly: (E) in SECURITY.md section 4.
     */
    public function requiresExplicitGrant(): bool
    {
        return in_array($this, [
            self::SuppliersCredentials,
            self::DigitalCodesReveal,
            self::PaymentsCredentials,
            self::SettingsManageCritical,
            self::ServiceAccountsManage,
        ], true);
    }

    /**
     * @return list<self>
     */
    public static function grantableToSuperAdmin(): array
    {
        return array_values(array_filter(self::cases(), static fn (self $permission): bool => ! $permission->requiresExplicitGrant()));
    }

    /**
     * Expand a wildcard such as "orders.*", excluding explicit-grant permissions.
     *
     * @return list<self>
     */
    public static function inGroup(string $group): array
    {
        return array_values(array_filter(
            self::grantableToSuperAdmin(),
            static fn (self $permission): bool => str_starts_with($permission->value, "{$group}."),
        ));
    }
}
