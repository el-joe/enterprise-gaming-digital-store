<?php

/**
 * OD-1, owner decision 6 (ADR-040): device-bound biometric re-authentication. A P-256 key pair
 * stands in for the device's secure hardware; the tests sign challenges as the app would.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Audit\Models\SecurityEvent;
use App\Domains\Identity\Enums\BiometricKeyRevocationReason;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function (): void {
    $this->customer = User::factory()->create(['email_verified_at' => now()]);
    $this->access = mobileSignIn($this->customer)['access_token'];
    $this->deviceKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
});

/**
 * Base64 DER SubjectPublicKeyInfo, as Android `PublicKey.getEncoded()` gives it.
 */
function biometricSpki(OpenSSLAsymmetricKey $key): string
{
    $pem = openssl_pkey_get_details($key)['key'];

    return trim(str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n"], '', $pem));
}

/**
 * Base64 of the 65-byte uncompressed point, as iOS `SecKeyCopyExternalRepresentation` gives it.
 */
function biometricRawPoint(OpenSSLAsymmetricKey $key): string
{
    $ec = openssl_pkey_get_details($key)['ec'];

    return base64_encode("\x04".str_pad($ec['x'], 32, "\0", STR_PAD_LEFT).str_pad($ec['y'], 32, "\0", STR_PAD_LEFT));
}

function biometricSign(string $challenge, OpenSSLAsymmetricKey $key): string
{
    openssl_sign($challenge, $signature, $key, OPENSSL_ALGO_SHA256);

    return base64_encode($signature);
}

function enrolBiometric(string $access, OpenSSLAsymmetricKey $key): void
{
    mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $access)->assertOk();
    mobileCall('POST', 'auth/biometric-key', ['public_key' => biometricSpki($key)], $access)->assertCreated();
}

/**
 * Let the password confirmation fall outside the window without expiring the 15-minute token.
 */
function lapseRecentAuth(): void
{
    PersonalAccessToken::query()->update(['password_confirmed_at' => now()->subHour()]);
}

function biometricChallenge(string $access): string
{
    return mobileCall('POST', 'auth/biometric-challenge', token: $access)->assertOk()->json('data.challenge.challenge');
}

describe('registration', function (): void {
    it('needs a recent password confirmation on this device', function (): void {
        mobileCall('POST', 'auth/biometric-key', ['public_key' => biometricSpki($this->deviceKey)], $this->access)
            ->assertStatus(423)->assertJsonPath('code', 'recent_auth_required');

        expect(MobileBiometricKey::query()->exists())->toBeFalse();
    });

    it('registers an Android SPKI key or an iOS raw point, one live key per device, and audits it', function (string $format): void {
        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $this->access)->assertOk();
        $encoded = $format === 'spki' ? biometricSpki($this->deviceKey) : biometricRawPoint($this->deviceKey);

        mobileCall('POST', 'auth/biometric-key', ['public_key' => $encoded], $this->access)
            ->assertCreated()->assertJsonPath('data.biometric_key.algorithm', 'ES256');
        mobileCall('POST', 'auth/biometric-key', ['public_key' => $encoded], $this->access)->assertCreated();

        expect(MobileBiometricKey::query()->whereNull('revoked_at')->count())->toBe(1)
            ->and(MobileBiometricKey::query()->whereNotNull('revoked_at')->sole()->revocation_reason)->toBe(BiometricKeyRevocationReason::Replaced)
            ->and(AuditLog::query()->where('action', 'auth.biometric_key_registered')->count())->toBe(2);
    })->with(['spki', 'raw point']);

    it('refuses a key that is not ECDSA P-256', function (array $options): void {
        mobileCall('POST', 'account/confirm-password', ['password' => 'password'], $this->access)->assertOk();

        mobileCall('POST', 'auth/biometric-key', ['public_key' => biometricSpki(openssl_pkey_new($options))], $this->access)
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed');
    })->with([
        'P-384' => [['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'secp384r1']],
        'RSA' => [['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]],
    ]);

    it('cannot be done with a biometric confirmation, so one key can never enrol another', function (): void {
        enrolBiometric($this->access, $this->deviceKey);
        lapseRecentAuth();

        $challenge = biometricChallenge($this->access);
        mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $this->deviceKey)], $this->access)->assertOk();

        $attacker = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        mobileCall('POST', 'auth/biometric-key', ['public_key' => biometricSpki($attacker)], $this->access)->assertStatus(423);
    });

    it('needs a customer bearer token', function (): void {
        mobileCall('POST', 'auth/biometric-challenge')->assertUnauthorized();
    });
});

