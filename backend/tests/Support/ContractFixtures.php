<?php

/**
 * Contract fixtures for the mobile app (M-A7, gap G11).
 *
 * Each contract test calls a `/api/v1/mobile` route the way the app does and passes the response
 * to contractFixture(). The response is normalized (ULIDs, UUIDs, numeric ids, order numbers,
 * timestamps and tokens replaced by stable placeholders of the same type; money strings and every
 * other value kept exactly), pretty-printed with sorted keys and compared with the committed file
 * `docs/api-fixtures/<area>/<name>.json`. A difference fails the test (contract drift). Run with
 * `UPDATE_API_FIXTURES=1` to rewrite the files after an intended contract change.
 *
 * The Flutter app copies these files (`application/tool/sync_fixtures.sh`) and parses them in its
 * DTO and repository tests.
 */

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Models\Payment;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use Carbon\CarbonImmutable;
use Illuminate\Testing\TestResponse;

/** The fixed clock of every contract test. */
const CONTRACT_NOW = '2026-11-10 12:00:00';

/** Seed for Faker so factory values (names, SKUs, slugs) are identical on every run. */
const CONTRACT_FAKER_SEED = 20261110;

/** Response headers the app reads; every other header is left out of the fixture. */
const CONTRACT_HEADERS = ['cache-control', 'content-language', 'idempotency-replayed', 'retry-after', 'x-cart-token'];

/** The canary code used by the reveal fixtures. It is not a real code and opens nothing. */
const CONTRACT_CANARY_CODE = 'FIXTURE-CODE-0000';

/**
 * Freeze the clock and seed Faker. Call from a beforeEach in every contract test file.
 */
function contractSetUp(): void
{
    test()->travelTo(CarbonImmutable::parse(CONTRACT_NOW, 'UTC'));
    fake()->seed(CONTRACT_FAKER_SEED);
    config(['app.url' => 'https://boxcards.test', 'catalog.media.base_url' => 'https://media.boxcards.test']);
    url()->forceRootUrl('https://boxcards.test');
}

/**
 * The variant most fixtures sell: "Orbit Card", 10 USD, cost 10.
 */
function contractVariant(string $cost = '10'): ProductVariant
{
    return sellableVariant($cost, ['title' => '10 USD', 'sku' => 'ORB-10-US'], ['name' => 'Orbit Card', 'slug' => 'orbit-card', 'sku' => 'ORB-CARD']);
}

/**
 * Store the canary code on the order's only unit and mark it delivered.
 */
function contractCanaryUnit(Order $order): OrderItemUnit
{
    $unit = OrderItemUnit::query()->where('order_id', $order->id)->sole();
    $unit->forceFill(['status' => OrderItemUnitStatus::Delivered, 'delivered_at' => now()])->save();
    app(DigitalCodeVault::class)->store(new DeliverableItem(CONTRACT_CANARY_CODE, 'FIXTURE-PIN-0000', 'https://redeem.example.com/fixture'), DigitalCodeSource::Supplier, ['order_item_unit_id' => $unit->id]);

    return $unit;
}

/**
 * Give a fixture order the values a real placement stores, so nothing in the fixture depends on
 * Faker: a fixed number, the item's display name and prices, and a payment on the Stripe gateway.
 */
function contractDecorateOrder(Order $order, string $number): Order
{
    $order->forceFill(['order_number' => $number])->save();

    foreach (OrderItem::query()->where('order_id', $order->id)->get() as $item) {
        $item->forceFill([
            'name_snapshot' => 'Orbit Card - 10 USD',
            'pricing_breakdown' => [...($item->pricing_breakdown ?? []), 'display_unit_amount' => '10.00', 'display_line_total' => bcmul('10.00', (string) $item->quantity, 2)],
        ])->save();
    }

    Payment::query()->where('order_id', $order->id)->update(['payment_gateway_id' => gatewayFor('stripe')->id]);

    return $order->fresh();
}

function contractFixtureRoot(): string
{
    return dirname(base_path()).'/docs/api-fixtures';
}

/**
 * Normalize the response, then compare it with (or, with UPDATE_API_FIXTURES=1, write it to)
 * `docs/api-fixtures/{$name}.json`. `$name` is `<area>/<fixture>`.
 */
