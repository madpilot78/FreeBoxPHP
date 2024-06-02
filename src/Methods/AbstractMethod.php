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

abstract class AbstractMethod implements MethodInterface
{
    protected const string API = '';
    protected const array ACTIONS = [];
    protected const array REQUIRED = [];
    protected const Permission PERM = Permission::None;
    protected const string FAIL_MESSAGE = '';

    protected array $authHeader;

    public function __construct(
        protected AuthSessionInterface $authSession,
        protected BoxInfoInterface $boxInfo,
        protected HttpClient $client,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function run(string $action = 'get', array $params = []): ?array
    {
        if (!in_array($action, static::ACTIONS)) {
            throw new InvalidArgumentException('Unknown action ' . $action);
        }

        $this->authHeader = $this->authSession->getAuthHeader();

        return $this->$action($params);
    }

    protected function get(): array
    {
        return $this->client->get(
            static::REQUIRED,
            $this->boxInfo->apiUrl . static::API,
            ['headers' => $this->authHeader],
        );
    }

    protected function set(array $params): void
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        $response = $this->client->post(
            $this->boxInfo->apiUrl . static::API,
            [
                'headers' => $this->authHeader,
                'json' => $params,
            ],
        );

        if (!$response['success']) {
            throw new ApiErrorException(static::FAIL_MESSAGE);
        }
    }
}
