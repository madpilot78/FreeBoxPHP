<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Exception\NotSupportedException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\HttpClientInterface;
use madpilot78\FreeBoxPHP\Methods\Discover;
use PHPUnit\Framework\MockObject\Stub;

class DiscoverTest extends TestCase
{
    private BoxInfo&Stub $boxInfoStub;
    private Discover $discover;
    private HttpClientInterface&Stub $httpClientStub;

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
            ->method('get')
            ->willReturn([]);
        $this->boxInfoStub
            ->method('save')
            ->willReturnSelf();
    }

    public function testDiscoverSuccess(): void
    {
        $this->boxInfoStub
            ->method('getProperty')
            ->willReturn(true);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($this->discover->run());
    }

    public function testDiscoverNoHttps(): void
    {
        $this->boxInfoStub
            ->method('getProperty')
            ->willReturn(false);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Only https enabled boxes supported.');

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($this->discover->run());
    }
}
