<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class FwRedir extends AbstractMethod implements MethodInterface
{
    protected const string API = '/fw/redir/';
    protected const array ACTIONS = ['get', 'set', 'update', 'delete'];
    protected const array REQUIRED_GET = [''];
    protected const array REQUIRED_GET_ID = [
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
    protected const array REQUIRED_SET = [
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
    protected const array REQUIRED_PUT = [
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
    protected const Permission PERM = Permission::Settings;
    protected const string FAIL_MESSAGE_SET = 'Failed to create redirect';
    protected const string FAIL_MESSAGE_DELETE = 'Failed to delete redirect';
}
