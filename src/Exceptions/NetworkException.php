<?php

declare(strict_types=1);

namespace Moderyo\Exceptions;

/**
 * Network error
 */
class NetworkException extends ModeryoException
{
    public function __construct(string $message = 'Network error', ?\Throwable $previous = null)
    {
        parent::__construct($message, 'NETWORK_ERROR', null, $previous);
    }
}
