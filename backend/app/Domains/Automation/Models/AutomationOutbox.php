<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AutomationOutboxStatus;
use Database\Factories\Automation\AutomationOutboxFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('automation_outbox')]
#[Fillable(['event_type', 'payload', 'next_attempt_at', 'delivered_at'])]
#[UseFactory(AutomationOutboxFactory::class)]
class AutomationOutbox extends Model
{
    /** @use HasFactory<AutomationOutboxFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the columns that receive a ULID on creation.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['event_id'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => AutomationOutboxStatus::class,
            'attempts' => 'integer',
            'next_attempt_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
