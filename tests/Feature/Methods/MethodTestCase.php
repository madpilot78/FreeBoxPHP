<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;

abstract class MethodTestCase extends TestCase
{
    protected const array FAKELOGINJSON = [
        <<<JSON
            {
                "success": true,
                "result": {
                    "logged_in": false,
                    "challenge": "fakeChallenge"
                }
            }
            JSON,
        <<<JSON
            {
               "success": true,
               "result": {
                    "session_token": "fakeToken",
                    "challenge": "fakeChallenge",
                    "permissions": {
                        "downloader": true
                    }
                }
            }
            JSON,
    ];

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
