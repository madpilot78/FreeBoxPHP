<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Auth;

use Psr\Log\LoggerInterface;
use madpilot78\FreeBoxPHP\Auth\ManagerInterface as AuthManagerInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\HttpClientInterface;
use Psr\SimpleCache\CacheInterface;

class Session implements SessionInterface
{
    private const string SESSION_KEY = 'SessionToken';
    private const string PERMISSIONS_KEY = 'Permissions';

    public function __construct(
        private AuthManagerInterface $authManager,
        private BoxInfoInterface $boxInfo,
        private Configuration $config,
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private CacheInterface $cache,
    ) {}

    private function login(): string
    {
        $this->logger->debug('FreeBoxPHP starting Auth\Session::login()');

        $session_token = $this->cache->get($this->config->cacheKeyBase . self::SESSION_KEY);
        $permissions = $this->cache->get($this->config->cacheKeyBase . self::PERMISSIONS_KEY);

        if (isset($session_token) && isset($permissions)) {
            $this->authManager
                ->setPermissions($permissions);

            $this->logger->debug('FreeBoxPHP ending Auth\Session::login() from cache');

            return $session_token;
        }

        $result = $this->client->get(
            $this->boxInfo->getApiUrl() . '/login/',
            ['challenge'],
        );

        $this->authManager->setChallenge($result['challenge']);

        $result = $this->client->post(
            $this->boxInfo->getApiUrl() . '/login/session/',
            ['session_token', 'challenge', 'permissions'],
            [
                'json' => [
                    'app_id' => $this->config->appId,
                    'password' => $this->authManager->getPassword(),
                ],
            ],
        );

        $this->authManager
            ->setChallenge($result['challenge'])
            ->setPermissions($result['permissions']);

        $this->cache->set(
            $this->config->cacheKeyBase . self::SESSION_KEY,
            $result['session_token'],
            $this->config->tokenTTL,
        );
        $this->cache->set(
            $this->config->cacheKeyBase . self::PERMISSIONS_KEY,
            $result['permissions'],
            $this->config->tokenTTL,
        );

        $this->logger->debug('FreeBoxPHP ending Auth\Session::login() after query');

        return $result['session_token'];
    }

    /**
     * @return non-empty-array<string, string>
     */
    public function getAuthHeader(): array
    {
        return ['X-Fbx-App-Auth' => $this->login()];
    }

    public function can(Permission $perm): bool
    {
        return $this->authManager->hasPermission($perm->value);
    }
}
