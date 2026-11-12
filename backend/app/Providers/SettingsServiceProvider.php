<?php

namespace App\Providers;

use App\Domains\Settings\Services\SettingDefinitions;
use App\Domains\Settings\Services\SettingsOverlay;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Settings from the database (owner request 2026-11-12): applies the database settings that
 * replaced `.env` keys over their config keys at boot, re-checks the version before each job in a
 * long-running queue worker, and before each request under Octane (listened to by class name;
 * Octane is not installed here). Registered first so later providers already see the values.
 */
class SettingsServiceProvider extends ServiceProvider
{
    /** Long-running worker commands that re-check the settings version before each job. */
    private const array WORKER_COMMANDS = ['queue:work', 'queue:listen', 'horizon', 'horizon:work'];

    public function register(): void
    {
        $this->app->singleton(SettingDefinitions::class);
        $this->app->singleton(SettingsOverlay::class, static fn ($app): SettingsOverlay => new SettingsOverlay($app->make(SettingDefinitions::class), $app->make(Repository::class)));
    }

    public function boot(): void
    {
        if ($this->app->runningConsoleCommand(SettingsOverlay::SKIPPED_COMMANDS)) {
            return;
        }

        $overlay = $this->app->make(SettingsOverlay::class);
        $overlay->boot();
        $overlay->watch($this->app->runningConsoleCommand(self::WORKER_COMMANDS));

        Event::listen(JobProcessing::class, static function () use ($overlay): void {
            if ($overlay->watching()) {
                $overlay->refresh();
            }
        });

        Event::listen('Laravel\Octane\Events\RequestReceived', static function (object $event) use ($overlay): void {
            $sandbox = $event->sandbox ?? null;

            if (is_object($sandbox) && method_exists($sandbox, 'make')) {
                $overlay->refresh(config: $sandbox->make('config'));
            }
        });
    }
}
