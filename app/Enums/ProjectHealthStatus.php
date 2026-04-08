<?php

namespace App\Enums;

enum ProjectHealthStatus: string
{
    case ON_TRACK = 'on_track';
    case AT_RISK = 'at_risk';
    case OFF_TRACK = 'off_track';
    case BLOCKED = 'blocked';
    case COMPLETED = 'completed';
    case ON_HOLD = 'on_hold';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ON_TRACK => 'On Track',
            self::AT_RISK => 'At Risk',
            self::OFF_TRACK => 'Off Track',
            self::BLOCKED => 'Blocked',
            self::COMPLETED => 'Completed',
            self::ON_HOLD => 'On Hold',
        };
    }

    /**
     * Get a color representation for UI purposes.
     */
    public function color(): string
    {
        return match ($this) {
            self::ON_TRACK => 'green',
            self::AT_RISK => 'yellow',
            self::OFF_TRACK => 'orange',
            self::BLOCKED => 'red',
            self::COMPLETED => 'blue',
            self::ON_HOLD => 'gray',
        };
    }

    /**
     * Get a description of what this status means.
     */
    public function description(): string
    {
        return match ($this) {
            self::ON_TRACK => 'Project is progressing as planned with no issues.',
            self::AT_RISK => 'Project has potential issues that may impact delivery.',
            self::OFF_TRACK => 'Project is behind schedule or over budget.',
            self::BLOCKED => 'Project cannot proceed due to blocking issues.',
            self::COMPLETED => 'Project has been successfully completed.',
            self::ON_HOLD => 'Project is temporarily paused.',
        };
    }
}
