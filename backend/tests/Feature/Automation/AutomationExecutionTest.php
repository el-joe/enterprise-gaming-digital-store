<?php

/**
 * Duplicate-execution protection, the execution log, alerts and the job endpoints (ADR-014).
 */

use App\Domains\Automation\Enums\AutomationLogKind;
use App\Domains\Automation\Enums\AutomationLogStatus;
use App\Domains\Automation\Models\AutomationLog;
use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Console\QueuedCommand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    enableAutomationWorkflows();
    ['token' => $this->token] = automationCredential();
});

function executionRows(): Collection
{
    return AutomationLog::query()->where('kind', AutomationLogKind::Execution)->get();
}

it('starts an execution once and reports the second start as a duplicate', function (): void {
    $options = ['workflow' => 'A01', 'key' => 'A01:2026-11-10'];

    automationPost($this->token, 'executions', ['action' => 'start'], $options)
        ->assertCreated()
        ->assertJsonPath('data.status', 'started')
        ->assertJsonPath('data.duplicate', false)
        ->assertJsonPath('data.attempt', 1);

    automationPost($this->token, 'executions', ['action' => 'start'], $options)
        ->assertOk()
        ->assertJsonPath('data.status', 'skipped_duplicate')
        ->assertJsonPath('data.duplicate', true);

    expect(executionRows())->toHaveCount(1)
        ->and(executionRows()->first()->status)->toBe(AutomationLogStatus::Started);
});

it('still refuses a duplicate after the execution succeeded', function (): void {
    $options = ['workflow' => 'A02', 'key' => 'A02:2026-11-10'];

    automationPost($this->token, 'executions', ['action' => 'start'], $options)->assertCreated();
    automationPost($this->token, 'executions', ['action' => 'finish', 'status' => 'success', 'summary' => ['orders' => 4]], $options)
        ->assertOk()
        ->assertJsonPath('data.status', 'success');
    automationPost($this->token, 'executions', ['action' => 'start'], $options)->assertJsonPath('data.status', 'skipped_duplicate');

    $row = executionRows()->sole();
    expect($row->status)->toBe(AutomationLogStatus::Success)
        ->and($row->summary['result'])->toBe(['orders' => 4])
        ->and($row->finished_at)->not->toBeNull()
        ->and(AutomationWorkflow::query()->where('workflow_id', 'A02')->value('last_success_at'))->not->toBeNull();
});

it('lets a failed execution be restarted and counts the attempt', function (): void {
    $options = ['workflow' => 'A03', 'key' => 'A03:2026-11-10T10:15'];

    automationPost($this->token, 'executions', ['action' => 'start'], $options)->assertCreated();
    automationPost($this->token, 'executions', ['action' => 'finish', 'status' => 'failed', 'error' => 'HTTP node timed out'], $options)->assertOk();
    automationPost($this->token, 'executions', ['action' => 'start'], $options)
        ->assertCreated()
        ->assertJsonPath('data.status', 'started')
        ->assertJsonPath('data.attempt', 2);

    expect(executionRows()->sole()->error)->toBeNull()
        ->and(AutomationWorkflow::query()->where('workflow_id', 'A03')->value('last_failure_at'))->not->toBeNull();
});

it('keeps executions of different keys and workflows apart', function (): void {
    automationPost($this->token, 'executions', ['action' => 'start'], ['workflow' => 'A01', 'key' => 'A01:2026-11-10'])->assertCreated();
    automationPost($this->token, 'executions', ['action' => 'start'], ['workflow' => 'A01', 'key' => 'A01:2026-11-11'])->assertCreated();
    automationPost($this->token, 'executions', ['action' => 'start'], ['workflow' => 'A02', 'key' => 'A01:2026-11-10'])->assertCreated();

    expect(executionRows())->toHaveCount(3);
});

it('refuses to finish an execution that is not running', function (): void {
    $options = ['workflow' => 'A01', 'key' => 'A01:never-started'];

    automationPost($this->token, 'executions', ['action' => 'finish', 'status' => 'success'], $options)
        ->assertStatus(409)
        ->assertJson(['code' => 'execution_not_started']);

    automationPost($this->token, 'executions', ['action' => 'start'], $options)->assertCreated();
    automationPost($this->token, 'executions', ['action' => 'finish', 'status' => 'success'], $options)->assertOk();
    automationPost($this->token, 'executions', ['action' => 'finish', 'status' => 'failed'], $options)->assertStatus(409);
});

it('validates the execution request', function (array $body): void {
    automationPost($this->token, 'executions', $body)->assertUnprocessable()->assertJson(['code' => 'validation_failed']);
})->with([
    'no action' => [[]],
    'unknown action' => [['action' => 'restart']],
    'finish without status' => [['action' => 'finish']],
    'start with status' => [['action' => 'start', 'status' => 'success']],
]);

