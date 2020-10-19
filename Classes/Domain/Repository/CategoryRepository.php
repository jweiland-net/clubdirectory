<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Repository;

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
     * @param int $categoryUid
     * @return QueryResultInterface
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
