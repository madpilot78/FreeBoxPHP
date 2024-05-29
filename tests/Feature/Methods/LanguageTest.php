<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\Permission;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class LanguageTest extends MethodTestCase
{
    use NeedsLogin;

    private const string GETJSON = <<<JSON
        {
            "success": true,
            "result": {
                "lang": "fra",
                "avalaible": [
                   "fra",
                   "eng"
                ]
            }
        }
        JSON;

    public function testLanguageGetSuccess(): void
    {
        $this->setupFakeLogin();

        $this->mock->append(new Response(body: self::GETJSON));
        $decoded = json_decode(self::GETJSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->language('get'));
    }

    public function testLanguageSetSuccess(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": true}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->language('set', 'eng'));
    }

    public function testLanguageSetNoPerm(): void
    {
        $this->setupFakeLogin();

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('No permission');

        $this->assertInstanceOf(Box::class, $box->language('set', 'eng'));
    }

    public function testLanguageSetFail(): void
    {
        $this->setupFakeLogin(Permission::Settings);

        $this->mock->append(new Response(body: '{"success": false}'));

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Failed to set language');

        $box->language('set', 'eng');
    }

    public function testLanguageWrongMethod(): void
    {
        $this->setupFakeLogin();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);
        $box->language('foo');
    }
}
