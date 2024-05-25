<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Exception\NotSupportedException;

class DiscoverTest extends MethodTestCase
{
    public function testDiscoverSuccess(): void
    {
        $this->mock->append(new Response(
            body: <<<JSON
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
                JSON,
        ));

        $box = new Box(client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->discover());
    }

    public function testDiscoverNoHttps(): void
    {

        $this->mock->append(new Response(
            body: <<<JSON
                {
                   "uid": "23b86ec8091013d668829fe12791fdab",
                   "device_name": "Freebox Server",
                   "box_model": "fbxgw7-r1/full",
                   "box_model_name": "Freebox v7 (r1)",
                   "api_version": "6.0",
                   "api_base_url": "/api/",
                   "api_domain": "example.fbxos.fr",
                   "https_available": false
                }
                JSON,
        ));

        $box = new Box(client: $this->guzzleClient);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Only https enabled boxes supported.');

        $this->assertInstanceOf(Box::class, $box->discover());
    }
}
