<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

interface HttpClientInterface
{
    public function get(string $url, array $required = [], array $options = []): array;

    public function post(string $url, array $required = [], array $options = []): array;

    public function put(string $url, array $required = [], array $options = []): array;

    public function delete(string $url, array $options = []): array;
}
