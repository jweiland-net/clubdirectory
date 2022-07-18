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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
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
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    public function initializeObject(): void
    {
        $this->objectType = Category::class;
    }

    /**
     * Get categories of configured root category
     */
    public function getCategories(): QueryResultInterface
    {
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        $query = $this->createQuery();
        return $query->matching($query->equals('parent', $extConf->getRootCategory()))->execute();
    }
}
