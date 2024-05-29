<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

use madpilot78\FreeBoxPHP\Exception\MissingAuthException;

class Manager
{
    private ?string $authToken = null;
    private ?string $sessionToken = null;
    private ?string $currentChallenge = null;
    private array $permissions = [];

    public function setAuthToken(string $token): self
    {
        $this->authToken = $token;

        return $this;
    }

    public function setSessionToken(string $token): self
    {
        $this->sessionToken = $token;

        return $this;
    }

    public function getSessionToken(): ?string
    {
        return $this->sessionToken;
    }

    public function setChallenge(string $challenge): self
    {
        $this->currentChallenge = $challenge;

        return $this;
    }

    public function hasChallenge(): bool
    {
        return isset($this->currentChallenge);
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return array_key_exists($permission, $this->permissions) && $this->permissions[$permission];
    }

    /**
     * @throws MissingAuthException
     */
    public function getPassword(?string $challenge = null): string
    {
        if (is_null($this->authToken)) {
            throw new MissingAuthException('Authorization Token missing');
        }

        if (!$this->hasChallenge() && is_null($challenge)) {
            throw new MissingAuthException('No Challenge provided');
        }

        return hash_hmac('sha1', $challenge ?? $this->currentChallenge, $this->authToken);
    }
}
