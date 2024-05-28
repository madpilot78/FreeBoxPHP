<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Exception\ApiAuthException;

class LogoutTest extends MethodTestCase
{
    use NeedsLogin;

    private Box $box;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupFakeLogin();
        $this->box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);
        $this->box->login();
    }

    public function testLogout(): void
    {
        $this->mock->append(new Response(body: '{"success": true}'));

        $this->assertInstanceOf(Box::class, $this->box->logout());
    }

    public function testLogoutFail(): void
    {
        $this->mock->append(new Response(body: '{"success": false}'));

        $this->expectException(ApiAuthException::class);
        $this->expectExceptionMessage('logout failed');

        $this->box->logout();
    }
}
