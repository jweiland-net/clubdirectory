<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Service;

use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for the database related checks of ClubAccessService
 */
class ClubAccessServiceTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
    }

    private function createSubject(int $frontendUserUid = 0, bool $isBackendAdmin = false): ClubAccessService
    {
        return new ClubAccessService(
            $this->get(ConnectionPool::class),
            $this->createContext($frontendUserUid, $isBackendAdmin),
            new HashService(),
        );
    }

    #[Test]
    public function canManageClubWithOwnerReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(self::OWNER)->canManageClub(self::VISIBLE_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithOwnerOfHiddenClubReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(self::OWNER)->canManageClub(self::HIDDEN_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithOwnerOfDeletedClubReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::OWNER)->canManageClub(self::DELETED_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithForeignUserReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::FOREIGN_USER)->canManageClub(self::VISIBLE_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithOwnerOfAnotherClubReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::OWNER)->canManageClub(self::FOREIGN_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithoutLoginReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject()->canManageClub(self::VISIBLE_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithUnknownClubReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::OWNER)->canManageClub(999),
        );
    }

    #[Test]
    public function canManageClubWithoutClubUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::OWNER, true)->canManageClub(0),
        );
    }

    #[Test]
    public function canManageClubWithBackendAdminReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(0, true)->canManageClub(self::FOREIGN_CLUB),
        );
    }

    #[Test]
    public function canManageClubWithNonAdminBackendUserReturnsFalse(): void
    {
        $context = $this->createContext(0, false, true);
        $subject = new ClubAccessService($this->get(ConnectionPool::class), $context, new HashService());

        self::assertFalse(
            $subject->canManageClub(self::FOREIGN_CLUB),
        );
    }

    public static function clubArgumentDataProvider(): array
    {
        return [
            'no addresses' => [
                ['title' => 'Swimming club'],
                self::VISIBLE_CLUB,
                true,
            ],
            'new addresses only' => [
                ['addresses' => [['title' => 'postAddress'], ['title' => 'clubAddress']]],
                self::VISIBLE_CLUB,
                true,
            ],
            'own address' => [
                ['addresses' => [['__identity' => '1', 'title' => 'organizationAddress']]],
                self::VISIBLE_CLUB,
                true,
            ],
            'own address with own POI' => [
                ['addresses' => [['__identity' => '1', 'txMaps2Uid' => ['__identity' => '11']]]],
                self::VISIBLE_CLUB,
                true,
            ],
            'own address with scalar POI identity' => [
                ['addresses' => [['__identity' => '1', 'txMaps2Uid' => '11']]],
                self::VISIBLE_CLUB,
                true,
            ],
            'own address as scalar identity' => [
                ['addresses' => ['1']],
                self::VISIBLE_CLUB,
                true,
            ],
            'own address with foreign POI' => [
                ['addresses' => [['__identity' => '1', 'txMaps2Uid' => ['__identity' => '12']]]],
                self::VISIBLE_CLUB,
                false,
            ],
            'foreign address' => [
                ['addresses' => [['__identity' => '3', 'title' => 'organizationAddress']]],
                self::VISIBLE_CLUB,
                false,
            ],
            'foreign address as scalar identity' => [
                ['addresses' => ['3']],
                self::VISIBLE_CLUB,
                false,
            ],
            'own and foreign address' => [
                ['addresses' => [['__identity' => '1'], ['__identity' => '3']]],
                self::VISIBLE_CLUB,
                false,
            ],
            'unknown address' => [
                ['addresses' => [['__identity' => '999']]],
                self::VISIBLE_CLUB,
                false,
            ],
            'new address with foreign POI' => [
                ['addresses' => [['title' => 'postAddress', 'txMaps2Uid' => ['__identity' => '12']]]],
                self::VISIBLE_CLUB,
                false,
            ],
            'existing address for new club' => [
                ['addresses' => [['__identity' => '1']]],
                0,
                false,
            ],
            'new address for new club' => [
                ['addresses' => [['title' => 'postAddress']]],
                0,
                true,
            ],
            'scalar address identity instead of list' => [
                ['addresses' => '3'],
                self::VISIBLE_CLUB,
                false,
            ],
            'empty string instead of list' => [
                ['addresses' => ''],
                self::VISIBLE_CLUB,
                true,
            ],
        ];
    }

    #[Test]
    #[DataProvider('clubArgumentDataProvider')]
    public function hasOnlyRecordsOfClubReturnsExpectedResult(array $clubArgument, int $clubUid, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->createSubject(self::OWNER)->hasOnlyRecordsOfClub($clubArgument, $clubUid),
        );
    }

    #[Test]
    public function hasOnlyRecordsOfClubWithDeletedAddressReturnsFalse(): void
    {
        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_address')
            ->update('tx_clubdirectory_domain_model_address', ['deleted' => 1], ['uid' => 1]);

        self::assertFalse(
            $this->createSubject(self::OWNER)->hasOnlyRecordsOfClub(
                ['addresses' => [['__identity' => '1']]],
                self::VISIBLE_CLUB,
            ),
        );
    }

    private function updateTimestampOfClub(int $clubUid, int $timestamp): void
    {
        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_club')
            ->update('tx_clubdirectory_domain_model_club', ['tstamp' => $timestamp], ['uid' => $clubUid]);
    }

    private function isValidModerationToken(string $moderationToken, int $clubUid): bool
    {
        $request = $this->createExtbaseRequest('Management', 'activate', [
            'club' => $clubUid,
            'moderationToken' => $moderationToken,
        ]);

        return $this->createSubject()->getValidModerationToken($request, $clubUid) === $moderationToken;
    }

    #[Test]
    public function generateModerationTokenReturnsDifferentTokensForDifferentClubs(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB);

        self::assertNotSame('', $token);
        self::assertSame($token, $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB));
        self::assertNotSame($token, $this->createSubject()->generateModerationToken(self::FOREIGN_CLUB));
    }

    #[Test]
    public function generateModerationTokenForHiddenClubReturnsToken(): void
    {
        self::assertNotSame('', $this->createSubject()->generateModerationToken(self::HIDDEN_CLUB));
    }

    #[Test]
    public function generateModerationTokenForDeletedClubReturnsEmptyString(): void
    {
        self::assertSame('', $this->createSubject()->generateModerationToken(self::DELETED_CLUB));
    }

    #[Test]
    public function generateModerationTokenForUnknownClubReturnsEmptyString(): void
    {
        self::assertSame('', $this->createSubject()->generateModerationToken(99999));
    }

    #[Test]
    public function generateModerationTokenDependsOnTimestampOfClub(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB);

        $this->updateTimestampOfClub(self::VISIBLE_CLUB, 1762000001);

        self::assertNotSame($token, $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB));
    }

    #[Test]
    public function generateModerationTokenDependsOnEncryptionKey(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'another-encryption-key';

        self::assertNotSame($token, $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB));
    }

    #[Test]
    public function getValidModerationTokenWithValidTokenReturnsToken(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::HIDDEN_CLUB);

        self::assertTrue($this->isValidModerationToken($token, self::HIDDEN_CLUB));
    }

    #[Test]
    public function getValidModerationTokenWithTokenOfOutdatedTimestampReturnsEmptyString(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::HIDDEN_CLUB);

        // The club was changed again, so only the token of the new mail is valid
        $this->updateTimestampOfClub(self::HIDDEN_CLUB, 1762000001);

        self::assertFalse($this->isValidModerationToken($token, self::HIDDEN_CLUB));
        self::assertTrue(
            $this->isValidModerationToken(
                $this->createSubject()->generateModerationToken(self::HIDDEN_CLUB),
                self::HIDDEN_CLUB,
            ),
        );
    }

    #[Test]
    public function getValidModerationTokenWithTamperedTokenReturnsEmptyString(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::HIDDEN_CLUB);
        $tamperedToken = substr($token, 0, -1) . ($token[-1] === 'a' ? 'b' : 'a');

        self::assertFalse($this->isValidModerationToken($tamperedToken, self::HIDDEN_CLUB));
    }

    #[Test]
    public function getValidModerationTokenWithTokenOfAnotherClubReturnsEmptyString(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::FOREIGN_CLUB);

        self::assertFalse($this->isValidModerationToken($token, self::VISIBLE_CLUB));
    }

    #[Test]
    public function getValidModerationTokenForDeletedClubReturnsEmptyString(): void
    {
        $token = $this->createSubject()->generateModerationToken(self::VISIBLE_CLUB);

        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_club')
            ->update('tx_clubdirectory_domain_model_club', ['deleted' => 1], ['uid' => self::VISIBLE_CLUB]);

        self::assertFalse($this->isValidModerationToken($token, self::VISIBLE_CLUB));
    }
}
