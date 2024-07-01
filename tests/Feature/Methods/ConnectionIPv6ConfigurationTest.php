<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class ConnectionIPv6ConfigurationTest extends MethodTestCase
{
    use NeedsLogin;

    private const string CONFJSON = <<<JSON
        {
            "success": true,
            "result": {
                "ipv6_enabled": true,
                "delegations": [
                    {
                        "prefix": "2a01:e30:d252:a2a0::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a1::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a2::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a3::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a4::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a5::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a6::/64",
                        "next_hop": ""
                    },
                    {
                        "prefix": "2a01:e30:d252:a2a7::/64",
                        "next_hop": ""
                    }
                ]
            }
        }
        JSON;

    public function testConnectonIPv6ConfigurationGet(): void
    {
        $this->setupFakeLogin();

        $this->mock->append(new Response(body: self::CONFJSON));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals(json_decode(self::CONFJSON, true)['result'], $box->connectionIPv6Configuration('get'));
    }

    public function testConnectonIPv6ConfigurationSetSuccess(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": true}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->connectionIPv6Configuration('update', [
            'ipv6_firewall' => true,
        ]));
    }

    public function testConnectonIPv6ConfigurationSetNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->connectionIPv6Configuration('update', [
            'ipv6_firewall' => true,
        ]));
    }

    public function testConnectonIPv6ConfigurationSetFail(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": false}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Failed to update connection IPv6 configuration');

        $box->connectionIPv6Configuration('update', [
            'ipv6_firewall' => true,
        ]);
    }

    public function testConnectonIPv6ConfigurationWrongMethod(): void
    {
        $this->setupFakeLogin();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);
        $box->connectionIPv6Configuration('foo');
    }
}
