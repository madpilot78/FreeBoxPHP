<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use InvalidArgumentException;
use OutOfBoundsException;

class BoxInfo implements BoxInfoInterface
{
    private const array REQUIRED = [
        'api_base_url',
        'device_name',
        'https_available',
        'api_domain',
        'api_version',
    ];

    private array $info = [];
    private ?string $apiUrl = null;

    public function __construct(private Configuration $config) {}

    /**
     * the api_version call should return a flat json
     * object with some properties.
     *
     * NOTE: Some can be overridden by configuration.
     *
     * @throws InvalidArgumentException
     */
    public function save(array $data): self
    {
        $req = self::REQUIRED;

        foreach ($data as $name => $value) {
            if (is_array($value) || is_object($value)) {
                throw new InvalidArgumentException('Invalid json returned');
            }

            if (in_array($name, $req)) {
                unset($req[array_search($name, $req)]);
            }

            $this->info[$name] = $value;
        }

        if (!empty($req)) {
            throw new InvalidArgumentException(
                'Returned json missing required propertie(s): ' . implode(', ', $req),
            );
        }

        if (!$this->config->isDefaulthostname()) {
            $this->info['api_domain'] = $this->config->hostname;
        }

        return $this;
    }

    private function makeApiUrl(): ?string
    {
        if (
            !isset($this->info['api_domain']) ||
            !isset($this->info['api_base_url']) ||
            !isset($this->info['api_version'])
        ) {
            return null;
        }

        $major = substr($this->info['api_version'], 0, strpos($this->info['api_version'], '.'));

        if ($this->info['https_available']) {
            $port = $this->config->localAccess ? '' : ':' . $this->info['https_port'];
            $scheme = 'https';
        } else {
            $port = '';
            $scheme = 'http';
        }

        return $scheme . '://' . $this->info['api_domain'] . $port . $this->info['api_base_url'] . 'v' . $major;
    }

    public function getInfo(): array
    {
        return $this->info;
    }

    public function getApiUrl(): string
    {
        $this->apiUrl ??= $this->makeApiUrl();

        return $this->apiUrl ?? '';
    }

    /**
     * @throws OutOfBoundsException
     */
    public function getProperty(string $name): null|bool|int|string
    {
        if (array_key_exists($name, $this->info)) {
            return $this->info[$name];
        }

        throw new OutOfBoundsException('Property ' . $name . ' not found', 101);
    }

    public function isPropertySet(string $name): bool
    {
        return isset($this->info[$name]);
    }
}
