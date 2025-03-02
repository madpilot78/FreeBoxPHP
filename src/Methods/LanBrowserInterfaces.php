<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

class LanBrowserInterfaces extends AbstractMethod implements MethodInterface
{
    protected const string API = '/lan/browser/interfaces/';
    protected const array ACTIONS = ['get'];
    protected const array REQUIRED_GET = [''];
    protected const string FAIL_MESSAGE_GET = 'Failed to get LAN interfaces list';
}
