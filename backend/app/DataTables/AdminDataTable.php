<?php

namespace App\DataTables;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\EloquentDataTable;

/**
 * Base of every admin table (M14, PROJECT_ARCHITECTURE.md section 3: DataTable classes own their
 * list queries). Server-side only, through Yajra:
 *
 * - The client never names a database column. Columns, sort expressions, search columns and
 *   filter values are declared here; the request is rewritten to that allow-list before Yajra
 *   reads it, so column search, regex and unknown parameters are ignored.
 * - Filters and the global search run in Yajra's filter callback, so `recordsTotal` counts the
 *   table's scope and `recordsFiltered` the filtered set. Context filters (the supplier of a log
 *   table) narrow the scope itself.
 * - Only declared columns and the row id leave the server (`only`); HTML columns are built by
 *   {@see Cell} from escaped values, everything else is escaped by Yajra.
 * - The same filtered query feeds the queued export, so an export holds exactly what the table
 *   shows.
 */
abstract class AdminDataTable
{
    protected User $viewer;

    private ?Model $cachedRow = null;

    /** @var list<Filter>|null */
    private ?array $filterCache = null;

    /** @var array<string, string> */
    private array $cachedCells = [];

    final public function __construct(User $viewer)
    {
        $this->viewer = $viewer;
    }

    /**
     * Key in URLs, saved filters, table states and exports.
     */
    abstract public static function key(): string;

    /**
     * The permission (Spatie middleware syntax, `a|b` for any of) that reads this table, exports
     * it and stores its saved filters.
     */
    abstract public static function permission(): string;

    /**
     * @return list<Column>
     */
    abstract public function columns(): array;

    /**
     * @return Builder<covariant Model>
     */
    abstract protected function query(): Builder;

    /**
     * Display values keyed by column key. Values of `html` columns must come from {@see Cell}.
     *
     * @return array<string, string>
     */
    abstract protected function cells(Model $row): array;

    /**
     * @return list<Filter>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Qualified columns matched by the search box (prefix match, so indexes can be used).
     *
     * @return list<string>
     */
    protected function searchColumns(): array
    {
        return [];
    }

    /**
     * @return array{0: string, 1: 'asc'|'desc'} column key and direction
     */
    public function defaultOrder(): array
    {
        return [$this->columns()[0]->key, 'desc'];
    }

    /**
     * Bulk actions offered on selected rows: key, label, permission, the JSON route and whether
     * the action is destructive (danger confirm).
     *
     * @return list<array{key: string, label: string, url: string, danger: bool, confirm: string}>
     */
    public function bulkActions(): array
    {
        return [];
    }

    /**
     * A server-formatted amount (CurrencyConverter::present); the browser never formats money.
     */
    protected function money(mixed $amount, ?string $currency): string
    {
        if ($amount === null || $currency === null || $currency === '') {
            return '—';
        }

        return app(CurrencyConverter::class)->present(Money::of((string) $amount, $currency))['formatted'];
    }

    protected function dateTime(?\DateTimeInterface $value): string
    {
        return $value === null ? '—' : Carbon::instance($value)->timezone((string) config('app.timezone'))->format('Y-m-d H:i');
    }

    public function rowId(Model $row): string
    {
        return (string) $row->getKey();
    }

