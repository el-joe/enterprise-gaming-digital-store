<?php

namespace App\Domains\Shared\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Audited create, update, delete, list and reorder for admin-managed records. Subclasses declare
 * what is listable and add their own rules through the protected hooks.
 *
 * @template TModel of Model
 */
abstract class CrudService
{
    private const int MYSQL_ROW_IS_REFERENCED = 1451;

    public function __construct(protected ListQueryApplier $lists, protected AuditLogger $audit) {}

    /**
     * @return class-string<TModel>
     */
    abstract protected function model(): string;

    /**
     * Audit action prefix, for example `catalog.platform`.
     */
    abstract protected function auditPrefix(): string;

    public function list(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            $this->model()::query()->with($this->relations()),
            $list,
            $this->filters(),
            $this->sorts(),
            $this->searchColumns(),
            $this->defaultSort(),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        $class = $this->model();

        return $this->persist(new $class, $attributes, 'created');
    }

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function update(Model $model, array $attributes): Model
    {
        return $this->persist($model, $attributes, 'updated');
    }

    /**
     * @param  TModel  $model
     */
    public function delete(Model $model): void
    {
        $this->guardDelete($model);

        try {
            DB::transaction(function () use ($model): void {
                $model->delete();

                $this->audit->record(new AuditEntry(
                    action: $this->auditPrefix().'.deleted',
                    subject: $model,
                    before: Arr::except($model->attributesToArray(), $this->auditIgnored()),
                ));

                $this->afterDelete($model);
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === self::MYSQL_ROW_IS_REFERENCED) {
                throw $this->inUse();
            }

            throw $exception;
        }
    }

    /**
     * Assign sort_order from the position of each id in the list.
     *
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            foreach (array_values($ids) as $position => $id) {
                $this->model()::query()->whereKey($id)->update(['sort_order' => $position]);
            }

            $this->audit->record(new AuditEntry(action: $this->auditPrefix().'.reordered', after: ['ids' => $ids]));
        });
    }

    /**
     * @return TModel
     */
    public function withRelations(Model $model): Model
    {
        return $model->loadMissing($this->relations());
    }

    /**
     * @return array<string, mixed>
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    protected function sorts(): array
    {
        return ['id', 'created_at'];
    }

    /**
     * @return list<string>
     */
    protected function searchColumns(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    protected function relations(): array
    {
        return [];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }

    /**
     * Columns kept out of audit snapshots because they are large display text.
     *
     * @return list<string>
     */
    protected function auditIgnored(): array
    {
        return [];
    }

    /**
     * Transform validated input before it is written. Runs inside the transaction.
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function prepare(Model $model, array $attributes): array
    {
        return $attributes;
    }

    /**
     * Runs inside the transaction after the row is saved.
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     */
    protected function afterSave(Model $model, array $attributes): void {}

    /**
     * @param  TModel  $model
     */
    protected function guardDelete(Model $model): void {}

    /**
     * @param  TModel  $model
     */
    protected function afterDelete(Model $model): void {}

    protected function inUse(string $message = 'This record is in use and cannot be deleted.'): ApplicationException
    {
        return new ApplicationException($message, 'conflict', 409);
    }

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    protected function persist(Model $model, array $attributes, string $verb): Model
    {
        try {
            DB::transaction(function () use ($model, $attributes, $verb): void {
                $data = $this->prepare($model, $attributes);
                $before = $model->exists ? Arr::only($model->getOriginal(), array_keys($data)) : null;

                $model->fill(Arr::only($data, $model->getFillable()));
                $this->applyGuarded($model, $data);
                $model->save();

                $this->afterSave($model, $attributes);

                $this->audit->record(new AuditEntry(
                    action: $this->auditPrefix().'.'.$verb,
                    subject: $model,
                    before: $before === null ? null : Arr::except($before, $this->auditIgnored()),
                    after: Arr::except($model->attributesToArray(), $this->auditIgnored()),
                ));
            });
        } catch (UniqueConstraintViolationException) {
            throw new ApplicationException('A record with these unique values already exists.', 'conflict', 409);
        }

        return $this->withRelations($model->refresh());
    }

    /**
     * Assign service-owned columns that are deliberately not mass assignable, such as status.
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     */
    protected function applyGuarded(Model $model, array $data): void {}
}
