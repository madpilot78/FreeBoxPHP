<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use madpilot78\FreeBoxPHP\Exception\ApiAuthException;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\NetworkErrorException;
use madpilot78\FreeBoxPHP\HttpClient;

class HttpClientTest extends TestCase
{
    private const URL = 'https://www.test.org/test';

    private HttpClient $httpClient;
    private MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock = new MockHandler();

        $this->httpClient = new HttpClient(
            new Client([
                'handler' => HandlerStack::create($this->mock),
            ]),
            new NullLogger(),
        );
    }

    public function testNotJson(): void
    {
        $this->mock->append(new Response(body: 'Test string, not json.'));

        $this->expectException(ApiErrorException::class);
        $this->httpClient->get(self::URL);
    }

    public function testNotAuthNoMsg(): void
    {
        $this->mock->append(
            new Response(
                status: 403,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'status' => 'Access denied',
                ]),
            ),
        );

        $this->expectException(ApiAuthException::class);
        $this->expectExceptionMessage('Unknown error');
        $this->httpClient->get(self::URL);
    }

    public function testNotAuthWithMsg(): void
    {
        $this->mock->append(
            new Response(
                status: 403,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'msg' => 'Access denied',
                ]),
            ),
        );

        $this->expectException(ApiAuthException::class);
        $this->expectExceptionMessage('Access denied');
        $this->httpClient->get(self::URL);
    }

    public function testNotFound(): void
    {
        $this->mock->append(
            new Response(
                status: 404,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'msg' => 'Richiesta non valida (404)',
                    'success' => false,
                    'error_code' => 'invalid_request',
                ]),
            ),
        );

        $this->expectException(ApiErrorException::class);
        $this->httpClient->get(self::URL);
    }

    public function testUknownStatus(): void
    {
        $this->mock->append(
            new Response(
                status: 410,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'msg' => 'Gone',
                ]),
            ),
        );

        $this->expectException(NetworkErrorException::class);
        $this->httpClient->get(self::URL);
    }

    public function testServerError(): void
    {
        $this->mock->append(new Response(status: 500, body: 'Server error'));

        $this->expectException(ServerException::class);
        $this->httpClient->get(self::URL);
    }

    public function testNot200Response(): void
    {
        $this->mock->append(new Response(status: 201, body: 'Created'));

        $this->expectException(NetworkErrorException::class);
        $this->httpClient->get(self::URL);
    }

    public function testJsonNoRequired(): void
    {
        $this->mock->append(
            new Response(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'foo' => 'bar',
                ]),
            ),
        );

        $content = $this->httpClient->get(self::URL);

        $this->assertArrayHasKey('foo', $content);
        $this->assertEquals('bar', $content['foo']);
    }

    public function testJsonRequiredMissing(): void
    {
        $this->mock->append(
            new Response(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'success' => true,
                    'result' => [
                        'foo' => 'bar',
                    ],
                ]),
            ),
        );

        $this->expectException(ApiErrorException::class);
        $this->httpClient->get(self::URL, ['baz']);
    }

    public function testJsonRequiredNoResult(): void
    {
        $this->mock->append(
            new Response(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'success' => true,
                ]),
            ),
        );

        $this->expectException(ApiErrorException::class);
        $this->httpClient->get(self::URL, ['foo']);
    }

    public function testJsonRequiredNoSuccess(): void
    {
        $this->mock->append(
            new Response(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'result' => [
                        'foo' => 'bar',
                    ],
                ]),
            ),
        );

        $this->expectException(ApiErrorException::class);
        $this->httpClient->get(self::URL, ['foo']);
    }

    public function testJsonRequiredGood(): void
    {
        $this->mock->append(
            new Response(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'success' => true,
                    'result' => [
                        'foo' => 'bar',
                    ],
                ]),
            ),
        );

        $content = $this->httpClient->post(self::URL, ['foo']);

        $this->assertArrayHasKey('foo', $content);
        $this->assertEquals('bar', $content['foo']);
    }

    public function testHsonRequiredArray(): void
    {
        $this->mock->append(
            new Response(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode([
                    'success' => true,
                    'result' => [
                        [
                            'foo' => '1',
                        ],
                        [
                            'bar' => '2',
                        ],
                    ],
                ]),
            ),
        );

        $content = $this->httpClient->post(self::URL, ['']);

        $this->assertCount(2, $content);
        $this->assertArrayHasKey('foo', $content[0]);
        $this->assertEquals('1', $content[0]['foo']);
        $this->assertArrayHasKey('bar', $content[1]);
        $this->assertEquals('2', $content[1]['bar']);
    }
}
