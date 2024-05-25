<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Exception\NotSupportedException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\Discover;

class DiscoverTest extends TestCase
{
    private BoxInfo $boxInfoStub;
    private Discover $discover;
    private HttpClient $httpClientStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->discover = new Discover(
            $this->httpClientStub,
            new Configuration(),
            $this->boxInfoStub,
        );

        $this->httpClientStub
            ->method('__call')
            ->willReturn([]);
        $this->boxInfoStub
            ->method('save')
            ->willReturnSelf();
    }

    public function testDiscoverSuccess(): void
    {
        $this->boxInfoStub
            ->method('__get')
            ->willReturn(true);

        $this->assertNull($this->discover->run());
    }

    public function testDiscoverNoHttps(): void
    {
        $this->boxInfoStub
            ->method('__get')
            ->willReturn(false);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Only https enabled boxes supported.');

        $this->assertNull($this->discover->run());
    }
}
