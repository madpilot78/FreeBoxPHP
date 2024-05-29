<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use InvalidArgumentException;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\AuthException;
use madpilot78\FreeBoxPHP\HttpClient;

class Language
{
    private const array ACTIONS = ['get', 'set'];

    private array $authHeader;

    public function __construct(
        private AuthSession $authSession,
        private BoxInfo $boxInfo,
        private HttpClient $client,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function run(string $action, string $lang = 'eng'): ?array
    {
        if (!in_array($action, self::ACTIONS)) {
            throw new InvalidArgumentException('Unknown action ' . $action);
        }

        $this->authHeader = $this->authSession->getAuthHeader();

        return $this->$action($lang);
    }

    private function get(string $lang): array
    {
        return $this->client->get(
            ['lang', 'avalaible'],
            $this->boxInfo->apiUrl . '/lang',
            ['headers' => $this->authHeader],
        );
    }

    private function set(string $lang): void
    {
        if (!$this->authSession->can(Permission::Settings)) {
            throw new AuthException(AuthSession::NO_PERM_MSG);
        }

        $response = $this->client->post(
            $this->boxInfo->apiUrl . '/lang',
            ['json' => ['lang' => $lang]],
        );

        if (!$response['success']) {
            throw new ApiErrorException('Failed to set language');
        }
    }
}
