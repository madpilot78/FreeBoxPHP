<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class LanBrowserInterfacesTest extends MethodTestCase
{
    use NeedsLogin;

    private const string INTERFACESJSON = <<<JSON
        {
            "success": true,
            "result": [
                {
                    "name": "pub",
                    "host_count": 3
                },
                {
                    "name": "test",
                    "host_count": 0
                }
            ]
        }
    JSON;

    public function testLanBrowserInterfacesGet(): void
    {
        $this->setupFakeLogin();

        $this->mock->append(new Response(body: self::INTERFACESJSON));
        $decoded = json_decode(self::INTERFACESJSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->lanBrowserInterfaces('get'));
    }
}
