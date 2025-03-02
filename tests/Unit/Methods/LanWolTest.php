<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\LanWol;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

class LanWolTest extends TestCase
{
    private const array LANWOLSET = [
        'mac' => '00:24:d4:7e:00:4c',
        'password' => '',
    ];

    private AuthSessionInterface&Stub $authSessionStub;
    private BoxInfoInterface&Stub $boxInfoStub;
    private HttpClient&MockObject $httpClientMock;
    private LanWol $lanWol;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientMock = $this->createMock(HttpClient::class);

        $this->lanWol = new LanWol(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientMock,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testSetLanWol(): void
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('post'),
                $this->equalTo([
                    $this->boxInfoStub->getApiUrl() . '/lan/wol/pub',
                    [
                        'headers' => $this->authSessionStub->getAuthHeader(),
                        'json' => self::LANWOLSET,
                    ],
                ]),
            )
            ->willReturn(['success' => true]);
        $this->authSessionStub
            ->method('can')
            ->willReturn(true);

        $this->assertNull($this->lanWol->run('set', 'pub', self::LANWOLSET));
    }
}