function contractFixture(string $name, TestResponse $response): TestResponse
{
    expect($name)->toMatch('#^[a-z_]+/[a-z0-9_]+$#');

    $json = contractFixtureJson($response);
    $file = contractFixtureRoot().'/'.$name.'.json';

    if (filter_var(env('UPDATE_API_FIXTURES', false), FILTER_VALIDATE_BOOLEAN)) {
        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        if (! is_file($file) || file_get_contents($file) !== $json) {
            file_put_contents($file, $json);
        }

        return $response;
    }

    expect(is_file($file))->toBeTrue("Missing contract fixture {$name}.json: run the MobileContract tests with UPDATE_API_FIXTURES=1.");
    expect($json)->toBe(file_get_contents($file), "Contract drift in {$name}.json: the response no longer matches the committed fixture. If the change is intended, regenerate with UPDATE_API_FIXTURES=1 and update the app.");

    return $response;
}

/**
 * The normalized fixture document of a response: request method and path, status, the headers
 * the app reads and the JSON body.
 */
function contractFixtureJson(TestResponse $response): string
{
    $normalizer = new ContractNormalizer;
    $request = $response->baseRequest;
    $headers = new stdClass;

    foreach (CONTRACT_HEADERS as $header) {
        if ($response->headers->has($header)) {
            $headers->{$header} = (string) $response->headers->get($header);
        }
    }

    // ULIDs of the path first (so path ids are numbered from the request), then the body, then the
    // numeric path ids, which reuse the placeholders of the same ids in the body.
    $path = $normalizer->strings($request->getPathInfo());
    $body = $normalizer->value(json_decode((string) $response->getContent(), false, 512, JSON_THROW_ON_ERROR), 'body');

    $document = (object) [
        'request' => (object) [
            'method' => $request->getMethod(),
            'path' => $normalizer->path($path, $request->query->all()),
        ],
        'status' => $response->getStatusCode(),
        'headers' => $normalizer->headers($headers),
        'body' => $body,
    ];

    return json_encode(ContractNormalizer::sortKeys($document), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR)."\n";
}

/**
 * Replaces run-dependent values with stable placeholders of the same JSON type.
 */
final class ContractNormalizer
{
    /** Keys whose string value is a secret or bearer value: replaced by `fixture-<key>`. The single-use
     * biometric `challenge` (M-A10) is random per run. */
    private const TOKEN_KEYS = ['access_token', 'refresh_token', 'token', 'cart_token', 'challenge_token', 'client_secret', 'secret', 'x-cart-token', 'challenge'];

    /** Keys whose string value is a human order or ticket number. */
    private const NUMBER_KEYS = ['order_number', 'number', 'ticket_number'];

    /** Every `request_id` (a ULID per request) becomes this one value. */
    private const REQUEST_ID = '01JF00000000000000000000RQ';

    /** Numeric path ids from this value up are test sentinels for unknown records and are kept. */
    private const SENTINEL_ID = 900000;

    private const ULID = '/\b[0-7][0-9A-HJKMNP-TV-Z]{25}\b/i';

    private const UUID = '/\b[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\b/i';

    private const TIMESTAMP = '/^(\d{4}-\d{2}-\d{2})([T ])(\d{2}:\d{2}:\d{2})(\.\d+)?(Z|[+-]\d{2}:?\d{2})?$/';

    /** @var array<string, string> */
    private array $ulids = [];

    /** @var array<string, string> */
    private array $uuids = [];

    /** @var array<string, array<int, int>> */
    private array $ids = [];

    /** @var array<string, string> */
    private array $numbers = [];

    private int $idCount = 0;

