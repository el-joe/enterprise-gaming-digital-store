<?php

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\RoleName;
use App\Domains\Identity\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

/**
 * Literal transcription of SECURITY.md section 4, kept independent of the enums under test.
 *
 * @return array<string, list<string>>
 */
function documentedRoleGrants(): array
{
    $explicit = ['suppliers.credentials', 'digital_codes.reveal', 'payments.credentials', 'settings.manage_critical', 'service_accounts.manage'];
    $all = ['dashboard.view', 'reports.sales', 'reports.profit', 'catalog.view', 'catalog.manage', 'catalog.publish', 'suppliers.view', 'suppliers.manage', 'suppliers.credentials', 'suppliers.sync', 'suppliers.logs', 'mappings.manage', 'orders.view', 'orders.review', 'orders.fulfill_manual', 'orders.reconcile', 'orders.fraud_review', 'digital_codes.import', 'digital_codes.reveal', 'refunds.view', 'refunds.create', 'refunds.approve', 'customers.view', 'customers.view_pii', 'customers.manage', 'customers.wallet_adjust', 'payments.view', 'payments.manage', 'payments.credentials', 'payments.webhook_logs', 'pricing.view', 'pricing.manage', 'pricing.override_margin', 'currency.manage', 'marketing.coupons', 'marketing.promotions', 'marketing.affiliates', 'marketing.banners', 'content.pages', 'content.blog', 'content.faq', 'content.menus', 'content.homepage', 'content.media', 'seo.manage', 'seo.redirects', 'support.view', 'support.reply', 'support.manage', 'support.refund_requests', 'users.manage', 'roles.manage', 'settings.view', 'settings.manage', 'settings.manage_critical', 'service_accounts.manage', 'mcp_actions.approve', 'ai_suggestions.review', 'audit.view'];
    $superAdmin = array_values(array_diff($all, $explicit));

    return [
        'Super Admin' => $superAdmin,
        'Admin' => array_values(array_diff($superAdmin, ['users.manage', 'roles.manage'])),
        'Operations Manager' => ['dashboard.view', 'orders.view', 'orders.review', 'orders.fulfill_manual', 'orders.reconcile', 'orders.fraud_review', 'suppliers.view', 'suppliers.sync', 'suppliers.logs', 'mappings.manage', 'digital_codes.import', 'refunds.view', 'customers.view'],
        'Finance Manager' => ['dashboard.view', 'reports.sales', 'reports.profit', 'payments.view', 'payments.webhook_logs', 'refunds.view', 'refunds.create', 'refunds.approve', 'pricing.view', 'orders.view'],
        'Support Agent' => ['support.view', 'support.reply', 'support.manage', 'support.refund_requests', 'orders.view', 'customers.view', 'customers.view_pii', 'refunds.view'],
        'Catalog Manager' => ['catalog.view', 'catalog.manage', 'catalog.publish', 'mappings.manage', 'content.media', 'pricing.view'],
        'Marketing Manager' => ['marketing.coupons', 'marketing.promotions', 'marketing.affiliates', 'marketing.banners', 'content.homepage', 'content.blog', 'content.media', 'ai_suggestions.review', 'reports.sales'],
        'SEO Manager' => ['seo.manage', 'seo.redirects', 'content.pages', 'content.blog', 'catalog.view'],
    ];
}

it('seeds exactly the documented permissions', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $documented = array_values(array_unique(array_merge(...array_values(documentedRoleGrants()))));

    expect(Permission::query()->pluck('name')->all())
        ->toEqualCanonicalizing([...$documented, 'suppliers.credentials', 'digital_codes.reveal', 'payments.credentials', 'settings.manage_critical', 'service_accounts.manage']);
});

it('reaches only the permissions granted to each role', function (string $role, array $granted): void {
    $this->seed(RolePermissionSeeder::class);

    foreach (PermissionName::cases() as $permission) {
        Route::middleware(['admin', "permission:{$permission->value}"])
            ->get("/admin/api/__probe/{$permission->value}", static fn (): array => ['ok' => true]);
    }

    $staff = User::factory()->staff()->withTwoFactor()->create();
    $staff->assignRole($role);
    actingAsVerifiedStaff($staff);

    foreach (PermissionName::cases() as $permission) {
        $this->getJson("/admin/api/__probe/{$permission->value}")
            ->assertStatus(in_array($permission->value, $granted, true) ? 200 : 403);
    }
})->with(fn (): array => array_map(
    static fn (string $role, array $granted): array => [$role, $granted],
    array_keys(documentedRoleGrants()),
    array_values(documentedRoleGrants()),
));

it('does not give Super Admin the explicit-grant permissions', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $staff = User::factory()->staff()->create();
    $staff->assignRole(RoleName::SuperAdmin->value);

    foreach (['digital_codes.reveal', 'suppliers.credentials', 'payments.credentials', 'settings.manage_critical', 'service_accounts.manage'] as $permission) {
        expect($staff->fresh()->can($permission))->toBeFalse();
    }
});

it('denies a staff member with no role any permission route', function (): void {
    $this->seed(RolePermissionSeeder::class);
    Route::middleware(['admin', 'permission:orders.view'])->get('/admin/api/__probe/orders', static fn (): array => ['ok' => true]);
    actingAsVerifiedStaff();

    $this->getJson('/admin/api/__probe/orders')->assertForbidden()->assertJsonPath('code', 'forbidden');
});

it('never exposes permissions to a customer holding a staff role', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $customer = User::factory()->withTwoFactor()->create();
    $customer->assignRole(RoleName::Admin->value);
    actingAsVerifiedStaff($customer);

    $this->getJson('/admin/api/me')->assertForbidden();
});

it('lists the signed-in staff permissions', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $staff = User::factory()->staff()->withTwoFactor()->create();
    $staff->assignRole(RoleName::SeoManager->value);
    actingAsVerifiedStaff($staff);

    $this->getJson('/admin/api/me')
        ->assertOk()
        ->assertJsonPath('data.roles', ['SEO Manager'])
        ->assertJsonPath('data.permissions', ['catalog.view', 'content.blog', 'content.pages', 'seo.manage', 'seo.redirects']);
});
