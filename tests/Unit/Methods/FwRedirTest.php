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
use madpilot78\FreeBoxPHP\Methods\FwRedir;

class FwRedirTest extends TestCase
{
    private const array REQ = [
        'enabled',
        'comment',
        'id',
        'host',
        'hostname',
        'lan_port',
        'wan_port_end',
        'wan_port_start',
        'lan_ip',
        'ip_proto',
        'src_ip',
    ];

    private const array FWLISTOBJ = [
        [
            'enabled' => true,
            'comment' => '',
            'id' => 1,
            'host' => [
                'l2ident' => [
                    'id' => 'd0:23:db:36:15:aa',
                    'type' => 'mac_address',
                ],
                'active' => true,
                'id' => 'ether-d0:23:db:36:15:aa',
                'last_time_reachable' => 1360669498,
                'persistent' => true,
                'names' => [
                    [
                        'name' => 'iPhone-r0ro',
                        'source' => 'dhcp',
                    ],
                ],
                'vendor_name' => 'Apple, Inc.',
                'l3connectivities' => [
                    [
                        'addr' => '192.168.69.22',
                        'active' => true,
                        'af' => 'ipv4',
                        'reachable' => true,
                        'last_activity' => 1360669498,
                        'last_time_reachable' => 1360669498,
                    ],
                ],
                'reachable' => true,
                'last_activity' => 1360669498,
                'primary_name_manual' => true,
                'primary_name' => 'iPhone r0ro',
            ],
            'hostname' => 'iPhone r0ro',
            'lan_port' => 69,
            'wan_port_end' => 69,
            'wan_port_start' => 69,
            'lan_ip' => '192.168.1.22',
            'ip_proto' => 'tcp',
            'src_ip' => '8.8.8.8',
        ],
        [
            'enabled' => true,
            'comment' => '',
            'id' => 2,
            'host' => [
                'l2ident' => [
                    'id' => 'd0:23:db:36:15:aa',
                    'type' => 'mac_address',
                ],
                'active' => true,
                'id' => 'ether-d0:23:db:36:15:aa',
                'last_time_reachable' => 1360669498,
                'persistent' => true,
                'names' => [
                    [
                        'name' => 'iPhone-r0ro',
                        'source' => 'dhcp',
                    ],
                ],
                'vendor_name' => 'Apple, Inc.',
                'l3connectivities' => [
                    [
                        'addr' => '192.168.69.22',
                        'active' => true,
                        'af' => 'ipv4',
                        'reachable' => true,
                        'last_activity' => 1360669498,
                        'last_time_reachable' => 1360669498,
                    ],
                ],
                'reachable' => true,
                'last_activity' => 1360669498,
                'primary_name_manual' => true,
                'primary_name' => 'iPhone r0ro',
            ],
            'hostname' => 'iPhone r0ro',
            'lan_port' => 1337,
            'wan_port_end' => 1340,
            'wan_port_start' => 1337,
            'lan_ip' => '192.168.1.22',
            'ip_proto' => 'udp',
            'src_ip' => '0.0.0.0',
        ],
    ];

    private const array FWREDIRSET = [
        'enabled' => true,
        'comment' => 'test',
        'lan_port' => 4242,
        'wan_port_end' => 4242,
        'wan_port_start' => 4242,
        'lan_ip' => '192.168.1.42',
        'ip_proto' => 'tcp',
        'src_ip' => '0.0.0.0',
    ];

    private const array FWSETUPOBJ = [
        'enabled' => true,
        'comment' => 'test',
        'id' => 3,
        'host' => [
            'l2ident' => [
                'id' => 'd0:23:db:36:15:aa',
                'type' => 'mac_address',
            ],
            'active' => true,
            'id' => 'ether-d0:23:db:36:15:aa',
            'last_time_reachable' => 1360669498,
            'persistent' => true,
            'names' => [
                [
                    'name' => 'iPhone-r0ro',
                    'source' => 'dhcp',
                ],
            ],
            'vendor_name' => 'Apple, Inc.',
            'l3connectivities' => [
                [
                    'addr' => '192.168.69.22',
                    'active' => true,
                    'af' => 'ipv4',
                    'reachable' => true,
                    'last_activity' => 1360669498,
                    'last_time_reachable' => 1360669498,
                ],
            ],
            'reachable' => true,
            'last_activity' => 1360669498,
            'primary_name_manual' => true,
            'primary_name' => 'iPhone r0ro',
        ],
        'hostname' => 'Mac-mini-de-Romain',
        'lan_port' => 4242,
        'wan_port_end' => 4242,
        'wan_port_start' => 4242,
        'lan_ip' => '192.168.1.42',
        'ip_proto' => 'tcp',
        'src_ip' => '0.0.0.0',
    ];

