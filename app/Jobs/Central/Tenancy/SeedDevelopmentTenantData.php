<?php

namespace App\Jobs\Central\Tenancy;

use Database\Seeders\Tenant\DevelopmentTenantDatabaseSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SeedDevelopmentTenantData
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        Artisan::call('tenants:seed', array_merge(
            (array) config('tenancy.seeder_parameters', []),
            [
                '--class' => DevelopmentTenantDatabaseSeeder::class,
                '--tenants' => [$this->tenantId],
            ],
        ));
    }
}
