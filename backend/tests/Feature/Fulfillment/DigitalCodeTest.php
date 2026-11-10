<?php

/**
 * M9: vault, keyring, stock import and allocation, and the audited reveal flows.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyMissing;
use App\Domains\Fulfillment\Exceptions\DuplicateDigitalCode;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Fulfillment\Services\DigitalCodeKeyring;
use App\Domains\Fulfillment\Services\DigitalCodeKeyRotator;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Fulfillment\Services\StockAllocator;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Notifications\GuestOrderAccessCode;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Services\GuestOrderAccessService;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Http\Controllers\Account\ConfirmPasswordController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

function vault(): DigitalCodeVault
{
    return app(DigitalCodeVault::class);
}

/**
 * A delivered unit with a code in the vault, on a paid order owned by `$user` (or a guest).
 *
 * @return array{0: Order, 1: OrderItemUnit, 2: DigitalCode}
 */
function deliveredCode(?User $user, string $code = 'GIFT-AAAA-BBBB-1234', Unit $status = Unit::Delivered): array
{
    $order = paidOrderFor(sellableVariant('9'), orderAttributes: [
        'user_id' => $user?->id,
        'guest_email' => $user === null ? 'guest@example.com' : null,
        'guest_email_verified_at' => $user === null ? now() : null,
    ]);
    $unit = OrderItemUnit::query()->where('order_id', $order->id)->sole();
    $unit->forceFill(['status' => $status])->save();

    return [$order, $unit, vault()->store(new DeliverableItem($code, '9876', 'https://redeem.example.com/x'), DigitalCodeSource::Supplier, ['order_item_unit_id' => $unit->id])];
}

function revealUrl(Order $order, OrderItemUnit $unit): string
{
    return "/api/v1/account/orders/{$order->public_id}/units/{$unit->public_id}/reveal";
}

describe('vault and keyring', function (): void {
    it('encrypts code, PIN and URL separately and masks all but the last four', function (): void {
        $code = vault()->store(new DeliverableItem('ABCD-EFGH-9999', '1234', 'https://r.example.com'), DigitalCodeSource::Stock);
        $row = DB::table('digital_codes')->where('id', $code->id)->first();

        expect($row->code_ciphertext)->not->toContain('ABCD')
            ->and($row->pin_ciphertext)->not->toContain('1234')
            ->and($row->code_ciphertext)->not->toBe($row->pin_ciphertext)
            ->and($row->masked_code)->toBe('****9999')
            ->and($row->key_version)->toBe(1)
            ->and($code->toArray())->not->toHaveKeys(['code_ciphertext', 'pin_ciphertext', 'redemption_url_ciphertext', 'code_hash'])
            ->and(vault()->decrypt($code)->code)->toBe('ABCD-EFGH-9999');
    });

    it('produces different ciphertext per key version and rotation re-encrypts every row', function (): void {
        $code = vault()->store(new DeliverableItem('ROTATE-ME-0001'), DigitalCodeSource::Stock);
        $before = DB::table('digital_codes')->where('id', $code->id)->value('code_ciphertext');

        config(['fulfillment.active_kid' => '2']);
        app()->forgetInstance(DigitalCodeKeyring::class);
        $rotated = app(DigitalCodeKeyRotator::class)->rotate();

        $code->refresh();
        expect($rotated)->toBe(1)
            ->and($code->key_version)->toBe(2)
            ->and(DB::table('digital_codes')->where('id', $code->id)->value('code_ciphertext'))->not->toBe($before)
            ->and(app(DigitalCodeVault::class)->decrypt($code)->code)->toBe('ROTATE-ME-0001')
            ->and(app(DigitalCodeKeyRotator::class)->rotate())->toBe(0);
    });

    it('rejects a duplicate code regardless of case, spaces and hyphens', function (): void {
        vault()->store(new DeliverableItem('abcd-1234-efgh'), DigitalCodeSource::Stock);

        expect(fn () => vault()->store(new DeliverableItem('ABCD 1234 EFGH'), DigitalCodeSource::Stock))->toThrow(DuplicateDigitalCode::class)
            ->and(DigitalCode::query()->count())->toBe(1);
    });

    it('refuses to store a code without a configured keyring', function (): void {
        config(['fulfillment.code_keys' => null]);
        app()->forgetInstance(DigitalCodeKeyring::class);

        expect(fn () => app(DigitalCodeVault::class)->store(new DeliverableItem('NO-KEY-0001'), DigitalCodeSource::Stock))
            ->toThrow(DigitalCodeKeyMissing::class);
    });

    it('refuses to serialize code material', function (): void {
        expect(fn () => serialize(new DeliverableItem('SECRET')))->toThrow(LogicException::class)
            ->and(print_r(new DeliverableItem('SECRET-123'), true))->not->toContain('SECRET-123');
    });
});

