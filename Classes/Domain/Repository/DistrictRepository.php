<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to get and search for districts
 */
class DistrictRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'district' => QueryInterface::ORDER_ASCENDING,
    ];
}
