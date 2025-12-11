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
use madpilot78\FreeBoxPHP\HttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
class SessionTest extends TestCase
{
    private const string MOCK_URL = 'https://host.test.net/api/v10';
    private const string CHALLENGE = 'challengeVal';
    private const string SESSION_TOKEN = 'SessionTokenVal';
    private const array PERMISSIONS = ['downloader' => true];

    private AuthSession $authSession;
    private AuthManager&MockObject $authManagerMock;
    private BoxInfo&Stub $boxInfoStub;
    private HttpClientInterface&Stub $httpClientStub;
    private CacheInterface&MockObject $cacheMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManagerMock = $this->createMock(AuthManager::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);

        $this->authSession = new AuthSession(
            $this->authManagerMock,
            $this->boxInfoStub,
            new Configuration(cache: $this->cacheMock),
            $this->httpClientStub,
            new NullLogger(),
            $this->cacheMock,
        );
    }

    public function testGetAuthMissingChallenge(): void
    {
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
            ->method('setPermissions')
            ->with($this->equalTo(self::PERMISSIONS))
            ->willReturnSelf();
        $this->boxInfoStub->method('getApiUrl')->willReturn(self::MOCK_URL);
        $this->httpClientStub
            ->method('get')
            ->willReturn([
                'logged_in' => false,
                'challenge' => self::CHALLENGE,
            ]);
        $this->httpClientStub
            ->method('post')
            ->willReturn([
                'session_token' => self::SESSION_TOKEN,
                'challenge' => self::CHALLENGE,
                'permissions' => self::PERMISSIONS,
            ]);
        $this->cacheMock
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(null);
        $this->cacheMock
            ->expects($this->exactly(2))
            ->method('set')
            ->willReturn(true);

        $returned = $this->authSession->getAuthHeader();

        $this->assertSame(['X-Fbx-App-Auth' => self::SESSION_TOKEN], $returned);
    }

    public function testGetAuthWithChallenge(): void
    {
        $this->authManagerMock
            ->expects($this->once())
            ->method('getPassword')
            ->willReturn('pwd');
        $this->authManagerMock
            ->expects($this->exactly(2))
            ->method('setChallenge')
            ->willReturnSelf();
        $this->authManagerMock
            ->expects($this->once())
            ->method('setPermissions')
            ->with($this->equalTo(self::PERMISSIONS))
            ->willReturnSelf();
        $this->boxInfoStub->method('getApiUrl')->willReturn(self::MOCK_URL);
        $this->httpClientStub
            ->method('get')
            ->willReturn([
                'challenge' => self::CHALLENGE,
            ]);
        $this->httpClientStub
            ->method('post')
            ->willReturn([
                'session_token' => self::SESSION_TOKEN,
                'challenge' => self::CHALLENGE,
                'permissions' => self::PERMISSIONS,
            ]);
        $this->cacheMock
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(null);
        $this->cacheMock
            ->expects($this->exactly(2))
            ->method('set')
            ->willReturn(true);

        $returned = $this->authSession->getAuthHeader();

        $this->assertSame(['X-Fbx-App-Auth' => self::SESSION_TOKEN], $returned);
    }

    public function testCached(): void
    {
        $this->cacheMock
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn(self::SESSION_TOKEN, self::PERMISSIONS);

        $returned = $this->authSession->getAuthHeader();

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
