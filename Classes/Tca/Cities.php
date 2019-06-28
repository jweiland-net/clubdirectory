<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Tca;

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
