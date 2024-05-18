<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use StdClass;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Configuration;

class BoxInfoTest extends TestCase
{
    private const array INFO = [
        'api_base_url' => '/api/',
        'device_name' => 'thisBox',
        'https_available' => true,
        'https_port' => 443,
        'api_domain' => 'example.net',
        'api_version' => '10.1',
    ];

    private BoxInfo $boxInfo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->boxInfo = new BoxInfo(new Configuration());
    }

    public function testSavingBoxinfoArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid json returned');
        $this->boxInfo->save([
            'foo' => ['bar', 'baz'],
        ]);
    }

    public function testSavingBoxinfoObject(): void
    {
        $obj = new StdClass();
        $obj->bar = 'baz';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid json returned');
        $this->boxInfo->save([
            'foo' => $obj,
        ]);
    }

    public function testSavingBoxinfoMissing(): void
    {
        $info = self::INFO;

        unset($info['api_version']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Returned json missing required propertie(s): api_version');
        $this->boxInfo->save($info);
    }

    public function testBoxInfoGetValue(): void
    {
        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save(self::INFO),
        );

        $this->assertEquals(self::INFO['api_domain'], $this->boxInfo->api_domain);
        $this->assertTrue(self::INFO['https_available']);
    }

    public function testBoxInfoGetValueNotSaved(): void
    {
        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save(self::INFO),
        );

        $this->expectException(OutOfBoundsException::class);
        $result = $this->boxInfo->foo;
    }

    public function testBoxInfoGetApiUrl(): void
    {
        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save(self::INFO),
        );
        $major = substr(self::INFO['api_version'], 0, strpos(self::INFO['api_version'], '.'));

        $this->assertEquals(
            'https://' . self::INFO['api_domain'] . self::INFO['api_base_url'] . 'v' . $major,
            $this->boxInfo->apiUrl,
        );
    }

    public function testBoxInfoGetApiUrlNonLocal(): void
    {
        $this->boxInfo = new BoxInfo(new Configuration(localAccess: false));
        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save(self::INFO),
        );
        $major = substr(self::INFO['api_version'], 0, strpos(self::INFO['api_version'], '.'));

        $this->assertEquals(
            'https://' . self::INFO['api_domain'] . ':' . self::INFO['https_port'] . self::INFO['api_base_url'] . 'v' . $major,
            $this->boxInfo->apiUrl,
        );
    }

    public function testBoxInfoNonDefaultHostname(): void
    {
        $custom = 'myhost.example.org';
        $this->boxInfo = new BoxInfo(new Configuration(hostname: $custom));
        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save(self::INFO),
        );

        $this->assertEquals($custom, $this->boxInfo->api_domain);
    }
}
