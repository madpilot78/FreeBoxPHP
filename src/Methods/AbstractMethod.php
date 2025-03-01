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
    protected const Permission PERM = Permission::None;
    protected const string FAIL_MESSAGE = '';
    protected const array REQUIRED_GET = [];
    protected const array REQUIRED_GET_ID = [];
    protected const array REQUIRED_SET = [];
    protected const array REQUIRED_PUT = [];
    protected const string FAIL_MESSAGE_SET = '';
    protected const string FAIL_MESSAGE_UPDATE = '';
    protected const string FAIL_MESSAGE_DELETE = '';

    protected array $authHeader;

    private $separator = '/';

    public function __construct(
        protected AuthSessionInterface $authSession,
        protected BoxInfoInterface $boxInfo,
        protected HttpClient $client,
    ) {
        if (str_ends_with(static::API, '/')) {
            $this->separator = '';
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function run(string $action = 'get', array|int|string $id = [], array $params = []): ?array
    {
        if (!in_array($action, static::ACTIONS)) {
            throw new InvalidArgumentException('Unknown action ' . $action);
        }

        if (is_array($id)) {
            $params = $id;
            $id = null;
        }

        $this->authHeader = $this->authSession->getAuthHeader();

        return $this->$action($id, $params);
    }

    protected function get(?int $id, array $params): array
    {
        $hasId = isset($id);
        return $this->client->get(
            $hasId ? static::REQUIRED_GET_ID : static::REQUIRED_GET,
            $this->boxInfo->apiUrl . static::API . ($hasId ? $this->separator . $id : ''),
            ['headers' => $this->authHeader],
        );
    }

    /**
     * @throws AuthException
     */
    protected function set(int|string|null $id, array $params): ?array
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        if (empty(static::REQUIRED_SET)) {
            $response = $this->client->post(
                $this->boxInfo->apiUrl . static::API . (isset($id) ? $this->separator . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );

            if (!$response['success']) {
                throw new ApiErrorException(static::FAIL_MESSAGE_SET);
            }

            return null;
        } else {
            return $this->client->post(
                static::REQUIRED_SET,
                $this->boxInfo->apiUrl . static::API . (isset($id) ? $this->separator . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );
        }
    }

    protected function update(?int $id, array $params): ?array
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        if (empty(static::REQUIRED_PUT)) {
            $response = $this->client->put(
                $this->boxInfo->apiUrl . static::API . (isset($id) ? $this->separator . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );

            if (!$response['success']) {
                throw new ApiErrorException(static::FAIL_MESSAGE_UPDATE);
            }

            return null;
        } else {
            return $this->client->put(
                static::REQUIRED_PUT,
                $this->boxInfo->apiUrl . static::API . (isset($id) ? $this->separator . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );
        }
    }

    protected function delete(int $id, array $params): void
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        $response = $this->client->delete(
            $this->boxInfo->apiUrl . static::API . $this->separator . $id,
            ['headers' => $this->authHeader],
        );

        if (!$response['success']) {
            throw new ApiErrorException(static::FAIL_MESSAGE_DELETE);
        }
    }
}
