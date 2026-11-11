<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Settings\Enums\ExportJobStatus;
use App\Domains\Settings\Models\AdminTableState;
use App\Domains\Settings\Models\ExportJob;
use App\Domains\Settings\Models\SavedFilter;
use App\Domains\Settings\Services\AdminTableExporter;
use App\Domains\Suppliers\Models\Supplier;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\Storage;

/*
| M14: queued CSV and XLSX exports (permission-checked, audited, owner-only downloads) and the
| per-staff table layout and saved filters.
*/

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
    Storage::fake('local');
});

describe('exports', function (): void {
    it('queues an export, writes the filtered rows as CSV and audits request and download', function (): void {
        $staff = adminWith(['orders.view']);
        Order::factory()->create(['order_number' => 'BX-EXPORT-1', 'status' => 'manual_review', 'customer_name' => '=HYPERLINK("http://evil")']);
        Order::factory()->create(['order_number' => 'BX-OTHER', 'status' => 'completed']);

        $response = $this->postJson('/admin/api/tables/orders/exports', ['format' => 'csv', 'filters' => ['status' => 'manual_review'], 'hidden_columns' => ['gateway']]);

        $response->assertAccepted()->assertJsonPath('data.status', 'completed')->assertJsonPath('data.rows', 1);
        $export = ExportJob::query()->sole();
        expect($export->user_id)->toBe($staff->id)
            ->and($export->filters['filters'])->toBe(['status' => 'manual_review']);

        $csv = Storage::disk('local')->get($export->media_path);
        expect($csv)->toStartWith("\xEF\xBB\xBF")
            ->toContain('Order,Customer,Status,Total,Charged,"Risk score",Country,Placed')
            ->not->toContain('Gateway')
            ->toContain('BX-EXPORT-1')
            ->not->toContain('BX-OTHER')
            ->toContain("'=HYPERLINK");

        $this->get($response->json('data.download_url'))->assertOk()->assertHeader('Cache-Control', 'no-store, private');

        expect(AuditLog::query()->where('action', 'admin.export_requested')->where('actor_id', $staff->id)->sole()->after)->toMatchArray(['table' => 'orders', 'format' => 'csv', 'filters' => ['status' => 'manual_review']])
            ->and(AuditLog::query()->where('action', 'admin.export_downloaded')->count())->toBe(1);
    });

    it('exports only the selected rows', function (): void {
        adminWith(['orders.view']);
        $picked = Order::factory()->create(['order_number' => 'BX-PICKED']);
        Order::factory()->create(['order_number' => 'BX-SKIPPED']);

        $this->postJson('/admin/api/tables/orders/exports', ['format' => 'csv', 'ids' => [$picked->public_id]])->assertAccepted()->assertJsonPath('data.rows', 1);

        expect(Storage::disk('local')->get(ExportJob::query()->sole()->media_path))->toContain('BX-PICKED')->not->toContain('BX-SKIPPED');
    });

    it('writes a valid XLSX workbook', function (): void {
        adminWith(['suppliers.view']);
        Supplier::factory()->create(['name' => 'مورد & Co <x>']);

        $this->postJson('/admin/api/tables/suppliers/exports', ['format' => 'xlsx'])->assertAccepted();

        $path = Storage::disk('local')->path(ExportJob::query()->sole()->media_path);
        $zip = new ZipArchive;
        expect($zip->open($path))->toBeTrue();
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        expect($zip->getFromName('[Content_Types].xml'))->toContain('spreadsheetml')
            ->and(simplexml_load_string($sheet))->not->toBeFalse()
            ->and($sheet)->toContain('مورد &amp; Co &lt;x&gt;');
        $zip->close();
    });

    it('refuses invalid export requests in the envelope', function (): void {
        adminWith(['orders.view']);

        $this->postJson('/admin/api/tables/orders/exports', ['format' => 'pdf', 'filters' => ['status' => 'nope']])
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed')->assertJsonValidationErrors('format');
        $this->postJson('/admin/api/tables/orders/exports', ['format' => 'csv', 'filters' => ['status' => 'nope']])
            ->assertUnprocessable()->assertJsonValidationErrors('filters.status');
        expect(ExportJob::query()->count())->toBe(0);
    });

    it('lets only the requester download, while they hold the permission and before expiry', function (): void {
        $owner = adminWith(['orders.view']);
        Order::factory()->create();
        $id = $this->postJson('/admin/api/tables/orders/exports', ['format' => 'csv'])->json('data.id');
        $url = "/admin/api/tables/orders/exports/{$id}/download";

        adminWith(['orders.view']);
        $this->getJson($url)->assertNotFound();
        $this->getJson("/admin/api/tables/orders/exports/{$id}")->assertNotFound();

        $this->actingAs(User::factory()->staff()->withTwoFactor()->create())->getJson($url)->assertForbidden();

        actingAsVerifiedStaff($owner);
        $this->getJson("/admin/api/tables/suppliers/exports/{$id}/download")->assertForbidden();
        ExportJob::query()->whereKey($id)->update(['expires_at' => now()->subMinute()]);
        $this->getJson($url)->assertStatus(410)->assertJsonPath('code', 'export_expired');
    });

    it('marks an export failed without leaving a file when writing breaks', function (): void {
        $staff = adminWith(['orders.view']);
        $export = (new ExportJob)->forceFill(['user_id' => $staff->id, 'table_key' => 'orders', 'status' => ExportJobStatus::Pending, 'filters' => ['format' => 'csv', 'filters' => ['status' => 'invalid']]]);
        $export->save();

        app(AdminTableExporter::class)->run($export->id);

        expect($export->refresh()->status)->toBe(ExportJobStatus::Failed)
            ->and($export->media_path)->toBeNull()
            ->and(Storage::disk('local')->allFiles())->toBe([]);
    });
});

