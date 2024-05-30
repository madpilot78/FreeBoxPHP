<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Exception\NotSupportedException;

class Discover
{
    public function __construct(
        private HttpClient $client,
        private Configuration $config,
        private BoxInfoInterface $boxInfo,
    ) {}

    /**
     * @throws NotSupportedException
     */
    public function run(): void
    {
        $this->boxInfo->save($this->client->get(
            'http' . ($this->config->https ? 's' : '') . '://' .
            $this->config->hostname .
            '/api_version',
        ));

        if (!$this->boxInfo->https_available) {
            throw new NotSupportedException('Only https enabled boxes supported.');
        }
    }
}
