<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\HttpClient;

class ConnectionStatus
{
    public function __construct(
        private AuthSession $authSession,
        private BoxInfo $boxInfo,
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
