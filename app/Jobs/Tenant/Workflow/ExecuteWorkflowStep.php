<?php

namespace App\Jobs\Tenant\Workflow;

use App\Models\Tenant\Workflow\WorkflowRun;
use App\Services\Tenant\Activity\DTOs\ActivityContext;
use App\Services\Tenant\Activity\Support\ActivityContextStore;
use App\Services\Tenant\Workflow\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteWorkflowStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $runId,
        public string $stepId,
        public array $context = [] // For loops or local variables
    ) {}

    public function handle(WorkflowExecutor $executor): void
    {
        Log::info("WorkflowStep: Executing step {$this->stepId} for run {$this->runId}");

        $run = WorkflowRun::query()->findOrFail($this->runId);

        $workflowId = $run?->workflow_id ? (string) $run?->workflow_id : null;
        $context = new ActivityContext(
            origin: 'workflow',
            workflowActorType: $workflowId !== null ? 'workflow' : null,
            workflowActorId: $workflowId,
            workflowActorLabel: $workflowId !== null ? 'workflow:'.$workflowId : null,
            workflowRunId: (string) $run?->getKey(),
            originMetadata: [
                'step_id' => $this->stepId,
            ],
        );

        ActivityContextStore::runWith($context, function () use ($executor, $run): void {
            $executor->processStep($run, $this->stepId, $this->context);
        });
    }
}
