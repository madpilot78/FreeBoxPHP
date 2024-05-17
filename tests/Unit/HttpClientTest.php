<?php

declare(strict_types=1);

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
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

        $handlerStack = HandlerStack::create($this->mock);

        $this->httpClient = new HttpClient(new Client(['handler' => $handlerStack]));
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
        $this->mock->append(new Response(status: 404, body: 'Not found'));

        $this->expectException(ClientException::class);
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
        $this->httpClient->get(['baz'], self::URL);
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
        $this->httpClient->get(['foo'], self::URL);
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
        $this->httpClient->get(['foo'], self::URL);
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

        $content = $this->httpClient->get(['foo'], self::URL);

        $this->assertArrayHasKey('foo', $content);
        $this->assertEquals('bar', $content['foo']);
    }
}
