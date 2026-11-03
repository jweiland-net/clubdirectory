<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\EventListener;

use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\EventListener\RegisterHiddenClubEventListener;
use JWeiland\Clubdirectory\Helper\HiddenObjectHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for RegisterHiddenClubEventListener
 */
class RegisterHiddenClubEventListenerTest extends UnitTestCase
{
    protected HiddenObjectHelper&MockObject $hiddenObjectHelperMock;

    protected ClubRepository $clubRepositoryStub;

    protected RegisterHiddenClubEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenObjectHelperMock = $this->createMock(HiddenObjectHelper::class);
        $this->clubRepositoryStub = self::createStub(ClubRepository::class);

        $this->subject = new RegisterHiddenClubEventListener(
            $this->hiddenObjectHelperMock,
            $this->clubRepositoryStub,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->hiddenObjectHelperMock,
            $this->clubRepositoryStub,
        );

        parent::tearDown();
    }

    private function createEvent(string $controllerName, string $actionName): PreProcessControllerActionEvent
    {
        $extbaseRequestParameters = (new ExtbaseRequestParameters())
            ->setControllerName($controllerName)
            ->setControllerActionName($actionName)
            ->setArguments(['club' => '2']);

        $serverRequest = (new ServerRequest('https://www.example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('extbase', $extbaseRequestParameters);

        return new PreProcessControllerActionEvent(new Request($serverRequest), new Arguments(), []);
    }

    public static function coveredActionDataProvider(): array
    {
        return [
            'Management edit' => ['Management', 'edit'],
            'Management update' => ['Management', 'update'],
            'Management confirmActivation' => ['Management', 'confirmActivation'],
            'Management activate' => ['Management', 'activate'],
            'Map new' => ['Map', 'new'],
            'Map create' => ['Map', 'create'],
        ];
    }

    #[Test]
    #[DataProvider('coveredActionDataProvider')]
    public function invokeWithCoveredActionRegistersHiddenClub(string $controllerName, string $actionName): void
    {
        $event = $this->createEvent($controllerName, $actionName);

        $this->hiddenObjectHelperMock
            ->expects(self::once())
            ->method('registerHiddenObjectInExtbaseSession')
            ->with(
                self::identicalTo($this->clubRepositoryStub),
                self::identicalTo($event->getRequest()),
                self::identicalTo('club'),
            );

        ($this->subject)($event);
    }

    public static function uncoveredActionDataProvider(): array
    {
        return [
            // Public actions must never register hidden clubs
            'Club show' => ['Club', 'show'],
            'Club list' => ['Club', 'list'],
            'Search listSearchResults' => ['Search', 'listSearchResults'],
            'Management listMyClubs' => ['Management', 'listMyClubs'],
            'Management new' => ['Management', 'new'],
            // A new club is never hidden. It gets hidden in createAction.
            'Management create' => ['Management', 'create'],
            'Map edit (removed)' => ['Map', 'edit'],
            'Map update (removed)' => ['Map', 'update'],
            'unknown controller' => ['Foo', 'edit'],
        ];
    }

    #[Test]
    #[DataProvider('uncoveredActionDataProvider')]
    public function invokeWithUncoveredActionDoesNotRegisterHiddenClub(string $controllerName, string $actionName): void
    {
        $this->hiddenObjectHelperMock
            ->expects(self::never())
            ->method('registerHiddenObjectInExtbaseSession');

        ($this->subject)($this->createEvent($controllerName, $actionName));
    }
}
