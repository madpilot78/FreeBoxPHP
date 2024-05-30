<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

use madpilot78\FreeBoxPHP\Enum\Permission;

interface SessionInterface
{
    public const string NO_PERM_MSG = 'No permission';

    public function getAuthHeader(): array;

    public function can(Permission $perm): bool;
}
