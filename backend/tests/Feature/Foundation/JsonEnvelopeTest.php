<?php

use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

function assertEnvelope(TestResponse $response, int $status, string $code, string $message): void
{
    $response->assertStatus($status)->assertExactJson([
        'success' => false,
        'message' => $message,
        'code' => $code,
        'request_id' => $response->headers->get('X-Request-Id'),
    ]);
}

it('renders 401 for guests', function (): void {
    assertEnvelope($this->getJson('/admin/api/me'), 401, 'unauthenticated', 'Unauthenticated.');
});

it('renders 403 for a customer on an admin route', function (): void {
    $response = $this->actingAs(User::factory()->create())->getJson('/admin/api/me');

    assertEnvelope($response, 403, 'forbidden', 'This action is unauthorized.');
});

it('renders 404 for an unknown route', function (): void {
    assertEnvelope($this->getJson('/api/v1/does-not-exist'), 404, 'not_found', 'The requested resource was not found.');
});

it('renders 422 with Laravel dot notation errors', function (): void {
    $response = $this->postJson('/login', []);

    $response->assertStatus(422)
        ->assertExactJsonStructure(['success', 'message', 'code', 'errors' => ['email', 'password'], 'request_id'])
        ->assertJsonPath('code', 'validation_failed')
        ->assertJsonPath('success', false);
});

it('renders 429 with Retry-After and records a security event', function (): void {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/login', ['email' => 'nobody@example.com', 'password' => 'wrong-password'])->assertStatus(422);
    }

    $response = $this->postJson('/login', ['email' => 'nobody@example.com', 'password' => 'wrong-password']);

    assertEnvelope($response, 429, 'rate_limited', 'Too many requests. Please try again later.');
    $response->assertHeader('Retry-After');
    $this->assertDatabaseHas('security_events', ['type' => 'auth.throttled', 'severity' => 'warning']);
});

it('renders 500 without leaking the exception, even in debug mode', function (): void {
    Exceptions::fake();
    config(['app.debug' => true]);
    Route::get('/api/v1/__boom', static fn () => throw new RuntimeException('secret provider body'));

    $response = $this->getJson('/api/v1/__boom');

    assertEnvelope($response, 500, 'server_error', 'Something went wrong. Please try again.');
    expect($response->getContent())->not->toContain('secret provider body');
});

it('renders domain exceptions with their own code', function (): void {
    Route::get('/api/v1/__conflict', static fn () => throw new ApplicationException('Already done.', 'illegal_transition', 409));

    assertEnvelope($this->getJson('/api/v1/__conflict'), 409, 'illegal_transition', 'Already done.');
});

it('renders 419 when the CSRF token has expired', function (): void {
    Route::post('/api/v1/__csrf', static fn () => throw new TokenMismatchException);

    assertEnvelope($this->postJson('/api/v1/__csrf'), 419, 'csrf_expired', 'Your session has expired. Please refresh and try again.');
});

it('echoes a well formed request id and replaces a malformed one', function (): void {
    $valid = '01JC0000000000000000000000';

    $this->getJson('/api/v1/nope', ['X-Request-Id' => $valid])->assertHeader('X-Request-Id', $valid);

    expect($this->getJson('/api/v1/nope', ['X-Request-Id' => "bad\nvalue"])->headers->get('X-Request-Id'))->not->toBe("bad\nvalue");
});
