<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public const string TEST_DATABASE = 'gaming-store_testing';

    /**
     * Creates the application and refuses to run against anything but the test database.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

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
