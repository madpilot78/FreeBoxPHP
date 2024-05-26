<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Psr7\Response;

trait NeedsLogin
{
    private const array FAKELOGINJSON = [
        <<<JSON
            {
                "success": true,
                "result": {
                    "logged_in": false,
                    "challenge": "fakeChallenge"
                }
            }
            JSON,
        <<<JSON
            {
               "success": true,
               "result": {
                    "session_token": "fakeToken",
                    "challenge": "fakeChallenge",
                    "permissions": {
                        "downloader": true
                    }
                }
            }
            JSON,
    ];

    private function setupFakeLogin(): void
    {
        $this->mock->append(
            new Response(body: self::FAKELOGINJSON[0]),
            new Response(body: self::FAKELOGINJSON[1]),
        );
    }
}
