<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use madpilot78\PhpCsFixerConfig\Config;

$config = new Config();
$config
    ->setRiskyAllowed(true)
    ->setRules([
        'declare_strict_types' => true,
    ])
    ->getFinder()
    ->ignoreVCSIgnored(true)
    ->notPath('vendor')
    ->in(__DIR__);

return $config;
