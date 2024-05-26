<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use InvalidArgumentException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;

class Language
{
    private const array ACTIONS = ['get'];

    private array $authHeader;

    public function __construct(
        private AuthSession $authSession,
        private BoxInfo $boxInfo,
        private HttpClient $client,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function run(string $action): ?array
    {
        if (!in_array($action, self::ACTIONS)) {
            throw new InvalidArgumentException('Unknown action ' . $action);
        }

        $this->authHeader = $this->authSession->getAuthHeader();

        return $this->$action();
    }

    private function get(): array
    {
        return $this->client->get(
            ['lang', 'avalaible'],
            $this->boxInfo->apiUrl . '/lang',
            ['headers' => $this->authHeader],
        );
    }
}
