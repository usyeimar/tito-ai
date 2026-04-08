<?php

namespace App\Enums;

enum FileUploadSessionStatus: string
{
    case INITIATED = 'initiated';
    case UPLOADING = 'uploading';
    case COMPLETING = 'completing';
    case COMPLETED = 'completed';
    case ABORTED = 'aborted';
    case EXPIRED = 'expired';
    case FAILED = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::ABORTED, self::EXPIRED, self::FAILED], true);
    }
}
