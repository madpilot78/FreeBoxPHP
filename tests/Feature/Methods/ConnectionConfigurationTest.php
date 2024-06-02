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

class ConnectionConfigurationTest extends MethodTestCase
{
    use NeedsLogin;

    private const string CONFJSON = <<<JSON
        {
            "success": true,
            "result": {
                "ping": true,
                "is_secure_pass": false,
                "remote_access_port": 80,
                "remote_access": false,
                "wol": false,
                "adblock": false,
                "adblock_not_set": false,
                "api_remote_access": true,
                "allow_token_request": true,
                "remote_access_ip": "312.13.37.42"
            }
        }
        JSON;

    public function testConnectonConfigurationGet(): void
    {
        $this->setupFakeLogin();

        $this->mock->append(new Response(body: self::CONFJSON));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals(json_decode(self::CONFJSON, true)['result'], $box->connectionConfiguration('get'));
    }

    public function testConnectonConfigurationSetSuccess(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": true}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->connectionConfiguration('set', [
            'ping' => false,
            'wol' => true,
        ]));
    }

    public function testConnectonConfigurationSetNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->connectionConfiguration('set', [
            'ping' => false,
            'wol' => true,
        ]));
    }

    public function testConnectonConfigurationSetFail(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": false}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Failed to set connection configuration');

        $box->connectionConfiguration('set', [
            'ping' => false,
            'wol' => true,
        ]);
    }

    public function testConnectonConfigurationWrongMethod(): void
    {
        $this->setupFakeLogin();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);
        $box->connectionConfiguration('foo');
    }
}
