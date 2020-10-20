<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tca;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add city names to selectbox in TCA of backend
 */
class Cities
{
    /**
     * Add cities to select box.
     *
     * @param array $parentArray
     */
    public function addCityItems(array $parentArray): void
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_address');
        $statement = $queryBuilder
            ->select('city')
            ->from('tx_clubdirectory_domain_model_address')
            ->groupBy('city')
            ->orderBy('city', 'ASC')
            ->execute();

        while ($address = $statement->fetch()) {
            $item = [];
            $item[0] = $address['city'];
            $item[1] = $address['city'];
            $item[2] = null;
            $parentArray['items'][] = $item;
        }
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
