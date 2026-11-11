<?php

namespace App\Http\Controllers\Admin\Tables;

use App\DataTables\AdminTables;
use App\Domains\Settings\Services\AdminTableExporter;
use App\Domains\Settings\Services\AdminTablePreferences;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tables\AdminTableRequest;
use App\Http\Requests\Admin\Tables\ExportTableRequest;
use App\Http\Requests\Admin\Tables\SaveFilterRequest;
use App\Http\Requests\Admin\Tables\SaveTableStateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The JSON side of every admin table (M14). Routes are registered per table with the table's
 * permission (routes/admin.php); the table is the one named by the route, never by input.
 */
class TableController extends Controller
{
    public function data(AdminTableRequest $request): JsonResponse
    {
        return AdminTables::make($this->key($request), $request->user())->respond($request, $request->tableInput());
    }

    public function saveState(SaveTableStateRequest $request, AdminTablePreferences $preferences): JsonResponse
    {
        $state = $preferences->saveState(
            $request->user(),
            AdminTables::make($this->key($request), $request->user()),
            $request->validated('hidden_columns'),
            (int) $request->validated('page_length'),
        );

        return $this->success(['hidden_columns' => $state->hidden_columns, 'page_length' => $state->page_length], 'Saved.');
    }

    public function storeSavedFilter(SaveFilterRequest $request, AdminTablePreferences $preferences): JsonResponse
    {
        $filter = $preferences->saveFilter(
            $request->user(),
            AdminTables::make($this->key($request), $request->user()),
            $request->validated('name'),
            (array) $request->validated('payload'),
            (bool) $request->validated('is_default', false),
        );

        return $this->success(['id' => $filter->id, 'name' => $filter->name, 'payload' => $filter->payload, 'is_default' => $filter->is_default], 'Saved.', status: 201);
    }

    public function destroySavedFilter(Request $request, AdminTablePreferences $preferences, int $savedFilter): JsonResponse
    {
        $preferences->deleteFilter($request->user(), AdminTables::make($this->key($request), $request->user()), $savedFilter);

        return $this->success(message: 'Deleted.');
    }

    public function storeExport(ExportTableRequest $request, AdminTableExporter $exporter): JsonResponse
    {
        $export = $exporter->request($request->user(), $this->key($request), $request->validated());

        return $this->success($exporter->present($export->refresh()), 'Export queued.', status: 202);
    }

    public function showExport(Request $request, AdminTableExporter $exporter, int $exportJob): JsonResponse
    {
        return $this->success($exporter->present($exporter->find($request->user(), $this->key($request), $exportJob)));
    }

    public function downloadExport(Request $request, AdminTableExporter $exporter, int $exportJob): StreamedResponse
    {
        return $exporter->download($request->user(), $this->key($request), $exportJob);
    }

    private function key(Request $request): string
    {
        return AdminTables::keyOfRoute($request->route()?->getName());
    }
}
