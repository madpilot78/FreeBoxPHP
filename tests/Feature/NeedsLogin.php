<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Psr7\Response;
use madpilot78\FreeBoxPHP\Enum\Permission;

trait NeedsLogin
{
    private function setupFakeLogin(Permission $perm = Permission::Downloader): void
    {
        $this->mock->append(
            new Response(body: <<<JSON
                {
                    "success": true,
                    "result": {
                        "logged_in": false,
                        "challenge": "fakeChallenge"
                    }
                }
                JSON
            ),
            new Response(body: <<<JSON
                {
                   "success": true,
                   "result": {
                        "session_token": "fakeToken",
                        "challenge": "fakeChallenge",
                        "permissions": {
                            "{$perm->value}": true
                        }
                    }
                }
                JSON
            ),
        );
    }
}
