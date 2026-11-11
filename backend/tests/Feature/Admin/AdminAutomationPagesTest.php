<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Automation\Models\AiSuggestion;
use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Settings\Enums\ExportJobStatus;
use App\Domains\Settings\Models\ExportJob;
use Illuminate\Support\Facades\Storage;

/*
| M15: AI draft review, the MCP action approval queue, MCP tokens and n8n credentials (shown
| once, never again), workflow switches, export pruning and the privacy commands.
*/

describe('pages and permissions', function (): void {
    it('guards each screen by its own permission and renders it with CSP', function (string $path, string $permission): void {
        adminWith(['orders.view']);
        $this->get($path)->assertForbidden();

        adminWith([$permission]);
        $page = $this->get($path)->assertOk();
        assertCspSafeHtml($page);
        $this->withSession(['admin.locale' => 'ar'])->get($path)->assertOk()->assertSee('dir="rtl"', false);
    })->with([
        'ai review' => ['/admin/automation/ai-suggestions', 'ai_suggestions.review'],
        'mcp approvals' => ['/admin/automation/mcp-actions', 'mcp_actions.approve'],
        'mcp tokens' => ['/admin/automation/mcp-tokens', 'service_accounts.manage'],
        'automation credentials' => ['/admin/automation/credentials', 'service_accounts.manage'],
    ]);

    it('serves each table to its permission only', function (string $key, string $permission): void {
        adminWith(['orders.view']);
        postTable($key)->assertForbidden();

        adminWith([$permission]);
        postTable($key)->assertOk();
    })->with([
        ['ai-suggestions', 'ai_suggestions.review'],
        ['mcp-actions', 'mcp_actions.approve'],
        ['mcp-tokens', 'service_accounts.manage'],
        ['automation-credentials', 'service_accounts.manage'],
        ['automation-workflows', 'service_accounts.manage'],
    ]);
});

describe('AI review', function (): void {
    it('approves once and rejects only with a note, audited, escaping the draft', function (): void {
        $draft = AiSuggestion::factory()->create(['status' => 'pending_review', 'content' => ['title' => '<script>alert(1)</script>']]);
        $other = AiSuggestion::factory()->create(['status' => 'pending_review']);
        adminWith(['ai_suggestions.review']);

        expect((string) postTable('ai-suggestions', ['filters' => ['status' => 'pending_review']])->json('data.1.draft').(string) postTable('ai-suggestions')->json('data.0.draft'))
            ->not->toContain('<script>');

        $this->postJson("/admin/api/ai-suggestions/{$other->id}/reject", [])->assertUnprocessable()->assertJsonValidationErrors('note');
        $this->postJson("/admin/api/ai-suggestions/{$other->id}/reject", ['note' => 'Off brand'])->assertOk();
        $this->postJson("/admin/api/ai-suggestions/{$draft->id}/approve", [])->assertOk();
        $this->postJson("/admin/api/ai-suggestions/{$draft->id}/reject", ['note' => 'Changed my mind'])->assertStatus(409);

        expect($draft->refresh()->status->value)->toBe('approved')->and($draft->reviewed_by)->not->toBeNull()
            ->and(AuditLog::query()->whereIn('action', ['ai_suggestions.approved', 'ai_suggestions.rejected'])->count())->toBe(2);
    });
});

