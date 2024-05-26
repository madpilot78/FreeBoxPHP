<?php

declare(strict_types=1);

namespace Tests\Unit\Methods;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use madpilot78\FreeBoxPHP\Auth\Session as AuthSession;
use madpilot78\FreeBoxPHP\BoxInfo;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\HttpClient;
use madpilot78\FreeBoxPHP\Methods\Language;

class LanguageTest extends TestCase
{
    private const array LANGOBJ = [
        'lang' => 'fra',
        'avalaible' => [
            'fra',
            'eng',
        ],
    ];
    private const array POSTRESPONSEOBJ = [
        'lang' => 'fra',
        'avalaible' => [
            'fra',
            'eng',
        ],
    ];

    private AuthSession $authSessionStub;
    private BoxInfo $boxInfoStub;
    private HttpClient $httpClientStub;
    private Language $language;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authSessionStub = $this->createStub(AuthSession::class);
        $this->boxInfoStub = $this->createStub(BoxInfo::class);
        $this->httpClientStub = $this->createStub(HttpClient::class);

        $this->language = new Language(
            $this->authSessionStub,
            $this->boxInfoStub,
            $this->httpClientStub,
        );

        $this->authSessionStub
            ->method('getAuthHeader')
            ->willReturn(['X-Fbx-App-Auth' => 'TokenStub']);
    }

    public function testGetLanguage(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(self::LANGOBJ);

        $this->assertEquals(self::LANGOBJ, $this->language->run('get'));
    }

    public function testSetLanguage(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => true]);

        $this->assertNull($this->language->run('set', 'eng'));
    }

    public function testSetLanguageFail(): void
    {
        $this->httpClientStub
            ->method('__call')
            ->willReturn(['success' => false]);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Failed to set language');

        $this->language->run('set', 'eng');
    }

    public function testWrongMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown action foo');

        $this->language->run('foo');
    }
}
