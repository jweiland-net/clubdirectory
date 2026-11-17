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
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for the database related checks of ClubAccessService
 */
class ClubAccessServiceTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

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
    public function isMemberOfUserGroupWithMemberReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(self::OWNER)->isMemberOfUserGroup(self::USER_GROUP),
        );
    }

    #[Test]
    public function isMemberOfUserGroupWithUserOfOtherGroupReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::OTHER_GROUP_USER)->isMemberOfUserGroup(self::USER_GROUP),
        );
    }

    #[Test]
    public function isMemberOfUserGroupWithAnyLoginGroupAndLoggedInUserReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(self::OTHER_GROUP_USER)->isMemberOfUserGroup(-2),
        );
    }

    public static function userGroupWithoutLoginDataProvider(): array
    {
        return [
            'configured usergroup' => [self::USER_GROUP],
            'no usergroup' => [0],
            'pseudo group "hide at login"' => [-1],
            'pseudo group "any login"' => [-2],
        ];
    }

    #[Test]
    #[DataProvider('userGroupWithoutLoginDataProvider')]
    public function isMemberOfUserGroupWithoutLoginReturnsFalse(int $userGroup): void
    {
        self::assertFalse(
            $this->createSubject()->isMemberOfUserGroup($userGroup),
        );
    }

    #[Test]
    public function isMemberOfUserGroupWithoutConfiguredUserGroupReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::OWNER)->isMemberOfUserGroup(0),
        );
    }

    #[Test]
    public function isMemberOfUserGroupWithBackendAdminAndWithoutFrontendLoginReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(0, true)->isMemberOfUserGroup(self::USER_GROUP),
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

    private function updateTimestampOfClub(int $clubUid, int $timestamp): void
    {
        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_club')
            ->update('tx_clubdirectory_domain_model_club', ['tstamp' => $timestamp], ['uid' => $clubUid]);
    }

    private function isValidModerationToken(string $moderationToken, int $clubUid): bool
    {
        $request = $this->createExtbaseRequest('Management', 'activate', [
            'clubUid' => $clubUid,
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

    public static function isClubHiddenDataProvider(): array
    {
        return [
            'hidden club' => [self::HIDDEN_CLUB, true],
            'visible club' => [self::VISIBLE_CLUB, false],
            'unknown club' => [999, false],
            'no club' => [0, false],
        ];
    }

    #[Test]
    #[DataProvider('isClubHiddenDataProvider')]
    public function isClubHiddenReturnsExpectedResult(int $clubUid, bool $expected): void
    {
        self::assertSame($expected, $this->createSubject()->isClubHidden($clubUid));
    }

    #[Test]
    public function isClubHiddenWithHiddenAndDeletedClubReturnsFalse(): void
    {
        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_club')
            ->update('tx_clubdirectory_domain_model_club', ['hidden' => 1], ['uid' => self::DELETED_CLUB]);

        self::assertFalse($this->createSubject()->isClubHidden(self::DELETED_CLUB));
    }

    private function createFormRequest(int $clubUid = 0, string $moderationToken = ''): ServerRequest
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_clubdirectory.' => [
                    'settings.' => [
                        'userGroup' => (string)self::USER_GROUP,
                    ],
                ],
            ],
        ]);

        return (new ServerRequest('https://www.example.com/', 'POST'))
            ->withQueryParams([
                'tx_clubdirectory_management' => array_filter([
                    'club' => $clubUid > 0 ? (string)$clubUid : '',
                    'moderationToken' => $moderationToken,
                ]),
            ])
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
    }

    #[Test]
    public function getClubUidFromQueryReturnsClubUid(): void
    {
        self::assertSame(
            self::HIDDEN_CLUB,
            $this->createSubject()->getClubUidFromQuery($this->createFormRequest(self::HIDDEN_CLUB)),
        );
    }

    #[Test]
    public function getClubUidFromQueryIgnoresArraysAndNegativeValues(): void
    {
        $subject = $this->createSubject();

        self::assertSame(0, $subject->getClubUidFromQuery(
            (new ServerRequest())->withQueryParams(['tx_clubdirectory_management' => ['club' => ['__identity' => '2']]]),
        ));
        self::assertSame(0, $subject->getClubUidFromQuery(
            (new ServerRequest())->withQueryParams(['tx_clubdirectory_management' => ['club' => '-2']]),
        ));
    }

    #[Test]
    public function isFormAccessAllowedForNewClubWithMemberReturnsTrue(): void
    {
        self::assertTrue($this->createSubject(self::OWNER)->isFormAccessAllowed($this->createFormRequest()));
    }

    #[Test]
    public function isFormAccessAllowedForNewClubWithUserOfOtherGroupReturnsFalse(): void
    {
        self::assertFalse($this->createSubject(self::OTHER_GROUP_USER)->isFormAccessAllowed($this->createFormRequest()));
    }

    #[Test]
    public function isFormAccessAllowedForNewClubWithoutLoginReturnsFalse(): void
    {
        self::assertFalse($this->createSubject()->isFormAccessAllowed($this->createFormRequest()));
    }

    #[Test]
    public function isFormAccessAllowedWithOwnerReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(self::OWNER)->isFormAccessAllowed($this->createFormRequest(self::HIDDEN_CLUB)),
        );
    }

    #[Test]
    public function isFormAccessAllowedWithForeignUserReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(self::FOREIGN_USER)->isFormAccessAllowed($this->createFormRequest(self::HIDDEN_CLUB)),
        );
    }

    #[Test]
    public function isFormAccessAllowedWithBackendAdminReturnsTrue(): void
    {
        self::assertTrue(
            $this->createSubject(0, true)->isFormAccessAllowed($this->createFormRequest(self::HIDDEN_CLUB)),
        );
    }

    #[Test]
    public function isFormAccessAllowedWithBackendAdminAndDeletedClubReturnsFalse(): void
    {
        self::assertFalse(
            $this->createSubject(0, true)->isFormAccessAllowed($this->createFormRequest(self::DELETED_CLUB)),
        );
    }

    #[Test]
    public function isFormAccessAllowedWithValidModerationTokenAndWithoutLoginReturnsTrue(): void
    {
        $moderationToken = $this->createSubject()->generateModerationToken(self::HIDDEN_CLUB);

        self::assertTrue(
            $this->createSubject()->isFormAccessAllowed($this->createFormRequest(self::HIDDEN_CLUB, $moderationToken)),
        );
    }

    #[Test]
    public function isFormAccessAllowedWithModerationTokenOfAnotherClubReturnsFalse(): void
    {
        $moderationToken = $this->createSubject()->generateModerationToken(self::FOREIGN_CLUB);

        self::assertFalse(
            $this->createSubject()->isFormAccessAllowed($this->createFormRequest(self::HIDDEN_CLUB, $moderationToken)),
        );
    }
}
