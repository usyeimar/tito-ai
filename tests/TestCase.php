<?php

namespace Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * The connections that should be refreshed during tests.
     * Both pgsql and central point to the same testing_app database,
     * so we only transact on pgsql. The central connection is configured
     * to reuse the same PDO instance in createApplication().
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['pgsql'];

    /**
     * Creates the application for the test.
     *
     * Sail's compose loads `.env` into the container environment, which
     * overrides phpunit.xml `<env force="true">`. We pin the test database
     * after boot so both the default and central connections hit
     * `testing_app` instead of the development database.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $app['config']->set('database.connections.pgsql.database', 'testing_app');
        $app['config']->set('database.connections.central.database', 'testing_app');
        $app['config']->set('app.env', 'testing');
        $app['env'] = 'testing';

        return $app;
    }

    /**
     * Ensure the central connection shares the same PDO as pgsql
     * so both connections participate in the same test transaction.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $pdo = $this->app['db']->connection('pgsql')->getPdo();
        $this->app['db']->connection('central')->setPdo($pdo)->setReadPdo($pdo);
    }

    /**
     * Skip the test if the given Fortify feature is disabled.
     */
    protected function skipUnlessFortifyFeature(string $feature): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped("Fortify feature [{$feature}] is disabled.");
        }
    }
}
