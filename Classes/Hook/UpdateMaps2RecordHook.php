<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Hook;

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
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Set title of POI collection to title of club record instead of title from address.
 */
class UpdateMaps2RecordHook
{
    /**
     * @param string $poiCollectionTableName
     * @param int $poiCollectionUid
     * @param string $foreignTableName
     * @param array $foreignLocationRecord
     * @param array $options
     */
    public function postUpdatePoiCollection(string $poiCollectionTableName, int $poiCollectionUid, string $foreignTableName, array $foreignLocationRecord, array $options)
    {
        // execute update only, if club column is filled. Else POI collection will be filled with title of address
        // before this SignalSlot was called.
        if (
            $foreignTableName === 'tx_clubdirectory_domain_model_address'
            && array_key_exists('club', $foreignLocationRecord)
            && MathUtility::canBeInterpretedAsInteger($foreignLocationRecord['club'])
        ) {
            $club = $this->getClubRecord((int)$foreignLocationRecord['club']);
            if (!empty($club)) {
                $connection = $this->getConnectionPool()->getConnectionForTable($poiCollectionTableName);

                // update amount of category relations
                $connection->update(
                    $poiCollectionTableName,
                    [
                        'title' => $club['title']
                    ],
                    [
                        'uid' => $poiCollectionUid
                    ]
                );
            }
        }
    }

    /**
     * Get club (parent record of address) record
     *
     * @param int $clubUid
     * @return array
     */
    protected function getClubRecord(int $clubUid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_club');
        $club = $queryBuilder
            ->select('uid', 'title')
            ->from('tx_clubdirectory_domain_model_club')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($clubUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();
        if (empty($club)) {
            $club = [];
        }

        return $club;
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
