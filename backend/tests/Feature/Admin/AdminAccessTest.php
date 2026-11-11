<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\AuthenticationService;
use App\Domains\Identity\Services\TwoFactorService;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Suppliers\Models\Supplier;
use Spatie\Permission\Models\Permission;

/*
| M14: admin sign-in pages, two-step enforcement, permission-based navigation and route denial.
*/

describe('sign-in and two-step verification', function (): void {
    it('shows the admin sign-in page in English and Arabic with a strict CSP', function (string $locale, string $title, string $dir): void {
        storefrontWithoutBuild();

        $response = $this->withSession(['admin.locale' => $locale])->get('/admin/login');

        $response->assertOk()
            ->assertSee($title)
            ->assertSee('dir="'.$dir.'"', false)
            ->assertSee('data-theme="dark"', false)
            ->assertSee('noindex,nofollow', false);
        assertCspSafeHtml($response);
    })->with([
        'en' => ['en', 'Staff sign in', 'ltr'],
        'ar' => ['ar', 'دخول الموظفين', 'rtl'],
    ]);

    it('sends a guest from any admin page to the admin sign-in', function (): void {
        $this->get('/admin/orders')->assertRedirect(route('admin.login'));
        $this->get('/admin')->assertRedirect(route('admin.login'));
    });

    it('refuses customers on admin pages', function (): void {
        storefrontWithoutBuild();
        $this->actingAs(User::factory()->create())->get('/admin/orders')->assertForbidden();
    });

    it('sends staff without two-step verification to set it up, and shows the setup page', function (): void {
        storefrontWithoutBuild();
        $staff = User::factory()->staff()->create();
        $staff->givePermissionTo(Permission::findOrCreate('orders.view', 'web'));

        $this->actingAs($staff)->get('/admin/orders')->assertRedirect(route('admin.two-factor.setup'));

        $page = $this->actingAs($staff)->get('/admin/two-factor/setup');
        $page->assertOk()->assertSee('Set up two-step verification')->assertHeader('Cache-Control', 'no-store, private');
        assertCspSafeHtml($page);
    });

    it('signs out staff whose session never passed the challenge', function (): void {
        $staff = User::factory()->staff()->withTwoFactor()->create();

        $this->actingAs($staff)->get('/admin/orders')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    });

    it('keeps the JSON admin API answering 403 two_factor_required', function (): void {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->getJson('/admin/api/me')->assertForbidden()->assertJsonPath('code', 'two_factor_required');
    });

    it('shows the challenge page only while a sign-in waits for its code', function (): void {
        storefrontWithoutBuild();

        $this->get('/admin/two-factor')->assertRedirect(route('admin.login'));
        $this->withSession([AuthenticationService::PENDING_KEY => ['user_id' => 1, 'remember' => false, 'expires_at' => now()->addMinute()->getTimestamp()]])
            ->get('/admin/two-factor')->assertOk()->assertSee('Two-step verification');
    });

    it('serves the setup QR code only for a pending enrolment, never cached', function (): void {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get('/admin/two-factor/setup/qr')->assertNotFound();

        app(TwoFactorService::class)->beginEnrollment($staff);
        $this->actingAs($staff)->get('/admin/two-factor/setup/qr')
            ->assertOk()->assertHeader('Content-Type', 'image/svg+xml')->assertHeader('Cache-Control', 'no-store, private');
    });

    it('sends a verified staff member from the sign-in page to the admin', function (): void {
        adminWith(['dashboard.view']);

        $this->get('/admin/login')->assertRedirect(route('admin.home'));
    });

    it('switches the admin language and returns to the admin page', function (): void {
        adminWith(['orders.view']);

        $this->from('/admin/orders')->post('/admin/locale', ['locale' => 'ar'])->assertRedirect('/admin/orders');
        $this->get('/admin/orders')->assertOk()->assertSee('dir="rtl"', false)->assertSee('الطلبات');

        $this->post('/admin/locale', ['locale' => 'fr'])->assertSessionHasErrors('locale');
    });
});

