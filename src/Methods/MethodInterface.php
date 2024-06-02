<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Methods;

interface MethodInterface
{
    public function run(string $action, array $params = []): ?array;
}
