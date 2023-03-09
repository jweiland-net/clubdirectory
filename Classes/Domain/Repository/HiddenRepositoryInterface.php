<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Repository;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Interface to identify Repositories which can find hidden objects.
 * Currently used in HiddenObjectsHelper.
 */
interface HiddenRepositoryInterface
{
    /**
     * Find object by a given property value whether it is hidden or not.
     *
     * @param mixed $value The Value to compare against $property
     * @return AbstractEntity|null Don't add return type. Let the individual repo decide
     */
    public function findHiddenObject($value, string $property = 'uid');
}
