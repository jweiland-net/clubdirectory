<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Repository;

use JWeiland\Clubdirectory\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Repository to manage records of fe_users table and retrieve current logged-in user
 *
 * @method FrontendUser findByUid(int $frontendUserUid)
 */
class FrontendUserRepository extends Repository
{
    public function getCurrentFrontendUserUid(): int
    {
        return $this->getCurrentFrontendUserRecord()['uid'] ?? 0;
    }

    public function getCurrentFrontendUserRecord(): array
    {
        if (!isset($GLOBALS['TSFE'])) {
            return [];
        }

        if (!$GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            return [];
        }

        if (!$GLOBALS['TSFE']->fe_user instanceof FrontendUserAuthentication) {
            return [];
        }

        return is_array($GLOBALS['TSFE']->fe_user->user) ? $GLOBALS['TSFE']->fe_user->user : [];
    }
}
