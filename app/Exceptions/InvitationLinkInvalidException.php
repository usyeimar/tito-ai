<?php

namespace App\Exceptions;

use RuntimeException;

class InvitationLinkInvalidException extends RuntimeException
{
    public function __construct(string $message = 'This invitation link is no longer valid. Please request a new invitation.')
    {
        parent::__construct($message);
    }
}
