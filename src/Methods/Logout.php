<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Exception\ApiAuthException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;

class Logout
{
    private array $authHeader;

    public function __construct(
        private AuthSession $authSession,
        private BoxInfo $boxInfo,
        private HttpClient $client,
    ) {}

    public function run(): void
    {
        $response = $this->client->get(
            $this->boxInfo->apiUrl . '/login/logout',
            ['headers' => $this->authSession->getAuthHeader()],
        );

        if (!$response['success']) {
            throw new ApiAuthException('logout failed');
        }
    }
}
