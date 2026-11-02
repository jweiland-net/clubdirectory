<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller\Traits;

use Psr\Http\Message\ResponseInterface;

/**
 * Redirect to the list action of ClubController. It belongs to another
 * plugin ("Clubdirectory") on another page (settings.pidOfListPage), so
 * ActionController::redirect() can not be used.
 *
 * Do not add flash messages to the queue of the "Clubdirectory" plugin:
 * its list action is cached, so the message would be rendered into the
 * page cache and shown to other visitors. Messages of the management
 * plugin are shown by its next (uncached) action.
 */
trait ClubListRedirectTrait
{
    protected function redirectToClubList(): ResponseInterface
    {
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
