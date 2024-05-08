<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Exception;

use Throwable;

/**
 * ApiAuthException thrown for API authentication (403) errors.
 */
class ApiAuthException extends AuthException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $code != 403 ? $previous : null);
    }
}
