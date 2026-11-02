<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Controller\Traits;

use JWeiland\Clubdirectory\Controller\ClubController;
use JWeiland\Clubdirectory\Controller\Traits\ClubListRedirectTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for the redirect to the list action of the "Clubdirectory" plugin
 */
class ClubListRedirectTraitTest extends UnitTestCase
{
    private const LIST_URI = 'https://www.example.com/clubs?tx_clubdirectory_clubdirectory%5Baction%5D=list';

    protected UriBuilder&MockObject $uriBuilder;

    protected FlashMessageService&MockObject $flashMessageService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uriBuilder = $this->createMock(UriBuilder::class);
        $this->uriBuilder
            ->expects(self::once())
            ->method('reset')
            ->willReturnSelf();
        $this->uriBuilder
            ->expects(self::once())
            ->method('uriFor')
            ->with('list', [], 'Club', 'Clubdirectory', 'Clubdirectory')
            ->willReturn(self::LIST_URI);

        $this->flashMessageService = $this->createMock(FlashMessageService::class);
    }

    protected function tearDown(): void
    {
        unset($this->uriBuilder, $this->flashMessageService);

        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function redirectToClubList(array $settings, string $flashMessage = ''): ResponseInterface
    {
        $controller = new class ($this->uriBuilder, $settings) extends ActionController {
            use ClubListRedirectTrait;

            /**
             * @param array<string, mixed> $settings
             */
            public function __construct(UriBuilder $uriBuilder, array $settings)
            {
                $this->uriBuilder = $uriBuilder;
                $this->settings = $settings;
            }

            public function callRedirectToClubList(string $flashMessage): ResponseInterface
            {
                return $this->redirectToClubList($flashMessage);
            }
        };
        $controller->injectInternalFlashMessageService($this->flashMessageService);

        return $controller->callRedirectToClubList($flashMessage);
    }

    #[Test]
    public function redirectToClubListRedirectsToListActionOnListPage(): void
    {
        $this->uriBuilder
            ->expects(self::once())
            ->method('setTargetPageUid')
            ->with(72)
            ->willReturnSelf();

        $response = $this->redirectToClubList(['pidOfListPage' => '72']);

        self::assertSame(303, $response->getStatusCode());
        self::assertSame(self::LIST_URI, $response->getHeaderLine('Location'));
    }

    #[Test]
    public function redirectToClubListWithoutListPageRedirectsToCurrentPage(): void
    {
        $this->uriBuilder
            ->expects(self::never())
            ->method('setTargetPageUid');

        $response = $this->redirectToClubList(['pidOfListPage' => '']);

        self::assertSame(303, $response->getStatusCode());
        self::assertSame(self::LIST_URI, $response->getHeaderLine('Location'));
    }

    #[Test]
    public function redirectToClubListAddsFlashMessageToQueueOfListPlugin(): void
    {
        $flashMessageQueue = $this->createMock(FlashMessageQueue::class);
        $flashMessageQueue
            ->expects(self::once())
            ->method('enqueue')
            ->with(self::callback(
                static fn(FlashMessage $flashMessage): bool => $flashMessage->getMessage() === 'Club activated'
                    && $flashMessage->isSessionMessage(),
            ));
        $this->flashMessageService
            ->expects(self::once())
            ->method('getMessageQueueByIdentifier')
            ->with(ClubController::FLASH_MESSAGE_QUEUE_IDENTIFIER)
            ->willReturn($flashMessageQueue);
        $this->uriBuilder
            ->method('setTargetPageUid')
            ->willReturnSelf();

        $response = $this->redirectToClubList(['pidOfListPage' => '72'], 'Club activated');

        self::assertSame(self::LIST_URI, $response->getHeaderLine('Location'));
    }

    #[Test]
    public function redirectToClubListWithoutFlashMessageKeepsQueueUntouched(): void
    {
        $this->flashMessageService
            ->expects(self::never())
            ->method('getMessageQueueByIdentifier');
        $this->uriBuilder
            ->method('setTargetPageUid')
            ->willReturnSelf();

        $this->redirectToClubList(['pidOfListPage' => '72']);
    }
}
