<?php

declare(strict_types=1);

namespace Tests\Feature\Methods;

use Tests\Feature\NeedsLogin;
use madpilot78\FreeBoxPHP\Box;

class LoginTest extends MethodTestCase
{
    use NeedsLogin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupFakeLogin();
    }

    public function testLogin(): void
    {
        $box = new Box(authToken: 'fakeToken', client: $this->guzzleClient);

        $this->assertInstanceOf(Box::class, $box->login());
    }
}
