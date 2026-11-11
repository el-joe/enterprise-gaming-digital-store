<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Models\Faq;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * FAQs (spec Phase 9.9), grouped, optionally on the homepage.
 */
class FaqScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'faqs';
    }

    public static function viewPermission(): string
    {
        return 'content.faq';
    }

    public static function managePermission(): string
    {
        return 'content.faq';
    }

    public static function model(): string
    {
        return Faq::class;
    }

    public static function table(): string
    {
        return 'faqs';
    }

    protected function apiPath(): string
    {
        return '/admin/api/faqs';
    }

    public function translations(): ?array
    {
        return ['type' => 'faq', 'fields' => (array) config('admin.translations.faq.fields')];
    }

    public function fields(?Model $record): array
    {
        return [
            'faq' => [
                Field::select('faq_group_id', $this->l('faq_group_id'), Options::faqGroups())->required(),
                Field::number('sort_order', $this->l('sort_order'))->default(0),
                Field::text('question', $this->l('question'))->required()->max(500)->wide(),
                Field::textarea('answer', $this->l('answer'))->required(),
                Field::toggle('show_on_homepage', $this->l('show_on_homepage')),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
            ],
        ];
    }
}
