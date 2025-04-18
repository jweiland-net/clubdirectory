<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Helper;

use JWeiland\Clubdirectory\Domain\Repository\HiddenRepositoryInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/*
 * Helper class to register hidden objects in extbase session container.
 * That way it's possible to call Controller Actions with hidden objects.
 */
class HiddenObjectHelper
{
    protected Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function registerHiddenObjectInExtbaseSession(
        RepositoryInterface $repository,
        RequestInterface $request,
        string $argumentName,
    ): void {
        if (
            $repository instanceof HiddenRepositoryInterface
            && $request->hasArgument($argumentName)
        ) {
            $objectRaw = $request->getArgument($argumentName);
            if (is_array($objectRaw)) {
                // get object from form ($_POST)
                $object = $repository->findHiddenObject((int)$objectRaw['__identity']);
            } else {
                // get object from UID
                $object = $repository->findHiddenObject((int)$objectRaw);
            }

            if ($object instanceof AbstractEntity) {
                $this->session->registerObject($object, (string)$object->getUid());
            }
        }
    }
}
