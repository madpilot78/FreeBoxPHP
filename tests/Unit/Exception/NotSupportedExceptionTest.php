<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\NotSupportedException;

class NotSupportedExceptionTest extends TestCase
{
    public function testNotSupportedExceptionExists(): void
    {
        $exception = new NotSupportedException('Test exception');

        $this->assertEquals('Test exception', $exception->getMessage());
    }
}
