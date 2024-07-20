<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\TestWith;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Exception\AuthException;

class RegisterTest extends MethodTestCase
{
    #[TestWith([true])]
    #[TestWith([false])]
    public function testDiscoverSuccess(bool $quiet): void
    {
        $this->mock->append(
            new Response(
                body: <<<JSON
                    {
                       "uid": "23b86ec8091013d668829fe12791fdab",
                       "device_name": "Freebox Server",
                       "box_model": "fbxgw7-r1/full",
                       "box_model_name": "Freebox v7 (r1)",
                       "api_version": "6.0",
                       "api_base_url": "/api/",
                       "api_domain": "example.fbxos.fr",
                       "https_available": true,
                       "https_port": 3615
                    }
                    JSON,
            ),
            new Response(
                body: <<<JSON
                    {
                        "success": true,
                        "result": {
                            "app_token": "AppTokenVal",
                            "track_id": 42
                        }
                    }
                    JSON,
            ),
            new Response(
                body: <<<JSON
                    {
                        "success": true,
                        "result": {
                            "status": "pending",
                            "challenge": "ChallengeVal"
                        }
                    }
                    JSON,
            ),
            new Response(
                body: <<<JSON
                    {
                        "success": true,
                        "result": {
                            "status": "granted",
                            "challenge": "ChallengeVal"
                        }
                    }
                    JSON,
            ),
        );

        $box = new Box(client: $this->guzzleClient);

        ob_start();
        $output = $box->discover()->register(quiet: $quiet, skipSleep: true);
        ob_end_clean();

        $this->assertEquals('AppTokenVal', $output);
    }

    private function mockDenied(): void
    {
        $this->mock->append(
            new Response(
                body: <<<JSON
                    {
                       "uid": "23b86ec8091013d668829fe12791fdab",
                       "device_name": "Freebox Server",
                       "box_model": "fbxgw7-r1/full",
                       "box_model_name": "Freebox v7 (r1)",
                       "api_version": "6.0",
                       "api_base_url": "/api/",
                       "api_domain": "example.fbxos.fr",
                       "https_available": true,
                       "https_port": 3615
                    }
                    JSON,
            ),
            new Response(
                body: <<<JSON
                    {
                        "success": true,
                        "result": {
                            "app_token": "AppTokenVal",
                            "track_id": 42
                        }
                    }
                    JSON,
            ),
            new Response(
                body: <<<JSON
                    {
                        "success": true,
                        "result": {
                            "status": "pending",
                            "challenge": "ChallengeVal"
                        }
                    }
                    JSON,
            ),
            new Response(
                body: <<<JSON
                    {
                        "success": true,
                        "result": {
                            "status": "denied",
                            "challenge": "ChallengeVal"
                        }
                    }
                    JSON,
            ),
        );
    }

    public function testDiscoverDeniedQuiet(): void
    {
        $this->mockDenied();

        $box = new Box(client: $this->guzzleClient);

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('the user denied the authorization request');

        $box->discover()->register(quiet: true, skipSleep: true);
    }

    public function testDiscoverDeniedNoisy(): void
    {
        $this->mockDenied();

        $box = new Box(client: $this->guzzleClient);

        ob_start();
        $returned = $box->discover()->register(quiet: false, skipSleep: true);
        $output = ob_get_clean();

        $this->assertEquals('', $returned);
        $this->assertEquals('Authorization request sent...Check router.' . PHP_EOL . 'Polling: .Denied.' . PHP_EOL, $output);
    }
}
