<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\ApiAuthException;

class ApiAuthExceptionTest extends TestCase
{
    public function testApiAuthExceptionExists(): void
    {
        $exception = new ApiAuthException('Test exception');

        $this->assertEquals('Test exception', $exception->getMessage());
    }

    public function testApiAuthException403(): void
    {
        $prev = new Exception('Previous exception');
        $exception = new ApiAuthException('Test exception', 42, $prev);

        $this->assertInstanceOf(Exception::class, $exception->getPrevious());

        $exception = new ApiAuthException('Test exception', 403, $prev);

        $this->assertNull($exception->getPrevious());
    }
}
