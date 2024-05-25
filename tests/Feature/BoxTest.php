<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Box;

class BoxTest extends TestCase
{
    private const string JSON = <<<JSON
        {
            "uid": "23b86ec8091013d668829fe12791fdab",
            "device_name": "Freebox Server",
            "box_model": "fbxgw7-r1/full",
            "box_model_name": "Freebox v7 (r1)",
            "api_version": "6.0",
            "api_base_url": "/api/",
            "api_domain": "example.fbxos.fr",
            "https_available": true,
            "https_port": 3615
        }
        JSON;

    private Client $guzzleClient;
    private MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler();

        $this->guzzleClient = new Client([
            'handler' => HandlerStack::create($this->mock),
        ]);
    }

    public function testBoxGetInfo(): void
    {
        $this->mock->append(new Response(body: self::JSON));

        $box = new Box(client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->discover());

        $this->assertEquals(json_decode(self::JSON, true), $box->getBoxInfo());
    }
}
