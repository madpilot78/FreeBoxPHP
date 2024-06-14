<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class Language extends AbstractMethod implements MethodInterface
{
    protected const string API = '/lang';
    protected const array ACTIONS = ['get', 'set'];
    protected const array REQUIRED_GET = [
        'lang',
        'avalaible',
    ];
    protected const Permission PERM = Permission::Settings;
    protected const string FAIL_MESSAGE_SET = 'Failed to set language';
}
