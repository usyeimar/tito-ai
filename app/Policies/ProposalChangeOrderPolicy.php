<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Proposals\ProposalChangeOrder;
use App\Services\Tenant\Assignments\AssignmentService;
use Illuminate\Database\Eloquent\Model;

class ProposalChangeOrderPolicy extends ModulePolicy
{
    protected string $module = 'proposal_change_order';

    public function __construct(
        private readonly AssignmentService $assignmentService,
    ) {}

    public function view(User $user, Model $model): bool
    {
        return $model instanceof ProposalChangeOrder
            && $this->canView($user)
            && $this->canViewOwner($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $model instanceof ProposalChangeOrder
            && $this->canManage($user)
            && $this->canViewOwner($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $model instanceof ProposalChangeOrder
            && $this->canDelete($user)
            && $this->canViewOwner($user, $model);
    }

    public function revise(User $user, ProposalChangeOrder $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function send(User $user, ProposalChangeOrder $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    public function attach(User $user, ProposalChangeOrder $proposal): bool
    {
        return $this->update($user, $proposal);
    }

    private function canViewOwner(User $user, ProposalChangeOrder $proposal): bool
    {
        $owner = $proposal->owner;

        if ($owner === null) {
            return false;
        }

        return $this->assignmentService->canViewUncloaked($user, $owner);
    }
}
