<?php

namespace Database\Seeders;

use App\Domains\Identity\Enums\RoleName;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * Create the first Super Admin from SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD.
     * No default credentials exist; the seeder fails when either variable is empty.
     * Run explicitly: php artisan db:seed --class=SuperAdminSeeder
     */
    public function run(): void
    {
        $email = config('identity.super_admin.email');
        $password = config('identity.super_admin.password');

        $missing = array_keys(array_filter(
            ['SUPER_ADMIN_EMAIL' => $email, 'SUPER_ADMIN_PASSWORD' => $password],
            static fn (mixed $value): bool => ! is_string($value) || trim($value) === '',
        ));

        if ($missing !== []) {
            throw new RuntimeException('SuperAdminSeeder requires environment variables: '.implode(', ', $missing).'.');
        }

        if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/', $password) !== 1) {
            throw new RuntimeException('SUPER_ADMIN_PASSWORD must be at least 12 characters with upper case, lower case and a number.');
        }

        $this->call(RolePermissionSeeder::class);

        $user = User::query()->firstOrNew(['email' => strtolower($email)]);

        $user->forceFill([
            'name' => 'Super Admin',
            'password' => $password,
            'type' => UserType::Staff,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ])->save();

        $user->syncRoles([RoleName::SuperAdmin->value]);
    }
}
