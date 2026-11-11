<?php

use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Catalog\Models\Product;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Shared\Models\Country;
use Spatie\Permission\Models\Role;

/*
| M15: every new admin JSON route refuses staff without its permission (403, before validation),
| and every new page route refuses them too. Hidden navigation is not the control.
*/

it('answers 403 on every new M15 action route without its permission', function (string $method, string $uri): void {
    $customer = User::factory()->create();
    $staff = User::factory()->staff()->create();
    $key = MobileBiometricKey::factory()->create(['user_id' => $customer->id]);
    $gateway = PaymentGateway::factory()->create();
    $rule = PaymentEligibilityRule::factory()->create(['payment_gateway_id' => $gateway->id]);
    $product = Product::factory()->create();
    $suggestion = AiSuggestion::factory()->create();
    Country::factory()->create(['code' => 'EG']);
    $role = Role::findOrCreate('Night shift', 'web');

    $uri = str_replace(
        ['{customer}', '{staff}', '{key}', '{gateway}', '{rule}', '{product}', '{suggestion}', '{role}'],
        [$customer->public_id, $staff->public_id, $key->public_id, (string) $gateway->id, (string) $rule->id, (string) $product->id, (string) $suggestion->id, (string) $role->id],
        $uri,
    );

    adminWith(['orders.view', 'dashboard.view']);

    $this->json($method, $uri, ['reason' => 'Checked by phone', 'note' => 'Checked by phone'])->assertForbidden();
})->with([
    'translation' => ['PUT', '/admin/api/translations/product/{product}'],
    'seo' => ['PUT', '/admin/api/seo/product/{product}'],
    'country currency' => ['PATCH', '/admin/api/countries/EG'],
    'bulk mapping' => ['POST', '/admin/api/supplier-mappings/bulk'],
    'bulk mapping status' => ['POST', '/admin/api/supplier-mappings/bulk-status'],
    'product bulk' => ['POST', '/admin/api/products/bulk'],
    'customer block' => ['POST', '/admin/api/customers/{customer}/block'],
    'customer unblock' => ['POST', '/admin/api/customers/{customer}/unblock'],
    'customer risk' => ['PUT', '/admin/api/customers/{customer}/risk'],
    'customer note' => ['POST', '/admin/api/customers/{customer}/notes'],
    'customer device' => ['DELETE', '/admin/api/customers/{customer}/devices/web_abc'],
    'customer biometric key' => ['DELETE', '/admin/api/customers/{customer}/biometric-keys/{key}'],
    'customer export' => ['GET', '/admin/api/customers/{customer}/export'],
    'customer erase' => ['POST', '/admin/api/customers/{customer}/anonymize'],
    'staff invite' => ['POST', '/admin/api/staff'],
    'staff roles' => ['PUT', '/admin/api/staff/{staff}/roles'],
    'staff permissions' => ['PUT', '/admin/api/staff/{staff}/permissions'],
    'staff status' => ['POST', '/admin/api/staff/{staff}/status'],
    'staff 2fa reset' => ['POST', '/admin/api/staff/{staff}/reset-two-factor'],
    'role create' => ['POST', '/admin/api/roles'],
    'role permissions' => ['PUT', '/admin/api/roles/{role}/permissions'],
    'role delete' => ['DELETE', '/admin/api/roles/{role}'],
    'settings' => ['PUT', '/admin/api/settings/orders'],
    'settings secret' => ['PUT', '/admin/api/settings/currency/secrets/oxr_app_id'],
    'rule create' => ['POST', '/admin/api/payment-gateways/{gateway}/eligibility-rules'],
    'rule replace' => ['PUT', '/admin/api/payment-eligibility-rules/{rule}'],
    'rule delete' => ['DELETE', '/admin/api/payment-eligibility-rules/{rule}'],
    'public key' => ['PUT', '/admin/api/payment-gateways/{gateway}/public-key'],
    'ai approve' => ['POST', '/admin/api/ai-suggestions/{suggestion}/approve'],
    'ai reject' => ['POST', '/admin/api/ai-suggestions/{suggestion}/reject'],
    'workflow enable' => ['POST', '/admin/api/automation-workflows/A01/enable'],
    'workflow disable' => ['POST', '/admin/api/automation-workflows/A01/disable'],
]);

it('answers 403 on every new M15 page without its permission', function (string $path): void {
    $customer = User::factory()->create();
    $product = Product::factory()->create();
    $path = str_replace(['{customer}', '{product}'], [$customer->public_id, $product->public_id], $path);

    adminWith(['orders.view', 'dashboard.view']);

    $this->get($path)->assertForbidden();
})->with([
    '/admin/catalog/products', '/admin/catalog/products/create', '/admin/catalog/products/{product}', '/admin/catalog/mappings',
    '/admin/content/media', '/admin/pricing/exchange-rates', '/admin/customers', '/admin/customers/{customer}',
    '/admin/support/tickets', '/admin/support/refund-requests', '/admin/reviews', '/admin/users/staff', '/admin/users/roles',
    '/admin/settings', '/admin/settings/mobile', '/admin/automation/ai-suggestions', '/admin/automation/mcp-actions',
    '/admin/automation/mcp-tokens', '/admin/automation/credentials',
]);
