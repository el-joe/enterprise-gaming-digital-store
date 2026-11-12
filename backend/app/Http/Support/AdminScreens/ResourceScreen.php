<?php

namespace App\Http\Support\AdminScreens;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An admin list-and-edit screen over an existing admin JSON resource (M15). The screen only
 * describes: its section and URL, the permission that opens it and the one that changes it,
 * its server-side table, the JSON route that writes it and the fields of its form. Pages only
 * read; the form posts JSON to the admin API, which validates (Form Request), writes through
 * its Service and audits.
 *
 * Routes (routes/admin_pages.php): `/admin/{section}/{slug}` (list), `/create` (needs the manage
 * permission) and `/{record}` (edit, read-only without the manage permission).
 */
abstract class ResourceScreen
{
    final public function __construct(protected User $viewer) {}

    abstract public static function section(): string;

    abstract public static function slug(): string;

    /**
     * Opens the list and the edit page (Spatie middleware syntax, `a|b` for any of).
     */
    abstract public static function viewPermission(): string;

    /**
     * Creates, saves and deletes.
     */
    abstract public static function managePermission(): string;

    /**
     * @return class-string<Model>
     */
    abstract public static function model(): string;

    /**
     * Key of the server-side table (App\DataTables\AdminTables).
     */
    abstract public static function table(): string;

    /**
     * The admin API collection path, for example `/admin/api/platforms`.
     */
    abstract protected function apiPath(): string;

    /**
     * Form fields. Grouped fields (`group => list<Field>`) become one card each.
     *
     * @return array<string, list<Field>>
     */
    abstract public function fields(?Model $record): array;

    public static function key(): string
    {
        return static::section().'.'.static::slug();
    }

    public static function creatable(): bool
    {
        return true;
    }

    public static function deletable(): bool
    {
        return true;
    }

    /**
     * The `content_translations` type and fields edited in Arabic on the edit page, or null.
     *
     * @return array{type: string, fields: array<string, string>}|null fields: name => input type (text, textarea)
     */
    public function translations(): ?array
    {
        return null;
    }

    /**
     * The `seo_metadata` type edited on the edit page (per locale), or null.
     */
    public function seoType(): ?string
    {
        return null;
    }

    /**
     * A custom list page instead of the server-side table, or null (Storefront v2: the
     * homepage sections page of Figma 116:11200).
     */
    public function indexView(): ?string
    {
        return null;
    }

    /**
     * Data of the custom list page, loaded here so the view never queries.
     *
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [];
    }

    /**
     * Field groups shown as cards in the side column (still part of the one form), for example
     * the category "Storefront placement" card (Storefront v2).
     *
     * @return list<string>
     */
    public function asideGroups(): array
    {
        return [];
    }

    /**
     * A read-only Blade partial under the side-column groups, with asideData(), or null.
     */
    public function asideNote(): ?string
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function asideData(?Model $record): array
    {
        return [];
    }

    /**
     * An extra Blade partial shown under the form of an existing record, or null.
     */
    public function panel(): ?string
    {
        return null;
    }

    /**
     * Data of the panel, loaded here so the view never queries.
     *
     * @return array<string, mixed>
     */
    public function panelData(Model $record): array
    {
        return [];
    }

    public function canManage(): bool
    {
        return $this->viewer->hasAnyPermission(explode('|', static::managePermission()));
    }

    public function find(string $key): Model
    {
        $class = static::model();
        $record = (new $class)->resolveRouteBinding($key);

        return $record instanceof Model ? $record : throw new NotFoundHttpException;
    }

    public function apiUrl(?Model $record = null): string
    {
        return url($this->apiPath().($record === null ? '' : '/'.$record->getRouteKey()));
    }

    /**
     * The method the edit form uses (most resources PATCH; some PUT).
     */
    public function updateMethod(): string
    {
        return 'PATCH';
    }

    public function title(?Model $record): string
    {
        if ($record === null) {
            return (string) __('admin.screens.'.static::slug().'.new');
        }

        foreach (['name', 'title', 'code', 'label', 'question', 'source_path', 'key'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return (string) $record->getKey();
    }

    /**
     * A field label from `admin.fields`.
     */
    protected function l(string $field): string
    {
        return (string) __('admin.fields.'.$field);
    }

    public static function routeName(string $page): string
    {
        return 'admin.'.static::routeSuffix($page);
    }

    /**
     * The route name inside the `admin.` page group.
     */
    public static function routeSuffix(string $page): string
    {
        return static::section().'.'.static::slug().'.'.$page;
    }

    public function indexUrl(): string
    {
        return route(static::routeName('index'));
    }

    public function editUrl(Model $record): string
    {
        return route(static::routeName('edit'), ['record' => $record->getRouteKey()]);
    }
}
