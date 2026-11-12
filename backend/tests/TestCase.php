<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\Support\TestConfiguration;

abstract class TestCase extends BaseTestCase
{
    public const string TEST_DATABASE = 'gaming-store_testing';

    /**
     * Creates the application and refuses to run against anything but the test database.
     */
    public function createApplication(): Application
    {
        $app = require Application::inferBasePath().'/bootstrap/app.php';

        $this->traitsUsedByTest = class_uses_recursive(static::class);

        // Test-only code-vault keys and mailer (owner decision 11: no longer read from the environment).
        TestConfiguration::register($app);

        $app->make(Kernel::class)->bootstrap();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'mysql' || $database !== self::TEST_DATABASE) {
            throw new RuntimeException('Refusing to run tests outside the '.self::TEST_DATABASE.' MySQL database.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }
}
