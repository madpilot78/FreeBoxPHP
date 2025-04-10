<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use InvalidArgumentException;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\AuthException;
use madpilot78\FreeBoxPHP\HttpClientInterface;

abstract class AbstractMethod implements MethodInterface
{
    protected const string API = '';
    /** @var list<string> */
    protected const array ACTIONS = [];
    protected const Permission PERM = Permission::None;
    protected const string FAIL_MESSAGE = '';
    /** @var list<string> */
    protected const array REQUIRED_GET = [];
    /** @var list<string> */
    protected const array REQUIRED_GET_ID = [];
    /** @var list<string> */
    protected const array REQUIRED_SET = [];
    /** @var list<string> */
    protected const array REQUIRED_PUT = [];
    protected const string FAIL_MESSAGE_SET = '';
    protected const string FAIL_MESSAGE_UPDATE = '';
    protected const string FAIL_MESSAGE_DELETE = '';

    /** @var non-empty-array<string, string> */
    protected array $authHeader;

    private string $separator = '/';

    public function __construct(
        protected AuthSessionInterface $authSession,
        protected BoxInfoInterface $boxInfo,
        protected HttpClientInterface $client,
    ) {
        if (str_ends_with(static::API, '/')) {
            $this->separator = '';
        }
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return null|array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public function run(string $action = 'get', null|int|string $id = null, array $params = []): ?array
    {
        if (!in_array($action, static::ACTIONS)) {
            throw new InvalidArgumentException('Unknown action ' . $action);
        }

        $this->authHeader = $this->authSession->getAuthHeader();

        return $this->$action($id, $params);
    }

    /**
     * @param array<string, bool|int|string> $params (unused)
     *
     * @return array<string, mixed>
     */
    protected function get(?int $id, array $params): array
    {
        $hasId = isset($id);
        return $this->client->get(
            $this->boxInfo->getApiUrl() . static::API . ($hasId ? $this->separator . $id : ''),
            $hasId ? static::REQUIRED_GET_ID : static::REQUIRED_GET,
            ['headers' => $this->authHeader],
        );
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return null|array<string, mixed>
     *
     * @throws AuthException
     */
    protected function set(null|int|string $id, array $params): ?array
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        if (empty(static::REQUIRED_SET)) {
            $response = $this->client->post(
                $this->boxInfo->getApiUrl() . static::API . (isset($id) ? $this->separator . $id : ''),
                [],
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
                $this->boxInfo->getApiUrl() . static::API . (isset($id) ? $this->separator . $id : ''),
                static::REQUIRED_SET,
                [
                    'headers' => $this->authHeader,
                    'json' => $params,
                ],
            );
        }
    }

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return null|array<string, mixed>
     */
    protected function update(?int $id, array $params): ?array
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        return $this->client->put(
            $this->boxInfo->getApiUrl() . static::API . (isset($id) ? $this->separator . $id : ''),
            static::REQUIRED_PUT,
            [
                'headers' => $this->authHeader,
                'json' => $params,
            ],
        );
}

    /**
     * @param array<string, bool|int|string> $params (unused)
     */
    protected function delete(int $id, array $params): void
    {
        if (static::PERM != Permission::None && !$this->authSession->can(static::PERM)) {
            throw new AuthException(AuthSessionInterface::NO_PERM_MSG);
        }

        $response = $this->client->delete(
            $this->boxInfo->getApiUrl() . static::API . $this->separator . $id,
            ['headers' => $this->authHeader],
        );

        if (!$response['success']) {
            throw new ApiErrorException(static::FAIL_MESSAGE_DELETE);
        }
    }
}
