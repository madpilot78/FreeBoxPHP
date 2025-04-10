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
        'ipv6_prefix_firewall',
        'delegations',
        'ipv6_firewall',
    ];
    protected const array REQUIRED_PUT = [
        'ipv6_enabled',
        'ipv6_prefix_firewall',
        'delegations',
        'ipv6_firewall',
    ];
    protected const Permission PERM = Permission::Settings;
}
