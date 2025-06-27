<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Configuration;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $configuration = new Configuration();

        $this->assertEquals('net.madpilot.freeboxphp', $configuration->appId);
        $this->assertEquals('FreeBoxPHP', $configuration->appName);
        $this->assertEquals(
            realpath(__DIR__ . '/../../data/FreeBox.pem'),
            $configuration->certFile,
        );
        $this->assertEquals(gethostname(), $configuration->deviceName);

        $this->assertTrue($configuration->isDefaultHostname());
    }

    public function testCustomConfiguration(): void
    {
        $configuration = new Configuration(
            hostname: 'box.example.org',
            certFile: null,
            deviceName: 'DeepThought',
        );

        $this->assertNull($configuration->certFile);
        $this->assertEquals('DeepThought', $configuration->deviceName);
        $this->assertEquals('box.example.org', $configuration->hostname);
        $this->assertFalse($configuration->isDefaultHostname());
    }
}
