<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class AuthExceptionTest extends TestCase
{
    public function testAuthExceptionExists(): void
    {
        $exception = new AuthException('Test exception');

        $this->assertInstanceOf(AuthException::class, $exception);
        $this->assertEquals('Test exception', $exception->getMessage());
    }
}
