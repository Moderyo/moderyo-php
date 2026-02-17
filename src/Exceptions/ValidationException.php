<?php

declare(strict_types=1);

namespace Moderyo\Exceptions;

/**
 * Validation error (400)
 */
class ValidationException extends ModeryoException
{
    public function __construct(
        string $message = 'Validation failed',
        public readonly ?string $field = null
    ) {
        parent::__construct($message, 'VALIDATION_ERROR', 400);
    }
}
