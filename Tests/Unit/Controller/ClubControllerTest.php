<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Controller;

use JWeiland\Clubdirectory\Controller\ClubController;
use JWeiland\Clubdirectory\Domain\Repository\DistrictRepository;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Frontend\Cache\CacheInstruction;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for the page cache handling of ClubController
 */
class ClubControllerTest extends UnitTestCase
{
    /**
     * @param FlashMessage[] $flashMessages
     */
    private function disablePageCacheForFlashMessages(array $flashMessages): CacheInstruction
    {
        $flashMessageQueue = $this->createMock(FlashMessageQueue::class);
        $flashMessageQueue
            ->method('getAllMessages')
            ->willReturn($flashMessages);
        $flashMessageQueue
            ->expects(self::never())
            ->method('getAllMessagesAndFlush');

        $flashMessageService = $this->createMock(FlashMessageService::class);
        $flashMessageService
            ->expects(self::once())
            ->method('getMessageQueueByIdentifier')
            ->with(ClubController::FLASH_MESSAGE_QUEUE_IDENTIFIER)
            ->willReturn($flashMessageQueue);

        $extensionService = $this->createMock(ExtensionService::class);
        $extensionService
            ->method('getPluginNamespace')
            ->with('Clubdirectory', 'Clubdirectory')
            ->willReturn('tx_clubdirectory_clubdirectory');

        $cacheInstruction = new CacheInstruction();
        $request = new Request(
            (new ServerRequest('https://www.example.com/'))
                ->withAttribute(
                    'extbase',
                    (new ExtbaseRequestParameters())
                        ->setControllerExtensionName('Clubdirectory')
                        ->setPluginName('Clubdirectory'),
                )
                ->withAttribute('frontend.cache.instruction', $cacheInstruction),
        );

        $controller = new class (
            $this->createMock(DistrictRepository::class),
            $this->createMock(PersistenceManagerInterface::class),
            $this->createMock(ErrorController::class),
        ) extends ClubController {
            public function callDisablePageCacheForFlashMessages(Request $request): void
            {
                $this->request = $request;
                $this->disablePageCacheForFlashMessages();
            }
        };
        $controller->injectInternalFlashMessageService($flashMessageService);
        $controller->injectInternalExtensionService($extensionService);
        $controller->callDisablePageCacheForFlashMessages($request);

        return $cacheInstruction;
    }

    #[Test]
    public function pendingFlashMessageDisablesPageCache(): void
    {
        $cacheInstruction = $this->disablePageCacheForFlashMessages([
            new FlashMessage('Club activated'),
        ]);

        self::assertFalse($cacheInstruction->isCachingAllowed());
        self::assertStringStartsWith('EXT:clubdirectory:', $cacheInstruction->getDisabledCacheReasons()[0]);
    }

    #[Test]
    public function emptyFlashMessageQueueKeepsPageCache(): void
    {
        $cacheInstruction = $this->disablePageCacheForFlashMessages([]);

        self::assertTrue($cacheInstruction->isCachingAllowed());
    }
}
