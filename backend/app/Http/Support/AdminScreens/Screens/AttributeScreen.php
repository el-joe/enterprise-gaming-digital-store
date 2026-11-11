<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Catalog\Enums\AttributeDefinitionType;
use App\Domains\Catalog\Models\AttributeDefinition;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Product attributes (spec Phase 9.2 "Product attributes").
 */
class AttributeScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'catalog';
    }

    public static function slug(): string
    {
        return 'attributes';
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
        return AttributeDefinition::class;
    }

    public static function table(): string
    {
        return 'attributes';
    }

    protected function apiPath(): string
    {
        return '/admin/api/attributes';
    }

    public function fields(?Model $record): array
    {
        return [
            'basics' => [
                Field::text('code', $this->l('code'))->required()->max(64)->ltr(),
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::select('type', $this->l('type'), Options::enum('attribute_type', AttributeDefinitionType::cases()))->required(),
                Field::toggle('is_filterable', $this->l('is_filterable')),
                Field::json('options', $this->l('options'))->nullable()->help(__('admin.help.options_json')),
            ],
        ];
    }
}
