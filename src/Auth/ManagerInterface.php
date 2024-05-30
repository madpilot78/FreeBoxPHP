<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

interface ManagerInterface
{
    public function setAuthToken(string $token): self;

    public function setSessionToken(string $token): self;

    public function getSessionToken(): ?string;

    public function setChallenge(string $challenge): self;

    public function hasChallenge(): bool;

    public function setPermissions(array $permissions): self;

    public function hasPermission(string $permission): bool;

    public function getPassword(?string $challenge = null): string;
}
