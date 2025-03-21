<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface BoxInterface
{
    /**
     * @return array<string, bool|int|string>
     */
    public function getBoxInfo(): array;

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function connectionConfiguration(string $action = 'get', array $params = []): array|BoxInterface;

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function connectionIPv6Configuration(string $action = 'get', array $params = []): array|BoxInterface;

    /**
     * @return array<string, mixed>|BoxInterface
     */
    public function connectionStatus(): array|BoxInterface;

    public function discover(): BoxInterface;

    /**
     * @param array<string, bool|int|string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function fwRedir(string $action = 'get', null|int|string $id = null, array $params = []): array|BoxInterface;

    /**
     * @return array<string, mixed>|BoxInterface
     */
    public function lanBrowserInterfaces(): array|BoxInterface;

    /**
     * @param array<string, string> $params
     *
     * @return array<string, mixed>|BoxInterface
     */
    public function language(string $action = 'get', array $params = []): array|BoxInterface;

    /**
     * @param array<string, string> $params
     */
    public function lanWol(string $id, array $params): BoxInterface;

    public function login(): BoxInterface;

    public function logout(): BoxInterface;

    public function register(bool $quiet = true, bool $skipSleep = false): string;
}
