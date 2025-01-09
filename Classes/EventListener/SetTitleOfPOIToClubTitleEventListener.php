<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Traits\ConnectionPoolTrait;
use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * By default, the title of the address record will be used as POI title. That's wrong.
 * In this special case we have to update the POI title with the title from club record.
 */
class SetTitleOfPOIToClubTitleEventListener
{
    use ConnectionPoolTrait;

    public function __invoke(PostProcessPoiCollectionRecordEvent $event): void
    {
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
            if ($club !== []) {
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

    /**
     * @throws \Doctrine\DBAL\Exception
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
                    $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        if ($club === false) {
            $club = [];
        }

        return $club;
    }
}
