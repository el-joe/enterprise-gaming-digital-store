<?php

namespace App\Domains\Cms\Models;

use Database\Factories\Cms\FaqGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('faq_groups')]
#[Fillable(['name', 'slug', 'sort_order'])]
#[UseFactory(FaqGroupFactory::class)]
class FaqGroup extends Model
{
    /** @use HasFactory<FaqGroupFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Faq, $this>
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'faq_group_id');
    }
}
