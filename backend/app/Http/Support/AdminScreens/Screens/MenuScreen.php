<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Cms\Models\Menu;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Navigation menus (spec Phase 9.9 "Menus") with their items on the edit page.
 */
class MenuScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'content';
    }

    public static function slug(): string
    {
        return 'menus';
    }

    public static function viewPermission(): string
    {
        return 'content.menus';
    }

    public static function managePermission(): string
    {
        return 'content.menus';
    }

    public static function model(): string
    {
        return Menu::class;
    }

    public static function table(): string
    {
        return 'menus';
    }

    protected function apiPath(): string
    {
        return '/admin/api/menus';
    }

    public function panel(): ?string
    {
        return 'admin.content.menu-items';
    }

    public function panelData(Model $record): array
    {
        return [
            'items' => $record->menuItems()->orderBy('parent_id')->orderBy('sort_order')->get(),
        ];
    }

    public function fields(?Model $record): array
    {
        return [
            'basics' => [
                Field::text('location', $this->l('location'))->required()->max(64)->ltr(),
                Field::text('name', $this->l('name'))->required()->max(120),
            ],
        ];
    }
}
