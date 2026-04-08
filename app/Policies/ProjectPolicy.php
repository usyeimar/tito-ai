<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\CRM\Projects\Project;
use App\Services\Tenant\Assignments\AssignmentService;
use Illuminate\Database\Eloquent\Model;

class ProjectPolicy extends ModulePolicy
{
    protected string $module = 'project';

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

    public function clone(User $user, Model $model): bool
    {
        return $this->canManage($user) && $this->assignmentService->canViewUncloaked($user, $model);
    }

    public function assign(User $user, Project $project): bool
    {
        return $this->canManage($user) && $this->assignmentService->canAssign($user, $project);
    }
}
