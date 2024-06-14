<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class FwRedirTest extends MethodTestCase
{
    use NeedsLogin;

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

    private const string GETREDIRSJSON = <<<JSON
        {
            "success": true,
            "result": [
                {
                    "enabled": true,
                    "comment": "",
                    "id": 1,
                    "host": {
                        "l2ident": {
                            "id": "d0:23:db:36:15:aa",
                            "type": "mac_address"
                        },
                        "active": true,
                        "id": "ether-d0:23:db:36:15:aa",
                        "last_time_reachable": 1360669498,
                        "persistent": true,
                        "names": [
                            {
                                "name": "iPhone-r0ro",
                                "source": "dhcp"
                            }
                        ],
                        "vendor_name": "Apple, Inc.",
                        "l3connectivities": [
                            {
                                "addr": "192.168.69.22",
                                "active": true,
                                "af": "ipv4",
                                "reachable": true,
                                "last_activity": 1360669498,
                                "last_time_reachable": 1360669498
                            }
                        ],
                        "reachable": true,
                        "last_activity": 1360669498,
                        "primary_name_manual": true,
                        "primary_name": "iPhone r0ro"
                    },
                    "hostname": "iPhone r0ro",
                    "lan_port": 69,
                    "wan_port_end": 69,
                    "wan_port_start": 69,
                    "lan_ip": "192.168.1.22",
                    "ip_proto": "tcp",
                    "src_ip": "8.8.8.8"
                },
                {
                    "enabled": true,
                    "comment": "",
                    "id": 2,
                    "host": {
                        "l2ident": {
                            "id": "d0:23:db:36:15:aa",
                            "type": "mac_address"
                        },
                        "active": true,
                        "id": "ether-d0:23:db:36:15:aa",
                        "last_time_reachable": 1360669498,
                        "persistent": true,
                        "names": [
                            {
                                "name": "iPhone-r0ro",
                                "source": "dhcp"
                            }
                        ],
                        "vendor_name": "Apple, Inc.",
                        "l3connectivities": [
                            {
                                "addr": "192.168.69.22",
                                "active": true,
                                "af": "ipv4",
                                "reachable": true,
                                "last_activity": 1360669498,
                                "last_time_reachable": 1360669498
                            }
                        ],
                        "reachable": true,
                        "last_activity": 1360669498,
                        "primary_name_manual": true,
                        "primary_name": "iPhone r0ro"
                    },
                    "hostname": "android-c5fe44a2c27be1e2",
                    "lan_port": 1337,
                    "wan_port_end": 1340,
                    "wan_port_start": 1337,
                    "lan_ip": "192.168.1.22",
                    "ip_proto": "udp",
                    "src_ip": "0.0.0.0"
                }
            ]
        }
        JSON;
    private const string GETREDIRJSON = <<<JSON
        {
            "success": true,
            "result": {
                "enabled": true,
                "comment": "",
                "id": 1,
                "host": {
                    "l2ident": {
                        "id": "d0:23:db:36:15:aa",
                        "type": "mac_address"
                    },
                    "active": true,
                    "id": "ether-d0:23:db:36:15:aa",
                    "last_time_reachable": 1360669498,
                    "persistent": true,
                    "names": [
                        {
                            "name": "iPhone-r0ro",
                            "source": "dhcp"
                        }
                    ],
                    "vendor_name": "Apple, Inc.",
                    "l3connectivities": [
                        {
                            "addr": "192.168.69.22",
                            "active": true,
                            "af": "ipv4",
                            "reachable": true,
                            "last_activity": 1360669498,
                            "last_time_reachable": 1360669498
                        }
                    ],
                    "reachable": true,
                    "last_activity": 1360669498,
                    "primary_name_manual": true,
                    "primary_name": "iPhone r0ro"
                },
                "hostname": "iPhone r0ro",
                "lan_port": 69,
                "wan_port_end": 69,
                "wan_port_start": 69,
                "lan_ip": "192.168.1.22",
                "ip_proto": "tcp",
                "src_ip": "8.8.8.8"
            }
        }
        JSON;
    private const string UPDATEREDIRJSON = <<<JSON
        {
            "success": false,
            "result": {
                "enabled": true,
                "comment": "",
                "id": 1,
                "host": {
                    "l2ident": {
                        "id": "d0:23:db:36:15:aa",
                        "type": "mac_address"
                    },
                    "active": true,
                    "id": "ether-d0:23:db:36:15:aa",
                    "last_time_reachable": 1360669498,
                    "persistent": true,
                    "names": [
                        {
                            "name": "iPhone-r0ro",
                            "source": "dhcp"
                        }
                    ],
                    "vendor_name": "Apple, Inc.",
                    "l3connectivities": [
                        {
                            "addr": "192.168.69.22",
                            "active": true,
                            "af": "ipv4",
                            "reachable": true,
                            "last_activity": 1360669498,
                            "last_time_reachable": 1360669498
                        }
                    ],
                    "reachable": true,
                    "last_activity": 1360669498,
                    "primary_name_manual": true,
                    "primary_name": "iPhone r0ro"
                },
                "hostname": "iPhone r0ro",
                "lan_port": 69,
                "wan_port_end": 69,
                "wan_port_start": 69,
                "lan_ip": "192.168.1.22",
                "ip_proto": "tcp",
                "src_ip": "8.8.8.8"
            }
        }
        JSON;

    public function testFwRedirGetSuccess(): void
    {
        $this->setupFakeLogin();

        $this->mock->append(new Response(body: self::GETREDIRSJSON));
        $decoded = json_decode(self::GETREDIRSJSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->fwRedir('get'));
    }

    public function testFwRedirGetOneSuccess(): void
    {
        $this->setupFakeLogin();

        $this->mock->append(new Response(body: self::GETREDIRJSON));
        $decoded = json_decode(self::GETREDIRJSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->fwRedir('get', 1));
    }

    public function testFwRedirSetSuccess(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: self::GETREDIRJSON));
        $decoded = json_decode(self::GETREDIRJSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->fwRedir('set', self::FWREDIRSET));
    }

    public function testFwRedirSetNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->fwRedir('set', self::FWREDIRSET));
    }

    public function testFwRedirSetFail(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": false}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Failed to create redirect');

        $box->fwRedir('set', self::FWREDIRSET);
    }

    public function testFwRedirUpdateSuccess(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: self::UPDATEREDIRJSON));
        $decoded = json_decode(self::UPDATEREDIRJSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->fwRedir('update', 1, ['enabled' => false]));
    }

    public function testFwRedirUpdateNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->fwRedir('update', 1, ['enabled' => false]));
    }

    public function testFwRedirDeleteSuccess(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": true}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->fwRedir('delete', 3));
    }

    public function testFwRedirDeleteNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->fwRedir('delete', 3));
    }

    public function testFwRedirDeleteFail(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": false}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Failed to delete redirect');

        $box->fwRedir('delete', 3);
    }

    public function testFwRedirWrongMethod(): void
    {
        $this->setupFakeLogin();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);
        $box->fwRedir('foo');
    }
}
