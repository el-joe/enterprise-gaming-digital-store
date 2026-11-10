<?php

namespace App\Console\Commands\Mobile;

use App\Domains\Settings\Enums\AppConfigSetting;
use App\Domains\Settings\Services\AppConfigService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mobile:app-config
    {action : show or set}
    {key? : Setting key (set), e.g. android_min_version, maintenance, terms_url}
    {value? : New value (set): a semantic version, true/false, a URL or a JSON message object}')]
#[Description('Show or change the public mobile app configuration (GET /api/v1/app/config) without a deploy')]
class AppConfigCommand extends Command
{
    public function handle(AppConfigService $config): int
    {
        return match ($this->argument('action')) {
            'show' => $this->show($config),
            'set' => $this->set($config),
            default => $this->failWith('Action must be show or set.'),
        };
    }

    private function show(AppConfigService $config): int
    {
        $this->table(['Key', 'Value'], array_map(
            static fn (AppConfigSetting $setting): array => [$setting->value, json_encode($config->value($setting), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
            AppConfigSetting::cases(),
        ));

        return self::SUCCESS;
    }

    private function set(AppConfigService $config): int
    {
        $setting = AppConfigSetting::tryFrom((string) $this->argument('key'));

        if ($setting === null) {
            return $this->failWith('Unknown key. Known: '.implode(', ', array_map(static fn (AppConfigSetting $case): string => $case->value, AppConfigSetting::cases())));
        }

        if ($this->argument('value') === null) {
            return $this->failWith('Give the new value.');
        }

        try {
            $config->set($setting, (string) $this->argument('value'));
        } catch (ApplicationException $exception) {
            return $this->failWith(implode(' ', array_merge(...array_values($exception->errors))));
        }

        $this->info("{$setting->value} saved. Clients see it within the response cache time.");

        return self::SUCCESS;
    }

    private function failWith(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
