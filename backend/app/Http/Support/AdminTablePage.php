<?php

namespace App\Http\Support;

use App\DataTables\AdminTables;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Services\AdminTablePreferences;

/**
 * Everything the `<x-admin.table>` component needs for one table (M14): the column and filter
 * definition, the staff member's saved layout and filters, and the JSON endpoints. Context
 * filters (the supplier of a nested table) are fixed here.
 */
class AdminTablePage
{
    public function __construct(private AdminTablePreferences $preferences) {}

    /**
     * @param  array<string, string>  $context
     * @return array<string, mixed>
     */
    public function make(string $key, User $viewer, array $context = []): array
    {
        $table = AdminTables::make($key, $viewer);

        return [
            ...$table->definition(),
            'context' => $context,
            'preferences' => $this->preferences->for($viewer, $table),
            'page_lengths' => (array) config('admin.tables.page_lengths'),
            'urls' => [
                'data' => route("admin.api.tables.{$key}.data"),
                'state' => route("admin.api.tables.{$key}.state"),
                'saved' => route("admin.api.tables.{$key}.saved-filters.store"),
                'saved_delete' => route("admin.api.tables.{$key}.saved-filters.destroy", ['savedFilter' => '__id__']),
                'export' => route("admin.api.tables.{$key}.exports.store"),
                'export_status' => route("admin.api.tables.{$key}.exports.show", ['exportJob' => '__id__']),
            ],
        ];
    }
}
