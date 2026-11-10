<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Mcp\Enums\McpPendingActionApprovedVia;
use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Domains\Mcp\Enums\McpScope;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Mcp\Repositories\McpPendingActionRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;

/**
 * The two-step write flow (MCP_SERVER.md section 5, ADR-020).
 *
 * 1. `request`: the agent asks; arguments are validated and a pending action is stored with a
 *    TTL. Nothing changes.
 * 2. Approval, out of band:
 *    a. `approve` by a staff member holding `mcp_actions.approve` and the tool's own permission
 *       (admin JSON endpoint). The only path for tier 2.
 *    b. `confirm` with the single-use confirmation token, for a tier 1 action requested by an
 *       account holding `actions:confirm`. The token is returned once to the MCP server, which
 *       keeps it from the model and sends it only after the user accepts an elicitation prompt.
 *       It is bound to the requesting Sanctum token, expires with the action and is cleared on use.
 * 3. Execution re-checks the arguments hash, re-validates the arguments, re-checks the account,
 *    the token and its scope, and then runs the action through the existing domain service.
 */
class McpPendingActionService
{
    public function __construct(
        private McpActionRegistry $registry,
        private McpPendingActionRepository $actions,
        private AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     * @return array{action: McpPendingAction, confirmation_token: ?string}
     */
    public function request(McpCaller $caller, McpWriteTool $tool, array $validated): array
    {
        $this->assertMayWrite($caller->account, $caller->scopes, $tool);

        $prepared = $this->registry->for($tool)->prepare($validated);
        $hash = $this->hash($tool, $prepared->arguments);

        if ($this->actions->livePendingDuplicate($caller->account->id, $hash) !== null) {
            throw new ApplicationException('An identical action is already waiting for approval.', 'duplicate_action', 409);
        }

        $elicitable = $prepared->riskTier === 1 && $caller->has(McpScope::ActionsConfirm);
        $confirmationToken = $elicitable ? Str::random(48) : null;

        $action = DB::transaction(function () use ($caller, $tool, $prepared, $hash, $confirmationToken): McpPendingAction {
            $action = new McpPendingAction([
                'service_account_id' => $caller->account->id,
                'requested_token_id' => $caller->tokenId(),
                'tool' => $tool->value,
                'arguments' => $prepared->arguments,
                'arguments_hash' => $hash,
                'risk_tier' => $prepared->riskTier,
                'summary' => Str::limit($prepared->summary, 997),
                'expires_at' => now()->addMinutes((int) config('mcp.pending_action_ttl_minutes')),
            ]);
            $action->forceFill([
                'status' => McpPendingActionStatus::Pending,
                'confirmation_hash' => $confirmationToken === null ? null : hash('sha256', $confirmationToken),
            ])->save();

            $this->audit->record(new AuditEntry(
                action: 'mcp.action_requested',
                subject: $action,
                after: ['tool' => $tool->value, 'risk_tier' => $prepared->riskTier, 'arguments' => $prepared->arguments],
                reason: is_string($prepared->arguments['reason'] ?? null) ? $prepared->arguments['reason'] : null,
            ));

            return $action;
        });

        return ['action' => $action->refresh(), 'confirmation_token' => $confirmationToken];
    }

    /**
     * Path 3b: the MCP server reports the user's acceptance of an elicitation prompt.
     */
    public function confirm(McpCaller $caller, string $publicId, string $confirmationToken): McpPendingAction
    {
        $action = $this->actions->findForAccount($publicId, $caller->account->id)
            ?? throw new ApplicationException('Action not found.', 'not_found', 404);

        if (! $caller->has(McpScope::ActionsConfirm)) {
            throw new ApplicationException('This token cannot confirm actions.', 'scope_missing', 403);
        }

        if ($action->requested_token_id !== $caller->tokenId()) {
            throw new ApplicationException('Only the token that requested this action can confirm it.', 'forbidden', 403);
        }

        if ($action->risk_tier !== 1) {
            throw new ApplicationException('This action needs approval by staff in the admin panel.', 'admin_approval_required', 403);
        }

        $approved = $this->approveLocked($action, function (McpPendingAction $locked) use ($confirmationToken): void {
            if ($locked->confirmation_hash === null || ! hash_equals($locked->confirmation_hash, hash('sha256', $confirmationToken))) {
                throw new ApplicationException('The confirmation token is not valid for this action.', 'confirmation_invalid', 403);
            }
        }, McpPendingActionApprovedVia::Elicitation, null);

        return $this->execute($approved, null);
    }

    /**
     * Path 3a: a staff member approves in the admin panel. Runs as that staff member.
     */
    public function approve(McpPendingAction $action, User $staff, ?string $note): McpPendingAction
    {
        $tool = McpWriteTool::from($action->tool);

        if (! $staff->checkPermissionTo($tool->staffPermission()->value)) {
            throw new ApplicationException("Approving this action also needs the {$tool->staffPermission()->value} permission.", 'forbidden', 403);
        }

        $approved = $this->approveLocked($action, static function (): void {}, McpPendingActionApprovedVia::AdminPanel, $staff, $note);

        return $this->execute($approved, $staff);
    }

    public function reject(McpPendingAction $action, User $staff, string $note): McpPendingAction
    {
        return DB::transaction(function () use ($action, $staff, $note): McpPendingAction {
            $locked = $this->actions->lock($action);

            if ($locked->status !== McpPendingActionStatus::Pending) {
                throw new ApplicationException('This action is no longer pending.', 'action_not_pending', 409);
            }

            $locked->forceFill([
                'status' => McpPendingActionStatus::Rejected,
                'confirmation_hash' => null,
                'approved_by' => $staff->id,
                'decided_at' => now(),
                'decision_note' => $note,
            ])->save();

            $this->audit->record(new AuditEntry('mcp.action_rejected', $locked, after: ['tool' => $locked->tool], reason: $note));

            return $locked;
        });
    }

    public function status(McpCaller $caller, string $publicId): McpPendingAction
    {
        $action = $this->actions->findForAccount($publicId, $caller->account->id)
            ?? throw new ApplicationException('Action not found.', 'not_found', 404);

        if (! array_filter($caller->scopes, static fn (McpScope $scope): bool => $scope->isWrite())) {
            throw new ApplicationException('get_action_status needs a write scope.', 'scope_missing', 403);
        }

        if ($action->status === McpPendingActionStatus::Pending && $action->hasExpired()) {
            $this->actions->expire($action->id);

            return $action->refresh();
        }

        return $action;
    }

    /**
     * Mark due pending actions expired (scheduled; also applied lazily on every read).
     */
    public function expireDue(int $limit = 500): int
    {
        $count = 0;

        foreach ($this->actions->dueForExpiry($limit) as $id) {
            $count += $this->actions->expire($id);
        }

        return $count;
    }

    /**
     * @param  list<McpScope>  $scopes
     */
    public function assertMayWrite(ServiceAccount $account, array $scopes, McpWriteTool $tool): void
    {
        if (! in_array($tool->scope(), $scopes, true)) {
            throw new ApplicationException("This token lacks the {$tool->scope()->value} scope.", 'scope_missing', 403);
        }

        if ($account->is_read_only) {
            throw new ApplicationException('This service account is read-only.', 'read_only', 403);
        }
    }

    /**
     * Lock the row, refuse anything but a live pending action, run the path-specific check and
     * mark it approved. The confirmation hash is cleared here, so it is single use.
     *
     * @param  callable(McpPendingAction): void  $check
     */
    private function approveLocked(McpPendingAction $action, callable $check, McpPendingActionApprovedVia $via, ?User $staff, ?string $note = null): McpPendingAction
    {
        $outcome = DB::transaction(function () use ($action, $check, $via, $staff, $note): McpPendingAction|string {
            $locked = $this->actions->lock($action);

            if ($locked->status !== McpPendingActionStatus::Pending) {
                return 'not_pending';
            }

            if ($locked->hasExpired()) {
                $locked->forceFill(['status' => McpPendingActionStatus::Expired, 'confirmation_hash' => null])->save();

                return 'expired';
            }

            $check($locked);

            $locked->forceFill([
                'status' => McpPendingActionStatus::Approved,
                'approved_via' => $via,
                'approved_by' => $staff?->id,
                'confirmation_hash' => null,
                'decided_at' => now(),
                'decision_note' => $note,
            ])->save();

            $this->audit->record(new AuditEntry(
                action: 'mcp.action_approved',
                subject: $locked,
                after: ['tool' => $locked->tool, 'approved_via' => $via->value],
                reason: $note,
            ));

            return $locked;
        });

        return match ($outcome) {
            'not_pending' => throw new ApplicationException('This action is no longer pending; a confirmation can be used once.', 'action_not_pending', 409),
            'expired' => throw new ApplicationException('This action expired before it was confirmed.', 'confirmation_expired', 410),
            default => $outcome,
        };
    }

    private function execute(McpPendingAction $action, ?User $approver): McpPendingAction
    {
        $tool = McpWriteTool::from($action->tool);
        $handler = $this->registry->for($tool);

        try {
            $this->assertStillAuthorized($action, $tool);
            $this->assertArgumentsUnchanged($action, $tool);

            $result = $handler->execute($action->arguments, $approver, $action->service_account_id);

            $action->forceFill(['status' => McpPendingActionStatus::Executed, 'executed_at' => now(), 'result' => $result])->save();
            $this->audit->record(new AuditEntry('mcp.action_executed', $action, after: ['tool' => $tool->value, 'result' => $result], reason: $this->reason($action), actorType: $approver === null ? AuditLogActorType::Mcp : null, actorId: $approver === null ? $action->service_account_id : null));
        } catch (ApplicationException|ValidationException $exception) {
            $failure = $exception instanceof ValidationException
                ? ['code' => 'invalid_input', 'message' => 'The stored arguments are no longer valid.', 'errors' => $exception->errors()]
                : ['code' => $exception->errorCode, 'message' => $exception->getMessage(), ...($exception->errors === [] ? [] : ['errors' => $exception->errors])];

            $this->fail($action, $tool, $failure, $approver);
        } catch (Throwable $exception) {
            report($exception);
            $this->fail($action, $tool, ['code' => 'server_error', 'message' => 'The action could not be completed.'], $approver);
        }

        return $action->refresh();
    }

    /**
     * @param  array<string, mixed>  $failure
     */
    private function fail(McpPendingAction $action, McpWriteTool $tool, array $failure, ?User $approver): void
    {
        $action->forceFill(['status' => McpPendingActionStatus::Failed, 'executed_at' => now(), 'result' => ['error' => $failure]])->save();
        $this->audit->record(new AuditEntry('mcp.action_failed', $action, after: ['tool' => $tool->value, 'error' => $failure], reason: $this->reason($action), actorType: $approver === null ? AuditLogActorType::Mcp : null, actorId: $approver === null ? $action->service_account_id : null));
    }

    /**
     * The account must still be usable and writable, and the requesting token must still exist
     * and hold the tool's scope (a revoked token cancels its approvals).
     */
    private function assertStillAuthorized(McpPendingAction $action, McpWriteTool $tool): void
    {
        $account = ServiceAccount::query()->withTrashed()->find($action->service_account_id);
        $token = $action->requested_token_id === null ? null : PersonalAccessToken::query()->find($action->requested_token_id);

        if ($account === null || ! $account->isUsable() || $token === null || ($token->expires_at !== null && $token->expires_at->isPast())) {
            throw new ApplicationException('The requesting service account or token is no longer valid.', 'forbidden', 403);
        }

        $this->assertMayWrite($account, McpScope::fromAbilities($token->abilities ?? []), $tool);
    }

    /**
     * The stored arguments must hash to the stored hash, still validate, and prepare to the same
     * canonical form with no higher risk tier than the one approved.
     */
    private function assertArgumentsUnchanged(McpPendingAction $action, McpWriteTool $tool): void
    {
        if (! hash_equals($action->arguments_hash, $this->hash($tool, $action->arguments))) {
            throw new ApplicationException('The stored arguments do not match what was requested.', 'arguments_tampered', 409);
        }

        $handler = $this->registry->for($tool);
        $validated = Validator::make($action->arguments, $handler->rules())->validate();
        $again = $handler->prepare($validated);

        if (! hash_equals($action->arguments_hash, $this->hash($tool, $again->arguments))) {
            throw new ApplicationException('The target changed since the action was requested.', 'arguments_changed', 409);
        }

        if ($again->riskTier > $action->risk_tier) {
            throw new ApplicationException('The action now needs a higher approval tier; request it again.', 'tier_changed', 409);
        }
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function hash(McpWriteTool $tool, array $arguments): string
    {
        return hash('sha256', $tool->value.'|'.json_encode($this->canonical($arguments), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<array-key, mixed>  $value
     * @return array<array-key, mixed>
     */
    private function canonical(array $value): array
    {
        ksort($value);

        return array_map(fn (mixed $item): mixed => is_array($item) ? $this->canonical($item) : $item, $value);
    }

    private function reason(McpPendingAction $action): ?string
    {
        $reason = $action->arguments['reason'] ?? null;

        return is_string($reason) ? $reason : null;
    }
}
