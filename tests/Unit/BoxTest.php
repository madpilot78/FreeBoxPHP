<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Configuration;

class BoxTest extends TestCase
{
    private Client $guzzleClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzleClient = new Client([
            'handler' => HandlerStack::create(new MockHandler()),
        ]);
    }

    public function testCreateBoxInstance(): void
    {
        $this->assertInstanceOf(Box::class, new Box(client: $this->guzzleClient));

        $this->assertInstanceOf(Box::class, new Box('token', new Configuration(), $this->guzzleClient));

        $this->assertInstanceOf(Box::class, new Box(null, new Configuration(), $this->guzzleClient));

        $this->assertInstanceOf(Box::class, new Box('token', new Configuration(), $this->guzzleClient));
    }

    public function testProvidingCustomContainer(): void
    {
        $container = $this->createStub(ContainerInterface::class);

        $this->assertInstanceOf(Box::class, new Box(null, new Configuration(container: $container)));
    }
}
