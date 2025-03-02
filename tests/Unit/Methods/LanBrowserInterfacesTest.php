<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\LanBrowserInterfaces;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

class LanBrowserInterfacesTest extends TestCase
{
    private const array LANINTERFACES = [
        [
            [
                'name' => 'pub',
                'host_count' => 3,
            ],
            [
                'name' => 'test',
                'host_count' => 0,
            ]
        ]
    ];

    private AuthSessionInterface&Stub $authSessionStub;
    private BoxInfoInterface&Stub $boxInfoStub;
    private HttpClient&MockObject $httpClientMock;
    private LanBrowserInterfaces $lanBrowserInterfaces;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientMock = $this->createMock(HttpClient::class);

        $this->lanBrowserInterfaces = new LanBrowserInterfaces(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientMock,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testGetLanBroserInterfaces(): void
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo([
                    [''],
                    $this->boxInfoStub->apiUrl . '/lan/browser/interfaces/',
                    ['headers' => $this->authSessionStub->getAuthHeader()],
                ]),
            )
            ->willReturn(self::LANINTERFACES);

        $this->assertEquals(self::LANINTERFACES, $this->lanBrowserInterfaces->run('get'));
    }
}
