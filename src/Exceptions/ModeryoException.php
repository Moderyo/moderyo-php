<?php

declare(strict_types=1);

namespace Moderyo\Exceptions;

/**
 * Base exception for Moderyo SDK
 */
class ModeryoException extends \Exception
{
    public readonly ?string $errorCode;
    public readonly ?int $statusCode;

    public function __construct(
        string $message = '',
        ?string $errorCode = null,
        ?int $statusCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }
}
