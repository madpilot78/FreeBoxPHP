<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;

class Login
{
    public function __construct(private AuthSession $authSession) {}

    public function run(): void
    {
        $this->authSession->getAuthHeader();
    }
}
