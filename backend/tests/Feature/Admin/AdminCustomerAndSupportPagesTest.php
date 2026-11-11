<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Reviews\Models\Review;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketMessage;
use App\Domains\Wallet\Models\CustomerWallet;

/*
| M15: customers (list, detail, block, risk, notes, devices and biometric keys, privacy),
| support tickets with internal notes, refund requests and review moderation.
*/

describe('customers', function (): void {
    it('guards the customer pages and masks contact details without customers.view_pii', function (): void {
        $customer = User::factory()->create(['email' => 'sara.ali@example.com', 'name' => 'Sara Ali']);
        adminWith(['orders.view']);
        $this->get('/admin/customers')->assertForbidden();
        $this->get('/admin/customers/'.$customer->public_id)->assertForbidden();
        postTable('customers')->assertForbidden();

        adminWith(['customers.view']);
        $page = $this->get('/admin/customers/'.$customer->public_id)->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('Sara Ali')->assertDontSee('sara.ali@example.com')->assertDontSee(__('admin.customers.block'));
        assertCspSafeHtml($page);

        expect((string) postTable('customers')->assertOk()->json('data.0.email'))->not->toContain('sara.ali@example.com');
        postTable('customers', ['search' => ['value' => 'sara.ali']])->assertOk()->assertJsonPath('recordsFiltered', 0);

        adminWith(['customers.view', 'customers.view_pii']);
        $this->get('/admin/customers/'.$customer->public_id)->assertSee('sara.ali@example.com');
        postTable('customers', ['search' => ['value' => 'sara.ali']])->assertJsonPath('recordsFiltered', 1);
    });

    it('never lists staff as customers and 404s their detail page', function (): void {
        $staff = User::factory()->staff()->create();
        adminWith(['customers.view']);

        postTable('customers')->assertOk()->assertJsonPath('recordsTotal', 0);
        $this->get('/admin/customers/'.$staff->public_id)->assertNotFound();
    });

    it('renders the detail page in Arabic with the wallet ledger and actions for managers', function (): void {
        $customer = User::factory()->create();
        CustomerWallet::factory()->create(['user_id' => $customer->id]);
        adminWith(['customers.view', 'customers.manage', 'customers.wallet_adjust', 'audit.view']);

        $this->withSession(['admin.locale' => 'ar'])->get('/admin/customers/'.$customer->public_id)->assertOk()
            ->assertSee('dir="rtl"', false)->assertSee('حظر العميل')->assertSee('name="idempotency_key"', false)
            ->assertSee(__('admin.customers.audit', locale: 'ar'))->assertDontSee('|[2,*]', false);

        postTable('customer-wallet')->assertUnprocessable()->assertJsonValidationErrors('filters.customer');
        postTable('customer-wallet', ['filters' => ['customer' => (string) $customer->id]])->assertOk();
    });

    it('blocks a customer with a reason, signs them out and audits; unblocks', function (): void {
        $customer = User::factory()->create();
        adminWith(['customers.view']);
        $this->postJson("/admin/api/customers/{$customer->public_id}/block", ['reason' => 'Chargeback fraud'])->assertForbidden();

        adminWith(['customers.manage']);
        $this->postJson("/admin/api/customers/{$customer->public_id}/block", ['reason' => 'x'])->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->postJson("/admin/api/customers/{$customer->public_id}/block", ['reason' => 'Chargeback fraud'])->assertOk();

        expect($customer->refresh()->status)->toBe(UserStatus::Suspended)
            ->and(AuditLog::query()->where('action', 'customers.blocked')->value('reason'))->toBe('Chargeback fraud');

        $this->postJson("/admin/api/customers/{$customer->public_id}/block", ['reason' => 'Again please'])->assertStatus(409);
        $this->postJson("/admin/api/customers/{$customer->public_id}/unblock", ['reason' => 'Resolved with bank'])->assertOk();
        expect($customer->refresh()->status)->toBe(UserStatus::Active);
    });

    it('refuses customer actions on staff accounts', function (): void {
        $staff = User::factory()->staff()->create();
        adminWith(['customers.manage']);

        $this->postJson("/admin/api/customers/{$staff->public_id}/block", ['reason' => 'Not allowed'])->assertNotFound();
    });

    it('sets the risk level and adds a note, both audited', function (): void {
        $customer = User::factory()->create();
        CustomerProfile::factory()->create(['user_id' => $customer->id, 'risk_level' => 'low']);
        adminWith(['customers.manage']);

        $this->putJson("/admin/api/customers/{$customer->public_id}/risk", ['risk_level' => 'severe', 'reason' => 'Manual review'])->assertUnprocessable()->assertJsonValidationErrors('risk_level');
        $this->putJson("/admin/api/customers/{$customer->public_id}/risk", ['risk_level' => 'high', 'risk_notes' => 'Velocity', 'reason' => 'Manual review'])->assertOk();
        $this->postJson("/admin/api/customers/{$customer->public_id}/notes", ['body' => 'Called about delayed top-up'])->assertCreated();

        expect($customer->customerProfile()->first()->risk_level->value)->toBe('high')
            ->and(AuditLog::query()->whereIn('action', ['customers.risk_level.changed', 'customers.note_added'])->count())->toBe(2);
    });

    it('revokes a biometric key of the customer (OD-1) and refuses another customer\'s key', function (): void {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $key = MobileBiometricKey::factory()->create(['user_id' => $customer->id]);
        $foreign = MobileBiometricKey::factory()->create(['user_id' => $other->id]);
        adminWith(['customers.view', 'customers.manage']);

        $this->get('/admin/customers/'.$customer->public_id)->assertSee('/biometric-keys/'.$key->public_id, false);
        $this->deleteJson("/admin/api/customers/{$customer->public_id}/biometric-keys/{$foreign->public_id}", ['reason' => 'Lost phone'])->assertNotFound();
        $this->deleteJson("/admin/api/customers/{$customer->public_id}/biometric-keys/{$key->public_id}", ['reason' => 'Lost phone'])->assertOk();

        expect($key->refresh()->revoked_at)->not->toBeNull()->and($key->revocation_reason?->value)->toBe('revoked_by_staff')
            ->and($foreign->refresh()->revoked_at)->toBeNull();
    });

    it('answers 404 for an unknown device', function (): void {
        $customer = User::factory()->create();
        adminWith(['customers.manage']);

        $this->deleteJson("/admin/api/customers/{$customer->public_id}/devices/web_unknown", ['reason' => 'Lost laptop'])->assertNotFound();
    });

    it('exports personal data without staff notes or internal ticket notes, audited', function (): void {
        $customer = User::factory()->create(['email' => 'export.me@example.com']);
        $ticket = SupportTicket::factory()->create(['user_id' => $customer->id]);
        SupportTicketMessage::factory()->create(['support_ticket_id' => $ticket->id, 'is_internal' => true, 'body' => 'INTERNAL-ONLY-NOTE']);
        SupportTicketMessage::factory()->create(['support_ticket_id' => $ticket->id, 'is_internal' => false, 'body' => 'Public reply']);
        adminWith(['customers.view']);
        $this->get("/admin/api/customers/{$customer->public_id}/export")->assertForbidden();

        adminWith(['customers.manage']);
        $export = $this->get("/admin/api/customers/{$customer->public_id}/export")->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        expect($export->headers->get('Content-Disposition'))->toContain('attachment')
            ->and($export->getContent())->toContain('export.me@example.com')->toContain('Public reply')->not->toContain('INTERNAL-ONLY-NOTE')
            ->and(AuditLog::query()->where('action', 'customers.data_exported')->exists())->toBeTrue();
    });

    it('erases a customer only with the id typed again and an empty wallet', function (): void {
        $customer = User::factory()->create(['email' => 'erase.me@example.com', 'phone' => '+201000000000']);
        $order = Order::factory()->create(['user_id' => $customer->id, 'contact_email' => 'erase.me@example.com']);
        $wallet = CustomerWallet::factory()->create(['user_id' => $customer->id, 'balance' => '5.000000']);
        adminWith(['customers.manage']);

        $this->postJson("/admin/api/customers/{$customer->public_id}/anonymize", ['reason' => 'GDPR request by email', 'confirm' => 'wrong'])
            ->assertUnprocessable()->assertJsonValidationErrors('confirm');
        $this->postJson("/admin/api/customers/{$customer->public_id}/anonymize", ['reason' => 'GDPR request by email', 'confirm' => $customer->public_id])
            ->assertStatus(409)->assertJsonPath('code', 'wallet_not_empty');

        $wallet->forceFill(['balance' => '0'])->save();
        $this->postJson("/admin/api/customers/{$customer->public_id}/anonymize", ['reason' => 'GDPR request by email', 'confirm' => $customer->public_id])->assertOk();

        $customer->refresh();
        expect($customer->email)->not->toContain('erase.me')->and($customer->phone)->toBeNull()->and($customer->status)->toBe(UserStatus::Closed)
            ->and($order->refresh()->contact_email)->not->toContain('erase.me')
            ->and(Order::query()->whereKey($order->id)->exists())->toBeTrue()
            ->and(AuditLog::query()->where('action', 'customers.anonymized')->value('reason'))->toBe('GDPR request by email');
    });
});

