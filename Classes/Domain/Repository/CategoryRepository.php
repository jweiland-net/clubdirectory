<?php
declare(strict_types=1);
namespace JWeiland\Clubdirectory\Domain\Repository;

/*
 * This file is part of the clubdirectory project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Clubdirectory\Configuration\ExtConf;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to get and search for categories stored in sys_category
 */
class CategoryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * Get sub categories of a given category UID
     *
     * @param int $categoryUId
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getSubCategories(int $categoryUid = 0): QueryResultInterface
    {
        /** @var ExtConf $extConf */
        $extConf = $this->objectManager->get(ExtConf::class);
        if (!$categoryUid) {
            $categoryUid = $extConf->getRootCategory();
        }

        $query = $this->createQuery();
        return $query->matching($query->equals('parent', $categoryUid))->execute();
    }
}
