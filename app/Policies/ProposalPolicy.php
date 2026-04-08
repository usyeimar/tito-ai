<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Proposals\Proposal;
use App\Services\Tenant\Assignments\AssignmentService;
use Illuminate\Database\Eloquent\Model;

class ProposalPolicy extends ModulePolicy
{
    protected string $module = 'proposal';

    public function __construct(
        private readonly AssignmentService $assignmentService,
    ) {}

    public function view(User $user, Model $model): bool
    {
        return $model instanceof Proposal
            && $this->canView($user)
            && $this->canViewOwner($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $model instanceof Proposal
            && $this->canManage($user)
            && $this->canViewOwner($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $model instanceof Proposal
            && $this->canDelete($user)
            && $this->canViewOwner($user, $model);
    }

    public function revise(User $user, Proposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function send(User $user, Proposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function attach(User $user, Proposal $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    private function canViewOwner(User $user, Proposal $proposal): bool
    {
        $owner = $proposal->owner;

        if ($owner === null) {
            return false;
        }

        return $this->assignmentService->canViewUncloaked($user, $owner);
    }
}
