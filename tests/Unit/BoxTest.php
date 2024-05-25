<?php

declare(strict_types=1);

namespace Tests\Unit;

use BadMethodCallException;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Configuration;

class BoxTest extends TestCase
{
    private Client $guzzleClient;
    private MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler();

        $handlerStack = HandlerStack::create($this->mock);

        $this->guzzleClient = new Client(['handler' => $handlerStack]);
    }

    public function testCreateBoxInstance(): void
    {
        $this->assertInstanceOf(Box::class, new Box());

        $this->assertInstanceOf(Box::class, new Box('token', new Configuration()));

        $this->assertInstanceOf(Box::class, new Box(null, new Configuration()));

        $this->assertInstanceOf(Box::class, new Box('token', new Configuration()));
    }

    public function testBoxBadMethodCall(): void
    {
        $box = new Box(client: $this->guzzleClient);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method foobar Not found');

        $box->foobar();
    }
}