describe('MCP approvals and tokens', function (): void {
    it('offers approve and reject only for live pending actions', function (): void {
        $live = McpPendingAction::factory()->create(['status' => 'pending', 'expires_at' => now()->addHour()]);
        McpPendingAction::factory()->create(['status' => 'pending', 'expires_at' => now()->subMinute()]);
        adminWith(['mcp_actions.approve']);

        $this->get('/admin/automation/mcp-actions')->assertOk();
        $rows = collect(postTable('mcp-actions')->assertOk()->json('data'));

        expect($rows->firstWhere('DT_RowId', $live->public_id)['actions'])->toContain('/mcp-actions/'.$live->public_id.'/approve')
            ->and($rows->where('DT_RowId', '!=', $live->public_id)->first()['actions'])->toBe('');
    });

    it('issues an MCP token shown only in the issue response, then lists it without the token', function (): void {
        adminWith(['service_accounts.manage']);

        $token = (string) $this->postJson('/admin/api/mcp-tokens', ['account' => 'Claude desktop', 'scopes' => ['products:read']])->assertCreated()->json('data.token');

        expect($token)->not->toBe('');
        $page = $this->get('/admin/automation/mcp-tokens')->assertOk()->assertSee('data-success="reveal"', false);
        $table = postTable('mcp-tokens')->assertOk();

        expect($page->getContent())->not->toContain($token)->and($table->getContent())->not->toContain($token)
            ->and($table->json('data.0.account'))->toBe('Claude desktop');
    });

    it('issues n8n credentials whose token and secret never appear again, and switches workflows', function (): void {
        $workflow = AutomationWorkflow::factory()->create(['workflow_id' => 'A01', 'is_enabled' => false]);
        adminWith(['service_accounts.manage']);

        $issued = $this->postJson('/admin/api/automation-credentials', ['account' => 'n8n', 'scopes' => ['reports:read']])->assertCreated()->json('data');
        $page = $this->get('/admin/automation/credentials')->assertOk();
        $table = postTable('automation-credentials')->assertOk();

        foreach ([$issued['token'], $issued['hmac_secret']] as $secret) {
            expect($page->getContent())->not->toContain($secret)->and($table->getContent())->not->toContain($secret);
        }

        expect($table->json('data.0.secret'))->toContain(__('admin.common.configured'));

        adminWith(['mcp_actions.approve']);
        $this->postJson('/admin/api/automation-workflows/A01/enable')->assertForbidden();

        adminWith(['service_accounts.manage']);
        $this->postJson('/admin/api/automation-workflows/A01/enable')->assertOk();
        $this->postJson('/admin/api/automation-workflows/Z99/enable')->assertNotFound();

        expect($workflow->refresh()->is_enabled)->toBeTrue()
            ->and(AuditLog::query()->where('action', 'automation.workflows_enabled')->exists())->toBeTrue();
    });
});

describe('scheduled and console tasks', function (): void {
    it('prunes expired export files and keeps the rows', function (): void {
        Storage::fake('local');
        Storage::disk('local')->put('exports/old.csv', 'a');
        Storage::disk('local')->put('exports/new.csv', 'b');
        $user = User::factory()->staff()->create();
        $old = ExportJob::factory()->create(['user_id' => $user->id, 'status' => ExportJobStatus::Completed, 'media_path' => 'exports/old.csv', 'expires_at' => now()->subHour()]);
        $new = ExportJob::factory()->create(['user_id' => $user->id, 'status' => ExportJobStatus::Completed, 'media_path' => 'exports/new.csv', 'expires_at' => now()->addHour()]);

        $this->artisan('admin:prune-exports')->assertSuccessful();

        Storage::disk('local')->assertMissing('exports/old.csv');
        Storage::disk('local')->assertExists('exports/new.csv');
        expect($old->refresh()->media_path)->toBeNull()->and($new->refresh()->media_path)->toBe('exports/new.csv');
    });

    it('exports and erases a customer from the console', function (): void {
        Storage::fake('local');
        $customer = User::factory()->create(['email' => 'console.erase@example.com']);

        $this->artisan('customers:export', ['customer' => $customer->public_id])->assertSuccessful();
        expect(Storage::disk('local')->allFiles('privacy-exports'))->toHaveCount(1);

        $this->artisan('customers:anonymize', ['customer' => $customer->public_id, '--reason' => 'short'])->assertFailed();
        $this->artisan('customers:anonymize', ['customer' => $customer->public_id, '--reason' => 'Written GDPR request', '--force' => true])->assertSuccessful();
        expect($customer->refresh()->email)->not->toBe('console.erase@example.com');
    });
});
