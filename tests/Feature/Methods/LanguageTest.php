<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;

class LanguageTest extends MethodTestCase
{
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupFakeLogin();
    }

    public function testLanguageGetSuccess(): void
    {
        $this->mock->append(new Response(body: self::GETJSON));
        $decoded = json_decode(self::JSON, true);

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertEquals($decoded['result'], $box->language('get'));
    }

    public function testLanguageWrongMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);
        $box->language('foo');
    }
}
