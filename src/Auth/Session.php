<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\HttpClient;

class Session
{
    public function __construct(
        private AuthManager $authManager,
        private BoxInfo $boxInfo,
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

        $this->authManager->setChallenge($result['challenge']);
        $this->authManager->setSessionToken($result['session_token']);
        $this->authManager->setPermissions($result['permissions']);

        return $result['session_token'];
    }

    public function getAuthHader(): array
    {
        return ['X-Fbx-App-Auth' => $this->authManager->getSessionToken() ?? $this->login()];
    }
}
