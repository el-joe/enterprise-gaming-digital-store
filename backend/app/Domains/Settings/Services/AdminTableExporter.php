<?php

namespace App\Domains\Settings\Services;

use App\DataTables\AdminTables;
use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\ExportJobStatus;
use App\Domains\Settings\Jobs\ExportAdminTable;
use App\Domains\Settings\Models\ExportJob;
use App\Domains\Settings\Services\Export\CsvTableWriter;
use App\Domains\Settings\Services\Export\TableWriter;
use App\Domains\Settings\Services\Export\XlsxTableWriter;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Admin table exports (M14, PROJECT_ARCHITECTURE.md section 4: a queued file job, no Excel
 * package). The request is checked against the table's filters, recorded in `export_jobs` and
 * audited; the job writes the file to the private disk; only the requester can download it,
 * while it has not expired and only while they still hold the table's permission (checked by
 * the route). Downloads are audited too.
 */
class AdminTableExporter
{
    private const string DISK = 'local';

    public function __construct(private AuditLogger $audit) {}

    /**
     * @param  array{format: string, search?: string|null, filters?: array<string, mixed>, order?: list<array{column: int, dir: string}>, ids?: list<string>, hidden_columns?: list<string>}  $request
     */
    public function request(User $user, string $tableKey, array $request): ExportJob
    {
        $table = AdminTables::make($tableKey, $user);
        $filters = $table->validatedFilters($request['filters'] ?? []);

        $export = (new ExportJob)->forceFill([
            'user_id' => $user->id,
            'table_key' => $tableKey,
            'status' => ExportJobStatus::Pending,
            'filters' => [
                'format' => $request['format'],
                'search' => (string) ($request['search'] ?? ''),
                'filters' => $filters,
                'order' => array_values($request['order'] ?? []),
                'ids' => array_values($request['ids'] ?? []),
                'hidden_columns' => array_values($request['hidden_columns'] ?? []),
                'locale' => app()->getLocale(),
            ],
        ]);
        $export->save();

        $this->audit->record(new AuditEntry('admin.export_requested', $export, after: [
            'table' => $tableKey,
            'format' => $request['format'],
            'filters' => $filters,
            'search' => (string) ($request['search'] ?? '') !== '',
            'selected_rows' => count($request['ids'] ?? []),
        ]));

        ExportAdminTable::dispatch($export->id)->afterCommit();

        return $export;
    }

    /**
     * Run by the queued job: write every row of the filtered query (up to the cap).
     */
    public function run(int $exportId): void
    {
        $export = ExportJob::query()->find($exportId);

        if ($export === null || $export->status !== ExportJobStatus::Pending) {
            return;
        }

        $export->forceFill(['status' => ExportJobStatus::Running])->save();
        $options = $export->filters ?? [];
        $writer = $this->writer((string) ($options['format'] ?? 'csv'));
        $relative = "exports/{$export->user_id}/".bin2hex(random_bytes(16)).'.'.$writer->extension();
        $previousLocale = app()->getLocale();

        try {
            app()->setLocale((string) ($options['locale'] ?? $previousLocale));
            $user = User::query()->findOrFail($export->user_id);
            $table = AdminTables::make($export->table_key, $user);
            $columns = $table->exportColumns($options['hidden_columns'] ?? []);
            $max = (int) config('admin.tables.export_max_rows');

            Storage::disk(self::DISK)->makeDirectory(dirname($relative));
            $writer->open(Storage::disk(self::DISK)->path($relative));
            $writer->row(array_map(static fn ($column): string => $column->label, $columns));

            $count = 0;

            foreach ($table->exportQuery($options)->lazy(500) as $row) {
                if ($count >= $max) {
                    break;
                }

                $writer->row($table->exportRow($row, $columns));
                $count++;
            }

            $writer->close();

            $export->forceFill([
                'status' => ExportJobStatus::Completed,
                'media_path' => $relative,
                'row_count' => $count,
                'expires_at' => now()->addHours((int) config('admin.tables.export_ttl_hours')),
            ])->save();
        } catch (Throwable $exception) {
            $writer->close();
            Storage::disk(self::DISK)->delete($relative);
            $export->forceFill(['status' => ExportJobStatus::Failed, 'error' => mb_substr(class_basename($exception), 0, 255)])->save();

            report($exception);
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    /**
     * The requester's own export of this table; anyone else's is not found.
     */
    public function find(User $user, string $tableKey, int $id): ExportJob
    {
        return ExportJob::query()->whereKey($id)->where('user_id', $user->id)->where('table_key', $tableKey)->first()
            ?? throw new ApplicationException('The requested resource was not found.', 'not_found', 404);
    }

    public function download(User $user, string $tableKey, int $id): StreamedResponse
    {
        $export = $this->find($user, $tableKey, $id);

        if ($export->status !== ExportJobStatus::Completed || $export->media_path === null) {
            throw new ApplicationException('The export is not ready.', 'export_not_ready', 409);
        }

        if ($export->expires_at === null || $export->expires_at->isPast() || ! Storage::disk(self::DISK)->exists($export->media_path)) {
            throw new ApplicationException('The export has expired. Request a new one.', 'export_expired', 410);
        }

        $this->audit->record(new AuditEntry('admin.export_downloaded', $export, after: ['table' => $tableKey, 'rows' => $export->row_count]));

        $writer = $this->writer((string) ($export->filters['format'] ?? 'csv'));
        $name = sprintf('%s-%s.%s', $tableKey, $export->created_at?->format('Ymd-His'), $writer->extension());

        return Storage::disk(self::DISK)->download($export->media_path, $name, [
            'Content-Type' => $writer->mimeType(),
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * Delete the files of exports past their expiry (M15, scheduled hourly). The row stays, with
     * no file, so the audit trail keeps who exported what; downloads already answer 410 after
     * expiry.
     *
     * @return int files removed
     */
    public function pruneExpired(int $limit = 1000): int
    {
        $removed = 0;

        ExportJob::query()->whereNotNull('media_path')->where('expires_at', '<', now())->orderBy('id')->limit($limit)->get()
            ->each(static function (ExportJob $export) use (&$removed): void {
                Storage::disk(self::DISK)->delete((string) $export->media_path);
                $export->forceFill(['media_path' => null])->save();
                $removed++;
            });

        return $removed;
    }

    /**
     * @return array{id: int, status: string, rows: ?int, download_url: ?string, expires_at: ?string}
     */
    public function present(ExportJob $export): array
    {
        $ready = $export->status === ExportJobStatus::Completed;

        return [
            'id' => $export->id,
            'status' => $export->status->value,
            'rows' => $export->row_count,
            'download_url' => $ready ? route("admin.api.tables.{$export->table_key}.exports.download", ['exportJob' => $export->id]) : null,
            'expires_at' => $export->expires_at?->toIso8601String(),
        ];
    }

    private function writer(string $format): TableWriter
    {
        return $format === 'xlsx' ? new XlsxTableWriter : new CsvTableWriter;
    }
}
