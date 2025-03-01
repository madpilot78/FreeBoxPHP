<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;

class Login extends AbstractMethod implements MethodInterface
{
    public function __construct(protected AuthSessionInterface $authSession) {}

    public function run(string $action = 'get', array|int|string $id = [], array $params = []): null
    {
        $this->authSession->getAuthHeader();

        return null;
    }
}
