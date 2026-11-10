<?php

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\Exceptions\AppendOnlyViolation;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;

it('writes an audit row with the actor and request id', function (): void {
    $staff = User::factory()->staff()->create();
    $this->actingAs($staff);
    Context::add('request_id', '01JC0000000000000000000000');

    $log = app(AuditLogger::class)->record(new AuditEntry(action: 'product.price.updated', subject: $staff, reason: 'test'));

    expect($log->actor_type)->toBe(AuditLogActorType::Staff)
        ->and($log->actor_id)->toBe($staff->id)
        ->and($log->subject_type)->toBe('user')
        ->and($log->request_id)->toBe('01JC0000000000000000000000');
});

it('records system as the actor when nobody is signed in', function (): void {
    $log = app(AuditLogger::class)->record(new AuditEntry(action: 'job.ran'));

    expect($log->actor_type)->toBe(AuditLogActorType::System)->and($log->actor_id)->toBeNull();
});

it('redacts secrets from audit snapshots', function (): void {
    $log = app(AuditLogger::class)->record(new AuditEntry(
        action: 'supplier.credentials.set',
        before: ['api_key' => 'CANARY', 'name' => 'Reloadly'],
        after: ['nested' => ['client_secret' => 'CANARY']],
    ));

    $stored = DB::table('audit_logs')->where('id', $log->id)->value('before').DB::table('audit_logs')->where('id', $log->id)->value('after');

    expect($stored)->not->toContain('CANARY')->toContain('Reloadly');
});

it('refuses to update or delete audit rows', function (): void {
    $log = app(AuditLogger::class)->record(new AuditEntry(action: 'job.ran'));

    expect(fn () => $log->update(['action' => 'changed']))->toThrow(AppendOnlyViolation::class)
        ->and(fn () => $log->delete())->toThrow(AppendOnlyViolation::class)
        ->and(AuditLog::query()->count())->toBe(1);
});

it('round-trips each setting type', function (SettingType $type, mixed $value, mixed $expected): void {
    $settings = app(SettingsService::class);

    $settings->set('group', 'key', $value, $type);

    expect($settings->get('group', 'key'))->toBe($expected);
})->with([
    'string' => [SettingType::String, 'USD', 'USD'],
    'int' => [SettingType::Int, 1440, 1440],
    'bool true' => [SettingType::Bool, true, true],
    'bool false' => [SettingType::Bool, false, false],
    'decimal stays a string' => [SettingType::Decimal, '10.5', '10.5'],
    'json' => [SettingType::Json, ['a' => 1], ['a' => 1]],
]);

it('returns the default for a missing setting', function (): void {
    expect(app(SettingsService::class)->get('group', 'missing', 'fallback'))->toBe('fallback');
});

it('rejects floats', function (): void {
    expect(fn () => app(SettingsService::class)->set('group', 'key', 1.5, SettingType::Decimal))
        ->toThrow(InvalidArgumentException::class);
});

it('reads the new value after an update, despite the cache', function (): void {
    $settings = app(SettingsService::class);
    $settings->set('group', 'key', 'first', SettingType::String);
    expect($settings->get('group', 'key'))->toBe('first');

    $settings->set('group', 'key', 'second', SettingType::String);

    expect($settings->get('group', 'key'))->toBe('second');
});

it('encrypts secret settings at rest and never audits their value', function (): void {
    $settings = app(SettingsService::class);

    $settings->set('oxr', 'app_id', 'CANARY-SECRET', SettingType::String, encrypted: true);

    $raw = DB::table('settings')->where('group', 'oxr')->value('value');

    expect($raw)->not->toContain('CANARY-SECRET')
        ->and($settings->get('oxr', 'app_id'))->toBe('CANARY-SECRET')
        ->and(json_encode(AuditLog::query()->first()->toArray()))->not->toContain('CANARY-SECRET')
        ->and(Setting::query()->first()->toArray())->not->toHaveKey('value');
});
