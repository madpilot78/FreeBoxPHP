<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

interface MethodInterface
{
    /**
     * @param array<string, bool|int|string> $params
     *
     * @return null|array<string, array<string, array<string, mixed>|bool|list<string>>|bool|int|list<int>|string>
     *
     * @throws \InvalidArgumentException
     */
    public function run(string $action = 'get', null|int|string $id = null, array $params = []): ?array;
}