describe('permission-based navigation', function (): void {
    it('lists only the sections the staff member may open', function (array $permissions, array $visible, array $hidden): void {
        adminWith($permissions);
        $response = $this->get('/admin/'.($permissions === ['refunds.view'] ? 'payments/refunds' : 'orders'));

        if (! in_array('orders.view', $permissions, true) && $permissions !== ['refunds.view']) {
            $response->assertForbidden();

            return;
        }

        $nav = (string) str($response->getContent())->between('data-admin-sidebar', '</aside>');

        foreach ($visible as $label) {
            expect($nav)->toContain('>'.$label.'</span>');
        }

        foreach ($hidden as $label) {
            expect($nav)->not->toContain('>'.$label.'</span>');
        }
    })->with([
        'orders only' => [['orders.view'], ['Orders'], ['Dashboard', 'Suppliers', 'Payments', 'Catalog', 'Customers', 'Settings']],
        'operations' => [['dashboard.view', 'orders.view', 'suppliers.view'], ['Dashboard', 'Orders', 'Suppliers'], ['Payments', 'Customers']],
        'finance' => [['orders.view', 'payments.view', 'refunds.view'], ['Orders', 'Payments'], ['Suppliers', 'Dashboard']],
        'refunds only (support agent)' => [['refunds.view'], ['Payments'], ['Orders', 'Dashboard', 'Suppliers']],
    ]);

    it('shows the M15 sections to holders of their permissions', function (): void {
        adminWith(['orders.view', 'catalog.view', 'customers.view', 'pricing.view', 'support.view', 'settings.view', 'users.manage']);

        $nav = (string) str($this->get('/admin/orders')->getContent())->between('data-admin-sidebar', '</aside>');

        expect($nav)->toContain('>Orders</span>')
            ->toContain('>Catalog</span>')
            ->toContain('>Customers</span>')
            ->toContain('>Pricing</span>')
            ->toContain('>Support</span>')
            ->toContain('>Settings</span>')
            ->toContain('>Users &amp; roles</span>')
            ->not->toContain('>Marketing</span>')
            ->not->toContain('>Content</span>');
    });

    it('shows each seeded role its own navigation', function (string $role, array $visible, array $hidden): void {
        adminWithRole($role);
        $home = $this->get('/admin');

        if ($visible === []) {
            $home->assertForbidden();

            return;
        }

        $nav = (string) str($this->get($home->headers->get('Location'))->assertOk()->getContent())->between('data-admin-sidebar', '</aside>');

        foreach ($visible as $label) {
            expect($nav)->toContain('>'.$label.'</span>');
        }

        foreach ($hidden as $label) {
            expect($nav)->not->toContain('>'.$label.'</span>');
        }
    })->with([
        'Operations Manager' => ['Operations Manager', ['Dashboard', 'Orders', 'Suppliers', 'Payments', 'Catalog', 'Customers'], ['Marketing', 'Settings']],
        'Finance Manager' => ['Finance Manager', ['Dashboard', 'Orders', 'Payments', 'Pricing'], ['Suppliers', 'Catalog']],
        'Support Agent' => ['Support Agent', ['Orders', 'Payments', 'Support', 'Customers'], ['Dashboard', 'Suppliers', 'Settings']],
        'Catalog Manager' => ['Catalog Manager', ['Catalog', 'Content', 'Pricing', 'Reviews'], ['Orders', 'Dashboard', 'Customers']],
    ]);

    it('opens the first permitted screen from the admin home', function (): void {
        adminWith(['refunds.view']);
        $this->get('/admin')->assertRedirect(route('admin.payments.refunds'));
    });

    it('shows only the payment tabs the staff member may open', function (): void {
        adminWith(['payments.view']);
        $page = $this->get('/admin/payments')->assertOk();

        $page->assertSee(route('admin.payments.transactions'))->assertDontSee(route('admin.payments.webhooks'))->assertDontSee(route('admin.payments.refunds'));
    });

    it('badges the orders entry with the orders that need a person', function (): void {
        adminWith(['orders.view']);
        Order::factory()->count(2)->create(['status' => 'manual_review']);
        Order::factory()->create(['status' => 'completed']);

        $this->get('/admin/orders')->assertSee('2 orders need attention');
    });
});

