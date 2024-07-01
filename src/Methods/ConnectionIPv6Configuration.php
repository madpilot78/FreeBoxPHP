<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class ConnectionIPv6Configuration extends AbstractMethod implements MethodInterface
{
    protected const string API = '/connection/ipv6/config';
    protected const array ACTIONS = ['get', 'update'];
    protected const array REQUIRED_GET = [
        'ipv6_enabled',
        'delegations',
    ];
    protected const Permission PERM = Permission::Settings;
    protected const string FAIL_MESSAGE_UPDATE = 'Failed to update connection IPv6 configuration';
}
