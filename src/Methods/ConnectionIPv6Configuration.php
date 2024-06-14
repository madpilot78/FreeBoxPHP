<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class ConnectionIPv6Configuration extends AbstractMethod implements MethodInterface
{
    protected const string API = '/connection/ipv6/config';
    protected const array ACTIONS = ['get', 'set'];
    protected const array REQUIRED_GET = [
        'ipv6_enabled',
        'delegations',
    ];
    protected const Permission PERM = Permission::Settings;
    protected const string FAIL_MESSAGE_SET = 'Failed to set connection IPv6 configuration';
}
