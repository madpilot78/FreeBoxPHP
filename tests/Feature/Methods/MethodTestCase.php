<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;

abstract class MethodTestCase extends TestCase
{
    protected Client $guzzleClient;
    protected MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler();

        $this->guzzleClient = new Client([
            'handler' => HandlerStack::create($this->mock),
        ]);
    }
}
