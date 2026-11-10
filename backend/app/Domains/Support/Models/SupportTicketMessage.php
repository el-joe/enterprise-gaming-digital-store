<?php

namespace App\Domains\Support\Models;

use App\Domains\Support\Enums\SupportTicketMessageAuthorType;
use Database\Factories\Support\SupportTicketMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('support_ticket_messages')]
#[Fillable(['support_ticket_id', 'author_type', 'author_id', 'body', 'is_internal'])]
#[UseFactory(SupportTicketMessageFactory::class)]
class SupportTicketMessage extends Model
{
    /** @use HasFactory<SupportTicketMessageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'author_type' => SupportTicketMessageAuthorType::class,
            'author_id' => 'integer',
            'is_internal' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<SupportTicket, $this>
     */
    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * @return HasMany<SupportTicketAttachment, $this>
     */
    public function supportTicketAttachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'support_ticket_message_id');
    }
}
