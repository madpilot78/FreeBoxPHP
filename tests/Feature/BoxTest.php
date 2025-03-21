<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Methods\LanWol;

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

    public function testBoxIsInterfaceCheck(): void
    {
        $discoverStub = $this->createStub(LanWol::class);
        $discoverStub
            ->method('run')
            ->willReturn([
                'test' => 'test',
            ]);

        $delegatedContainer = new Container();
        $delegatedContainer->add(LanWol::class, $discoverStub);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected object type returned');

        $box = new Box(
            client: $this->guzzleClient,
            configuration: new Configuration(container: $delegatedContainer),
        );

        $box->lanWol('foo', ['bar' => 'baz']);
    }
}
