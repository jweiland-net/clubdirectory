<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Reads the records of a club, which are shown and stored by the club form
 * based on EXT:form. Hidden records are included, as clubs are hidden until
 * they get activated. Deleted records are never returned.
 *
 * Addresses and file references are addressed by their position (1, 2, 3),
 * as the form contains a fixed number of elements for them. Addresses are
 * ordered by UID, file references by "sorting_foreign".
 */
readonly class ClubFormRecordService
{
    public const TABLE_CLUB = 'tx_clubdirectory_domain_model_club';

    public const TABLE_ADDRESS = 'tx_clubdirectory_domain_model_address';

    public const TABLE_POI_COLLECTION = 'tx_maps2_domain_model_poicollection';

    public const TABLE_FILE_REFERENCE = 'sys_file_reference';

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getClubRecord(int $clubUid): array
    {
        return $this->getRecordByUid(self::TABLE_CLUB, $clubUid);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPoiCollectionRecord(int $poiCollectionUid): array
    {
        return $this->getRecordByUid(self::TABLE_POI_COLLECTION, $poiCollectionUid);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAddressRecord(int $clubUid, int $position): array
    {
        $queryBuilder = $this->getQueryBuilder(self::TABLE_ADDRESS);
        $queryBuilder
            ->where($queryBuilder->expr()->eq('club', $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)))
            ->orderBy('uid', 'ASC');

        return $this->fetchRecordAtPosition($queryBuilder, $clubUid, $position);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFileReferenceRecord(int $clubUid, string $fieldName, int $position): array
    {
        $queryBuilder = $this->getQueryBuilder(self::TABLE_FILE_REFERENCE);
        $queryBuilder
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TABLE_CLUB)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($fieldName)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)),
            )
            ->orderBy('sorting_foreign', 'ASC')
            ->addOrderBy('uid', 'ASC');

        return $this->fetchRecordAtPosition($queryBuilder, $clubUid, $position);
    }

    public function countFileReferences(int $clubUid, string $fieldName): int
    {
        $queryBuilder = $this->getQueryBuilder(self::TABLE_FILE_REFERENCE);

        return (int)$queryBuilder
            ->count('uid')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter(self::TABLE_CLUB)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($fieldName)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($clubUid, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @return array<string, mixed>
     */
    private function getRecordByUid(string $table, int $uid): array
    {
        if ($uid <= 0) {
            return [];
        }

        $queryBuilder = $this->getQueryBuilder($table);
        $record = $queryBuilder
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($record) ? $record : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchRecordAtPosition(QueryBuilder $queryBuilder, int $clubUid, int $position): array
    {
        if ($clubUid <= 0 || $position <= 0) {
            return [];
        }

        $record = $queryBuilder
            ->setFirstResult($position - 1)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        return is_array($record) ? $record : [];
    }

    private function getQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
        $queryBuilder->select('*')->from($table);

        return $queryBuilder;
    }
}
