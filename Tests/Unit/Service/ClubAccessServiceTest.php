<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Service;

use JWeiland\Clubdirectory\Service\ClubAccessService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for the methods of ClubAccessService without database access
 */
class ClubAccessServiceTest extends UnitTestCase
{
    protected ClubAccessService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'clubdirectory-unit-test-encryption-key';

        $this->subject = new ClubAccessService(
            self::createStub(ConnectionPool::class),
            new Context(),
            new HashService(),
        );
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function createRequest(array $arguments): Request
    {
        $serverRequest = (new ServerRequest('https://www.example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('extbase', (new ExtbaseRequestParameters())->setArguments($arguments));

        return new Request($serverRequest);
    }

    public static function identityDataProvider(): array
    {
        return [
            'integer' => [12, 12],
            'numeric string' => ['12', 12],
            'array with integer identity' => [['__identity' => 12], 12],
            'array with numeric string identity' => [['__identity' => '12', 'title' => 'Club'], 12],
            'array without identity' => [['title' => 'Club'], 0],
            'array with non-numeric identity' => [['__identity' => 'foo'], 0],
            'array with nested identity' => [['__identity' => ['__identity' => 12]], 0],
            'non-numeric string' => ['foo', 0],
            'empty string' => ['', 0],
            'null' => [null, 0],
            'boolean' => [true, 0],
            'object' => [new \stdClass(), 0],
        ];
    }

    #[Test]
    #[DataProvider('identityDataProvider')]
    public function getIdentityReturnsUidOfArgument(mixed $argument, int $expectedUid): void
    {
        self::assertSame(
            $expectedUid,
            $this->subject->getIdentity($argument),
        );
    }

    #[Test]
    public function generateModerationTokenReturnsDifferentTokensForDifferentClubs(): void
    {
        $token = $this->subject->generateModerationToken(1);

        self::assertNotSame('', $token);
        self::assertSame($token, $this->subject->generateModerationToken(1));
        self::assertNotSame($token, $this->subject->generateModerationToken(2));
    }

    #[Test]
    public function generateModerationTokenDependsOnEncryptionKey(): void
    {
        $token = $this->subject->generateModerationToken(1);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'another-encryption-key';

        self::assertNotSame($token, $this->subject->generateModerationToken(1));
    }

    #[Test]
    public function getValidModerationTokenWithValidTokenReturnsToken(): void
    {
        $token = $this->subject->generateModerationToken(1);

        self::assertSame(
            $token,
            $this->subject->getValidModerationToken($this->createRequest(['moderationToken' => $token]), 1),
        );
    }

    #[Test]
    public function getValidModerationTokenWithTamperedTokenReturnsEmptyString(): void
    {
        $token = $this->subject->generateModerationToken(1);
        $tamperedToken = substr($token, 0, -1) . ($token[-1] === 'a' ? 'b' : 'a');

        self::assertSame(
            '',
            $this->subject->getValidModerationToken($this->createRequest(['moderationToken' => $tamperedToken]), 1),
        );
    }

    #[Test]
    public function getValidModerationTokenWithTokenOfAnotherClubReturnsEmptyString(): void
    {
        $token = $this->subject->generateModerationToken(2);

        self::assertSame(
            '',
            $this->subject->getValidModerationToken($this->createRequest(['moderationToken' => $token]), 1),
        );
    }

    #[Test]
    public function getValidModerationTokenWithoutTokenReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getValidModerationToken($this->createRequest(['club' => 1]), 1),
        );
    }

    #[Test]
    public function getValidModerationTokenWithEmptyTokenReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getValidModerationToken($this->createRequest(['moderationToken' => '']), 1),
        );
    }

    #[Test]
    public function getValidModerationTokenWithArrayTokenReturnsEmptyString(): void
    {
        $token = $this->subject->generateModerationToken(1);

        self::assertSame(
            '',
            $this->subject->getValidModerationToken($this->createRequest(['moderationToken' => [$token]]), 1),
        );
    }

    #[Test]
    public function getValidModerationTokenWithoutClubUidReturnsEmptyString(): void
    {
        $token = $this->subject->generateModerationToken(0);

        self::assertSame(
            '',
            $this->subject->getValidModerationToken($this->createRequest(['moderationToken' => $token]), 0),
        );
    }
}
