<?php

/**
 * M13 code reveal UX (owner decision 7): the shared reveal component on the account order page,
 * the digital codes page and the order result page; password re-authentication (423, then
 * confirm-password, then the reveal); `no-store` on every response that carries a code; the
 * auto-hide and clipboard wiring; and no code in a page, log or browser storage.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    storefrontWithoutBuild();
    Queue::fake([FulfilOrder::class]);
    $this->customer = User::factory()->create(['email_verified_at' => now()]);
    [$this->order, $this->unit] = accountPageDeliveredOrder($this->customer);
    $this->revealUrl = "/api/v1/account/orders/{$this->order->public_id}/units/{$this->unit->public_id}/reveal";
});

it('wires the same reveal component on every page that shows codes, with the 60 second hide and clipboard clear', function (string $page): void {
    $path = str_replace('{order}', $this->order->public_id, $page);

    $response = $this->actingAs($this->customer)->get($path)->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('data-module="reveal"', false)
        ->assertSee('data-hide-after="60"', false)
        ->assertSee('data-clipboard-clear-after="60"', false)
        ->assertSee('data-confirm-url="'.url('/api/v1/account/confirm-password').'"', false)
        ->assertSee('data-reveal-dialog', false)
        ->assertSee('data-step="password"', false)
        ->assertSee('data-step="limited"', false)
        ->assertSee('data-reveal="'.$this->unit->public_id.'"', false)
        ->assertDontSee(ACCOUNT_PAGE_CANARY_CODE)
        ->assertDontSee('9876');

    assertCspSafeHtml($response);
})->with([
    'account order' => ['/account/orders/{order}'],
    'account order (ar)' => ['/ar/account/orders/{order}'],
    'digital codes' => ['/account/codes'],
    'digital codes (ar)' => ['/ar/account/codes'],
    'order result page' => ['/orders/{order}'],
]);

it('takes the hide time from configuration', function (): void {
    config(['fulfillment.web_reveal_hide_seconds' => 45]);

    $this->actingAs($this->customer)->get('/account/codes')->assertSee('data-hide-after="45"', false)->assertSee('data-clipboard-clear-after="45"', false);
});

it('asks for the password first (423), then reveals with no-store after confirm-password', function (): void {
    $this->actingAs($this->customer)->postJson($this->revealUrl)->assertStatus(423)->assertJsonPath('code', 'recent_auth_required');

    $this->actingAs($this->customer)->postJson('/api/v1/account/confirm-password', ['password' => 'wrong-password'])->assertUnprocessable()->assertJsonValidationErrors('password');
    $this->actingAs($this->customer)->postJson($this->revealUrl)->assertStatus(423);

    $this->actingAs($this->customer)->postJson('/api/v1/account/confirm-password', ['password' => 'password'])->assertOk();
    $response = $this->actingAs($this->customer)->postJson($this->revealUrl)->assertOk()
        ->assertJsonPath('data.code', ACCOUNT_PAGE_CANARY_CODE);

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Pragma'))->toBe('no-cache')
        ->and($this->unit->fresh()->first_revealed_at)->not->toBeNull();
});

it('shows a revealed unit as revealed afterwards, still without the code', function (): void {
    $this->actingAs($this->customer)->withSession(['auth.password_confirmed_at' => now()->getTimestamp()])->postJson($this->revealUrl)->assertOk();

    $this->actingAs($this->customer)->get('/account/orders/'.$this->order->public_id)->assertOk()
        ->assertSee('data-sealed="0"', false)
        ->assertSee('Revealed')
        ->assertDontSee(ACCOUNT_PAGE_CANARY_CODE);
});

it('never writes a revealed code to the log or the audit trail', function (): void {
    Log::spy();

    $this->actingAs($this->customer)->withSession(['auth.password_confirmed_at' => now()->getTimestamp()])->postJson($this->revealUrl)->assertOk();

    Log::shouldNotHaveReceived('info', [Mockery::on(fn ($message) => str_contains((string) $message, ACCOUNT_PAGE_CANARY_CODE)), Mockery::any()]);
    expect(AuditLog::query()->get()->map->toJson()->implode(''))->not->toContain(ACCOUNT_PAGE_CANARY_CODE);
});

it('keeps the reveal module free of browser storage and wired to auto-hide, tab hiding and clipboard clearing', function (): void {
    $source = file_get_contents(resource_path('js/modules/reveal.js'));

    expect($source)
        ->not->toContain('localStorage')
        ->not->toContain('sessionStorage')
        ->not->toContain('indexedDB')
        ->not->toContain('console.')
        ->not->toContain('innerHTML')
        ->toContain("addEventListener('visibilitychange'")
        ->toContain('dataset.hideAfter')
        ->toContain('dataset.clipboardClearAfter')
        ->toContain("navigator.clipboard.writeText('')")
        ->toContain('result.status === 423')
        ->toContain('result.status === 429');
});
