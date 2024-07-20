<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use StdClass;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
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

    private BoxInfoInterface $boxInfo;

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
        $this->assertTrue($this->boxInfo->https_available);
    }

    public function testBoxInfoIsset(): void
    {
        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save(self::INFO),
        );

        $this->assertTrue(isset($this->boxInfo->api_domain));
        $this->assertFalse(isset($this->boxInfo->notset));
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

    public function testBoxInfoGetApiUrlHttps(): void
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

    public function testBoxInfoGetApiUrlHttp(): void
    {
        $info = self::INFO;
        $info['https_available'] = false;

        $this->assertInstanceOf(
            BoxInfo::class,
            $this->boxInfo->save($info),
        );
        $major = substr($info['api_version'], 0, strpos($info['api_version'], '.'));

        $this->assertEquals(
            'http://' . $info['api_domain'] . $info['api_base_url'] . 'v' . $major,
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

    public function testBoxInfoGetInfo(): void
    {
        $this->assertEquals(self::INFO, $this->boxInfo->save(self::INFO)->getInfo());
    }
}
