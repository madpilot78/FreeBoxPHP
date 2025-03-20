<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Exception;

use RuntimeException;

/**
 * ApiErrorException thrown for any error talking to the API.
 */
class ApiErrorException extends RuntimeException
{
    public const string SUCCESS_MISSING = 'Success field missing in API response';
    public const string GENERIC_ERROR = 'API call did not return expected data';
    public const string FIELD_MISSING = 'Expected field missing in API response';
    public const string RESULT_MISSING = 'Result missing in API response';

    /**
     * @param array<string, array<string, mixed>|bool|int|string> $apiResponse
     */
    public function __construct(string $message, array $apiResponse = [], int $code = 0, ?\Throwable $previous = null)
    {
        if (strlen($message) === 0) {
            if (!array_key_exists('success', $apiResponse)) {
                $message = self::SUCCESS_MISSING;
            } elseif (!$apiResponse['success']) {
                if (array_key_exists('error_code', $apiResponse) && is_int($apiResponse['error_code'])) {
                    $code = $apiResponse['error_code'];
                }

                if (array_key_exists('msg', $apiResponse)) {
                    $message = $apiResponse['msg'];
                }
            }
        }

        parent::__construct($message, $code, $previous);
    }
}
