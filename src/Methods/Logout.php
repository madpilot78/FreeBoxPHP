<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\Exception\ApiAuthException;

class Logout extends AbstractMethod implements MethodInterface
{
    public function run(string $action = 'get', array|int $id = [], array $params = []): ?array
    {
        $response = $this->client->get(
            $this->boxInfo->apiUrl . '/login/logout',
            ['headers' => $this->authSession->getAuthHeader()],
        );

        if (!$response['success']) {
            throw new ApiAuthException('logout failed');
        }

        return null;
    }
}
