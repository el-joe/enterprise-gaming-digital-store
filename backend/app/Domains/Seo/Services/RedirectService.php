<?php

namespace App\Domains\Seo\Services;

use App\Domains\Seo\Models\Redirect;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @extends CrudService<Redirect>
 */
class RedirectService extends CrudService
{
    public static function hash(string $path): string
    {
        return hash('sha256', self::normalize($path));
    }

    public static function normalize(string $path): string
    {
        $normalized = '/'.ltrim(strtolower($path), '/');

        return strlen($normalized) > 1 ? rtrim($normalized, '/') : $normalized;
    }

    protected function model(): string
    {
        return Redirect::class;
    }

    protected function auditPrefix(): string
    {
        return 'seo.redirect';
    }

    protected function filters(): array
    {
        return ['status_code' => 'exact', 'is_active' => 'bool'];
    }

    protected function sorts(): array
    {
        return ['id', 'hits', 'created_at'];
    }

    protected function searchColumns(): array
    {
        return ['source_path', 'target_path'];
    }

    /**
     * @param  Redirect  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $state = [...$model->only(['source_path', 'target_path', 'status_code']), ...$attributes];

        if ((int) $state['status_code'] === 410) {
            $attributes['target_path'] = null;
            $state['target_path'] = null;
        } elseif (empty($state['target_path'])) {
            throw ValidationException::withMessages(['target_path' => ['A redirect needs a target path.']]);
        }

        if (array_key_exists('source_path', $attributes)) {
            $attributes['source_path'] = self::normalize($attributes['source_path']);
            $attributes['source_hash'] = self::hash($attributes['source_path']);
        }

        $this->assertNoLoop($model, (string) $state['source_path'], $state['target_path']);

        return $attributes;
    }

    /**
     * A target that is itself a redirect source could create a loop or a long chain.
     */
    private function assertNoLoop(Redirect $redirect, string $source, ?string $target): void
    {
        if ($target === null) {
            return;
        }

        if (self::normalize($source) === self::normalize($target)) {
            throw ValidationException::withMessages(['target_path' => ['A redirect cannot point to itself.']]);
        }

        $chains = Redirect::query()
            ->where('source_hash', self::hash($target))
            ->where('is_active', true)
            ->when($redirect->exists, static fn ($query) => $query->whereKeyNot($redirect->id))
            ->exists();

        if ($chains) {
            throw ValidationException::withMessages(['target_path' => ['The target is already redirected; point to the final destination.']]);
        }
    }
}
