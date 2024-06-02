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
use madpilot78\FreeBoxPHP\Methods\ConnectionIPv6Configuration;

class ConnectionIPv6ConfigurationTest extends TestCase
{
private const array CONFOBJ = [
        'ipv6_enabled': true,
        'delegations': [
            [
                'prefix': '2a01:e30:d252:a2a0::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a1::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a2::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a3::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a4::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a5::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a6::/64',
                'next_hop': '',
            ],
            [
                'prefix': '2a01:e30:d252:a2a7::/64',
                'next_hop': '',
            ],
        ],
    ];

    private AuthSessionInterface $authSessionStub;
    private BoxInfoInterface $boxInfoStub;
    private HttpClient $httpClientStub;
    private ConnectionConfiguration $connectionIPv6Configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->connectionIPv6Configuration = new ConnectionIPv6Configuration(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientStub,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testGetConnectionIPv6Configuration(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(self::CONFOBJ);

        $this->assertEquals(self::CONFOBJ, $this->connectionIPv6Configuration->run('get'));
    }

    public function testSetConnectionIPv6Configuration(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => true]);
        $this->authSessionStub
            ->method('can')
            ->willReturn(true);

        $this->assertNull($this->connectionIPv6Configuration->run('set', [
            'ipv6_firewall' => true,
        ]));
    }

    public function testSetConnectionIPv6ConfigurationNoPerm(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => true]);
        $this->authSessionStub
            ->method('can')
            ->willReturn(false);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->connectionIPv6Configuration->run('set', [
            'ipv6_firewall' => true,
        ]);
    }

    public function testWrongMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $this->connectionIPv6Configuration->run('foo');
    }
}