describe('layout and saved filters', function (): void {
    it('saves the column layout per staff member, keeping only known columns, audited', function (): void {
        $staff = adminWith(['orders.view']);

        $this->putJson('/admin/api/tables/orders/state', ['hidden_columns' => ['gateway', 'not_a_column'], 'page_length' => 50])
            ->assertOk()->assertJsonPath('data.hidden_columns', ['gateway'])->assertJsonPath('data.page_length', 50);
        $this->putJson('/admin/api/tables/orders/state', ['hidden_columns' => [], 'page_length' => 7])->assertUnprocessable()->assertJsonValidationErrors('page_length');

        expect(AdminTableState::query()->where('user_id', $staff->id)->sole()->hidden_columns)->toBe(['gateway'])
            ->and(AuditLog::query()->where('action', 'admin.table_state_saved')->count())->toBe(1);

        $page = $this->get('/admin/orders')->assertOk();
        expect(html_entity_decode((string) str($page->getContent())->between('data-table-config="', '"')))->toContain('"hidden_columns":["gateway"]')->toContain('"page_length":50');
    });

    it('stores, replaces and deletes saved filters, validated against the table', function (): void {
        $staff = adminWith(['orders.view']);

        $first = $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => 'Needs action', 'is_default' => true, 'payload' => ['search' => 'BX', 'filters' => ['attention' => '1'], 'order' => [['column' => 0, 'dir' => 'asc']], 'hidden_columns' => ['risk']]]);
        $first->assertCreated()->assertJsonPath('data.payload.filters', ['attention' => '1'])->assertJsonPath('data.is_default', true);

        $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => 'Needs action', 'payload' => ['filters' => ['status' => 'failed']]])->assertCreated();
        expect(SavedFilter::query()->where('user_id', $staff->id)->count())->toBe(1)
            ->and(SavedFilter::query()->sole()->payload['filters'])->toBe(['status' => 'failed']);

        $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => 'Bad', 'payload' => ['filters' => ['status' => 'hacked']]])
            ->assertUnprocessable()->assertJsonValidationErrors('filters.status');
        $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => '', 'payload' => []])->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->deleteJson('/admin/api/tables/orders/saved-filters/'.SavedFilter::query()->sole()->id)->assertOk();
        expect(SavedFilter::query()->count())->toBe(0)
            ->and(AuditLog::query()->whereIn('action', ['admin.saved_filter_saved', 'admin.saved_filter_deleted'])->count())->toBe(3);
    });

    it('never shows or deletes another staff member\'s saved filters', function (): void {
        $other = User::factory()->staff()->create();
        $theirs = SavedFilter::factory()->create(['user_id' => $other->id, 'table_key' => 'orders', 'name' => 'Theirs', 'payload' => []]);

        adminWith(['orders.view']);

        $this->get('/admin/orders')->assertOk()->assertDontSee('Theirs');
        $this->deleteJson("/admin/api/tables/orders/saved-filters/{$theirs->id}")->assertNotFound();
        expect($theirs->fresh())->not->toBeNull();
    });

    it('caps saved filters per table', function (): void {
        config(['admin.tables.saved_filters_per_table' => 2]);
        adminWith(['orders.view']);

        $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => 'One', 'payload' => []])->assertCreated();
        $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => 'Two', 'payload' => []])->assertCreated();
        $this->postJson('/admin/api/tables/orders/saved-filters', ['name' => 'Three', 'payload' => []])->assertUnprocessable()->assertJsonValidationErrors('name');
    });
});
