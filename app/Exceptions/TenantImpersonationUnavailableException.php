<?php

namespace App\Exceptions;

use RuntimeException;

class TenantImpersonationUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Your account cannot access this workspace right now.')
    {
        parent::__construct($message);
    }
}
