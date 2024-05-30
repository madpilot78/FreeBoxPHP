<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;

class Login
{
    public function __construct(private AuthSessionInterface $authSession) {}

    public function run(): void
    {
        $this->authSession->getAuthHeader();
    }
}
