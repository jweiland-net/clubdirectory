<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\EventListener;

use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\EventListener\RestrictAccessEventListener;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for RestrictAccessEventListener
 */
class RestrictAccessEventListenerTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    protected RestrictAccessEventListener $subject;

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

        $this->subject = $this->get(RestrictAccessEventListener::class);
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    private function loginUser(int $frontendUserUid = 0, bool $isBackendAdmin = false): void
    {
        $this->setUserAspects($this->get(Context::class), $frontendUserUid, $isBackendAdmin);
    }

    private function getModerationToken(int $clubUid): string
    {
        return $this->get(ClubAccessService::class)->generateModerationToken($clubUid);
    }

    private function assertAccessDenied(PreProcessControllerActionEvent $event): void
    {
        try {
            ($this->subject)($event);
        } catch (PropagateResponseException $propagateResponseException) {
            self::assertSame(1762090800, $propagateResponseException->getCode());
            self::assertSame(403, $propagateResponseException->getResponse()->getStatusCode());
            return;
        }

        self::fail('Access was granted, but PropagateResponseException with a 403 response was expected.');
    }

    private function assertAccessGranted(PreProcessControllerActionEvent $event): void
    {
        $request = $event->getRequest();

        ($this->subject)($event);

        // The listener must neither stop nor modify the request
        self::assertSame($request, $event->getRequest());
    }

    public static function restrictedActionDataProvider(): array
    {
        return [
            'Management perform' => ['Management', 'perform'],
        ];
    }

    public static function clubArgumentDataProvider(): array
    {
        $arguments = [];
        foreach (self::restrictedActionDataProvider() as $label => [$controllerName, $actionName]) {
            $arguments[$label . ' with scalar club'] = [$controllerName, $actionName, (string)self::HIDDEN_CLUB];
            $arguments[$label . ' with club identity'] = [
                $controllerName,
                $actionName,
                ['__identity' => (string)self::HIDDEN_CLUB, 'title' => 'Hidden club'],
            ];
        }

        return $arguments;
    }

    #[Test]
    #[DataProvider('clubArgumentDataProvider')]
    public function restrictedActionWithOwnerGrantsAccess(
        string $controllerName,
        string $actionName,
        string|array $club,
    ): void {
        $this->loginUser(self::OWNER);

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, ['club' => $club]));
    }

    #[Test]
    #[DataProvider('clubArgumentDataProvider')]
    public function restrictedActionWithForeignUserDeniesAccess(
        string $controllerName,
        string $actionName,
        string|array $club,
    ): void {
        $this->loginUser(self::FOREIGN_USER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, ['club' => $club]));
    }

    #[Test]
    #[DataProvider('clubArgumentDataProvider')]
    public function restrictedActionWithoutLoginDeniesAccess(
        string $controllerName,
        string $actionName,
        string|array $club,
    ): void {
        $this->loginUser();

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, ['club' => $club]));
    }

    #[Test]
    #[DataProvider('restrictedActionDataProvider')]
    public function restrictedActionWithOwnerOfDeletedClubDeniesAccess(string $controllerName, string $actionName): void
    {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, ['club' => (string)self::DELETED_CLUB]));
    }

    #[Test]
    #[DataProvider('restrictedActionDataProvider')]
    public function restrictedActionWithBackendAdminGrantsAccess(string $controllerName, string $actionName): void
    {
        $this->loginUser(0, true);

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, ['club' => (string)self::OTHER_GROUP_CLUB]));
    }

    public static function managementActionDataProvider(): array
    {
        return [
            'Management perform' => ['Management', 'perform'],
        ];
    }

    #[Test]
    #[DataProvider('managementActionDataProvider')]
    public function managementActionWithBackendAdminGrantsAccessToActiveClub(string $controllerName, string $actionName): void
    {
        $this->loginUser(0, true);

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, ['club' => (string)self::FOREIGN_CLUB]));
    }

    #[Test]
    #[DataProvider('restrictedActionDataProvider')]
    public function restrictedActionWithValidModerationTokenGrantsAccessWithoutLogin(
        string $controllerName,
        string $actionName,
    ): void {
        $this->loginUser();

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    public static function actionWithoutClubDataProvider(): array
    {
        return [
            'Management confirmActivation' => ['Management', 'confirmActivation'],
            'Management activate' => ['Management', 'activate'],
        ];
    }

    #[Test]
    #[DataProvider('actionWithoutClubDataProvider')]
    public function restrictedActionWithoutClubDeniesAccess(string $controllerName, string $actionName): void
    {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName));
    }

    #[Test]
    #[DataProvider('actionWithoutClubDataProvider')]
    public function restrictedActionWithNewClubDeniesAccess(string $controllerName, string $actionName): void
    {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, ['club' => ['title' => 'New club']]));
    }

    public static function unrestrictedActionDataProvider(): array
    {
        return [
            'Management listMyClubs' => ['Management', 'listMyClubs'],
            'Club list' => ['Club', 'list'],
            'Club show' => ['Club', 'show'],
            'Search show' => ['Search', 'show'],
            'Management new (removed)' => ['Management', 'new'],
            'Management edit (removed)' => ['Management', 'edit'],
            'Map new (removed)' => ['Map', 'new'],
            'Map create (removed)' => ['Map', 'create'],
        ];
    }

    #[Test]
    #[DataProvider('unrestrictedActionDataProvider')]
    public function unrestrictedActionWithoutLoginGrantsAccess(string $controllerName, string $actionName): void
    {
        $this->loginUser();

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, ['club' => (string)self::FOREIGN_CLUB]));
    }

    public static function newClubActionDataProvider(): array
    {
        return [
            'Management perform without club' => ['Management', 'perform', []],
            'Management perform with empty club' => ['Management', 'perform', ['club' => '0']],
        ];
    }

    #[Test]
    #[DataProvider('newClubActionDataProvider')]
    public function newClubActionWithMemberOfUserGroupGrantsAccess(
        string $controllerName,
        string $actionName,
        array $arguments,
    ): void {
        $this->loginUser(self::OWNER);

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, $arguments));
    }

    #[Test]
    #[DataProvider('newClubActionDataProvider')]
    public function newClubActionWithoutLoginDeniesAccess(string $controllerName, string $actionName, array $arguments): void
    {
        $this->loginUser();

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, $arguments));
    }

    #[Test]
    #[DataProvider('newClubActionDataProvider')]
    public function newClubActionWithUserOfOtherGroupDeniesAccess(
        string $controllerName,
        string $actionName,
        array $arguments,
    ): void {
        $this->loginUser(self::OTHER_GROUP_USER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, $arguments));
    }

    #[Test]
    #[DataProvider('newClubActionDataProvider')]
    public function newClubActionWithBackendAdminAndWithoutFrontendLoginDeniesAccess(
        string $controllerName,
        string $actionName,
        array $arguments,
    ): void {
        $this->loginUser(0, true);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, $arguments));
    }

    #[Test]
    #[DataProvider('newClubActionDataProvider')]
    public function newClubActionWithoutConfiguredUserGroupDeniesAccess(
        string $controllerName,
        string $actionName,
        array $arguments,
    ): void {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, $arguments, 0));
    }

    #[Test]
    #[DataProvider('restrictedActionDataProvider')]
    public function restrictedActionWithOwnerOfOtherGroupDeniesAccess(string $controllerName, string $actionName): void
    {
        $this->loginUser(self::OTHER_GROUP_USER);

        $this->assertAccessDenied($this->createEvent($controllerName, $actionName, ['club' => (string)self::OTHER_GROUP_CLUB]));
    }

    #[Test]
    #[DataProvider('restrictedActionDataProvider')]
    public function restrictedActionWithValidModerationTokenGrantsAccessToUserOfOtherGroup(
        string $controllerName,
        string $actionName,
    ): void {
        $this->loginUser(self::OTHER_GROUP_USER);

        $this->assertAccessGranted($this->createEvent($controllerName, $actionName, [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function confirmActivationWithOwnerAndWithoutModerationTokenDeniesAccess(): void
    {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent('Management', 'confirmActivation', ['club' => (string)self::HIDDEN_CLUB]));
    }

    #[Test]
    public function confirmActivationWithBackendAdminAndWithoutModerationTokenDeniesAccess(): void
    {
        $this->loginUser(0, true);

        $this->assertAccessDenied($this->createEvent('Management', 'confirmActivation', ['club' => (string)self::HIDDEN_CLUB]));
    }

    #[Test]
    public function confirmActivationWithValidModerationTokenAndWithoutLoginGrantsAccess(): void
    {
        $this->loginUser();

        $this->assertAccessGranted($this->createEvent('Management', 'confirmActivation', [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function activateWithOwnerAndWithoutModerationTokenDeniesAccess(): void
    {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent('Management', 'activate', ['clubUid' => (string)self::HIDDEN_CLUB]));
    }

    #[Test]
    public function activateWithValidModerationTokenAndWithoutLoginGrantsAccess(): void
    {
        $this->loginUser();

        $this->assertAccessGranted($this->createEvent('Management', 'activate', [
            'clubUid' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function activateWithTamperedModerationTokenDeniesAccess(): void
    {
        $this->loginUser();
        $token = $this->getModerationToken(self::HIDDEN_CLUB);
        $tamperedToken = substr($token, 0, -1) . ($token[-1] === 'a' ? 'b' : 'a');

        $this->assertAccessDenied($this->createEvent('Management', 'activate', [
            'clubUid' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $tamperedToken,
        ]));
    }

    #[Test]
    public function activateWithModerationTokenOfOutdatedMailDeniesAccess(): void
    {
        $this->loginUser();
        $token = $this->getModerationToken(self::HIDDEN_CLUB);

        // The club was updated again after the mail with this token was sent
        $this->getConnectionPool()
            ->getConnectionForTable('tx_clubdirectory_domain_model_club')
            ->update('tx_clubdirectory_domain_model_club', ['tstamp' => 1762000001], ['uid' => self::HIDDEN_CLUB]);

        $this->assertAccessDenied($this->createEvent('Management', 'activate', [
            'clubUid' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $token,
        ]));
    }

    #[Test]
    public function activateWithModerationTokenOfAnotherClubDeniesAccess(): void
    {
        $this->loginUser(self::OWNER);

        $this->assertAccessDenied($this->createEvent('Management', 'activate', [
            'clubUid' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::VISIBLE_CLUB),
        ]));
    }

    #[Test]
    public function activateWithBackendAdminAndWithoutModerationTokenDeniesAccess(): void
    {
        $this->loginUser(0, true);

        $this->assertAccessDenied($this->createEvent('Management', 'activate', ['clubUid' => (string)self::HIDDEN_CLUB]));
    }

    #[Test]
    public function activateWithBackendAdminAndValidModerationTokenGrantsAccess(): void
    {
        $this->loginUser(0, true);

        $this->assertAccessGranted($this->createEvent('Management', 'activate', [
            'clubUid' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function activateWithClubArgumentInsteadOfClubUidDeniesAccess(): void
    {
        $this->loginUser();

        $this->assertAccessDenied($this->createEvent('Management', 'activate', [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function activateWithArrayInClubUidDeniesAccess(): void
    {
        $this->loginUser();

        $this->assertAccessDenied($this->createEvent('Management', 'activate', [
            'clubUid' => ['__identity' => (string)self::HIDDEN_CLUB],
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function performWithClubOfOtherUserAndValidModerationTokenGrantsAccess(): void
    {
        $this->loginUser(self::FOREIGN_USER);

        $this->assertAccessGranted($this->createEvent('Management', 'perform', [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::HIDDEN_CLUB),
        ]));
    }

    #[Test]
    public function performWithModerationTokenOfAnotherClubDeniesAccess(): void
    {
        $this->loginUser(self::FOREIGN_USER);

        $this->assertAccessDenied($this->createEvent('Management', 'perform', [
            'club' => (string)self::HIDDEN_CLUB,
            'moderationToken' => $this->getModerationToken(self::FOREIGN_CLUB),
        ]));
    }
}
