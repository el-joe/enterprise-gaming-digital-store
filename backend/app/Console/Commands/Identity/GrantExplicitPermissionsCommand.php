<?php

namespace App\Console\Commands\Identity;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\ExplicitPermissionGrantService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('staff:grant-explicit {email : A Super Admin\'s email} {permissions?* : Explicit-grant permissions, or "all"} {--revoke : Remove them instead of granting}')]
#[Description('Grant or revoke explicit-grant (E) permissions for a Super Admin from the server (audited)')]
class GrantExplicitPermissionsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ExplicitPermissionGrantService $grants): int
    {
        $staff = User::query()->where('email', strtolower(trim((string) $this->argument('email'))))->first();

        if ($staff === null) {
            $this->error('No account has that email.');

            return self::FAILURE;
        }

        $explicit = array_map(static fn (PermissionName $permission): string => $permission->value, ExplicitPermissionGrantService::explicitPermissions());
        $requested = array_values((array) $this->argument('permissions'));

        if ($requested === []) {
            $held = $grants->heldBy($staff);
            $this->table(['Explicit-grant permission', 'Held'], array_map(static fn (string $name): array => [$name, in_array($name, $held, true) ? 'yes' : 'no'], $explicit));
            $this->line('Grant with: php artisan staff:grant-explicit '.$staff->email.' <permission ...> (or "all"); add --revoke to remove.');

            return self::SUCCESS;
        }

        if ($requested === ['all']) {
            $requested = $explicit;
        }

        $unknown = array_values(array_diff($requested, array_map(static fn (PermissionName $permission): string => $permission->value, PermissionName::cases())));

        if ($unknown !== []) {
            $this->error('Unknown permission: '.implode(', ', $unknown).'. Explicit-grant permissions: '.implode(', ', $explicit).'.');

            return self::FAILURE;
        }

        try {
            $after = $grants->change($staff, array_map(static fn (string $name): PermissionName => PermissionName::from($name), $requested), (bool) $this->option('revoke'));
        } catch (ApplicationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(($this->option('revoke') ? 'Revoked' : 'Granted').'. '.$staff->email.' now holds: '.($after === [] ? 'no explicit-grant permissions' : implode(', ', $after)).'. Audited.');

        return self::SUCCESS;
    }
}
