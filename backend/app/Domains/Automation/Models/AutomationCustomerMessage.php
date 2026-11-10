<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\CustomerMessageStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use Database\Factories\Automation\AutomationCustomerMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A secondary-channel message that an n8n customer workflow reports as sent or failed. Holds no
 * message text or contact details.
 */
#[Table('automation_customer_messages')]
#[Fillable(['workflow_id', 'user_id', 'channel', 'category', 'dedupe_key', 'event_id', 'provider_message_id', 'error'])]
#[UseFactory(AutomationCustomerMessageFactory::class)]
class AutomationCustomerMessage extends Model
{
    /** @use HasFactory<AutomationCustomerMessageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'category' => NotificationPreferenceCategory::class,
            'status' => CustomerMessageStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
