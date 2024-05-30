<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Auth\SessionInterface as AuthSessionInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\HttpClient;

class ConnectionStatus
{
    public function __construct(
        private AuthSessionInterface $authSession,
        private BoxInfoInterface $boxInfo,
        private HttpClient $client,
    ) {}

    public function run(): array
    {
        return $this->client->get(
            ['type', 'ipv4', 'ipv4_port_range', 'ipv6', 'state'],
            $this->boxInfo->apiUrl . '/connection',
            ['headers' => $this->authSession->getAuthHeader()],
        );
    }
}
