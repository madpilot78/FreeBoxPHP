<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Exception\ApiAuthException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;

class Logout
{
    private array $authHeader;

    public function __construct(
        private AuthSessionInterface $authSession,
        private BoxInfoInterface $boxInfo,
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
