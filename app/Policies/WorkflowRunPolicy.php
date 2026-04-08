<?php

namespace App\Policies;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Workflow\WorkflowRun;

class WorkflowRunPolicy extends ModulePolicy
{
    protected string $module = 'workflow_run';

    public function retry(User $user, WorkflowRun $run): bool
    {
        // Retry requires 'run' or 'update' permission on the workflow
        return $user->can('run', $run->workflow) || $user->can('update', $run->workflow);
    }

    public function cancel(User $user, WorkflowRun $run): bool
    {
        // Cancel requires 'run' or 'update' permission on the workflow
        return $user->can('run', $run->workflow) || $user->can('update', $run->workflow);
    }
}
