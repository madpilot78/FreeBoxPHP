<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class Register
{
    private const int POLL_WAIT = 5;
    private const int POLL_MAX = 60; /* 5 minutes */

    private int $trackId;
    private string $unauthToken;

    public function __construct(
        private AuthManager $authManager,
        private BoxInfo $boxInfo,
        private Configuration $config,
        private HttpClient $client,
    ) {}

    public function run(bool $quiet = true, bool $skipSleep = false): ?string
    {
        if (!$quiet) {
            echo 'Authorization request sent...Check router.' . PHP_EOL;
        }

        $result = $this->client->post(
            ['app_token', 'track_id'],
            $this->boxInfo->apiUrl . '/login/authorize',
            [
                'json' => [
                    'app_id' => $this->config->appId,
                    'app_name' => $this->config->appName,
                    'app_version' => Configuration::VERSION,
                    'device_name' => $this->config->deviceName,
                ],
            ],
        );

        $this->unauthToken = $result['app_token'];
        $this->trackId = $result['track_id'];

        return $this->poll($quiet, $skipSleep);
    }

    /**
     * @throws AuthException
     */
    private function poll(bool $quiet = true, bool $skipSleep = false): string
    {
        if (!$quiet) {
            echo 'Polling: ';
        }

        for ($i = 0; $i < self::POLL_MAX; $i++) {
            $result = $this->client->get(
                ['status', 'challenge'],
                $this->boxInfo->apiUrl . '/login/authorize/' . $this->trackId,
            );

            $this->authManager->setChallenge($result['challenge']);

            switch ($result['status']) {
                case 'unknown':
                    throw new AuthException('app_token is invalid or has been revoked');
                    break;

                case 'pending':
                    if (!$quiet) {
                        echo '.';
                    }
                    if (!$skipSleep) {
                        sleep(self::POLL_WAIT);
                    }
                    break;

                case 'timeout':
                    if (!$quiet) {
                        echo 'Timed out.' . PHP_EOL;
                        return '';
                    } else {
                        throw new AuthException('the user did not confirm the authorization within the given time');
                    }
                    break;

                case 'granted':
                    if (!$quiet) {
                        echo 'Granted.' . PHP_EOL;
                    }
                    $this->authManager->setAuthToken($this->unauthToken);
                    return $this->unauthToken;
                    break;

                case 'denied':
                    if (!$quiet) {
                        echo 'Denied.' . PHP_EOL;
                        return '';
                    } else {
                        throw new AuthException('the user denied the authorization request');
                    }
                    break;

                default:
                    throw new AuthException('Unknown authorization tracking status returned');
                    break;
            }
        }

        if (!$quiet) {
            echo 'Giving up.' . PHP_EOL;
        }

        return '';
    }
}
