<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class ConnectionStatus extends AbstractMethod implements MethodInterface
{
    protected const string API = '/connection';
    protected const array ACTIONS = ['get'];
    protected const array REQUIRED_GET = [
        'type',
        'ipv4',
        'ipv4_port_range',
        'ipv6',
        'state',
    ];
    protected const Permission PERM = Permission::None;
}
