<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface BoxInfoInterface
{
    /**
     * the api_version call should return a flat json
     * object with some properties.
     *
     * NOTE: Some can be overridden by configuration.
     *
     * @param array<string, bool|int|string> $data
     *
     * @throws \InvalidArgumentException
     */
    public function save(array $data): self;

    /**
     * @return array<string, bool|int|string>
     */
    public function getInfo(): array;

    public function getApiUrl(): string;

    /**
     * @throws \OutOfBoundsException
     */
    public function getProperty(string $name): null|bool|int|string;

    public function isPropertySet(string $name): bool;
}
