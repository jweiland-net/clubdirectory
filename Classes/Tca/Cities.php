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
    public function addCityItems(array $parentArray)
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_address');
        $addresses = $queryBuilder
            ->select('city')
            ->from('tx_clubdirectory_domain_model_address')
            ->groupBy('city')
            ->orderBy('city', 'ASC')
            ->execute()
            ->fetchAll();

        foreach ($addresses as $address) {
            $item = [];
            $item[0] = $address['city'];
            $item[1] = $address['city'];
            $item[2] = null;
            $parentArray['items'][] = $item;
        }
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
