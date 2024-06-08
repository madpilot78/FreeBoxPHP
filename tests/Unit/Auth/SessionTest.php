<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\HttpClient;

class SessionTest extends TestCase
{
    private const string MOCK_URL = 'https://host.test.net/api/v10';
    private const string CHALLENGE = 'challengeVal';
    private const string SESSION_TOKEN = 'SessionTokenVal';
    private const array PERMISSIONS = ['downloader' => true];

    private AuthSession $authSession;
    private AuthManager $authManagerMock;
    private BoxInfo $boxInfoStub;
    private HttpClient $httpClientStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManagerMock = $this->createMock(AuthManager::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->authSession = new AuthSession(
            $this->authManagerMock,
            $this->boxInfoStub,
            new Configuration(),
            $this->httpClientStub,
            new NullLogger(),
        );
    }

    public function testGetAuthMissingChallenge(): void
    {
        $this->authManagerMock
            ->expects($this->once())
            ->method('getSessionToken')
            ->willReturn(null);
        $this->authManagerMock
            ->expects($this->once())
            ->method('hasChallenge')
            ->willReturn(false);
        $this->authManagerMock
            ->expects($this->exactly(2))
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();
        $this->authManagerMock
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn('pwd');
        $this->authManagerMock
            ->expects($this->once())
            ->method('setSessionToken')
            ->with($this->equalTo(self::SESSION_TOKEN))
            ->willReturnSelf();
        $this->authManagerMock
            ->expects($this->once())
            ->method('setPermissions')
            ->with($this->equalTo(self::PERMISSIONS))
            ->willReturnSelf();
        $this->boxInfoStub->method('__get')->willReturn(self::MOCK_URL);
        $this->httpClientStub->method('__call')->willReturn(
            [
                'logged_in' => false,
                'challenge' => self::CHALLENGE,
            ],
            [
                'session_token' => self::SESSION_TOKEN,
                'challenge' => self::CHALLENGE,
                'permissions' => self::PERMISSIONS,
            ],
        );

        $returned = $this->authSession->getAuthHeader();

        $this->assertIsArray($returned);
        $this->assertSame(['X-Fbx-App-Auth' => self::SESSION_TOKEN], $returned);
    }

    public function testGetAuthWithChallenge(): void
    {
        $this->authManagerMock
            ->expects($this->once())
            ->method('getSessionToken')
            ->willReturn(null);
        $this->authManagerMock
            ->expects($this->once())
            ->method('hasChallenge')
            ->willReturn(true);
        $this->authManagerMock
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn('pwd');
        $this->authManagerMock
            ->expects($this->once())
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();
        $this->authManagerMock
            ->expects($this->once())
            ->method('setSessionToken')
            ->with($this->equalTo(self::SESSION_TOKEN))
            ->willReturnSelf();
        $this->authManagerMock
            ->expects($this->once())
            ->method('setPermissions')
            ->with($this->equalTo(self::PERMISSIONS))
            ->willReturnSelf();
        $this->boxInfoStub->method('__get')->willReturn(self::MOCK_URL);
        $this->httpClientStub->method('__call')->willReturn([
            'session_token' => self::SESSION_TOKEN,
            'challenge' => self::CHALLENGE,
            'permissions' => self::PERMISSIONS,
        ]);

        $returned = $this->authSession->getAuthHeader();

        $this->assertIsArray($returned);
        $this->assertSame(['X-Fbx-App-Auth' => self::SESSION_TOKEN], $returned);
    }

    public function testHasSessionToken(): void
    {
        $this->authManagerMock
            ->expects($this->once())
            ->method('getSessionToken')
            ->willReturn(self::SESSION_TOKEN);
        $this->authManagerMock
            ->expects($this->never())
            ->method('setChallenge');
        $this->authManagerMock
            ->expects($this->never())
            ->method('setSessionToken');
        $this->authManagerMock
            ->expects($this->never())
            ->method('setPermissions');

        $returned = $this->authSession->getAuthHeader();

        $this->assertIsArray($returned);
        $this->assertSame(['X-Fbx-App-Auth' => self::SESSION_TOKEN], $returned);
    }

    public function testCan(): void
    {
        $this->authManagerMock
            ->expects($this->once())
            ->method('hasPermission')
            ->willReturn(true);

        $this->assertTrue($this->authSession->can(Permission::Pvr));
    }
}
