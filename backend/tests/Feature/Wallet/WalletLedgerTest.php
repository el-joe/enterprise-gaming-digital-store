<?php

/**
 * The wallet ledger (architecture 14, BD3), the customer's read-only wallet routes and staff
 * adjustments. In-process; the concurrent cases are in WalletConcurrencyTest.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\AppendOnlyViolation;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Exceptions\WalletOperationRefused;
use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Services\WalletLedger;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
    $this->user = User::factory()->create();
    $this->ledger = app(WalletLedger::class);
});

function walletEntry(User $user, WalletTransactionType $type, string $amount, string $key): WalletEntry
{
    return new WalletEntry($user->id, $type, Money::of($amount, 'USD'), $key);
}

describe('ledger', function (): void {
    it('keeps the balance equal to the sum of entries and records each balance_after', function (): void {
        fundWallet($this->user, '50', 'k1');
        $this->ledger->post(walletEntry($this->user, WalletTransactionType::DebitPurchase, '12.50', 'k2'));
        $this->ledger->post(walletEntry($this->user, WalletTransactionType::CreditAdmin, '0.25', 'k3'));
        $this->ledger->post(walletEntry($this->user, WalletTransactionType::Reversal, '7.75', 'k4'));

        $totals = walletTotals($this->user);
        expect($totals['balance'])->toBe('30.000000')
            ->and($totals['sum'])->toBe('30.000000')
            ->and(WalletTransaction::query()->orderBy('id')->pluck('balance_after')->all())->toBe(['50.000000', '37.500000', '37.750000', '30.000000'])
            ->and(WalletTransaction::query()->orderBy('id')->pluck('amount')->all())->toBe(['50.000000', '-12.500000', '0.250000', '-7.750000'])
            ->and($this->ledger->isConsistent(walletOf($this->user)))->toBeTrue();
    });

    it('creates the wallet in the base currency on the first credit', function (): void {
        expect(walletOf($this->user))->toBeNull();

        fundWallet($this->user, '5');

        expect(walletOf($this->user)->currency)->toBe('USD')
            ->and(walletOf($this->user)->status)->toBe(CustomerWalletStatus::Active);
    });

    it('posts a credit once however often the same key is sent', function (): void {
        $first = fundWallet($this->user, '10', 'refund:abc');
        $second = fundWallet($this->user, '10', 'refund:abc');

        expect($second->id)->toBe($first->id)
            ->and(WalletTransaction::query()->count())->toBe(1)
            ->and(walletTotals($this->user)['balance'])->toBe('10.000000');
    });

    it('refuses a key reused for a different movement', function (): void {
        fundWallet($this->user, '10', 'refund:abc');

        expect(fn () => fundWallet($this->user, '11', 'refund:abc'))->toThrow(WalletOperationRefused::class, 'idempotency key')
            ->and(fn () => $this->ledger->post(walletEntry($this->user, WalletTransactionType::DebitAdmin, '10', 'refund:abc')))->toThrow(WalletOperationRefused::class);
    });

    it('refuses a debit beyond the balance and changes nothing', function (): void {
        fundWallet($this->user, '10');

        try {
            $this->ledger->post(walletEntry($this->user, WalletTransactionType::DebitPurchase, '10.01', 'too-much'));
            $this->fail('Expected a refusal.');
        } catch (WalletOperationRefused $exception) {
            expect($exception->errorCode)->toBe('wallet_insufficient_balance');
        }

        expect(walletTotals($this->user))->toBe(['balance' => '10.000000', 'sum' => '10.000000']);
    });

    it('allows a debit of exactly the balance', function (): void {
        fundWallet($this->user, '10');
        $this->ledger->post(walletEntry($this->user, WalletTransactionType::DebitPurchase, '10', 'all-of-it'));

        expect(walletTotals($this->user)['balance'])->toBe('0.000000');
    });

    it('refuses a debit from a customer who has no wallet', function (): void {
        expect(fn () => $this->ledger->post(walletEntry($this->user, WalletTransactionType::DebitPurchase, '1', 'nothing')))->toThrow(WalletOperationRefused::class)
            ->and(CustomerWallet::query()->count())->toBe(0);
    });

    it('refuses debits from a frozen wallet but still accepts refund credits', function (): void {
        fundWallet($this->user, '10');
        walletOf($this->user)->forceFill(['status' => CustomerWalletStatus::Frozen])->save();

        expect(fn () => $this->ledger->post(walletEntry($this->user, WalletTransactionType::DebitPurchase, '1', 'frozen')))->toThrow(WalletOperationRefused::class, 'frozen');

        fundWallet($this->user, '2');
        expect(walletTotals($this->user)['balance'])->toBe('12.000000');
    });

    it('refuses zero, negative and other-currency amounts', function (Closure $entry): void {
        fundWallet($this->user, '10');

        expect(fn () => $this->ledger->post($entry($this->user)))->toThrow(WalletOperationRefused::class, 'positive');
    })->with([
        'zero' => [fn (User $user) => walletEntry($user, WalletTransactionType::CreditAdmin, '0', 'zero')],
        'negative' => [fn (User $user) => walletEntry($user, WalletTransactionType::CreditAdmin, '-1', 'negative')],
        'other currency' => [fn (User $user) => new WalletEntry($user->id, WalletTransactionType::CreditAdmin, Money::of('1', 'EGP'), 'egp')],
    ]);

    it('has a database check that rejects a negative balance written around the ledger', function (): void {
        fundWallet($this->user, '1');

        expect(fn () => CustomerWallet::query()->where('user_id', $this->user->id)->update(['balance' => '-0.01']))->toThrow(QueryException::class);
    });

    it('keeps ledger rows append-only', function (): void {
        $entry = fundWallet($this->user, '1');

        expect(fn () => $entry->forceFill(['amount' => '100'])->save())->toThrow(AppendOnlyViolation::class);
    });

    it('detects a wallet whose balance no longer matches its ledger', function (): void {
        fundWallet($this->user, '10');
        $other = User::factory()->create();
        fundWallet($other, '5');
        CustomerWallet::query()->where('user_id', $other->id)->update(['balance' => '6']);
        Log::spy();

        expect(Artisan::call('wallet:verify-ledger'))->toBe(1);
        Log::shouldHaveReceived('alert')->once()->with('wallet.ledger_mismatch', ['customer_wallet_id' => walletOf($other)->id]);

        CustomerWallet::query()->where('user_id', $other->id)->update(['balance' => '5']);
        expect(Artisan::call('wallet:verify-ledger'))->toBe(0);
    });
});

describe('customer wallet routes', function (): void {
    it('shows the balance and statement of the signed-in customer only, with no top-up', function (): void {
        fundWallet($this->user, '25', 'mine');
        fundWallet(User::factory()->create(), '99', 'theirs');

        $this->actingAs($this->user)->getJson('/api/v1/account/wallet')->assertOk()
            ->assertJsonPath('data.wallet.balance.amount', '25.00')
            ->assertJsonPath('data.wallet.currency', 'USD')
            ->assertJsonPath('data.wallet.top_up_available', false);

        $this->actingAs($this->user)->getJson('/api/v1/account/wallet/transactions')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.amount', '25.000000')
            ->assertJsonMissingPath('data.0.idempotency_key')
            ->assertJsonMissingPath('data.0.created_by');
    });

    it('shows an empty wallet before the first credit', function (): void {
        $this->actingAs($this->user)->getJson('/api/v1/account/wallet')->assertOk()->assertJsonPath('data.wallet.balance.amount', '0.00');
        $this->actingAs($this->user)->getJson('/api/v1/account/wallet/transactions')->assertOk()->assertJsonCount(0, 'data');
    });

    it('requires a signed-in customer and offers no way to add money', function (): void {
        $this->getJson('/api/v1/account/wallet')->assertUnauthorized();
        $this->actingAs($this->user)->postJson('/api/v1/account/wallet', ['amount' => '10'])->assertStatus(405);
        $this->actingAs($this->user)->postJson('/api/v1/account/wallet/top-up', ['amount' => '10'])->assertNotFound();
    });
});

describe('staff adjustments', function (): void {
    it('credits and debits with a reason, once per Idempotency-Key, and audits it', function (): void {
        $staff = actingAsStaffWith(['customers.wallet_adjust', 'customers.view']);
        $url = "/admin/api/customers/{$this->user->public_id}/wallet/adjustments";

        $this->postJson($url, ['direction' => 'credit', 'amount' => '15.00', 'reason' => 'Goodwill credit'], ['Idempotency-Key' => 'adjust-0001'])
            ->assertCreated()->assertJsonPath('data.wallet.balance.amount', '15.00')->assertJsonPath('data.transaction.type', 'credit_admin');
        $this->postJson($url, ['direction' => 'credit', 'amount' => '15.00', 'reason' => 'Goodwill credit'], ['Idempotency-Key' => 'adjust-0001'])
            ->assertCreated()->assertJsonPath('data.wallet.balance.amount', '15.00');
        $this->postJson($url, ['direction' => 'debit', 'amount' => '5', 'reason' => 'Correction'], ['Idempotency-Key' => 'adjust-0002'])
            ->assertCreated()->assertJsonPath('data.wallet.balance.amount', '10.00');

        expect(WalletTransaction::query()->count())->toBe(2)
            ->and(WalletTransaction::query()->where('type', 'debit_admin')->sole()->created_by)->toBe($staff->id)
            ->and(AuditLog::query()->where('action', 'wallet.adjusted')->count())->toBe(2);

        $this->getJson("/admin/api/customers/{$this->user->public_id}/wallet/transactions")->assertOk()->assertJsonCount(2, 'data');
    });

    it('refuses a debit beyond the balance', function (): void {
        actingAsStaffWith(['customers.wallet_adjust']);
        fundWallet($this->user, '1');

        $this->postJson("/admin/api/customers/{$this->user->public_id}/wallet/adjustments", ['direction' => 'debit', 'amount' => '2', 'reason' => 'Correction'], ['Idempotency-Key' => 'adjust-0003'])
            ->assertUnprocessable()->assertJsonPath('code', 'wallet_insufficient_balance');
    });

    it('validates the body and the Idempotency-Key header', function (array $body, array $headers, string $field): void {
        actingAsStaffWith(['customers.wallet_adjust']);

        $this->postJson("/admin/api/customers/{$this->user->public_id}/wallet/adjustments", $body, $headers)
            ->assertUnprocessable()->assertJsonValidationErrors($field);
    })->with([
        'missing key' => [['direction' => 'credit', 'amount' => '1', 'reason' => 'Because'], [], 'idempotency_key'],
        'number amount' => [['direction' => 'credit', 'amount' => 1, 'reason' => 'Because'], ['Idempotency-Key' => 'adjust-0004'], 'amount'],
        'zero amount' => [['direction' => 'credit', 'amount' => '0', 'reason' => 'Because'], ['Idempotency-Key' => 'adjust-0005'], 'amount'],
        'no reason' => [['direction' => 'credit', 'amount' => '1'], ['Idempotency-Key' => 'adjust-0006'], 'reason'],
        'top up' => [['direction' => 'top_up', 'amount' => '1', 'reason' => 'Because'], ['Idempotency-Key' => 'adjust-0007'], 'direction'],
    ]);

    it('needs customers.wallet_adjust to adjust and customers.view to read', function (): void {
        actingAsStaffWith(['customers.view']);
        $this->postJson("/admin/api/customers/{$this->user->public_id}/wallet/adjustments", ['direction' => 'credit', 'amount' => '1', 'reason' => 'Because'], ['Idempotency-Key' => 'adjust-0008'])->assertForbidden();
        $this->getJson("/admin/api/customers/{$this->user->public_id}/wallet")->assertOk();

        actingAsStaffWith([]);
        $this->getJson("/admin/api/customers/{$this->user->public_id}/wallet")->assertForbidden();
    });

    it('freezes a wallet with a reason, which stops wallet payments', function (): void {
        actingAsStaffWith(['customers.wallet_adjust']);
        fundWallet($this->user, '10');

        $this->postJson("/admin/api/customers/{$this->user->public_id}/wallet/status", ['status' => 'frozen', 'reason' => 'Fraud review'])
            ->assertOk()->assertJsonPath('data.wallet.status', 'frozen');

        expect($this->ledger->spendableBalance($this->user->id))->toBeNull()
            ->and(AuditLog::query()->where('action', 'wallet.status_changed')->exists())->toBeTrue();
    });
});
