<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use Psr\Log\LoggerInterface;
use madpilot78\FreeBoxPHP\Auth\ManagerInterface as AuthManagerInterface;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Exception\AuthException;
use madpilot78\FreeBoxPHP\HttpClientInterface;

class Register
{
    private const int POLL_WAIT = 5;
    private const int POLL_MAX = 60; /* 5 minutes */

    private int $trackId;
    private string $unauthToken;

    public function __construct(
        private AuthManagerInterface $authManager,
        private BoxInfoInterface $boxInfo,
        private Configuration $config,
        private HttpClientInterface $client,
        private LoggerInterface $logger,
    ) {}

    public function run(bool $quiet = true, bool $skipSleep = false): ?string
    {
        $this->logger->notice('FreeBoxPHP Registration started');
        if (!$quiet) {
            echo 'Authorization request sent...Check router.' . PHP_EOL;
        }

        $result = $this->client->post(
            $this->boxInfo->getApiUrl() . '/login/authorize',
            ['app_token', 'track_id'],
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

        $ret = $this->poll($quiet, $skipSleep);
        $this->logger->notice('FreeBoxPHP Registration ended');

        return $ret;
    }

    /**
     * @throws AuthException
     */
    private function poll(bool $quiet = true, bool $skipSleep = false): string
    {
        $this->logger->info('FreeBoxPHP Registration polling');

        if (!$quiet) {
            echo 'Polling: ';
        }

        for ($i = 0; $i < self::POLL_MAX; $i++) {
            $result = $this->client->get(
                $this->boxInfo->getApiUrl() . '/login/authorize/' . $this->trackId,
                ['status', 'challenge'],
            );

            $this->authManager->setChallenge($result['challenge']);

            switch ($result['status']) {
                case 'unknown':
                    $this->logger->alert('FreeBoxPHP Registration got invalid status');
                    throw new AuthException('app_token is invalid or has been revoked');

                case 'pending':
                    $this->logger->info('FreeBoxPHP Registration still pending');
                    if (!$quiet) {
                        echo '.';
                    }
                    if (!$skipSleep) {
                        sleep(self::POLL_WAIT);
                    }
                    break;

                case 'timeout':
                    $this->logger->warning('FreeBoxPHP Registration timed out');
                    if (!$quiet) {
                        echo 'Timed out.' . PHP_EOL;
                        return '';
                    } else {
                        throw new AuthException('the user did not confirm the authorization within the given time');
                    }

                case 'granted':
                    $this->logger->info('FreeBoxPHP Registration granted');
                    if (!$quiet) {
                        echo 'Granted.' . PHP_EOL;
                    }
                    $this->authManager->setAuthToken($this->unauthToken);
                    return $this->unauthToken;

                case 'denied':
                    $this->logger->error('FreeBoxPHP Registration denied');
                    if (!$quiet) {
                        echo 'Denied.' . PHP_EOL;
                        return '';
                    } else {
                        throw new AuthException('the user denied the authorization request');
                    }

                default:
                    $this->logger->alert('FreeBoxPHP Registration got unknown status');
                    throw new AuthException('Unknown authorization tracking status returned');
            }
        }

        $this->logger->warning('FreeBoxPHP Registration giving up');
        if (!$quiet) {
            echo 'Giving up.' . PHP_EOL;
        }

        return '';
    }
}
