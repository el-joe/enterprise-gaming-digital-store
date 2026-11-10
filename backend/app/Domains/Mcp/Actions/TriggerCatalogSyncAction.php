<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Contracts\McpWriteAction;
use App\Domains\Mcp\DTOs\PreparedAction;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Mcp\Repositories\McpOperationsRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Jobs\SyncSupplierCatalog;
use App\Domains\Suppliers\Models\Supplier;

/**
 * Queues the same catalog sync job the admin endpoint and the scheduler use. The sync run row
 * records `triggered_by = mcp:{service_account_id}`.
 */
class TriggerCatalogSyncAction implements McpWriteAction
{
    public function __construct(private McpOperationsRepository $operations) {}

    public function tool(): McpWriteTool
    {
        return McpWriteTool::TriggerCatalogSync;
    }

    public function rules(): array
    {
        return [
            'supplier' => ['required', 'string', 'max:120'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function prepare(array $validated): PreparedAction
    {
        $supplier = $this->supplier((string) $validated['supplier']);

        return new PreparedAction(
            ['supplier' => (string) $supplier->id, 'reason' => (string) $validated['reason']],
            1,
            "Queue a catalog sync for supplier {$supplier->name} ({$supplier->adapter_key}).",
        );
    }

    public function execute(array $arguments, ?User $approver, int $serviceAccountId): array
    {
        $supplier = $this->supplier((string) $arguments['supplier']);

        SyncSupplierCatalog::dispatch($supplier->id, 'catalog', 'mcp:'.$serviceAccountId);

        return ['supplier' => $supplier->name, 'queued' => true, 'type' => 'catalog'];
    }

    private function supplier(string $identifier): Supplier
    {
        $supplier = $this->operations->findSupplier($identifier);

        if ($supplier === null) {
            throw new ApplicationException('No supplier matches.', 'not_found', 404);
        }

        if ($supplier->status !== SupplierStatus::Active) {
            throw new ApplicationException('The supplier is not active.', 'supplier_inactive', 409);
        }

        return $supplier;
    }
}
