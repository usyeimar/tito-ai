<?php

namespace App\Support\Celery;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CeleryDispatcher
{
    /**
     * The Redis connection to use.
     */
    protected string $connection;

    /**
     * The Celery queue name (Redis list name).
     */
    protected string $queue;

    public function __construct(string $connection = 'default', string $queue = 'celery')
    {
        $this->connection = $connection;
        $this->queue = $queue;
    }

    /**
     * Dispatch a task to Celery.
     *
     * @param  string  $task  Name of the Python task
     * @param  array  $args  Positional arguments
     * @param  array  $kwargs  Keyword arguments
     */
    public function dispatch(string $task, array $args = [], array $kwargs = []): bool
    {
        try {
            $payload = CeleryTask::payload($task, $args, $kwargs);

            // Push to the Redis list
            Redis::connection($this->connection)->rpush(
                $this->queue,
                json_encode($payload)
            );

            Log::debug("Celery task dispatched: {$task}", [
                'id' => $payload['headers']['id'],
                'queue' => $this->queue,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to dispatch Celery task: {$e->getMessage()}", [
                'task' => $task,
                'exception' => $e,
            ]);

            return false;
        }
    }
}
