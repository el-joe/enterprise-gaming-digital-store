<?php

namespace App\Domains\Settings\Services;

use App\DataTables\AdminDataTable;
use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Models\AdminTableState;
use App\Domains\Settings\Models\SavedFilter;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;

/**
 * A staff member's own table layout and saved filters (M14). Rows always belong to the caller:
 * another member's saved filter is not found. Changes are audited like every admin write.
 */
class AdminTablePreferences
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * @return array{hidden_columns: list<string>|null, page_length: int, saved_filters: list<array{id: int, name: string, payload: array<string, mixed>, is_default: bool}>}
     */
    public function for(User $user, AdminDataTable $table): array
    {
        $state = AdminTableState::query()->where('user_id', $user->id)->where('table_key', $table::key())->first();

        return [
            'hidden_columns' => $state?->hidden_columns,
            'page_length' => $state?->page_length ?? (int) config('admin.tables.default_page_length'),
            'saved_filters' => SavedFilter::query()->where('user_id', $user->id)->where('table_key', $table::key())->orderBy('name')->get()
                ->map(static fn (SavedFilter $filter): array => ['id' => $filter->id, 'name' => $filter->name, 'payload' => $filter->payload, 'is_default' => $filter->is_default])
                ->all(),
        ];
    }

    /**
     * @param  list<string>  $hiddenColumns  keys of the table's columns
     */
    public function saveState(User $user, AdminDataTable $table, array $hiddenColumns, int $pageLength): AdminTableState
    {
        $known = array_map(static fn ($column): string => $column->key, $table->columns());
        $hidden = array_values(array_intersect($known, $hiddenColumns));

        $state = AdminTableState::query()->firstOrNew(['user_id' => $user->id, 'table_key' => $table::key()]);
        $state->forceFill(['user_id' => $user->id, 'hidden_columns' => $hidden, 'page_length' => $pageLength])->save();

        $this->audit->record(new AuditEntry('admin.table_state_saved', $state, after: ['table' => $table::key(), 'hidden_columns' => $hidden, 'page_length' => $pageLength]));

        return $state;
    }

    /**
     * Store a named filter set (replacing one of the same name). Filter values are checked
     * against the table's declared filters first.
     *
     * @param  array{search?: string|null, filters?: array<string, mixed>, order?: list<array{column: int, dir: string}>, hidden_columns?: list<string>}  $payload
     */
    public function saveFilter(User $user, AdminDataTable $table, string $name, array $payload, bool $isDefault): SavedFilter
    {
        $clean = [
            'search' => (string) ($payload['search'] ?? ''),
            'filters' => $table->validatedFilters($payload['filters'] ?? []),
            'order' => array_values($payload['order'] ?? []),
            'hidden_columns' => array_values(array_intersect(array_map(static fn ($column): string => $column->key, $table->columns()), $payload['hidden_columns'] ?? [])),
        ];

        return DB::transaction(function () use ($user, $table, $name, $clean, $isDefault): SavedFilter {
            $existing = SavedFilter::query()->where('user_id', $user->id)->where('table_key', $table::key())->lockForUpdate()->get();

            if ($existing->firstWhere('name', $name) === null && $existing->count() >= (int) config('admin.tables.saved_filters_per_table')) {
                throw new ApplicationException('Too many saved filters for this table. Delete one first.', 'saved_filter_limit', 422, ['name' => [__('admin.table.saved_limit')]]);
            }

            if ($isDefault) {
                SavedFilter::query()->where('user_id', $user->id)->where('table_key', $table::key())->update(['is_default' => false]);
            }

            $filter = $existing->firstWhere('name', $name) ?? new SavedFilter;
            $filter->forceFill(['user_id' => $user->id, 'table_key' => $table::key(), 'name' => $name, 'payload' => $clean, 'is_default' => $isDefault])->save();

            $this->audit->record(new AuditEntry('admin.saved_filter_saved', $filter, after: ['table' => $table::key(), 'name' => $name]));

            return $filter;
        });
    }

    public function deleteFilter(User $user, AdminDataTable $table, int $id): void
    {
        $filter = SavedFilter::query()->where('user_id', $user->id)->where('table_key', $table::key())->whereKey($id)->first()
            ?? throw new ApplicationException('The requested resource was not found.', 'not_found', 404);

        $filter->delete();

        $this->audit->record(new AuditEntry('admin.saved_filter_deleted', $filter, before: ['table' => $table::key(), 'name' => $filter->name]));
    }
}
