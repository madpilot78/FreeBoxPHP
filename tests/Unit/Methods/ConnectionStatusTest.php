<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\ConnectionStatus;

class ConnectionStatusTest extends TestCase
{
    private const array STATUSOBJ = [
        'type' => 'ethernet',
        'rate_down' => 61,
        'bytes_up' => 5489542,
        'rate_up' => 0,
        'bandwidth_up' => 100000000,
        'ipv4' => '13.37.42.42',
        'ipv4_port_range' => [
            0 => 0,
            1 => 65535,
        ],
        'ipv6' => '2a01:e30:d252:a2a0::1',
        'bandwidth_down' => 100000000,
        'state' => 'up',
        'bytes_down' => 13332830,
        'media' => 'ftth',
    ];

    private AuthSession $authSessionStub;
    private BoxInfo $boxInfoStub;
    private HttpClient $httpClientStub;
    private ConnectionStatus $connectionStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->connectionStatus = new ConnectionStatus(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientStub,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testGetConnectionStatus(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(self::STATUSOBJ);

        $this->assertEquals(self::STATUSOBJ, $this->connectionStatus->run());
    }
}
