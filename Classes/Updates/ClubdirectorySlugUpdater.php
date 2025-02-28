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
use JWeiland\Clubdirectory\Traits\ConnectionPoolTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Updater to fill empty slug columns of clubdirectory records
 */
#[UpgradeWizard('myUpgradeWizard')]
class ClubdirectorySlugUpdater implements UpgradeWizardInterface
{
    use ConnectionPoolTrait;

    protected string $tableName = 'tx_clubdirectory_domain_model_club';

    protected string $fieldName = 'path_segment';

    protected PathSegmentHelper $pathSegmentHelper;

    public function __construct(PathSegmentHelper $pathSegmentHelper = null)
    {
        $this->pathSegmentHelper = $pathSegmentHelper;
    }

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     */
    public function getIdentifier(): string
    {
        return 'clubdirectoryUpdateSlug';
    }

    public function getTitle(): string
    {
        return '[clubdirectory] Update Slug of clubdirectory records';
    }

    public function getDescription(): string
    {
        return 'Update empty slug column "path_segment" of clubdirectory records with an URI compatible version of the title';
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $amountOfRecordsWithEmptySlug = $queryBuilder
            ->count('*')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        $this->fieldName,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(
                        $this->fieldName,
                    ),
                ),
            )
            ->executeQuery()
            ->fetchOne();

        return (bool)$amountOfRecordsWithEmptySlug;
    }

    /**
     * Performs the accordant updates.
     */
    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $statement = $queryBuilder
            ->select('uid', 'pid', 'title')
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        $this->fieldName,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(
                        $this->fieldName,
                    ),
                ),
            )
            ->executeQuery();

        $connection = $this->getConnectionPool()->getConnectionForTable($this->tableName);
        while ($recordToUpdate = $statement->fetchAssociative()) {
            if ((string)$recordToUpdate['title'] !== '') {
                $connection->update(
                    $this->tableName,
                    [
                        $this->fieldName => $this->pathSegmentHelper->generatePathSegment(
                            $recordToUpdate,
                            (int)$recordToUpdate['pid'],
                        ),
                    ],
                    [
                        'uid' => (int)$recordToUpdate['uid'],
                    ],
                );
            }
        }

        return true;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
