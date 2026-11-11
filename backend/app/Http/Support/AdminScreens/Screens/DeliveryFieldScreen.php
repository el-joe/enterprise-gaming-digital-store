<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Required delivery fields such as a player id (spec Phase 9.2), validated at cart and checkout.
 */
class DeliveryFieldScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'catalog';
    }

    public static function slug(): string
    {
        return 'delivery-fields';
    }

    public static function viewPermission(): string
    {
        return 'catalog.view';
    }

    public static function managePermission(): string
    {
        return 'catalog.manage';
    }

    public static function model(): string
    {
        return DeliveryFieldDefinition::class;
    }

    public static function table(): string
    {
        return 'delivery-fields';
    }

    protected function apiPath(): string
    {
        return '/admin/api/delivery-fields';
    }

    public function fields(?Model $record): array
    {
        return [
            'basics' => [
                Field::text('code', $this->l('code'))->required()->max(64)->ltr(),
                Field::text('label', $this->l('label'))->required()->max(120),
                Field::select('input_type', $this->l('input_type'), Options::enum('input_type', DeliveryFieldDefinitionInputType::cases()))->required(),
                Field::text('validation_regex', $this->l('validation_regex'))->nullable()->max(255)->ltr()->help(__('admin.help.regex')),
                Field::text('help_text', $this->l('help_text'))->nullable()->max(255)->wide(),
                Field::json('options', $this->l('options'))->nullable()->help(__('admin.help.options_json')),
            ],
        ];
    }
}
