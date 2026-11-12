<?php

namespace Tests\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

/**
 * Test-only configuration ("only the Super Admin and technical bootstrap keys stay in .env",
 * owner decision 11). The code-vault keys and the mailer are database settings now, so the config
 * files no longer read them from the environment; the test suite gets them here instead, applied
 * right after the config files load and before any provider boots. They are therefore the config
 * defaults SettingsOverlay captures, exactly as the former phpunit.xml environment values were.
 *
 * The keys are the former phpunit.xml values: test-only, never used anywhere else.
 */
final class TestConfiguration
{
    public const string CODE_KEYS = '1:leAn/2LNolW8/NgFlK6W2OyXpl+8JhoAlzXY1uoP8zA=,2:Jfh7N9JV12lrBqJ3Vd3dzz6cyQCuc61yoe9QvkSHrnU=';

    public const string ACTIVE_KID = '1';

    public const string BLIND_INDEX_KEY = 'beb8142fdba11be5e392d3f477696005c9125c2cc0c9f5352ed8f5bd229267e9';

    /**
     * @var array<string, mixed>
     */
    public const array VALUES = [
        'fulfillment.code_keys' => self::CODE_KEYS,
        'fulfillment.active_kid' => self::ACTIVE_KID,
        'fulfillment.blind_index_key' => self::BLIND_INDEX_KEY,
        'mail.default' => 'array',
    ];

    /**
     * Apply the values to an application before it is bootstrapped (TestCase, concurrency workers).
     */
    public static function register(Application $app): void
    {
        $app->afterBootstrapping(LoadConfiguration::class, static function (Application $app): void {
            self::apply($app->make('config'));
        });
    }

    public static function apply(Repository $config): void
    {
        $config->set(self::VALUES);
    }
}
