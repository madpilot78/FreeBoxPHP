<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\NetworkErrorException;

class NetworkErrorExceptionTest extends TestCase
{
    public function testNetworkErrorExceptionExists(): void
    {
        $exception = new NetworkErrorException('Test exception');

        $this->assertInstanceOf(NetworkErrorException::class, $exception);
        $this->assertEquals('Test exception', $exception->getMessage());
    }
}