    /**
     * Map numeric ids of a path whose ULIDs and UUIDs are already replaced, and add the query.
     *
     * @param  array<string, mixed>  $query
     */
    public function path(string $path, array $query): string
    {
        $path = (string) preg_replace_callback('#/(\d+)(?=/|$)#', function (array $match): string {
            $id = (int) $match[1];

            if ($id >= self::SENTINEL_ID) {
                return $match[0];
            }

            foreach ($this->ids as $mapped) {
                if (isset($mapped[$id])) {
                    return '/'.$mapped[$id];
                }
            }

            $this->ids['path'][$id] = ++$this->idCount;

            return '/'.$this->idCount;
        }, $path);

        if ($query === []) {
            return $path;
        }

        ksort($query);
        foreach (['signature', 'expires'] as $signed) {
            if (array_key_exists($signed, $query)) {
                $query[$signed] = 'fixture-'.$signed;
            }
        }

        return $path.'?'.http_build_query(array_map(fn (mixed $value): mixed => is_string($value) ? $this->strings($value) : $value, $query));
    }

    public function headers(stdClass $headers): stdClass
    {
        if (isset($headers->{'x-cart-token'})) {
            $headers->{'x-cart-token'} = 'fixture-cart-token';
        }

        return $headers;
    }

    public function value(mixed $value, string $path, ?string $key = null): mixed
    {
        if ($value instanceof stdClass) {
            $out = new stdClass;
            foreach (get_object_vars($value) as $childKey => $child) {
                $out->{$childKey} = $this->value($child, $path.'.'.$childKey, (string) $childKey);
            }

            return $out;
        }

        if (is_array($value)) {
            if ($key === 'recovery_codes') {
                return array_map(static fn (mixed $code, int $index): string => 'fixture-recovery-code-'.($index + 1), $value, array_keys($value));
            }

            return array_map(fn (mixed $child): mixed => $this->value($child, $path.'.*', $key), $value);
        }

        if (is_int($value) && $key !== null && ($key === 'id' || str_ends_with($key, '_id'))) {
            $this->ids[$path] ??= [];

            return $this->ids[$path][$value] ??= ++$this->idCount;
        }

        if (! is_string($value) || $key === null) {
            return $value;
        }

        // Inside a validation envelope (`errors.<field>`) the values are messages about the field, not
        // the field's value, so `errors.token` keeps "The token field is required." (M-A18).
        if (in_array($key, self::TOKEN_KEYS, true) && ! str_contains($path, '.errors.')) {
            return 'fixture-'.str_replace('_', '-', $key);
        }

        if ($key === 'request_id') {
            return self::REQUEST_ID;
        }

        if ($key === 'otpauth_uri') {
            return 'otpauth://totp/boxcards.shop:customer%40example.com?secret=fixture-secret&issuer=boxcards.shop';
        }

        if (in_array($key, self::NUMBER_KEYS, true)) {
            return $this->numbers[$value] ??= sprintf('FIXTURE-%04d', count($this->numbers) + 1);
        }

        if (preg_match(self::TIMESTAMP, $value, $parts) === 1) {
            return '2026-11-10'.$parts[2].'12:00:00'.($parts[4] ?? '').($parts[5] ?? '');
        }

        return $this->strings($value);
    }

    /**
     * Replace ULIDs and UUIDs inside any string (ids in URLs included), consistently within one
     * fixture.
     */
    public function strings(string $value): string
    {
        $value = (string) preg_replace_callback(self::ULID, function (array $match): string {
            $placeholder = $this->ulids[strtoupper($match[0])] ??= sprintf('01JF%022d', count($this->ulids) + 1);

            return $match[0] === strtolower($match[0]) ? strtolower($placeholder) : $placeholder;
        }, $value);
        $value = (string) preg_replace_callback(self::UUID, fn (array $match): string => $this->uuids[strtolower($match[0])] ??= sprintf('00000000-0000-4000-8000-%012d', count($this->uuids) + 1), $value);

        return preg_replace('/([?&])(signature|expires)=[^&]*/', '$1$2=fixture-$2', $value) ?? $value;
    }

    /**
     * Sort object keys recursively; list order is kept.
     */
    public static function sortKeys(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            $properties = get_object_vars($value);
            ksort($properties, SORT_STRING);
            $sorted = new stdClass;
            foreach ($properties as $key => $child) {
                $sorted->{$key} = self::sortKeys($child);
            }

            return $sorted;
        }

        if (is_array($value)) {
            return array_map(self::sortKeys(...), $value);
        }

        return $value;
    }
}
