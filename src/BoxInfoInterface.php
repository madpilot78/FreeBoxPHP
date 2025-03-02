<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface BoxInfoInterface
{
    public function save(array $data): self;

    public function getInfo(): array;

    public function getApiUrl(): string;

    public function getProperty(string $name): null|bool|int|string;

    public function isPropertySet(string $name): bool;
}
