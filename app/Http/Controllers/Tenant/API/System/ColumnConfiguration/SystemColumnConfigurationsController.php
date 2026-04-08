<?php

namespace App\Http\Controllers\Tenant\API\System\ColumnConfiguration;

use App\Actions\Tenant\System\ColumnConfiguration\CreateSystemColumnConfiguration;
use App\Actions\Tenant\System\ColumnConfiguration\DeleteSystemColumnConfiguration;
use App\Actions\Tenant\System\ColumnConfiguration\ListSystemColumnConfigurations;
use App\Actions\Tenant\System\ColumnConfiguration\UpdateSystemColumnConfiguration;
use App\Data\Tenant\System\ColumnConfiguration\CreateSystemColumnConfigurationData;
use App\Data\Tenant\System\ColumnConfiguration\SystemColumnConfigurationData;
use App\Data\Tenant\System\ColumnConfiguration\UpdateSystemColumnConfigurationData;
use App\Http\Controllers\Controller;
use App\Models\Tenant\System\ColumnConfiguration\SystemColumnConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\PaginatedDataCollection;

final class SystemColumnConfigurationsController extends Controller
{
    public function index(Request $request, ListSystemColumnConfigurations $action): PaginatedDataCollection
    {
        Gate::authorize('viewAny', SystemColumnConfiguration::class);

        return $action($request->all());
    }

    public function store(Request $request, CreateSystemColumnConfigurationData $data, CreateSystemColumnConfiguration $action): JsonResponse
    {
        Gate::authorize('create', SystemColumnConfiguration::class);

        return SystemColumnConfigurationData::from($action($data))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function show(SystemColumnConfiguration $systemColumnConfiguration): SystemColumnConfigurationData
    {
        Gate::authorize('view', $systemColumnConfiguration);

        return SystemColumnConfigurationData::from($systemColumnConfiguration);
    }

    public function update(SystemColumnConfiguration $systemColumnConfiguration, UpdateSystemColumnConfigurationData $data, UpdateSystemColumnConfiguration $action): SystemColumnConfigurationData
    {
        Gate::authorize('update', $systemColumnConfiguration);

        return SystemColumnConfigurationData::from($action($systemColumnConfiguration, $data));
    }

    public function destroy(SystemColumnConfiguration $systemColumnConfiguration, DeleteSystemColumnConfiguration $action): Response
    {
        Gate::authorize('delete', $systemColumnConfiguration);

        $action($systemColumnConfiguration);

        return response()->noContent();
    }
}
