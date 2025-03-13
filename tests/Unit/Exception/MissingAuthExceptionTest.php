<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\MissingAuthException;

class MissingAuthExceptionTest extends TestCase
{
    public function testMissingAuthExceptionExists(): void
    {
        $exception = new MissingAuthException('Test exception');

        $this->assertEquals('Test exception', $exception->getMessage());
    }
}
