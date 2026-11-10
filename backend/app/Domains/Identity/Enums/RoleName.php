<?php

namespace App\Domains\Identity\Enums;

enum RoleName: string
{
    case SuperAdmin = 'Super Admin';
    case Admin = 'Admin';
    case OperationsManager = 'Operations Manager';
    case FinanceManager = 'Finance Manager';
    case SupportAgent = 'Support Agent';
    case CatalogManager = 'Catalog Manager';
    case MarketingManager = 'Marketing Manager';
    case SeoManager = 'SEO Manager';

    /**
     * Get the permissions granted to the role (SECURITY.md section 4).
     *
     * @return list<PermissionName>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => PermissionName::grantableToSuperAdmin(),
            self::Admin => array_values(array_filter(
                PermissionName::grantableToSuperAdmin(),
                static fn (PermissionName $permission): bool => ! in_array($permission, [PermissionName::UsersManage, PermissionName::RolesManage], true),
            )),
            self::OperationsManager => [
                PermissionName::DashboardView,
                ...PermissionName::inGroup('orders'),
                PermissionName::SuppliersView,
                PermissionName::SuppliersSync,
                PermissionName::SuppliersLogs,
                PermissionName::MappingsManage,
                PermissionName::DigitalCodesImport,
                PermissionName::RefundsView,
                PermissionName::CustomersView,
            ],
            self::FinanceManager => [
                PermissionName::DashboardView,
                ...PermissionName::inGroup('reports'),
                PermissionName::PaymentsView,
                PermissionName::PaymentsWebhookLogs,
                ...PermissionName::inGroup('refunds'),
                PermissionName::PricingView,
                PermissionName::OrdersView,
            ],
            self::SupportAgent => [
                ...PermissionName::inGroup('support'),
                PermissionName::OrdersView,
                PermissionName::CustomersView,
                PermissionName::CustomersViewPii,
                PermissionName::RefundsView,
            ],
            self::CatalogManager => [
                ...PermissionName::inGroup('catalog'),
                PermissionName::MappingsManage,
                PermissionName::ContentMedia,
                PermissionName::PricingView,
            ],
            self::MarketingManager => [
                ...PermissionName::inGroup('marketing'),
                PermissionName::ContentHomepage,
                PermissionName::ContentBlog,
                PermissionName::ContentMedia,
                PermissionName::AiSuggestionsReview,
                PermissionName::ReportsSales,
            ],
            self::SeoManager => [
                ...PermissionName::inGroup('seo'),
                PermissionName::ContentPages,
                PermissionName::ContentBlog,
                PermissionName::CatalogView,
            ],
        };
    }
}
