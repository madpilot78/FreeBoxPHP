<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface BoxInterface
{
    public function getBoxInfo(): array;

    public function connectionConfiguration(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface;

    public function connectionIPv6Configuration(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface;

    public function connectionStatus(string $action = 'get', array|int|string $id = [], array $params = []): array;

    public function discover(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface;

    public function fwRedir(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface;

    public function lanBrowserInterfaces(string $action = 'get', array|int|string $id = [], array $params = []): array;

    public function language(string $action = 'get', array|int|string $id = [], array $params = []): array|BoxInterface;

    public function lanWol(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface;

    public function login(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface;

    public function logout(string $action = 'get', array|int|string $id = [], array $params = []): BoxInterface;

    public function register(bool $quiet = true, bool $skipSleep = false): string;
}
