<?php

namespace App\Domains\Support\Models;

use App\Domains\Cms\Models\Media;
use Database\Factories\Support\SupportTicketAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('support_ticket_attachments')]
#[Fillable(['support_ticket_message_id', 'media_id'])]
#[UseFactory(SupportTicketAttachmentFactory::class)]
class SupportTicketAttachment extends Model
{
    /** @use HasFactory<SupportTicketAttachmentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<SupportTicketMessage, $this>
     */
    public function supportTicketMessage(): BelongsTo
    {
        return $this->belongsTo(SupportTicketMessage::class, 'support_ticket_message_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
