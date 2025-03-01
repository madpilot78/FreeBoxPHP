<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class LanWolTest extends MethodTestCase
{
    use NeedsLogin;

    public function testLanWolSet(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": true}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->lanWol('set', 'pub', [
            'mac' => '00:24:d4:7e:00:4c',
            'password' => '',
        ]));
    }

    public function testLanWolSetNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->lanWol('set', 'pub', [
            'mac' => '00:24:d4:7e:00:4c',
            'password' => '',
        ]));
    }
}
