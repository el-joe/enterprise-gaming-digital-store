<?php

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * @return list<string>
 */
function domainNames(): array
{
    return array_map('basename', glob(dirname(__DIR__, 3).'/app/Domains/*', GLOB_ONLYDIR));
}

/**
 * @return list<class-string>
 */
function classesIn(string $relativeGlob): array
{
    $root = dirname(__DIR__, 3);

    return array_map(
        static fn (string $file): string => 'App\\'.str_replace(['/', '.php'], ['\\', ''], substr($file, strlen($root.'/app/'))),
        glob($root.'/'.$relativeGlob) ?: [],
    );
}

arch('Livewire and Inertia are never used')
    ->expect(['Livewire', 'Inertia'])
    ->not->toBeUsed();

arch('debug helpers are not committed')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('controllers do not build queries directly')
    ->expect('App\Http\Controllers')
    ->not->toUse([DB::class, EloquentBuilder::class, QueryBuilder::class]);

it('has no static Eloquent query calls in controllers', function (): void {
    foreach (glob(dirname(__DIR__, 3).'/app/Http/Controllers/{*,*/*,*/*/*}.php', GLOB_BRACE) as $file) {
        expect(file_get_contents($file))->not->toMatch('/\b[A-Z]\w+::(query|where|create|find|all|with)\(/');
    }
});

it('keeps repositories private to their domain', function (string $domain): void {
    $others = array_values(array_filter(domainNames(), static fn (string $other): bool => $other !== $domain));

    expect("App\\Domains\\{$domain}")->not->toUse(array_map(
        static fn (string $other): string => "App\\Domains\\{$other}\\Repositories",
        $others,
    ));
})->with(fn (): array => domainNames());

it('stores enums as string backed PHP enums', function (string $enum): void {
    expect((new ReflectionEnum($enum))->getBackingType()?->getName())->toBe('string');
})->with(fn (): array => classesIn('app/Domains/*/Enums/*.php'));

it('has no float types on value objects or DTOs', function (string $class): void {
    $reflection = new ReflectionClass($class);

    $types = array_map(static fn (ReflectionProperty $property): string => (string) $property->getType(), $reflection->getProperties());

    foreach ($reflection->getMethods() as $method) {
        $types[] = (string) $method->getReturnType();

        foreach ($method->getParameters() as $parameter) {
            $types[] = (string) $parameter->getType();
        }
    }

    expect(implode('|', $types))->not->toContain('float');
})->with(fn (): array => [...classesIn('app/Domains/Shared/ValueObjects/*.php'), ...classesIn('app/Domains/*/DTOs/*.php')]);

it('does not cast to float in money code', function (): void {
    expect(file_get_contents(dirname(__DIR__, 3).'/app/Domains/Shared/ValueObjects/Money.php'))
        ->not->toMatch('/\(float\)|floatval|round\(|number_format/');
});

arch('DTOs are readonly')
    ->expect('App\Domains\Identity\DTOs')
    ->toBeReadonly();

arch('order, checkout and fulfilment code never names a supplier adapter (rule 7)')
    ->expect(['App\\Domains\\Orders', 'App\\Domains\\Fulfillment', 'App\\Domains\\Pricing', 'App\\Http\\Controllers\\Storefront', 'App\\Http\\Controllers\\Account'])
    ->not->toUse('App\\Domains\\Suppliers\\Adapters');

arch('jobs carry ids, never code material')
    ->expect('App\\Domains\\Fulfillment\\Jobs')
    ->not->toUse(['App\\Domains\\Suppliers\\DTOs\\DeliverableItem', 'App\\Domains\\Suppliers\\DTOs\\DeliverablePayload', 'App\\Domains\\Fulfillment\\DTOs\\RevealedCode']);
