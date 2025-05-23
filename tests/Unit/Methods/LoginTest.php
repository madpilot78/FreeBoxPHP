<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\Methods\Login;
use PHPUnit\Framework\MockObject\Stub;

class LoginTest extends TestCase
{
    private AuthSessionInterface&Stub $authSessionStub;
    private Login $login;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);

        $this->login = new Login($this->authSessionStub);

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testLogin(): void
    {
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($this->login->run());
    }
}
