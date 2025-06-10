<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

interface ManagerInterface
{
    public function setAuthToken(#[\SensitiveParameter] string $token): self;

    public function setChallenge(#[\SensitiveParameter] string $challenge): self;

    public function hasChallenge(): bool;

    /**
     * @param array<string, bool> $permissions
     */
    public function setPermissions(array $permissions): self;

    public function hasPermission(string $permission): bool;

    public function getPassword(?string $challenge = null): string;
}