describe('stock', function (): void {
    it('imports codes, rejecting duplicates by position without echoing them', function (): void {
        $variant = sellableVariant('9');
        vault()->store(new DeliverableItem('EXISTING-0001'), DigitalCodeSource::Stock, ['product_variant_id' => $variant->id, 'stock_status' => DigitalCodeStockStatus::Available]);
        actingAsStaffWith(['digital_codes.import']);

        $response = $this->postJson('/admin/api/digital-codes/import', ['product_variant_id' => $variant->id, 'codes' => [
            ['code' => 'NEW-CODE-0001', 'pin' => '11'],
            ['code' => 'existing-0001'],
        ]])->assertCreated()->assertJsonPath('data.imported', 1)->assertJsonPath('data.rejected.0', ['index' => 1, 'reason' => 'duplicate_code']);

        expect($response->getContent())->not->toContain('NEW-CODE-0001')->not->toContain('EXISTING')
            ->and(AuditLog::query()->where('action', 'digital_codes.imported')->exists())->toBeTrue();
    });

    it('requires the import permission', function (): void {
        actingAsStaffWith([]);

        $this->postJson('/admin/api/digital-codes/import', ['product_variant_id' => 1, 'codes' => [['code' => 'X-0001']]])->assertForbidden();
    });

    it('allocates with SKIP LOCKED: a code locked by another connection is passed over', function (): void {
        commitTestTransaction();
        config(['database.connections.mysql_second' => config('database.connections.mysql')]);

        try {
            $variant = sellableVariant('9');
            $first = vault()->store(new DeliverableItem('SKIP-0001'), DigitalCodeSource::Stock, ['product_variant_id' => $variant->id, 'stock_status' => DigitalCodeStockStatus::Available]);
            $second = vault()->store(new DeliverableItem('SKIP-0002'), DigitalCodeSource::Stock, ['product_variant_id' => $variant->id, 'stock_status' => DigitalCodeStockStatus::Available]);

            $other = DB::connection('mysql_second');
            $other->beginTransaction();
            $other->table('digital_codes')->where('id', $first->id)->lockForUpdate()->first();

            $reserved = app(StockAllocator::class)->reserve($variant->id, SupplierTransaction::factory()->create()->id);
            $other->rollBack();

            expect($reserved->id)->toBe($second->id)
                ->and($first->fresh()->stock_status)->toBe(DigitalCodeStockStatus::Available);
        } finally {
            DB::purge('mysql_second');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach (['digital_codes', 'supplier_transactions', 'order_item_units', 'order_items', 'orders', 'suppliers', 'product_variants', 'products', 'categories', 'gaming_platforms', 'currencies', 'countries'] as $table) {
                DB::table($table)->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            reopenTestTransaction();
        }
    });
});

describe('customer reveal', function (): void {
    beforeEach(function (): void {
        $this->customer = User::factory()->create(['email_verified_at' => now()]);
    });

    it('reveals to the owner after a recent password confirmation, uncached and audited', function (): void {
        [$order, $unit] = deliveredCode($this->customer);

        $this->actingAs($this->customer)->withSession([ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()])
            ->postJson(revealUrl($order, $unit))
            ->assertOk()
            ->assertJsonPath('data.code', 'GIFT-AAAA-BBBB-1234')
            ->assertJsonPath('data.pin', '9876')
            ->assertHeader('Cache-Control', 'no-store, private');

        expect(DigitalCodeAccess::query()->sole()->granted)->toBeTrue()
            ->and($unit->fresh()->first_revealed_at)->not->toBeNull()
            ->and(AuditLog::query()->where('action', 'digital_codes.revealed')->count())->toBe(1);
    });

    it('confirms the password through the account API', function (): void {
        $user = User::factory()->create(['password' => 'correct-horse-battery', 'email_verified_at' => now()]);

        $this->actingAs($user)->postJson('/api/v1/account/confirm-password', ['password' => 'wrong'])->assertUnprocessable();
        $this->actingAs($user)->postJson('/api/v1/account/confirm-password', ['password' => 'correct-horse-battery'])->assertOk()->assertSessionHas(ConfirmPasswordController::SESSION_KEY);
    });

    it('denies and audits every failed attempt', function (Closure $arrange, int $status, string $reason): void {
        [$order, $unit, $code, $user, $session] = $arrange($this->customer);

        $response = $this->actingAs($user)->withSession($session)->postJson(revealUrl($order, $unit))->assertStatus($status);

        expect($response->getContent())->not->toContain('GIFT-AAAA')
            ->and(AuditLog::query()->where('action', 'digital_codes.reveal_denied')->sole()->after['denial_reason'])->toBe($reason)
            ->and(DigitalCodeAccess::query()->where('granted', false)->where('denial_reason', $reason)->count())->toBe($code === null ? 0 : 1)
            ->and($unit->fresh()->first_revealed_at)->toBeNull();
    })->with([
        'non-owner' => [fn (User $owner): array => [...deliveredCode($owner), User::factory()->create(['email_verified_at' => now()]), [ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()]], 404, 'not_owner'],
        'unpaid order' => [function (User $owner): array {
            [$order, $unit, $code] = deliveredCode($owner);
            $order->forceFill(['paid_at' => null])->save();

            return [$order, $unit, $code, $owner, [ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()]];
        }, 403, 'unpaid'],
        'undelivered unit' => [fn (User $owner): array => [...deliveredCode($owner, status: Unit::SupplierPending), $owner, [ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()]], 403, 'not_delivered'],
        'no recent password confirmation' => [fn (User $owner): array => [...deliveredCode($owner), $owner, [ConfirmPasswordController::SESSION_KEY => now()->subMinutes(16)->getTimestamp()]], 423, 'recent_auth_required'],
        'unverified email' => [function (User $owner): array {
            $owner->forceFill(['email_verified_at' => null])->save();

            return [...deliveredCode($owner), $owner, [ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()]];
        }, 403, 'email_unverified'],
    ]);

    it('rate limits reveals to 10 per hour', function (): void {
        [$order, $unit] = deliveredCode($this->customer);
        $request = $this->actingAs($this->customer)->withSession([ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()]);

        foreach (range(1, 10) as $attempt) {
            $request->postJson(revealUrl($order, $unit))->assertOk();
        }

        $request->postJson(revealUrl($order, $unit))->assertTooManyRequests();
    });
});

describe('guest reveal', function (): void {
    beforeEach(function (): void {
        [$this->order, $this->unit] = deliveredCode(null);
        $this->url = "/api/v1/checkout/guest/orders/{$this->order->public_id}/units/{$this->unit->public_id}/reveal";
        $this->link = OrderDelivered::link($this->order);
    });

    it('needs both the signed link and the email one-time code in the session', function (): void {
        $this->postJson($this->url)->assertForbidden();

        $this->getJson($this->link)->assertOk()->assertJsonPath('data.otp_required', true);
        $this->postJson($this->url)->assertForbidden();

        $this->withSession(['guest.order_links' => [$this->order->public_id], 'guest.verified_orders' => [$this->order->public_id]])
            ->postJson($this->url)->assertOk()->assertJsonPath('data.code', 'GIFT-AAAA-BBBB-1234');

        expect(DigitalCodeAccess::query()->where('granted', false)->count())->toBe(2)
            ->and(DigitalCodeAccess::query()->where('granted', true)->sole()->actor_type->value)->toBe('guest');
    });

    it('refuses a tampered or expired link', function (): void {
        $this->getJson($this->link.'x')->assertForbidden();

        $this->travel(73)->hours();
        $this->getJson($this->link)->assertForbidden();
    });

    it('refuses a guest whose email was never verified', function (): void {
        $this->order->forceFill(['guest_email_verified_at' => null])->save();

        $this->withSession(['guest.order_links' => [$this->order->public_id], 'guest.verified_orders' => [$this->order->public_id]])
            ->postJson($this->url)->assertForbidden();
    });

    it('marks the session verified when the one-time code is accepted', function (): void {
        Notification::fake();
        app(GuestOrderAccessService::class)->sendCode($this->order->public_id, 'guest@example.com');
        $code = null;
        Notification::assertSentOnDemand(GuestOrderAccessCode::class, function ($notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });

        $this->postJson('/api/v1/checkout/guest/otp/verify', ['order_public_id' => $this->order->public_id, 'code' => $code])
            ->assertOk()->assertSessionHas('guest.verified_orders', [$this->order->public_id]);
    });
});

describe('staff reveal', function (): void {
    it('needs the explicit permission and a reason, and is audited', function (): void {
        [, , $code] = deliveredCode(User::factory()->create());

        actingAsStaffWith(['orders.view']);
        $this->postJson("/admin/api/digital-codes/{$code->public_id}/reveal", ['reason' => 'Customer dispute check'])->assertForbidden();

        actingAsStaffWith(['digital_codes.reveal']);
        $this->postJson("/admin/api/digital-codes/{$code->public_id}/reveal", [])->assertUnprocessable();
        $this->postJson("/admin/api/digital-codes/{$code->public_id}/reveal", ['reason' => 'Customer dispute check'])
            ->assertOk()->assertJsonPath('data.code', 'GIFT-AAAA-BBBB-1234');

        expect(DigitalCodeAccess::query()->sole()->reason)->toBe('Customer dispute check');
    });
});

it('sends delivery emails with a link and never the code', function (): void {
    $customer = User::factory()->create();
    [$order] = deliveredCode($customer, 'NEVER-IN-MAIL-4321');

    $mail = (new OrderDelivered($order->id))->toMail($customer);
    $rendered = (string) $mail->render();

    expect($rendered)->not->toContain('NEVER-IN-MAIL')->not->toContain('4321')
        ->and($mail->actionUrl)->toContain($order->public_id);
});
