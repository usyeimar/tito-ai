<?php

namespace App\Jobs\Tenant\Workflow;

use App\Models\Tenant\Workflow\WorkflowRun;
use App\Models\Tenant\Workflow\WorkflowVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $versionId,
        protected array $initialContext,
        protected ?string $triggerContextId = null,
        protected string $triggerType = 'MANUAL',
        protected array $changes = []
    ) {}

    /**
     * Prevent race conditions if the same record triggers multiple workflows at once.
     */
    public function middleware(): array
    {
        $key = $this->getLockKey();

        if (! $key) {
            return [];
        }

        return [new WithoutOverlapping($key)
            ->releaseAfter(10)
            ->expireAfter(60)];
    }

    /**
     * Genera una llave de bloqueo inteligente según el origen del evento.
     *
     * @throws \JsonException
     */
    protected function getLockKey(): ?string
    {
        return match ($this->triggerType) {
            'RECORD_CREATED', 'RECORD_UPDATED', 'RECORD_DELETED' => "wf_lock:{$this->versionId}:rec:".($this->triggerContextId ?? 'unknown'),

            'SCHEDULED' => "wf_lock:{$this->versionId}:scheduled",

            'WEBHOOK' => "wf_lock:{$this->versionId}:wh:".($this->triggerContextId ?? md5(json_encode($this->initialContext, JSON_THROW_ON_ERROR))),

            'RING_CENTRAL_INSIGHTS_READY' => "wf_lock:{$this->versionId}:rc_ins:".($this->triggerContextId ?? 'global'),
            'RING_CENTRAL_VOICEMAIL_RECEIVED' => "wf_lock:{$this->versionId}:rc_vm:".($this->triggerContextId ?? 'global'),

            'MANUAL' => null,

            default => "wf_lock:{$this->versionId}:gen:".($this->triggerContextId ?? 'global'),
        };
    }

    public function handle(): void
    {
        Log::info("Workflow: Processing start for version {$this->versionId} (Trigger: {$this->triggerType})");

        $version = WorkflowVersion::query()->findOrFail($this->versionId);

        $trigger = $version?->trigger ?? [];
        $next_step_ids = $trigger['next_step_ids'] ?? [];

        //        if (empty($next_step_ids)) {
        //            $steps = collect($version->steps ?? []);
        //            $next_step_ids = $steps->filter(function ($step) {
        //                $parents = $step['parent_step_ids'] ?? [];
        //
        //                return empty($parents) || in_array('trigger', $parents, true);
        //            })->pluck('id')->toArray();
        //
        //            if (! empty($next_step_ids)) {
        //                Log::info("Workflow: No explicit next_step_ids in trigger for version {$this->versionId}. Found " . count($next_step_ids) . " starting steps via parent references: " . implode(', ', $next_step_ids));
        //            }
        //        }

        if (empty($next_step_ids)) {
            Log::warning("Workflow: Version {$this->versionId} has no starting steps. Run will be empty.");
        }

        $context_data = match ($this->triggerType) {
            'WEBHOOK' => ['request' => $this->initialContext],
            'SCHEDULED' => ['scheduled_time' => now()->toIso8601String()],
            'RECORD_DELETED' => ['record' => $this->initialContext],
            'RING_CENTRAL_INSIGHTS_READY' => [
                'call_id' => $this->triggerContextId,
                'insights' => $this->initialContext,
            ],
            'RING_CENTRAL_VOICEMAIL_RECEIVED' => [
                'voicemail_id' => $this->triggerContextId,
                'voicemail' => $this->initialContext,
            ],
            default => ['record' => $this->initialContext],
        };

        $run = WorkflowRun::create([
            'workflow_version_id' => $version->id,
            'workflow_id' => $version->workflow_id,
            'trigger_record_id' => $this->triggerContextId,
            'trigger_type' => $this->triggerType,
            'status' => 'RUNNING',
            'started_at' => now(),
            'pending_steps' => count($next_step_ids),
            'state' => [
                'trigger' => array_filter([
                    'type' => $this->triggerType,
                    ...$context_data,
                    'changes' => $this->changes,
                    'timestamp' => now()->toISOString(),
                ], fn ($v) => ! is_null($v)),
                'steps' => [],
            ],
        ]);

        Log::info("Workflow: Created run {$run->id} for version {$this->versionId}");

        foreach ($next_step_ids as $stepId) {
            Log::info("Workflow: Dispatching initial step {$stepId} for run {$run->id}");
            ExecuteWorkflowStep::dispatch($run->id, $stepId, []);
        }
    }
}
