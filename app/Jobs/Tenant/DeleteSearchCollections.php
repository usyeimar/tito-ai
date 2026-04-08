<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Search\Typesense\TypesenseHttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;

class DeleteSearchCollections implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected TenantWithDatabase $tenant) {}

    public function handle(TypesenseHttpClient $client): void
    {
        $prefix = $this->tenant->scout_prefix;

        foreach ($client->listCollections() as $collection) {
            $name = (string) ($collection['name'] ?? '');

            if ($name !== '' && str_starts_with($name, $prefix)) {
                $client->deleteCollection($name);
            }
        }
    }
}
