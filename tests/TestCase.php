<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * The connections that should be refreshed during tests.
     * We transact the default connection (pgsql) which is the central database.
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['pgsql'];

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
