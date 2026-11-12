<?php

/*
| Settings from the database (owner request 2026-11-12): a generated Google service-account key
| (never a real one) and helpers for the `.env` values the import command reads.
*/

/**
 * A service-account JSON key with a freshly generated RSA key.
 *
 * @return array{json: string, key: OpenSSLAsymmetricKey}
 */
function fakeServiceAccountJson(string $projectId = 'boxcards-test', ?string $clientEmail = null): array
{
    $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
    openssl_pkey_export($key, $pem);

    return ['key' => $key, 'json' => json_encode([
        'type' => 'service_account',
        'project_id' => $projectId,
        'private_key_id' => 'test-key-id',
        'private_key' => $pem,
        'client_email' => $clientEmail ?? 'push-sender@'.$projectId.'.iam.gserviceaccount.com',
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)];
}

/**
 * Set (or with null, unset) environment variables the way a loaded `.env` file does, for the
 * import command. Returns the names so they can be restored with withoutEnvironment().
 *
 * @param  array<string, string|null>  $values
 * @return list<string>
 */
function withEnvironment(array $values): array
{
    foreach ($values as $name => $value) {
        if ($value === null) {
            unset($_ENV[$name], $_SERVER[$name]);
            putenv($name);

            continue;
        }

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv($name.'='.$value);
    }

    return array_keys($values);
}

/**
 * @param  list<string>  $names
 */
function withoutEnvironment(array $names): void
{
    withEnvironment(array_fill_keys($names, null));
}
