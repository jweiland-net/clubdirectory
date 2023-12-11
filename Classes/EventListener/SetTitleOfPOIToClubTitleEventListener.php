<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * By default the title of the address record will be used as POI title. That's wrong.
 * In this special case we have to update the POI title with the title from club record.
 */
class SetTitleOfPOIToClubTitleEventListener
{
    protected Typo3Version $typo3Version;

    public function __construct(Typo3Version $typo3Version)
    {
        $this->typo3Version = $typo3Version;
    }

    public function __invoke(PostProcessPoiCollectionRecordEvent $event): void
    {
        if (version_compare($this->typo3Version->getBranch(), '11.0', '>=')) {
            $foreignLocationRecord = $event->getForeignLocationRecord();
            $poiCollectionTableName = $event->getPoiCollectionTableName();

            // Execute update only, if club column is filled. Else POI collection will be filled with title of address
            // before this EventListener was called.
            if (
                array_key_exists('club', $foreignLocationRecord)
                && $event->getForeignTableName() === 'tx_clubdirectory_domain_model_address'
                && MathUtility::canBeInterpretedAsInteger($foreignLocationRecord['club'])
            ) {
                $club = $this->getClubRecord((int)$foreignLocationRecord['club']);
                if (!empty($club)) {
                    $connection = $this->getConnectionPool()->getConnectionForTable($poiCollectionTableName);

                    $connection->update(
                        $poiCollectionTableName,
                        [
                            'title' => $club['title'],
                        ],
                        [
                            'uid' => $event->getPoiCollectionUid(),
                        ]
                    );
                }
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
            ->executeQuery()
            ->fetchOne();

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
