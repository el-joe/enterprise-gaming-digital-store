<?php

/**
 * The committed contract fixtures (docs/api-fixtures) are read by the app's tests and live in the
 * repository: they must hold no secret, no real code and no money as a JSON number.
 */
beforeEach(function (): void {
    if (filter_var(env('UPDATE_API_FIXTURES', false), FILTER_VALIDATE_BOOLEAN)) {
        $this->markTestSkipped('Fixtures are being rewritten; run the suite again without UPDATE_API_FIXTURES to check them.');
    }
});

/**
 * @return list<string>
 */
function contractFixtureFiles(): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(contractFixtureRoot(), FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'json') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

/**
 * Every value under the given key names, anywhere in the document.
 *
 * @param  list<string>  $keys
 * @return list<mixed>
 */
function contractValuesOf(mixed $node, array $keys): array
{
    $found = [];

    if (is_array($node)) {
        foreach ($node as $key => $child) {
            if (is_string($key) && in_array($key, $keys, true)) {
                $found[] = $child;
            }
            $found = [...$found, ...contractValuesOf($child, $keys)];
        }
    }

    return $found;
}

it('has fixtures for every area the app uses', function (): void {
    $areas = array_map('basename', glob(contractFixtureRoot().'/*', GLOB_ONLYDIR));

    expect($areas)->toEqualCanonicalizing(['account', 'app_config', 'auth', 'cart', 'catalog', 'checkout', 'code_reveal', 'guest_orders', 'home', 'notifications', 'orders', 'payments', 'product', 'reviews', 'search', 'security', 'support', 'wallet'])
        ->and(count(contractFixtureFiles()))->toBeGreaterThan(100);
});

it('holds no secret key material, provider secrets or bearer values', function (): void {
    $forbidden = [
        '/(?<![A-Za-z])sk_(live|test)_/', '/(?<![A-Za-z])rk_(live|test)_/', '/whsec_/', '/pk_live_/', '/egy_sk_/', '/egy_csk_(?!test_fixture)/',
        '/paypal-client-secret/', '/paymob-api-key/', '/BEGIN [A-Z ]*PRIVATE KEY/', '/"(password|current_password)"\s*:\s*"/', '/\b\d+\|[A-Za-z0-9]{20,}/',
        '/JBSWY3DPEHPK3PXP/', '/GIFT-[A-Z0-9-]+/',
    ];

    foreach (contractFixtureFiles() as $file) {
        $content = (string) file_get_contents($file);

        foreach ($forbidden as $pattern) {
            expect(preg_match($pattern, $content))->toBe(0, "{$file} matches {$pattern}");
        }

        $document = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        foreach (contractValuesOf($document, ['access_token', 'refresh_token', 'token', 'cart_token', 'client_secret', 'secret', 'x-cart-token']) as $value) {
            expect($value === null || (is_string($value) && str_starts_with($value, 'fixture-')))->toBeTrue("{$file} holds a token value");
        }
        foreach (contractValuesOf($document, ['recovery_codes']) as $codes) {
            expect($codes)->each->toStartWith('fixture-recovery-code-');
        }
    }
});

it('reveals only the canary code', function (): void {
    foreach (contractFixtureFiles() as $file) {
        $document = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        if (str_contains($document['request']['path'], '/reveal') && $document['status'] === 200) {
            expect($document['body']['data']['code'])->toBe(CONTRACT_CANARY_CODE)
                ->and($document['body']['data']['pin'])->toBe('FIXTURE-PIN-0000');
        }
    }
});

it('sends money as strings, never as JSON numbers', function (): void {
    foreach (contractFixtureFiles() as $file) {
        $document = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        foreach (contractValuesOf($document, ['amount']) as $amount) {
            expect($amount === null || is_string($amount) || is_array($amount))->toBeTrue("{$file} has a non-string amount");
        }
    }
});
