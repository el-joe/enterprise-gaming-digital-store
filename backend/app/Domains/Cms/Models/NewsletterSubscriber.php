<?php

namespace App\Domains\Cms\Models;

use App\Domains\Cms\Enums\NewsletterSubscriberStatus;
use App\Domains\Identity\Models\User;
use Database\Factories\Cms\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('newsletter_subscribers')]
#[Fillable(['email', 'locale', 'confirmation_token_hash', 'confirmed_at', 'unsubscribed_at'])]
#[UseFactory(NewsletterSubscriberFactory::class)]
#[Hidden(['confirmation_token_hash'])]
class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NewsletterSubscriberStatus::class,
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
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
