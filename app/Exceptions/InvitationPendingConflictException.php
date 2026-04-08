<?php

namespace App\Exceptions;

use RuntimeException;

class InvitationPendingConflictException extends RuntimeException
{
    public function __construct(string $message = 'A pending invitation already exists for this email in this workspace.')
    {
        parent::__construct($message);
    }
}
