<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class ConnectionConfiguration extends AbstractMethod implements MethodInterface
{
    protected const string API = '/connection/config';
    protected const array ACTIONS = ['get', 'set'];
    protected const array REQUIRED = [
        'ping',
        'is_secure_pass',
        'remote_access_port',
        'remote_access',
        'wol',
        'adblock',
        'adblock_not_set',
        'api_remote_access',
        'allow_token_request',
        'remote_access_ip',
    ];
    protected const Permission PERM = Permission::Settings;
    protected const string FAIL_MESSAGE = 'Failed to set connection configuration';
}
