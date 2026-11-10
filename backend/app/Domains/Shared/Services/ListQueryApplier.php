<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\DTOs\ListQuery;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Applies allow-listed filters, sorting and search. Anything not on a list is ignored, so user
 * input can never name a column.
 */
class ListQueryApplier
{
    /**
     * @param  Builder<*>  $query
     * @param  array<string, 'bool'|'exact'|Closure(Builder<*>, mixed): void>  $filters
     * @param  list<string>  $sorts
     * @param  list<string>  $searchColumns
     */
    public function paginate(
        Builder $query,
        ListQuery $list,
        array $filters = [],
        array $sorts = [],
        array $searchColumns = [],
        string $defaultSort = '-id',
    ): LengthAwarePaginator {
        foreach ($filters as $key => $type) {
            if (! array_key_exists($key, $list->filters) || $list->filters[$key] === '' || $list->filters[$key] === null) {
                continue;
            }

            $value = $list->filters[$key];

            match (true) {
                $type instanceof Closure => $type($query, $value),
                $type === 'bool' => $query->where($key, filter_var($value, FILTER_VALIDATE_BOOLEAN)),
                default => $query->where($key, $value),
            };
        }

        if ($list->search !== null && $searchColumns !== []) {
            $term = '%'.addcslashes($list->search, '%_\\').'%';

            $query->where(static function (Builder $inner) use ($searchColumns, $term): void {
                foreach ($searchColumns as $column) {
                    $inner->orWhere($column, 'like', $term);
                }
            });
        }

        $sort = in_array(ltrim((string) $list->sort, '-'), $sorts, true) ? (string) $list->sort : $defaultSort;

        $query->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc');

        return $query->paginate($list->perPage, ['*'], 'page', $list->page);
    }
}
