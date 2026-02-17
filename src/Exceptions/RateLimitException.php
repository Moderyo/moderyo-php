<?php

declare(strict_types=1);

namespace Moderyo\Exceptions;

/**
 * Rate limit exceeded (429)
 */
class RateLimitException extends ModeryoException
{
    public function __construct(
        string $message = 'Rate limit exceeded',
        public readonly float $retryAfter = 60.0
    ) {
        parent::__construct($message, 'RATE_LIMIT_ERROR', 429);
    }
}
