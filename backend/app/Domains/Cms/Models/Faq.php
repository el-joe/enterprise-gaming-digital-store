<?php

namespace App\Domains\Cms\Models;

use Database\Factories\Cms\FaqFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('faqs')]
#[Fillable(['faq_group_id', 'question', 'answer', 'show_on_homepage', 'is_active', 'sort_order'])]
#[UseFactory(FaqFactory::class)]
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_on_homepage' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<FaqGroup, $this>
     */
    public function faqGroup(): BelongsTo
    {
        return $this->belongsTo(FaqGroup::class, 'faq_group_id');
    }
}
