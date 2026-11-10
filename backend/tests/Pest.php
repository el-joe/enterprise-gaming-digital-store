<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\TwoFactorService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Create a staff member signed in with a session that has passed the two-factor challenge.
 */
function actingAsVerifiedStaff(?User $staff = null): User
{
    $staff ??= User::factory()->staff()->withTwoFactor()->create();

    test()->actingAs($staff)->withSession([TwoFactorService::SESSION_PASSED_KEY => now()->getTimestamp()]);

    return $staff;
}
