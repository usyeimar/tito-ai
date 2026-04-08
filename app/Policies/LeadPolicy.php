<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\Leads\Lead;
use App\Services\Tenant\Assignments\AssignmentService;
use Illuminate\Database\Eloquent\Model;

class LeadPolicy extends ModulePolicy
{
    protected string $module = 'lead';

    public function __construct(
        private readonly AssignmentService $assignmentService,
    ) {}

    public function view(User $user, Model $model): bool
    {
        return $this->canView($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->canManage($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->canDelete($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->canManage($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->canDelete($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function convert(User $user, Model $model): bool
    {
        return $this->canManage($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $this->canManage($user) && $this->assignmentService->canAssign($user, $lead);
    }
}
