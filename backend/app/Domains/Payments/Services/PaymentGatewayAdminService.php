<?php

namespace App\Domains\Payments\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Gateway configuration for the admin payments screens (M14, spec Phase 9.6). Credentials are
 * write-only: they are replaced as a whole per mode, stored in the encrypted columns, audited
 * by field name only and never returned; reads say only whether a mode is configured. The
 * charge currency is fixed per gateway by owner decision OD-1 (`default_currency`) and is not
 * edited here.
 */
class PaymentGatewayAdminService
{
    public function __construct(private AuditLogger $audit, private PaymentReconciler $reconciler) {}

    /**
     * @return Collection<int, PaymentGateway>
     */
    public function gateways(): Collection
    {
        return PaymentGateway::query()
            ->with(['paymentGatewayCredentials:id,payment_gateway_id,mode,rotated_at', 'paymentGatewayCurrencies'])
            ->withCount(['paymentEligibilityRules as active_rules_count' => static fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function gateway(PaymentGateway $gateway): PaymentGateway
    {
        return $gateway->load([
            'paymentGatewayCredentials:id,payment_gateway_id,mode,rotated_at',
            'paymentGatewayCurrencies',
            'paymentEligibilityRules' => static fn ($query) => $query->orderBy('priority')->orderBy('id'),
        ]);
    }

    /**
     * Configured state per mode, with when the values were last replaced.
     *
     * @return array<string, array{configured: bool, rotated_at: ?string}>
     */
    public function credentialState(PaymentGateway $gateway): array
    {
        $rows = $gateway->relationLoaded('paymentGatewayCredentials')
            ? $gateway->paymentGatewayCredentials
            : $gateway->paymentGatewayCredentials()->get(['id', 'payment_gateway_id', 'mode', 'rotated_at']);
        $state = [];

        foreach (EnvironmentMode::cases() as $mode) {
            $row = $rows->first(static fn (PaymentGatewayCredential $credential): bool => $credential->mode === $mode);
            $state[$mode->value] = ['configured' => $row !== null, 'rotated_at' => $row?->rotated_at?->toIso8601String()];
        }

        return $state;
    }

    /**
     * @param  array{is_active?: bool, mode?: string}  $changes
     */
    public function update(PaymentGateway $gateway, array $changes): PaymentGateway
    {
        return DB::transaction(function () use ($gateway, $changes): PaymentGateway {
            $locked = PaymentGateway::query()->lockForUpdate()->findOrFail($gateway->id);
            $before = ['is_active' => $locked->is_active, 'mode' => $locked->mode->value];

            if (isset($changes['mode'])) {
                $locked->mode = EnvironmentMode::from($changes['mode']);
            }

            if (array_key_exists('is_active', $changes)) {
                $locked->is_active = (bool) $changes['is_active'];
            }

            if ($locked->is_active && ! PaymentGatewayCredential::query()->where('payment_gateway_id', $locked->id)->where('mode', $locked->mode)->exists()) {
                throw new ApplicationException('Add credentials for this mode before activating the gateway.', 'validation_failed', 422, ['is_active' => [__('admin.gateways.needs_credentials')]]);
            }

            $locked->save();
            $this->audit->record(new AuditEntry('payments.gateway_updated', $locked, before: $before, after: ['is_active' => $locked->is_active, 'mode' => $locked->mode->value]));

            return $locked;
        });
    }

    /**
     * Replace a mode's credentials. Only the field names are audited.
     *
     * @param  array<string, string>  $credentials
     */
    public function setCredentials(PaymentGateway $gateway, EnvironmentMode $mode, array $credentials, ?string $webhookSecret): void
    {
        DB::transaction(function () use ($gateway, $mode, $credentials, $webhookSecret): void {
            $row = PaymentGatewayCredential::query()->where('payment_gateway_id', $gateway->id)->where('mode', $mode)->lockForUpdate()->first()
                ?? (new PaymentGatewayCredential)->forceFill(['payment_gateway_id' => $gateway->id, 'mode' => $mode]);

            $row->forceFill(['credentials' => $credentials, 'webhook_secret' => $webhookSecret, 'rotated_at' => now()])->save();

            $this->audit->record(new AuditEntry('payments.credentials_set', $gateway, after: [
                'mode' => $mode->value,
                'keys' => array_keys($credentials),
                'webhook_secret' => $webhookSecret !== null,
            ]));
        });
    }

    public function setRuleActive(PaymentEligibilityRule $rule, bool $active): PaymentEligibilityRule
    {
        $before = $rule->is_active;
        $rule->forceFill(['is_active' => $active])->save();

        $this->audit->record(new AuditEntry('payments.eligibility_rule_toggled', $rule, before: ['is_active' => $before], after: ['is_active' => $active]));

        return $rule;
    }

    /**
     * Create or edit an eligibility rule of a gateway (M15). Lists of countries and currencies
     * are stored upper-case; an empty list means "any". Audited with before and after.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveRule(PaymentGateway $gateway, ?PaymentEligibilityRule $rule, array $data): PaymentEligibilityRule
    {
        foreach (['countries', 'ip_countries', 'currencies'] as $list) {
            if (array_key_exists($list, $data)) {
                $values = array_values(array_unique(array_map('strtoupper', (array) ($data[$list] ?? []))));
                $data[$list] = $values === [] ? null : $values;
            }
        }

        return DB::transaction(function () use ($gateway, $rule, $data): PaymentEligibilityRule {
            $rule ??= new PaymentEligibilityRule(['payment_gateway_id' => $gateway->id]);
            $before = $rule->exists ? Arr::except($rule->attributesToArray(), ['created_at', 'updated_at']) : null;

            $rule->fill(Arr::except($data, ['payment_gateway_id']))->save();

            $this->audit->record(new AuditEntry($before === null ? 'payments.eligibility_rule_created' : 'payments.eligibility_rule_updated', $rule, $before, Arr::except($rule->attributesToArray(), ['created_at', 'updated_at'])));

            return $rule;
        });
    }

    public function deleteRule(PaymentEligibilityRule $rule): void
    {
        DB::transaction(function () use ($rule): void {
            $this->audit->record(new AuditEntry('payments.eligibility_rule_deleted', $rule, Arr::except($rule->attributesToArray(), ['created_at', 'updated_at'])));
            $rule->delete();
        });
    }

    /**
     * Ask the providers now about open attempts, refunds and failed webhooks (the job that runs
     * every five minutes), on demand.
     *
     * @return array{payments: int, expired: int, refunds: int, webhooks: int}
     */
    public function reconcileNow(User $staff): array
    {
        $counts = $this->reconciler->reconcileOpen();
        $this->audit->record(new AuditEntry('payments.reconciled', $staff, after: $counts));

        return $counts;
    }

    /**
     * Close reconciliation differences as resolved or ignored, with the staff member's note.
     *
     * @param  list<int>  $ids
     * @return int items changed (already closed ones are skipped)
     */
    public function resolveItems(array $ids, ReconciliationItemStatus $status, string $note, User $staff): int
    {
        return DB::transaction(function () use ($ids, $status, $note, $staff): int {
            $items = ReconciliationItem::query()->whereKey($ids)->where('status', ReconciliationItemStatus::Open)->lockForUpdate()->get();

            foreach ($items as $item) {
                $item->forceFill(['status' => $status, 'resolution_note' => $note, 'resolved_by' => $staff->id])->save();
                $this->audit->record(new AuditEntry('payments.reconciliation_item_'.$status->value, $item, reason: $note));
            }

            return $items->count();
        });
    }
}
