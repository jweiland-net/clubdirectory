<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Updates;

use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Updater to fill empty slug columns of clubdirectory records
 */
#[UpgradeWizard('clubdirectoryUpdateSlug')]
final readonly class ClubdirectorySlugUpdater implements UpgradeWizardInterface
{
    private const TABLE_NAME = 'tx_clubdirectory_domain_model_club';

    private const FIELD_NAME = 'path_segment';

    public function __construct(
        private ConnectionPool $connectionPool,
        private PathSegmentHelper $pathSegmentHelper,
    ) {}

    public function getTitle(): string
    {
        return '[clubdirectory] Update Slug of clubdirectory records';
    }

    public function getDescription(): string
    {
        return 'Update empty slug column "path_segment" of clubdirectory records with an URI compatible version '
            . 'of the title';
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getQueryBuilderForRecordsWithEmptySlug();

        return (bool)$queryBuilder
            ->count('*')
            ->executeQuery()
            ->fetchOne();
    }

    public function executeUpdate(): bool
    {
        $statement = $this->getQueryBuilderForRecordsWithEmptySlug()
            ->select('uid', 'pid', 'title')
            ->executeQuery();

        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        while ($recordToUpdate = $statement->fetchAssociative()) {
            if ((string)$recordToUpdate['title'] === '') {
                continue;
            }

            $connection->update(
                self::TABLE_NAME,
                [
                    self::FIELD_NAME => $this->pathSegmentHelper->generatePathSegment(
                        $recordToUpdate,
                        (int)$recordToUpdate['pid'],
                    ),
                ],
                [
                    'uid' => (int)$recordToUpdate['uid'],
                ],
            );
        }

        return true;
    }

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    /**
     * Returns a QueryBuilder with FROM and WHERE parts set. The caller has
     * to add the SELECT or COUNT part.
     */
    private function getQueryBuilderForRecordsWithEmptySlug(): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(new DeletedRestriction());

        return $queryBuilder
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        self::FIELD_NAME,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(self::FIELD_NAME),
                ),
            );
    }
}