it('rate limits challenge and confirm requests per customer', function (): void {
    enrolBiometric($this->access, $this->deviceKey);

    foreach (range(1, 9) as $request) {
        mobileCall('POST', 'auth/biometric-challenge', token: $this->access)->assertOk();
    }

    mobileCall('POST', 'auth/biometric-challenge', token: $this->access)->assertStatus(429);
});

describe('confirmation', function (): void {
    beforeEach(function (): void {
        $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => $this->customer->id]);
        $this->unit = deliveredUnitOf($order);
        enrolBiometric($this->access, $this->deviceKey);
        lapseRecentAuth();
    });

    it('satisfies the reveal re-auth without the password and audits it', function (): void {
        $path = 'account/orders/'.$this->unit->order->public_id.'/units/'.$this->unit->public_id.'/reveal';
        mobileCall('POST', $path, token: $this->access)->assertStatus(423);

        $challenge = biometricChallenge($this->access);
        mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $this->deviceKey)], $this->access)
            ->assertOk()->assertJsonPath('data.confirmed_for_minutes', 15);

        mobileCall('POST', $path, token: $this->access)->assertOk();
        expect(AuditLog::query()->where('action', 'auth.biometric_confirmed')->count())->toBe(1);
    });

    it('uses every challenge once', function (): void {
        $challenge = biometricChallenge($this->access);
        $body = ['challenge' => $challenge, 'signature' => biometricSign($challenge, $this->deviceKey)];

        mobileCall('POST', 'auth/confirm-biometric', $body, $this->access)->assertOk();
        mobileCall('POST', 'auth/confirm-biometric', $body, $this->access)->assertUnprocessable()->assertJsonPath('code', 'biometric_challenge_invalid');
    });

    it('refuses an expired challenge', function (): void {
        $challenge = biometricChallenge($this->access);
        $this->travel(61)->seconds();

        mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $this->deviceKey)], $this->access)
            ->assertUnprocessable()->assertJsonPath('code', 'biometric_challenge_invalid');
    });

    it('binds a challenge to the device and token that asked for it', function (): void {
        $challenge = biometricChallenge($this->access);
        $other = mobileSignIn($this->customer, 'Tablet')['access_token'];

        mobileCall('POST', 'auth/biometric-challenge', token: $other)->assertNotFound()->assertJsonPath('code', 'biometric_not_registered');
        mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $this->deviceKey)], $other)
            ->assertUnprocessable()->assertJsonPath('code', 'biometric_challenge_invalid');
    });

    it('refuses a signature from another key, and revokes the key after five failures', function (): void {
        $wrong = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);

        foreach (range(1, 4) as $attempt) {
            $challenge = biometricChallenge($this->access);
            mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $wrong)], $this->access)
                ->assertUnprocessable()->assertJsonPath('code', 'biometric_failed');
        }

        // Past the per-minute limiter (challenge + confirm count as two requests each).
        $this->travel(1)->minutes();
        $challenge = biometricChallenge($this->access);
        mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $wrong)], $this->access)
            ->assertStatus(423)->assertJsonPath('code', 'biometric_locked');

        expect(MobileBiometricKey::query()->sole()->revocation_reason)->toBe(BiometricKeyRevocationReason::TooManyFailures)
            ->and(SecurityEvent::query()->where('type', 'auth.biometric_failed')->count())->toBe(5);
        mobileCall('POST', 'auth/biometric-challenge', token: $this->access)->assertNotFound();
    });

    it('stops working when turned off, or after a password change', function (string $how): void {
        if ($how === 'turned off') {
            mobileCall('DELETE', 'auth/biometric-key', token: $this->access)->assertOk();
        } else {
            mobileCall('PUT', 'account/password', ['current_password' => 'password', 'password' => 'N3w-Passw0rd!long', 'password_confirmation' => 'N3w-Passw0rd!long'], $this->access)->assertOk();
        }

        mobileCall('POST', 'auth/biometric-challenge', token: $this->access)->assertNotFound()->assertJsonPath('code', 'biometric_not_registered');
    })->with(['turned off', 'password change']);

    it('stops working when the device is signed out from another device', function (): void {
        $challenge = biometricChallenge($this->access);
        $familyId = MobileBiometricKey::query()->sole()->family_id;
        $other = mobileSignIn($this->customer, 'Tablet')['access_token'];

        mobileCall('DELETE', 'account/devices/'.$familyId, token: $other)->assertOk();

        mobileCall('POST', 'auth/confirm-biometric', ['challenge' => $challenge, 'signature' => biometricSign($challenge, $this->deviceKey)], $this->access)->assertUnauthorized();
        expect(OrderItemUnit::query()->whereNotNull('first_revealed_at')->exists())->toBeFalse();
    });
});
