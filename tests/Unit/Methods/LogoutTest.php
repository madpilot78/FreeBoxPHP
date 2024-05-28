<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Exception\ApiAuthException;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\Logout;

class LogoutTest extends TestCase
{
    private AuthSession $authSessionStub;
    private BoxInfo $boxInfoStub;
    private HttpClient $httpClientStub;
    private Logout $logout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->logout = new Logout(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientStub,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testLogout(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => true]);

        $this->assertNull($this->logout->run());
    }

    public function testLogoutFail(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => false]);

        $this->expectException(ApiAuthException::class);
        $this->expectExceptionMessage('logout failed');

        $this->logout->run();
    }
}
