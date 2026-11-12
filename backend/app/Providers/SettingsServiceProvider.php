<?php

namespace App\Providers;

use App\Domains\Settings\Services\SettingDefinitions;
use App\Domains\Settings\Services\SettingsOverlay;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Bootstrap\SetRequestForConsole;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Settings from the database (owner request 2026-11-12, owner decision 11): applies the database
 * settings that replaced `.env` keys over their config keys at boot, re-checks the version before
 * each job in a long-running queue worker, and before each request under Octane (listened to by
 * class name; Octane is not installed here). Registered first so later providers already see the
 * values.
 *
 * Some services are built from config before this provider boots, or are kept by a long-running
 * worker; after the overlay changed their keys (at boot and before a worker's job, never in the
 * middle of a request) syncRuntime() brings them in line: the translator locales, resolved mailers
 * and disks, and in the console the request the URL generator builds links from (`app.url`, which
 * Laravel reads into a fake console request before any provider runs).
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
        $this->syncRuntime($overlay->boot());
        $overlay->watch($this->app->runningConsoleCommand(self::WORKER_COMMANDS));

        Event::listen(JobProcessing::class, function () use ($overlay): void {
            if ($overlay->watching()) {
                $this->syncRuntime($overlay->refresh());
            }
        });

        Event::listen('Laravel\Octane\Events\RequestReceived', static function (object $event) use ($overlay): void {
            $sandbox = $event->sandbox ?? null;

            if (is_object($sandbox) && method_exists($sandbox, 'make')) {
                $overlay->refresh(config: $sandbox->make('config'));
            }
        });
    }

    /**
     * Reset what was built from config keys the overlay just changed.
     *
     * @param  list<string>  $changed
     */
    public function syncRuntime(array $changed): void
    {
        if ($changed === []) {
            return;
        }

        $config = $this->app->make('config');

        if (in_array('app.locale', $changed, true)) {
            $this->app->setLocale((string) $config->get('app.locale'));
        }

        if (in_array('app.fallback_locale', $changed, true) && $this->app->resolved('translator')) {
            $this->app->make('translator')->setFallback((string) $config->get('app.fallback_locale'));
        }

        if (in_array('app.url', $changed, true) && $this->app->runningInConsole()) {
            (new SetRequestForConsole)->bootstrap($this->app);
        }

        if ($this->changedUnder($changed, 'mail.') && $this->app->resolved('mail.manager')) {
            $this->app->make('mail.manager')->forgetMailers();
            $this->app->forgetInstance('mailer');
        }

        if ($this->changedUnder($changed, 'filesystems.') && $this->app->resolved('filesystem')) {
            $this->app->make('filesystem')->forgetDisk(array_keys((array) $config->get('filesystems.disks')));
            $this->app->forgetInstance('filesystem.disk');
            $this->app->forgetInstance('filesystem.cloud');
        }
    }

    /**
     * @param  list<string>  $changed
     */
    private function changedUnder(array $changed, string $prefix): bool
    {
        foreach ($changed as $key) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
