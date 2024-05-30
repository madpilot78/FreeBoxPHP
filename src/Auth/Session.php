<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

use madpilot78\FreeBoxPHP\Auth\ManagerInterface as AuthManagerInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Enum\Permission;

class Session implements SessionInterface
{
    public function __construct(
        private AuthManagerInterface $authManager,
        private BoxInfoInterface $boxInfo,
        private Configuration $config,
        private HttpClient $client,
    ) {}

    private function login(): string
    {
        if (!$this->authManager->hasChallenge()) {
            $result = $this->client->get(
                ['challenge'],
                $this->boxInfo->apiUrl . '/login/',
            );

            $this->authManager->setChallenge($result['challenge']);
        }

        $result = $this->client->post(
            ['session_token', 'challenge', 'permissions'],
            $this->boxInfo->apiUrl . '/login/session/',
            [
                'json' => [
                    'app_id' => $this->config->appId,
                    'password' => $this->authManager->getPassword(),
                ],
            ],
        );

        $this->authManager
            ->setChallenge($result['challenge'])
            ->setSessionToken($result['session_token'])
            ->setPermissions($result['permissions']);

        return $result['session_token'];
    }

    public function getAuthHeader(): array
    {
        return ['X-Fbx-App-Auth' => $this->authManager->getSessionToken() ?? $this->login()];
    }

    public function can(Permission $perm): bool
    {
        return $this->authManager->hasPermission($perm->value);
    }
}
