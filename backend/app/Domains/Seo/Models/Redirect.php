<?php

namespace App\Domains\Seo\Models;

use App\Domains\Seo\Services\RedirectResolver;
use Database\Factories\Seo\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('redirects')]
#[Fillable(['source_path', 'source_hash', 'target_path', 'status_code', 'is_active'])]
#[UseFactory(RedirectFactory::class)]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    /**
     * Every change invalidates the cached runtime lookups (M18). Hit counting updates through
     * the query builder and fires no event.
     */
    protected static function booted(): void
    {
        static::saved(static function (): void {
            RedirectResolver::flush();
        });
        static::deleted(static function (): void {
            RedirectResolver::flush();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
