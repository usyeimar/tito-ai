<?php

namespace App\Http\Controllers\Tenant\API\System\ColumnConfiguration;

use App\Actions\Tenant\System\ColumnConfiguration\CreateSystemUserColumnConfiguration;
use App\Actions\Tenant\System\ColumnConfiguration\DeleteSystemUserColumnConfiguration;
use App\Actions\Tenant\System\ColumnConfiguration\ListSystemUserColumnConfigurations;
use App\Actions\Tenant\System\ColumnConfiguration\UpdateSystemUserColumnConfiguration;
use App\Data\Tenant\System\ColumnConfiguration\CreateSystemUserColumnConfigurationData;
use App\Data\Tenant\System\ColumnConfiguration\SystemUserColumnConfigurationData;
use App\Data\Tenant\System\ColumnConfiguration\UpdateSystemUserColumnConfigurationData;
use App\Http\Controllers\Controller;
use App\Models\Tenant\System\ColumnConfiguration\SystemUserColumnConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\PaginatedDataCollection;

final class SystemUserColumnConfigurationsController extends Controller
{
    public function index(Request $request, ListSystemUserColumnConfigurations $action): PaginatedDataCollection
    {
        Gate::authorize('viewAny', SystemUserColumnConfiguration::class);

        return $action($request->all(), $request->user()->getKey());
    }

    public function store(Request $request, CreateSystemUserColumnConfigurationData $data, CreateSystemUserColumnConfiguration $action): JsonResponse
    {
        Gate::authorize('create', SystemUserColumnConfiguration::class);

        return SystemUserColumnConfigurationData::from($action($data, $request->user()->getKey()))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function show(SystemUserColumnConfiguration $systemUserColumnConfiguration): SystemUserColumnConfigurationData
    {
        $this->authorizeOwnership($systemUserColumnConfiguration);

        return SystemUserColumnConfigurationData::from($systemUserColumnConfiguration);
    }

    public function update(
        SystemUserColumnConfiguration $systemUserColumnConfiguration,
        UpdateSystemUserColumnConfigurationData $data,
        UpdateSystemUserColumnConfiguration $action,
    ): SystemUserColumnConfigurationData {
        $this->authorizeOwnership($systemUserColumnConfiguration);

        return SystemUserColumnConfigurationData::from($action($systemUserColumnConfiguration, $data));
    }

    public function destroy(SystemUserColumnConfiguration $systemUserColumnConfiguration, DeleteSystemUserColumnConfiguration $action): Response
    {
        $this->authorizeOwnership($systemUserColumnConfiguration);

        $action($systemUserColumnConfiguration);

        return response()->noContent();
    }

    private function authorizeOwnership(SystemUserColumnConfiguration $config): void
    {
        abort_unless($config->user_id === auth()->id(), 403);
    }
}