describe('support', function (): void {
    it('shows internal notes to staff only on the thread, marked staff only', function (): void {
        $ticket = SupportTicket::factory()->create(['subject' => 'Top-up missing']);
        SupportTicketMessage::factory()->create(['support_ticket_id' => $ticket->id, 'is_internal' => true, 'author_type' => 'staff', 'body' => 'Velocity flag']);
        adminWith(['orders.view']);
        $this->get('/admin/support/tickets/'.$ticket->public_id)->assertForbidden();
        postTable('tickets')->assertForbidden();

        adminWith(['support.view', 'support.reply', 'support.manage']);
        $page = $this->get('/admin/support/tickets/'.$ticket->public_id)->assertOk()
            ->assertSee('Top-up missing')->assertSee('Velocity flag')->assertSee(__('admin.tickets.internal_badge'))
            ->assertSee('name="is_internal"', false)->assertSee('name="assigned_to"', false);
        assertCspSafeHtml($page);

        postTable('tickets', ['filters' => ['assignee' => 'none']])->assertOk()->assertJsonPath('data.0.DT_RowId', $ticket->public_id);
    });

    it('hides the reply box without support.reply and the status controls without support.manage', function (): void {
        $ticket = SupportTicket::factory()->create();
        adminWith(['support.view']);

        $this->get('/admin/support/tickets/'.$ticket->public_id)->assertOk()
            ->assertDontSee('name="is_internal"', false)->assertDontSee('name="assigned_to"', false);
        $this->postJson('/admin/api/tickets/'.$ticket->public_id.'/messages', ['body' => 'Hi'])->assertForbidden();
    });

    it('lists refund requests and opens one with its units for approval', function (): void {
        $request = RefundRequest::factory()->create();
        adminWith(['support.view']);
        $this->get('/admin/support/refund-requests')->assertForbidden();

        adminWith(['support.refund_requests']);
        postTable('refund-requests')->assertOk()->assertJsonPath('recordsTotal', 1);
        $this->get('/admin/support/refund-requests/'.$request->id)->assertOk()
            ->assertSee(__('admin.refund_requests.reject'))->assertDontSee(__('admin.refund_requests.approve_note'));

        adminWith(['support.refund_requests', 'refunds.create']);
        $this->get('/admin/support/refund-requests/'.$request->id)->assertSee(__('admin.refund_requests.approve_note'));
    });

    it('moderates reviews from the queue with row actions behind catalog.manage', function (): void {
        $review = Review::factory()->create(['status' => 'pending']);
        adminWith(['catalog.view']);
        $this->get('/admin/reviews')->assertForbidden();

        adminWith(['catalog.manage']);
        $this->get('/admin/reviews')->assertOk()->assertSee('data-module="table action"', false);
        $actions = (string) postTable('reviews', ['filters' => ['status' => 'pending']])->assertOk()->json('data.0.actions');

        expect($actions)->toContain(e(url('/admin/api/reviews/'.$review->id.'/approve')))->toContain('data-reason=');
    });
});
