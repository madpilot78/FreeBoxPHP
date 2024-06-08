<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use madpilot78\FreeBoxPHP\Auth\Manager as AuthManager;
use madpilot78\FreeBoxPHP\Auth\ManagerInterface as AuthManagerInterface;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\BoxInfoInterface;
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Exception\AuthException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\Register;

class RegisterTest extends TestCase
{
    private const string CHALLENGE = 'challengeVal';
    private const string APPTOKEN = 'apptokenVal';

    private AuthManagerInterface $authManagerMock;
    private BoxInfoInterface $boxInfoStub;
    private HttpClient $httpClientStub;
    private Register $register;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManagerMock = $this->createMock(AuthManager::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->register = new Register(
            $this->authManagerMock,
            $this->boxInfoStub,
            new Configuration(),
            $this->httpClientStub,
            new NullLogger,
        );
    }

    public function testRegisterUnknown(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(
                [
                    'app_token' => 'Token',
                    'track_id' => 42,
                ],
                [
                    'status' => 'unknown',
                    'challenge' => self::CHALLENGE,
                ],
            );

        $this->authManagerMock
            ->expects($this->once())
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('app_token is invalid or has been revoked');

        $this->register->run();
    }

    public function testRegisterPendingGranted(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(
                [
                    'app_token' => self::APPTOKEN,
                    'track_id' => 42,
                ],
                [
                    'status' => 'pending',
                    'challenge' => self::CHALLENGE,
                ],
                [
                    'status' => 'granted',
                    'challenge' => self::CHALLENGE,
                ],
            );

        $this->authManagerMock
            ->expects($this->exactly(2))
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();
        $this->authManagerMock
            ->expects($this->once())
            ->method('setAuthToken')
            ->with($this->equalTo(self::APPTOKEN))
            ->willReturnSelf();

        $this->assertEquals(self::APPTOKEN, $this->register->run(skipSleep: true));
    }

    public function testRegisterPendingTimeout(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(
                [
                    'app_token' => self::APPTOKEN,
                    'track_id' => 42,
                ],
                [
                    'status' => 'pending',
                    'challenge' => self::CHALLENGE,
                ],
                [
                    'status' => 'timeout',
                    'challenge' => self::CHALLENGE,
                ],
            );

        $this->authManagerMock
            ->expects($this->exactly(2))
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('the user did not confirm the authorization within the given time');

        $this->register->run(skipSleep: true);
    }

    public function testRegisterDenied(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(
                [
                    'app_token' => self::APPTOKEN,
                    'track_id' => 42,
                ],
                [
                    'status' => 'denied',
                    'challenge' => self::CHALLENGE,
                ],
            );

        $this->authManagerMock
            ->expects($this->once())
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('the user denied the authorization request');

        $this->register->run();
    }

    public function testRegisterOther(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(
                [
                    'app_token' => self::APPTOKEN,
                    'track_id' => 42,
                ],
                [
                    'status' => 'unexpected value',
                    'challenge' => self::CHALLENGE,
                ],
            );

        $this->authManagerMock
            ->expects($this->once())
            ->method('setChallenge')
            ->with($this->equalTo(self::CHALLENGE))
            ->willReturnSelf();

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Unknown authorization tracking status returned');

        $this->register->run();
    }
}
