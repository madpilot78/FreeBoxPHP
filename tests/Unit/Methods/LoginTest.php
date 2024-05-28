<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\Login;

class LoginTest extends TestCase
{
    private AuthSession $authSessionStub;
    private BoxInfo $boxInfoStub;
    private HttpClient $httpClientStub;
    private Login $login;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->login = new Login(
            $this->authSessionStub,
            $this->boxInfoStub,
            new Configuration(),
            $this->httpClientStub,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testLogin(): void
    {
        $this->assertNull($this->login->run());
    }
}
