<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Exception\AuthException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\ConnectionConfiguration;

class ConnectionConfigurationTest extends TestCase
{
    private const array CONFOBJ = [
        'ping' => true,
        'is_secure_pass' => false,
        'remote_access_port' => 80,
        'remote_access' => false,
        'wol' => false,
        'adblock' => false,
        'adblock_not_set' => false,
        'api_remote_access' => true,
        'allow_token_request' => true,
        'remote_access_ip' => '312.13.37.42',
    ];

    private AuthSessionInterface $authSessionStub;
    private BoxInfoInterface $boxInfoStub;
    private HttpClient $httpClientStub;
    private ConnectionConfiguration $connectionConfiguration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->connectionConfiguration = new ConnectionConfiguration(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientStub,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testGetConnectionConfiguration(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(self::CONFOBJ);

        $this->assertEquals(self::CONFOBJ, $this->connectionConfiguration->run('get'));
    }

    public function testSetConnectionConfiguration(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => true]);
        $this->authSessionStub
            ->method('can')
            ->willReturn(true);

        $this->assertNull($this->connectionConfiguration->run('set', [
            'ping' => false,
            'wol' => true,
        ]));
    }

    public function testSetConnectionConfigurationNoPerm(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => true]);
        $this->authSessionStub
            ->method('can')
            ->willReturn(false);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->connectionConfiguration->run('set', [
            'ping' => false,
            'wol' => true,
        ]);
    }

    public function testWrongMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $this->connectionConfiguration->run('foo');
    }
}
