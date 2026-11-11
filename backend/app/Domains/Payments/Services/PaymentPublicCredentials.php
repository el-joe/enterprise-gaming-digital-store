<?php

namespace App\Domains\Payments\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;

/**
 * Publishable provider identifiers (M-A4, gap G9): the Stripe publishable key, the PayPal client
 * id and the Paymob public key. They live in the gateway's encrypted credentials for a mode,
 * like the secrets, so a test key can never pair with live secrets. Only the one field named in
 * `payments.public_credentials` is ever read for publication, and it must look public: a value
 * that fails the mode's prefix or looks like a secret is published as null.
 */
class PaymentPublicCredentials
{
    /** Prefixes of provider secrets (Stripe secret, restricted and webhook keys). */
    private const array SECRET_PREFIXES = ['sk_', 'rk_', 'whsec_'];

    private const string FORMAT = '/^[A-Za-z0-9_\-.]{8,255}$/';

    public function __construct(private AuditLogger $audit) {}

    /**
     * Each active gateway's public credential for its current mode, keyed by gateway key: e.g.
     * `['stripe' => ['publishable_key' => 'pk_test_...'], 'wallet' => []]`. A missing or invalid
     * value is null.
     *
     * @return array<string, array<string, string|null>>
     */
    public function forActiveGateways(): array
    {
        $gateways = PaymentGateway::query()
            ->where('is_active', true)
            ->with('paymentGatewayCredentials')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $published = [];

        foreach ($gateways as $gateway) {
            $spec = $this->specFor($gateway);

            if ($spec === null) {
                $published[$gateway->key] = [];

                continue;
            }

            $row = $gateway->paymentGatewayCredentials->first(static fn (PaymentGatewayCredential $credential): bool => $credential->mode === $gateway->mode);
            $value = $row?->credentials[$spec['field']] ?? null;

            $published[$gateway->key] = [$spec['field'] => is_string($value) && $this->isPublishable($value, $spec, $gateway->mode) ? $value : null];
        }

        return $published;
    }

    /**
     * The public credential of a gateway for each mode, for the admin gateway page (M15): only a
     * value that passes the publishable check is returned, so a secret is never shown.
     *
     * @return array{field: string, values: array<string, string|null>}|null null when the gateway has none
     */
    public function forGateway(PaymentGateway $gateway): ?array
    {
        $spec = $this->specFor($gateway);

        if ($spec === null) {
            return null;
        }

        $values = [];
        $rows = $gateway->paymentGatewayCredentials()->get(['id', 'payment_gateway_id', 'mode', 'credentials']);

        foreach (EnvironmentMode::cases() as $mode) {
            $row = $rows->first(static fn (PaymentGatewayCredential $credential): bool => $credential->mode === $mode);
            $value = $row?->credentials[$spec['field']] ?? null;
            $values[$mode->value] = is_string($value) && $this->isPublishable($value, $spec, $mode) ? $value : null;
        }

        return ['field' => $spec['field'], 'values' => $values];
    }

    /**
     * Store (or, with null, remove) a gateway's public credential for a mode. The mode must
     * already have credentials: a public key alone would make the gateway look configured.
     *
     * @throws ApplicationException
     */
    public function set(string $gatewayKey, EnvironmentMode $mode, ?string $value): void
    {
        $gateway = PaymentGateway::query()->where('key', $gatewayKey)->first()
            ?? throw new ApplicationException('Unknown payment gateway.', 'not_found', 404);

        $spec = $this->specFor($gateway)
            ?? throw new ApplicationException("{$gateway->name} has no public credential.", 'validation_failed', 422, ['gateway' => ['This gateway has no public credential.']]);

        if ($value !== null && ! $this->isPublishable($value, $spec, $mode)) {
            $prefix = $spec['prefixes'][$mode->value] ?? null;

            throw new ApplicationException('The value is not a public credential.', 'validation_failed', 422, ['value' => [
                $prefix === null ? 'Give the provider\'s public identifier (8 to 255 letters, digits, dots, dashes or underscores; never a secret key).' : "Give the {$mode->value} key, starting with {$prefix}.",
            ]]);
        }

        DB::transaction(function () use ($gateway, $mode, $value, $spec): void {
            $row = PaymentGatewayCredential::query()
                ->where('payment_gateway_id', $gateway->id)
                ->where('mode', $mode)
                ->lockForUpdate()
                ->first();

            if ($row === null || ! is_array($row->credentials) || $row->credentials === []) {
                throw new ApplicationException("{$gateway->name} has no credentials for {$mode->value} mode yet.", 'conflict', 409);
            }

            $credentials = $row->credentials;
            $before = $credentials[$spec['field']] ?? null;

            if ($value === null) {
                unset($credentials[$spec['field']]);
            } else {
                $credentials[$spec['field']] = $value;
            }

            $row->forceFill(['credentials' => $credentials])->save();

            $this->audit->record(new AuditEntry(
                action: 'payments.public_credential_updated',
                subject: $gateway,
                before: [$spec['field'] => $before, 'mode' => $mode->value],
                after: [$spec['field'] => $value, 'mode' => $mode->value],
            ));
        });
    }

    /**
     * @return array{field: string, prefixes: array<string, string>}|null
     */
    private function specFor(PaymentGateway $gateway): ?array
    {
        $spec = config("payments.public_credentials.{$gateway->adapter_key}");

        return is_array($spec) && is_string($spec['field'] ?? null) ? ['field' => $spec['field'], 'prefixes' => (array) ($spec['prefixes'] ?? [])] : null;
    }

    /**
     * @param  array{field: string, prefixes: array<string, string>}  $spec
     */
    private function isPublishable(string $value, array $spec, EnvironmentMode $mode): bool
    {
        if (preg_match(self::FORMAT, $value) !== 1) {
            return false;
        }

        foreach (self::SECRET_PREFIXES as $secretPrefix) {
            if (str_starts_with(strtolower($value), $secretPrefix)) {
                return false;
            }
        }

        $prefix = $spec['prefixes'][$mode->value] ?? null;

        return $prefix === null || str_starts_with($value, $prefix);
    }
}
