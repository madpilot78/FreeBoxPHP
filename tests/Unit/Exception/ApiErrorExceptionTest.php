<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;

class ApiErrorExceptionTest extends TestCase
{
    public function testApiErrorExceptionExists(): void
    {
        $exception = new ApiErrorException('API Error');

        $this->assertEquals('API Error', $exception->getMessage());
    }

    public function testApiErrorExceptionMissingSuccess(): void
    {
        $exception = new ApiErrorException('', ['foo' => 'bar']);

        $this->assertEquals(ApiErrorException::SUCCESS_MISSING, $exception->getMessage());
    }

    public function testApiErrorExceptionPopulatesMessageAndCode(): void
    {
        $exception = new ApiErrorException('', [
            'success' => false,
            'error_code' => 42,
            'msg' => 'Oh no, not again!',
        ], 99);

        $this->assertEquals(42, $exception->getCode());
        $this->assertEquals('Oh no, not again!', $exception->getMessage());
    }
}
