<?php

namespace App\Domains\Settings\Models;

use App\Domains\Identity\Models\User;
use Database\Factories\Settings\AdminTableStateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A staff member's layout of one admin table (M14): hidden columns and page length.
 */
#[Table('admin_table_states')]
#[Fillable(['table_key', 'hidden_columns', 'page_length'])]
#[UseFactory(AdminTableStateFactory::class)]
class AdminTableState extends Model
{
    /** @use HasFactory<AdminTableStateFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hidden_columns' => 'array',
            'page_length' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
