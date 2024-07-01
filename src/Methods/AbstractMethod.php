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
    public function run(string $action = 'get', array|int $id = [], array $params = []): ?array
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
            $this->boxInfo->apiUrl . static::API . ($hasId ? '/' . $id : ''),
            ['headers' => $this->authHeader],
        );
    }

    /**
     * @throws AuthException
     */
    protected function set(?int $id, array $params): ?array
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        if (defined('static::REQUIRED_SET')) {
            return $this->client->post(
                static::REQUIRED_SET,
                $this->boxInfo->apiUrl . static::API . (isset($id) ? '/' . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );
        } else {
            $response = $this->client->post(
                $this->boxInfo->apiUrl . static::API . (isset($id) ? '/' . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );

            if (!$response['success']) {
                throw new ApiErrorException(static::FAIL_MESSAGE_SET);
            }

            return null;
        }
    }

    protected function update(?int $id, array $params): ?array
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        if (defined('static::REQUIRED_PUT')) {
            return $this->client->put(
                static::REQUIRED_PUT,
                $this->boxInfo->apiUrl . static::API . (isset($id) ? '/' . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );
        } else {
            $response = $this->client->put(
                $this->boxInfo->apiUrl . static::API . (isset($id) ? '/' . $id : ''),
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );

            if (!$response['success']) {
                throw new ApiErrorException(static::FAIL_MESSAGE_UPDATE);
            }

            return null;
        }
    }

    protected function delete(int $id, array $params): void
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        $response = $this->client->delete(
            $this->boxInfo->apiUrl . static::API . '/' . $id,
            ['headers' => $this->authHeader],
        );

        if (!$response['success']) {
            throw new ApiErrorException(static::FAIL_MESSAGE_DELETE);
        }
    }
}
