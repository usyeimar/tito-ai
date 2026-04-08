<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Workflow\Workflow;

class WorkflowPolicy extends ModulePolicy
{
    protected string $module = 'workflow';

    public function run(User $user, Workflow $workflow): bool
    {
        return $this->isVerified($user) && $user->can('workflow.execute');
    }

    public function approve(User $user, Workflow $workflow): bool
    {
        return $this->isVerified($user) && $user->can('workflow.approve');
    }
}
