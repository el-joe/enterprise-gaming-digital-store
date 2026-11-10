<?php

namespace App\Domains\Seo\Models;

use Database\Factories\Seo\NotFoundLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('not_found_logs')]
#[Fillable(['path_hash', 'path', 'last_seen_at'])]
#[UseFactory(NotFoundLogFactory::class)]
class NotFoundLog extends Model
{
    /** @use HasFactory<NotFoundLogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hits' => 'integer',
            'last_seen_at' => 'datetime',
        ];
    }
}
