<?php

declare(strict_types=1);

namespace Moderyo\Exceptions;

/**
 * Quota exceeded (402)
 */
class QuotaExceededException extends ModeryoException
{
    public function __construct(string $message = 'Quota exceeded')
    {
        parent::__construct($message, 'QUOTA_EXCEEDED', 402);
    }
}
