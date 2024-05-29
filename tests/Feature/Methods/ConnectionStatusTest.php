<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;

class ConnectionStatusTest extends MethodTestCase
{
    use NeedsLogin;

    private const string STATUSJSON = <<<JSON
        {
            "success": true,
            "result": {
                "type": "ethernet",
                "rate_down": 61,
                "bytes_up": 5489542,
                "rate_up": 0,
                "bandwidth_up": 100000000,
                "ipv4": "13.37.42.42",
                "ipv4_port_range": [
                    0,
                    65535
                ],
                "ipv6": "2a01:e30:d252:a2a0::1",
                "bandwidth_down": 100000000,
                "state": "up",
                "bytes_down": 13332830,
                "media": "ftth"
            }
        }
        JSON;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupFakeLogin();
    }

    public function testLogin(): void
    {
        $this->mock->append(new Response(body: self::STATUSJSON));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals(json_decode(self::STATUSJSON, true)['result'], $box->connectionStatus());
    }
}