describe('route denial', function (): void {
    it('answers 403 on every page without its permission and 200 with it', function (string $path, string $permission): void {
        $path = str_replace(['{supplier}', '{gateway}', '{order}'], [
            (string) Supplier::factory()->create()->id,
            (string) PaymentGateway::factory()->create()->id,
            (string) Order::factory()->create()->public_id,
        ], $path);
        adminWith(['catalog.view']);
        $this->get($path)->assertForbidden();

        adminWith([$permission]);
        $this->get($path)->assertOk();
    })->with([
        'dashboard' => ['/admin/dashboard', 'dashboard.view'],
        'orders' => ['/admin/orders', 'orders.view'],
        'order detail' => ['/admin/orders/{order}', 'orders.view'],
        'suppliers' => ['/admin/suppliers', 'suppliers.view'],
        'supplier detail' => ['/admin/suppliers/{supplier}', 'suppliers.view'],
        'gateways' => ['/admin/payments', 'payments.view'],
        'gateway detail' => ['/admin/payments/gateways/{gateway}', 'payments.view'],
        'transactions' => ['/admin/payments/transactions', 'payments.view'],
        'webhooks' => ['/admin/payments/webhooks', 'payments.webhook_logs'],
        'refunds' => ['/admin/payments/refunds', 'refunds.view'],
        'disputes' => ['/admin/payments/disputes', 'payments.view'],
        'reconciliation' => ['/admin/payments/reconciliation', 'payments.view'],
    ]);

    it('answers 403 on every table endpoint without the table permission', function (string $key, string $permission): void {
        adminWith(['dashboard.view']);
        postTable($key, ['filters' => ['supplier' => '1']])->assertForbidden()->assertJsonPath('code', 'forbidden');
        $this->postJson("/admin/api/tables/{$key}/exports", ['format' => 'csv'])->assertForbidden();
        $this->putJson("/admin/api/tables/{$key}/state", ['hidden_columns' => [], 'page_length' => 25])->assertForbidden();
        $this->postJson("/admin/api/tables/{$key}/saved-filters", ['name' => 'Mine', 'payload' => []])->assertForbidden();

        adminWith([$permission]);
        postTable($key, ['filters' => ['supplier' => (string) Supplier::factory()->create()->id]])->assertOk();
    })->with([
        ['orders', 'orders.view'],
        ['suppliers', 'suppliers.view'],
        ['supplier-logs', 'suppliers.logs'],
        ['supplier-mappings', 'mappings.manage'],
        ['payments', 'payments.view'],
        ['webhooks', 'payments.webhook_logs'],
        ['refunds', 'refunds.view'],
        ['disputes', 'payments.view'],
        ['reconciliation', 'payments.view'],
    ]);

    it('answers 403 on every action endpoint without its permission', function (string $method, string $uri, string $permission): void {
        $order = Order::factory()->create(['status' => 'manual_review']);
        $gateway = PaymentGateway::factory()->create();
        $uri = str_replace(['{order}', '{gateway}'], [$order->public_id, (string) $gateway->id], $uri);

        adminWith(['orders.view', 'payments.view', 'suppliers.view']);

        $this->json($method, $uri, ['reason' => 'Checked by phone', 'ids' => [$order->public_id], 'note' => 'Checked by phone'])->assertForbidden();
    })->with([
        'reject' => ['POST', '/admin/api/orders/{order}/reject', 'orders.review'],
        'approve' => ['POST', '/admin/api/orders/{order}/review', 'orders.review'],
        'reconcile' => ['POST', '/admin/api/orders/{order}/reconcile', 'orders.reconcile'],
        'refund' => ['POST', '/admin/api/orders/{order}/refunds', 'refunds.create'],
        'bulk reconcile' => ['POST', '/admin/api/tables/orders/bulk/reconcile', 'orders.reconcile'],
        'gateway update' => ['PATCH', '/admin/api/payment-gateways/{gateway}', 'payments.manage'],
        'gateway credentials' => ['POST', '/admin/api/payment-gateways/{gateway}/credentials', 'payments.credentials'],
        'payments reconcile' => ['POST', '/admin/api/payments/reconcile', 'payments.manage'],
        'bulk resolve' => ['POST', '/admin/api/tables/reconciliation/bulk/resolve', 'payments.manage'],
    ]);
});
