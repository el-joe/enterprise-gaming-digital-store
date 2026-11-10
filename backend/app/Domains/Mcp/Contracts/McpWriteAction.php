<?php

namespace App\Domains\Mcp\Contracts;

use App\Domains\Identity\Models\User;
use App\Domains\Mcp\DTOs\PreparedAction;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * One MCP write tool. `prepare` runs when the agent asks (nothing changes); `execute` runs only
 * after approval and goes through the existing domain service, so its business rules (margin
 * guard, order state machine, supplier gate) apply exactly as they do for staff.
 */
interface McpWriteAction
{
    public function tool(): McpWriteTool;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array;

    /**
     * Look up the target and decide the risk tier and summary.
     *
     * @param  array<string, mixed>  $validated
     *
     * @throws ApplicationException when the target does not exist or the action cannot apply
     */
    public function prepare(array $validated): PreparedAction;

    /**
     * Perform the approved action. `$approver` is the staff member who approved it in the admin
     * panel, or null when a tier 1 action was confirmed through MCP elicitation.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed> result stored on the pending action (no secrets, no codes)
     *
     * @throws ApplicationException when a business rule refuses it
     */
    public function execute(array $arguments, ?User $approver, int $serviceAccountId): array;
}
