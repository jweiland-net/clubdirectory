<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller\Traits;

use JWeiland\Clubdirectory\Controller\ClubController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Redirect to the list action of ClubController. It belongs to another
 * plugin ("Clubdirectory") on another page (settings.pidOfListPage), so
 * ActionController::redirect() can not be used.
 *
 * The flash message is added to the queue of the "Clubdirectory" plugin,
 * as the list action renders this queue only. The list action disables
 * the page cache, while this queue contains messages. So the message is
 * never stored in the page cache and never shown to other visitors.
 */
trait ClubListRedirectTrait
{
    protected function redirectToClubList(string $flashMessage = ''): ResponseInterface
    {
        if ($flashMessage !== '') {
            $this->getFlashMessageQueue(ClubController::FLASH_MESSAGE_QUEUE_IDENTIFIER)->enqueue(
                GeneralUtility::makeInstance(FlashMessage::class, $flashMessage, '', ContextualFeedbackSeverity::OK, true),
            );
        }

        $this->uriBuilder->reset();

        $pidOfListPage = (int)($this->settings['pidOfListPage'] ?? 0);
        if ($pidOfListPage > 0) {
            $this->uriBuilder->setTargetPageUid($pidOfListPage);
        }

        return $this->redirectToUri(
            $this->uriBuilder->uriFor('list', [], 'Club', 'Clubdirectory', 'Clubdirectory'),
        );
    }
}
