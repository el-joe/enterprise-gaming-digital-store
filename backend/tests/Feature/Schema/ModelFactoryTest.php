<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @return list<class-string<Model>>
 */
function domainModels(): array
{
    $root = dirname(__DIR__, 3);

    return array_map(
        static fn (string $file): string => 'App\\'.str_replace(['/', '.php'], ['\\', ''], substr($file, strlen($root.'/app/'))),
        array_values(array_filter(
            glob($root.'/app/Domains/*/Models/*.php'),
            static fn (string $file): bool => basename($file) !== 'MorphMap.php',
        )),
    );
}

it('creates a valid row and resolves every relation', function (string $class): void {
    /** @var Model $model */
    $model = $class::factory()->create();

    expect($model->exists)->toBeTrue();

    foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $type = $method->getReturnType();

        if ($method->class === $class && $type instanceof ReflectionNamedType && is_subclass_of($type->getName(), Relation::class)) {
            $model->load($method->getName());

            expect($model->relationLoaded($method->getName()))->toBeTrue();
        }
    }
})->with(fn (): array => domainModels());
