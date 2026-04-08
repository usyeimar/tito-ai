<?php

declare(strict_types=1);

namespace App\Enums\Workflow;

enum WorkflowRunStatus: string
{
    case PENDING = 'PENDING';
    case RUNNING = 'RUNNING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';

    /**
     * Get all possible values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if the status is terminal (cannot be changed).
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }

    /**
     * Check if the status is active (can be cancelled).
     */
    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::RUNNING]);
    }
}
