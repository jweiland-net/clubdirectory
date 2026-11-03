<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\EventListener;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Dispatch the event with all registered listeners to verify, that
 * RegisterHiddenClubEventListener is executed after RestrictAccessEventListener.
 */
class RegisterHiddenClubEventListenerTest extends FunctionalTestCase
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

    private function isClubRegisteredInSession(int $clubUid): bool
    {
        return $this->get(Session::class)->hasIdentifier((string)$clubUid, Club::class);
    }

    public static function coveredActionDataProvider(): array
    {
        return [
            'Management edit' => ['Management', 'edit'],
            'Management confirmActivation' => ['Management', 'confirmActivation'],
            'Management activate' => ['Management', 'activate'],
            'Map new' => ['Map', 'new'],
        ];
    }

    #[Test]
    #[DataProvider('coveredActionDataProvider')]
    public function dispatchWithGrantedAccessRegistersHiddenClub(string $controllerName, string $actionName): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);
        $arguments = ['club' => (string)self::HIDDEN_CLUB];
        if (in_array($actionName, ['confirmActivation', 'activate'], true)) {
            $arguments['moderationToken'] = $this->get(ClubAccessService::class)
                ->generateModerationToken(self::HIDDEN_CLUB);
        }

        $this->get(EventDispatcherInterface::class)->dispatch(
            $this->createEvent($controllerName, $actionName, $arguments),
        );

        self::assertTrue($this->isClubRegisteredInSession(self::HIDDEN_CLUB));
    }

    #[Test]
    #[DataProvider('coveredActionDataProvider')]
    public function dispatchWithDeniedAccessDoesNotRegisterHiddenClub(string $controllerName, string $actionName): void
    {
        $this->setUserAspects($this->get(Context::class), self::FOREIGN_USER);

        try {
            $this->get(EventDispatcherInterface::class)->dispatch(
                $this->createEvent($controllerName, $actionName, ['club' => (string)self::HIDDEN_CLUB]),
            );
            self::fail('Access was granted, but PropagateResponseException was expected.');
        } catch (PropagateResponseException) {
        }

        self::assertFalse($this->isClubRegisteredInSession(self::HIDDEN_CLUB));
    }

    #[Test]
    public function dispatchWithPublicShowActionDoesNotRegisterHiddenClub(): void
    {
        $this->setUserAspects($this->get(Context::class), self::OWNER);

        $this->get(EventDispatcherInterface::class)->dispatch(
            $this->createEvent('Club', 'show', ['club' => (string)self::HIDDEN_CLUB]),
        );

        self::assertFalse($this->isClubRegisteredInSession(self::HIDDEN_CLUB));
    }
}
