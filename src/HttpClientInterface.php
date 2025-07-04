<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface HttpClientInterface
{
    /**
     * @param list<string> $required
     * @param array<string, array<int|string, mixed>|string> $options
     *
     * @return array<string, array<string, mixed>|bool|int|string>
     */
    public function get(string $url, array $required = [], array $options = []): array;

    /**
     * @param list<string> $required
     * @param array<string, array<int|string, mixed>|string> $options
     *
     * @return array<string, array<string, mixed>|bool|int|string>
     */
    public function post(string $url, array $required = [], array $options = []): array;

    /**
     * @param list<string> $required
     * @param array<string, array<int|string, mixed>|string> $options
     *
     * @return array<string, array<string, mixed>|bool|int|string>
     */
    public function put(string $url, array $required = [], array $options = []): array;

    /**
     * @param array<string, array<int|string, mixed>|string> $options
     *
     * @return array<string, array<string, mixed>|bool|int|string>
     */
    public function delete(string $url, array $options = []): array;
}
