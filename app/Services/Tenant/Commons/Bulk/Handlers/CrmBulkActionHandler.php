<?php

namespace App\Services\Tenant\Commons\Bulk\Handlers;

use App\Actions\Tenant\Crm\VendorCompany\DeleteVendorCompany;
use App\Actions\Tenant\Crm\VendorCompany\ForceDeleteVendorCompany;
use App\Actions\Tenant\Crm\VendorCompany\RestoreVendorCompany;
use App\Enums\BulkTaskItemStatus;
use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\VendorCompanies\VendorCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CrmBulkActionHandler
{
    public function __construct(
        private readonly DeleteVendorCompany $deleteVendorCompany,
        private readonly RestoreVendorCompany $restoreVendorCompany,
        private readonly ForceDeleteVendorCompany $forceDeleteVendorCompany,
    ) {}

    public function resolveModel(string $resource, string $id, string $action): ?Model
    {
        return match ($resource) {
            'vendor_company' => $this->resolveVendorCompany($id, $action),
            default => null,
        };
    }

    public function execute(User $actor, string $resource, string $action, Model $model): array
    {
        if ($this->isInvalidState($model, $action)) {
            return [
                'status' => BulkTaskItemStatus::SKIPPED_INVALID_STATE,
                'code' => 'INVALID_STATE',
                'detail' => 'Record state is not valid for this action.',
                'http_status' => 409,
                'result' => null,
            ];
        }

        try {
            return match ($resource) {
                'vendor_company' => $this->executeVendorCompany($actor, $action, $model),
                default => [
                    'status' => BulkTaskItemStatus::FAILED_VALIDATION,
                    'code' => 'VALIDATION_ERROR',
                    'detail' => 'Unsupported resource.',
                    'http_status' => 422,
                    'result' => null,
                ],
            };
        } catch (ValidationException $e) {
            return [
                'status' => BulkTaskItemStatus::FAILED_VALIDATION,
                'code' => 'VALIDATION_ERROR',
                'detail' => (string) $e->getMessage(),
                'http_status' => 422,
                'result' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => BulkTaskItemStatus::FAILED_EXCEPTION,
                'code' => 'INTERNAL_ERROR',
                'detail' => $e->getMessage(),
                'http_status' => 500,
                'result' => null,
            ];
        }
    }

    private function resolveVendorCompany(string $id, string $action): ?VendorCompany
    {
        try {
            $query = VendorCompany::query();

            if ($action !== 'clone') {
                $query->withTrashed();
            }

            return $query->whereKey($id)->firstOrFail();
        } catch (\Throwable) {
            return null;
        }
    }

    private function executeVendorCompany(User $actor, string $action, VendorCompany $vendorCompany): array
    {
        return match ($action) {
            'delete' => $this->success(function () use ($vendorCompany): void {
                ($this->deleteVendorCompany)($vendorCompany);
            }),
            'restore' => $this->success(function () use ($vendorCompany): void {
                ($this->restoreVendorCompany)($vendorCompany);
            }),
            'force' => $this->success(function () use ($vendorCompany): void {
                ($this->forceDeleteVendorCompany)($vendorCompany);
            }),
            default => $this->unsupportedAction(),
        };
    }

    private function success(callable $callback): array
    {
        $callback();

        return [
            'status' => BulkTaskItemStatus::SUCCESS,
            'code' => null,
            'detail' => null,
            'http_status' => 200,
            'result' => null,
        ];
    }

    private function successWithResult(callable $callback): array
    {
        return [
            'status' => BulkTaskItemStatus::SUCCESS,
            'code' => null,
            'detail' => null,
            'http_status' => 200,
            'result' => $callback(),
        ];
    }

    private function unsupportedAction(): array
    {
        return [
            'status' => BulkTaskItemStatus::FAILED_VALIDATION,
            'code' => 'VALIDATION_ERROR',
            'detail' => 'Unsupported action.',
            'http_status' => 422,
            'result' => null,
        ];
    }

    private function isInvalidState(Model $model, string $action): bool
    {
        if (! method_exists($model, 'trashed')) {
            return false;
        }

        $trashed = $model->trashed();

        return match ($action) {
            'delete', 'clone' => $trashed,
            'restore' => ! $trashed,
            default => false,
        };
    }
}
