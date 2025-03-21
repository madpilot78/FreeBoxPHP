<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\Exception\MissingAuthException;

class ManagerTest extends TestCase
{
    private const HASH = '0c534563a85c1e77106850f4fe745daa1be1ebea';
    private AuthManager $authManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManager = new AuthManager();
    }

    public function testWithoutAuthToken(): void
    {
        $this->expectException(MissingAuthException::class);
        $this->authManager->getPassword('challenge');
    }

    public function testWithAuthTokenNoChallenge(): void
    {
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(
            AuthManager::class,
            $this->authManager->setAuthToken('token'),
        );
        $this->expectException(MissingAuthException::class);
        $this->authManager->getPassword();
    }

    public function testWithAuthTokenAndChallengeArg(): void
    {
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(
            AuthManager::class,
            $this->authManager->setAuthToken('token'),
        );

        $this->assertEquals(self::HASH, $this->authManager->getPassword('challenge'));
    }

    public function testSetHasChallenge(): void
    {
        $this->assertFalse($this->authManager->hasChallenge());

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(
            AuthManager::class,
            $this->authManager->setChallenge('challenge'),
        );

        $this->assertTrue($this->authManager->hasChallenge());
    }

    public function testWithAuthTokenAndChallengeProp(): void
    {
        $this->authManager
            ->setAuthToken('token')
            ->setChallenge('challenge');
        $result = $this->authManager->getPassword();

        $this->assertEquals(self::HASH, $result);
    }

    public function testSetHasPermission(): void
    {
        $this->assertFalse($this->authManager->hasPermission('foo'));
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(
            AuthManager::class,
            $this->authManager->setPermissions([
                'foo' => true,
                'bar' => true,
                'zzz' => false,
            ]),
        );
        $this->assertTrue($this->authManager->hasPermission('foo'));
        $this->assertFalse($this->authManager->hasPermission('baz'));
        $this->assertFalse($this->authManager->hasPermission('zzz'));
    }
}
