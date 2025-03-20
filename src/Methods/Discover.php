<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\HttpClientInterface;
use madpilot78\FreeBoxPHP\Exception\NotSupportedException;

class Discover extends AbstractMethod implements MethodInterface
{
    public function __construct(
        protected HttpClientInterface $client,
        protected Configuration $config,
        protected BoxInfoInterface $boxInfo,
    ) {}

    /**
     * @throws NotSupportedException
     */
    public function run(string $action = 'get', null|int|string $id = null, array $params = []): null
    {
        $this->boxInfo->save($this->client->get(
            'http' . ($this->config->https ? 's' : '') . '://' .
            $this->config->hostname .
            '/api_version',
            [],
        ));

        if (!$this->boxInfo->getProperty('https_available')) {
            throw new NotSupportedException('Only https enabled boxes supported.');
        }

        return null;
    }
}
