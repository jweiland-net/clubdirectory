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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
        if ($this->getTypo3Request() === null) {
            return [];
        }

        $frontendUserAuthentication = $this->getTypo3Request()->getAttribute('frontend.user');
        if (!$frontendUserAuthentication instanceof FrontendUserAuthentication) {
            return [];
        }

        return is_array($frontendUserAuthentication->user)
            ? $frontendUserAuthentication->user
            : [];
    }

    protected function getTypo3Request(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
