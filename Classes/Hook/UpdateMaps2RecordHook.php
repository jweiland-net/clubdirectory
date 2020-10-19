<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Hook;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Set title of POI collection to title of club record instead of title from address.
 */
class UpdateMaps2RecordHook
{
    public function postUpdatePoiCollection(string $poiCollectionTableName, int $poiCollectionUid, string $foreignTableName, array $foreignLocationRecord, array $options): void
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

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
