<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Enum\Permission;

class LanWol extends AbstractMethod implements MethodInterface
{
    protected const string API = '/lan/wol/';
    protected const array ACTIONS = ['set'];
    protected const Permission PERM = Permission::Settings;
    protected const string FAIL_MESSAGE_SET = 'Failed to send WOL';
}