    private AuthSessionInterface $authSessionStub;
    private BoxInfoInterface $boxInfoStub;
    private HttpClient $httpClientMock;
    private FwRedir $fwRedir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientMock = $this->createMock(HttpClient::class);

        $this->fwRedir = new FwRedir(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientMock,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testGetFwRedirs(): void
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo([
                    [''],
                    $this->boxInfoStub->apiUrl . '/fw/redir',
                    ['headers' => $this->authSessionStub->getAuthHeader()],
                ]),
            )
            ->willReturn(self::FWLISTOBJ);

        $this->assertEquals(self::FWLISTOBJ, $this->fwRedir->run('get'));
    }

    public function testGetFwRedir(): void
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('get'),
                $this->equalTo([
                    self::REQ,
                    $this->boxInfoStub->apiUrl . '/fw/redir/1',
                    ['headers' => $this->authSessionStub->getAuthHeader()],
                ]),
            )
            ->willReturn(self::FWLISTOBJ[1]);

        $this->assertEquals(self::FWLISTOBJ[1], $this->fwRedir->run('get', 1));
    }

    public function testSetFwRedir(): void
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('post'),
                $this->equalTo([
                    self::REQ,
                    $this->boxInfoStub->apiUrl . '/fw/redir',
                    [
                        'headers' => $this->authSessionStub->getAuthHeader(),
                        'json' => self::FWREDIRSET,
                    ],
                ]),
            )
            ->willReturn(self::FWSETUPOBJ);
        $this->authSessionStub
            ->method('can')
            ->willReturn(true);

        $this->assertEquals(self::FWSETUPOBJ, $this->fwRedir->run('set', self::FWREDIRSET));
    }

    public function testSetFwRedirNoPerm(): void
    {
        $this->httpClientMock
            ->expects($this->never())
            ->method('__call');
        $this->authSessionStub
            ->method('can')
            ->willReturn(false);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->fwRedir->run('set', self::FWREDIRSET);
    }

    public function testUpdateFwRedir(): void
    {
        $res = self::FWSETUPOBJ;
        $res['enabled'] = false;

        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('put'),
                $this->equalTo([
                    self::REQ,
                    $this->boxInfoStub->apiUrl . '/fw/redir/1',
                    [
                        'headers' => $this->authSessionStub->getAuthHeader(),
                        'json' => ['enabled' => false],
                    ],
                ]),
            )
            ->willReturn($res);
        $this->authSessionStub
            ->method('can')
            ->willReturn(true);

        $this->assertEquals($res, $this->fwRedir->run('update', 1, ['enabled' => false]));
    }

    public function testUpdateFwRedirNoPerm(): void
    {
        $this->httpClientMock
            ->expects($this->never())
            ->method('__call');
        $this->authSessionStub
            ->method('can')
            ->willReturn(false);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->fwRedir->run('update', 1, ['enabled' => false]);
    }

    public function testDeleteFwRedir(): void
    {
        $this->httpClientMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('delete'),
                $this->equalTo([
                    $this->boxInfoStub->apiUrl . '/fw/redir/3',
                    ['headers' => $this->authSessionStub->getAuthHeader()],
                ]),
            )
            ->willReturn(['success' => true]);
        $this->authSessionStub
            ->method('can')
            ->willReturn(true);

        $this->assertNull($this->fwRedir->run('delete', 3));
    }

    public function testDeleteFwRedirNoPerm(): void
    {
        $this->httpClientMock
            ->expects($this->never())
            ->method('__call');
        $this->authSessionStub
            ->method('can')
            ->willReturn(false);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->fwRedir->run('delete', 3);
    }

    public function testFwRedirWrongMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $this->fwRedir->run('foo');
    }
}
