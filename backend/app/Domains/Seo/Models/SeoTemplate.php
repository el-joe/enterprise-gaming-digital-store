<?php

namespace App\Domains\Seo\Models;

use App\Domains\Seo\Enums\SeoTemplatePageType;
use Database\Factories\Seo\SeoTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('seo_templates')]
#[Fillable(['page_type', 'locale', 'title_template', 'description_template'])]
#[UseFactory(SeoTemplateFactory::class)]
class SeoTemplate extends Model
{
    /** @use HasFactory<SeoTemplateFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_type' => SeoTemplatePageType::class,
        ];
    }
}
