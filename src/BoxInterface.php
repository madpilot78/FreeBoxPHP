<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface BoxInterface
{
    public function getBoxInfo(): array;

    public function connectionConfiguration(string $action = 'get', array $params = []): array|BoxInterface;

    public function connectionIPv6Configuration(string $action = 'get', array $params = []): array|BoxInterface;

    public function connectionStatus(): array;

    public function discover(): BoxInterface;

    public function fwRedir(string $action = 'get', null|int|string $id = null, array $params = []): array|BoxInterface;

    public function lanBrowserInterfaces(): array;

    public function language(string $action = 'get', array $params = []): array|BoxInterface;

    public function lanWol(string $id, array $params): BoxInterface;

    public function login(): BoxInterface;

    public function logout(): BoxInterface;

    public function register(bool $quiet = true, bool $skipSleep = false): string;
}
