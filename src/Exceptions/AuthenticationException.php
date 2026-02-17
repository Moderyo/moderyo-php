<?php

declare(strict_types=1);

namespace Moderyo\Exceptions;

/**
 * Authentication failed (401)
 */
class AuthenticationException extends ModeryoException
{
    public function __construct(string $message = 'Authentication failed')
    {
        parent::__construct($message, 'AUTHENTICATION_ERROR', 401);
    }
}