    /**
     * Plain-text values for the export; by default the display values without markup.
     *
     * @return array<string, string>
     */
    protected function exportCells(Model $row): array
    {
        return array_map(static fn (string $value): string => trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5)), $this->cellsFor($row));
    }

    /**
     * Rows selected by id for "export selected": the column that holds the row id.
     */
    protected function idColumn(): string
    {
        return $this->query()->getModel()->getQualifiedKeyName();
    }

    /**
     * The JSON answer to a DataTables server-side request.
     *
     * @param  array{draw: int, start: int, length: int, search: string, order: list<array{column: int, dir: string}>, filters: array<string, mixed>}  $input
     */
    public function respond(Request $request, array $input): JsonResponse
    {
        $filters = $this->validatedFilters($input['filters']);
        $columns = $this->columns();

        $request->replace([
            'draw' => $input['draw'],
            'start' => $input['start'],
            'length' => $input['length'],
            'search' => ['value' => '', 'regex' => false],
            'columns' => array_map(static fn (Column $column): array => [
                'data' => $column->key,
                'name' => $column->key,
                'searchable' => false,
                'orderable' => $column->orderBy !== null,
                'search' => ['value' => '', 'regex' => false],
            ], $columns),
        ]);

        $table = (new EloquentDataTable($this->scoped($filters)))
            ->filter(function (Builder $query) use ($filters, $input): void {
                $this->applyFilters($query, $filters);
                $this->applySearch($query, $input['search']);
            })
            ->order(function (Builder $query) use ($input): void {
                $this->applyOrder($query, $input['order']);
            })
            ->setRowId(fn (Model $row): string => $this->rowId($row));

        foreach ($columns as $column) {
            $table->addColumn($column->key, fn (Model $row): string => $this->cellsFor($row)[$column->key] ?? '');
        }

        return $table
            ->rawColumns(array_values(array_map(static fn (Column $column): string => $column->key, array_filter($columns, static fn (Column $column): bool => $column->html))))
            ->only([...array_map(static fn (Column $column): string => $column->key, $columns), 'DT_RowId'])
            ->toJson();
    }

    /**
     * The filtered, ordered query an export reads, with the export's own row selection.
     *
     * @param  array{search?: string, filters?: array<string, mixed>, order?: list<array{column: int, dir: string}>, ids?: list<string>}  $input
     * @return Builder<covariant Model>
     */
    public function exportQuery(array $input): Builder
    {
        $filters = $this->validatedFilters($input['filters'] ?? []);
        $query = $this->scoped($filters);
        $this->applyFilters($query, $filters);
        $this->applySearch($query, (string) ($input['search'] ?? ''));

        if (($input['ids'] ?? []) !== []) {
            $query->whereIn($this->idColumn(), $input['ids']);
        }

        $this->applyOrder($query, $input['order'] ?? []);

        return $query;
    }

    /**
     * Export columns: exportable, and not hidden by the staff member.
     *
     * @param  list<string>  $hidden
     * @return list<Column>
     */
    public function exportColumns(array $hidden = []): array
    {
        return array_values(array_filter($this->columns(), static fn (Column $column): bool => $column->exportable && ! in_array($column->key, $hidden, true)));
    }

    /**
     * @param  list<Column>  $columns
     * @return list<string>
     */
    public function exportRow(Model $row, array $columns): array
    {
        $values = $this->exportCells($row);

        return array_map(static fn (Column $column): string => $values[$column->key] ?? '', $columns);
    }

    /**
     * Checked filter values: unknown filters are dropped, a value outside a select's options or
     * an invalid date is a validation error on `filters.<key>`, and a missing context filter is
     * required.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     *
     * @throws ValidationException
     */
    public function validatedFilters(array $values): array
    {
        $clean = [];
        $errors = [];

        foreach ($this->filterList() as $filter) {
            $value = $values[$filter->key] ?? null;
            $value = is_scalar($value) ? trim((string) $value) : null;

            if ($value === null || $value === '') {
                if ($filter->type === 'context') {
                    $errors["filters.{$filter->key}"] = [__('admin.table.filter_required')];
                }

                continue;
            }

            $valid = match ($filter->type) {
                'select' => array_key_exists($value, $filter->options),
                'date' => $this->isDate($value),
                'context' => ctype_digit($value),
                default => false,
            };

            if (! $valid) {
                $errors["filters.{$filter->key}"] = [__('admin.table.filter_invalid')];

                continue;
            }

            $clean[$filter->key] = $value;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $clean;
    }

    /**
     * Columns, filters and bulk actions for the page's table component.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$orderKey, $orderDir] = $this->defaultOrder();
        $columns = $this->columns();

        return [
            'key' => static::key(),
            'columns' => array_map(static fn (Column $column): array => [
                'key' => $column->key,
                'label' => $column->label,
                'orderable' => $column->orderBy !== null,
                'visible' => $column->visible,
                'numeric' => $column->numeric,
            ], $columns),
            'filters' => array_values(array_map(static fn (Filter $filter): array => [
                'key' => $filter->key,
                'label' => $filter->label,
                'type' => $filter->type,
                'options' => $filter->options,
            ], array_filter($this->filterList(), static fn (Filter $filter): bool => $filter->type !== 'context'))),
            'order' => [max(0, (int) array_search($orderKey, array_map(static fn (Column $column): string => $column->key, $columns), true)), $orderDir],
            'bulk' => $this->bulkActions(),
            'searchable' => $this->searchColumns() !== [],
        ];
    }

    /**
     * @return list<Filter>
     */
    private function filterList(): array
    {
        return $this->filterCache ??= $this->filters();
    }

    /**
     * @param  array<string, string>  $filters
     * @return Builder<covariant Model>
     */
    private function scoped(array $filters): Builder
    {
        $query = $this->query();

        foreach ($this->filterList() as $filter) {
            if ($filter->type === 'context' && isset($filters[$filter->key])) {
                ($filter->apply)($query, $filters[$filter->key]);
            }
        }

        return $query;
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @param  array<string, string>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        foreach ($this->filterList() as $filter) {
            if ($filter->type !== 'context' && isset($filters[$filter->key])) {
                ($filter->apply)($query, $filters[$filter->key]);
            }
        }
    }

    /**
     * @param  Builder<covariant Model>  $query
     */
    private function applySearch(Builder $query, string $term): void
    {
        $term = trim(mb_substr($term, 0, 100));
        $columns = $this->searchColumns();

        if ($term === '' || $columns === []) {
            return;
        }

        $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(static function (Builder $where) use ($columns, $like): void {
            foreach ($columns as $column) {
                $where->orWhere($column, 'like', $like);
            }
        });
    }

    /**
     * Sort by the requested sortable columns (at most three), then by the key for a stable page
     * order; the declared default when none was asked for.
     *
     * @param  Builder<covariant Model>  $query
     * @param  list<array{column: int, dir: string}>  $order
     */
    private function applyOrder(Builder $query, array $order): void
    {
        $columns = $this->columns();
        $applied = false;

        foreach (array_slice($order, 0, 3) as $item) {
            $column = $columns[$item['column']] ?? null;

            if ($column?->orderBy === null) {
                continue;
            }

            $query->orderBy($column->orderBy, $item['dir'] === 'asc' ? 'asc' : 'desc');
            $applied = true;
        }

        if (! $applied) {
            [$key, $dir] = $this->defaultOrder();
            $default = Collection::make($columns)->first(static fn (Column $column): bool => $column->key === $key);

            if ($default?->orderBy !== null) {
                $query->orderBy($default->orderBy, $dir);
            }
        }

        $query->orderBy($query->getModel()->getQualifiedKeyName(), 'desc');
    }

    /**
     * @return array<string, string>
     */
    private function cellsFor(Model $row): array
    {
        if ($this->cachedRow !== $row) {
            $this->cachedRow = $row;
            $this->cachedCells = $this->cells($row);
        }

        return $this->cachedCells;
    }

    private function isDate(string $value): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return false;
        }

        try {
            return Carbon::createFromFormat('!Y-m-d', $value)?->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }
}
