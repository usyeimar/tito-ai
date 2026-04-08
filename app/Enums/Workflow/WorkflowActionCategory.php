<?php

declare(strict_types=1);

namespace App\Enums\Workflow;

enum WorkflowActionCategory: string
{
    case LOGIC = 'LOGIC';
    case CRM = 'CRM';
    case COMMUNICATION = 'COMMUNICATION';
    case HELPER = 'HELPER';
    case AI = 'AI';

    public function label(): string
    {
        return match ($this) {
            self::LOGIC => 'Logic & Flow',
            self::CRM => 'CRM Operations',
            self::COMMUNICATION => 'Communication',
            self::HELPER => 'Helpers & Utilities',
            self::AI => 'AI & Intelligence',
        };
    }
}
