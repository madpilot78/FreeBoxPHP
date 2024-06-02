<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use InvalidArgumentException;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\AuthException;
use madpilot78\FreeBoxPHP\HttpClient;

class ConnectionConfiguration
{
    private const array ACTIONS = ['get', 'set'];

    private array $authHeader;

    public function __construct(
        private AuthSessionInterface $authSession,
        private BoxInfoInterface $boxInfo,
        private HttpClient $client,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function run(string $action, array $params = []): ?array
    {
        if (!in_array($action, self::ACTIONS)) {
            throw new InvalidArgumentException('Unknown action ' . $action);
        }

        $this->authHeader = $this->authSession->getAuthHeader();

        return $this->$action($params);
    }

    private function get(): array
    {
        return $this->client->get(
            [
                'ping',
                'is_secure_pass',
                'remote_access_port',
                'remote_access',
                'wol',
                'adblock',
                'adblock_not_set',
                'api_remote_access',
                'allow_token_request',
                'remote_access_ip',
            ],
            $this->boxInfo->apiUrl . '/connection/config',
            ['headers' => $this->authHeader],
        );
    }

    private function set(array $params): void
    {
        if (!$this->authSession->can(Permission::Settings)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        $response = $this->client->post(
            $this->boxInfo->apiUrl . '/connection/config',
            [
                'headers' => $this->authHeader,
                'json' => $params,
            ],
        );

        if (!$response['success']) {
            throw new ApiErrorException('Failed to set connection configuration');
        }
    }
}