it('records an alert from the error workflow and closes the failed execution', function (): void {
    Log::spy();
    automationPost($this->token, 'executions', ['action' => 'start'], ['workflow' => 'A05', 'key' => 'A05:2026-11-10T10'])->assertCreated();

    automationPost($this->token, 'alerts', [
        'severity' => 'critical',
        'title' => 'Workflow A05 failed',
        'message' => 'Supplier balance request failed',
        'failed_workflow_id' => 'A05',
        'failed_execution_key' => 'A05:2026-11-10T10',
        'n8n_execution_id' => '1234',
        'node' => 'Fetch balances',
    ], ['workflow' => 'Z01', 'key' => 'Z01:1234'])
        ->assertCreated()
        ->assertJsonPath('data.execution_closed', true);

    $execution = executionRows()->sole();
    expect($execution->status)->toBe(AutomationLogStatus::Failed)
        ->and($execution->error)->toBe('Supplier balance request failed')
        ->and(AutomationWorkflow::query()->where('workflow_id', 'A05')->value('last_failure_at'))->not->toBeNull();

    Log::shouldHaveReceived('log')->withArgs(fn (string $level, string $message, array $context): bool => $level === 'critical' && $message === 'automation.alert' && $context['failed_workflow_id'] === 'A05')->once();
});

it('queues a task only when n8n is its driver, once per execution', function (): void {
    Queue::fake();
    $options = ['workflow' => 'A09', 'key' => 'A09:2026-11-10'];

    startAutomationExecution($this->token, 'A09', 'A09:2026-11-10');
    automationPost($this->token, 'jobs/catalog-sync', [], $options)
        ->assertStatus(409)
        ->assertJson(['code' => 'task_driver_mismatch']);
    Queue::assertNothingPushed();

    app(SettingsService::class)->set('automation', 'task_drivers', ['catalog_sync' => 'n8n'], SettingType::Json);

    automationPost($this->token, 'jobs/catalog-sync', [], $options)
        ->assertStatus(202)
        ->assertJsonPath('data.command', 'suppliers:sync-catalog')
        ->assertJsonPath('data.repeated', false);
    automationPost($this->token, 'jobs/catalog-sync', [], $options)
        ->assertOk()
        ->assertJsonPath('data.repeated', true);

    Queue::assertPushed(QueuedCommand::class, 1);
    expect(executionRows()->sole()->summary['job']['command'])->toBe('suppliers:sync-catalog');
});

it('refuses a task for an execution that is not running or another workflow', function (): void {
    Queue::fake();
    app(SettingsService::class)->set('automation', 'task_drivers', ['exchange_rates' => 'n8n', 'payment_reconciliation' => 'n8n'], SettingType::Json);

    automationPost($this->token, 'jobs/exchange-rates', [], ['workflow' => 'A10', 'key' => 'A10:2026-11-10T10'])
        ->assertStatus(409)
        ->assertJson(['code' => 'execution_not_started']);

    startAutomationExecution($this->token, 'A01', 'A01:2026-11-10');
    automationPost($this->token, 'jobs/reconcile-payments', [], ['workflow' => 'A01', 'key' => 'A01:2026-11-10'])->assertForbidden();

    startAutomationExecution($this->token, 'E02', 'E02:2026-11-10');
    automationPost($this->token, 'jobs/reconcile-payments', [], ['workflow' => 'E02', 'key' => 'E02:2026-11-10'])->assertStatus(202);

    Queue::assertPushed(QueuedCommand::class, 1);
});

it('closes the newest running execution when the error workflow cannot name the key', function (): void {
    automationPost($this->token, 'executions', ['action' => 'start'], ['workflow' => 'A06', 'key' => 'A06:2026-11-10T10:00'])->assertCreated();
    $this->travel(5)->minutes();
    automationPost($this->token, 'executions', ['action' => 'start'], ['workflow' => 'A06', 'key' => 'A06:2026-11-10T10:05'])->assertCreated();

    automationPost($this->token, 'alerts', ['severity' => 'critical', 'title' => 'Workflow A06 failed', 'message' => 'Timeout', 'failed_workflow_id' => 'A06'], ['workflow' => 'Z01', 'key' => 'Z01:5678'])
        ->assertCreated()
        ->assertJsonPath('data.execution_closed', true);

    $rows = executionRows()->keyBy('execution_key');
    expect($rows['A06:2026-11-10T10:05']->status)->toBe(AutomationLogStatus::Failed)
        ->and($rows['A06:2026-11-10T10:00']->status)->toBe(AutomationLogStatus::Started);
});
