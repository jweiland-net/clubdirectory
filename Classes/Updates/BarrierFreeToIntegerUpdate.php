<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Updates;

use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallIntType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Since ext_tables.sql was removed, TYPO3 derives the column type of
 * "barrier_free" (TCA type "check") as SMALLINT UNSIGNED. Existing
 * installations still have a VARCHAR column which may contain empty
 * strings, NULL or other non-numeric values. These values let the ALTER
 * statement of the database analyzer fail in strict SQL mode, so this
 * wizard converts them to 0 before the column type gets changed.
 */
#[UpgradeWizard('clubdirectory_barrierFreeToIntegerUpdate')]
final readonly class BarrierFreeToIntegerUpdate implements UpgradeWizardInterface
{
    private const TABLE_NAME = 'tx_clubdirectory_domain_model_address';

    private const FIELD_NAME = 'barrier_free';

    private const MAX_VALUE = 65535;

    private const CHUNK_SIZE = 500;

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function getTitle(): string
    {
        return '[clubdirectory] Convert non-numeric values of address column "barrier_free" to 0';
    }

    public function getDescription(): string
    {
        return 'The column "barrier_free" of table "' . self::TABLE_NAME . '" will be changed from VARCHAR '
            . 'to SMALLINT by the database analyzer. Empty strings, NULL and other non-numeric values let this '
            . 'change fail in strict SQL mode. This wizard sets these values to 0. Run the database analyzer '
            . '("Update database schema") AFTER this wizard to change the column type.';
    }

    public function updateNecessary(): bool
    {
        if (!$this->isStringColumn()) {
            return false;
        }

        return $this->getRecordsToConvert() !== [];
    }

    public function executeUpdate(): bool
    {
        if (!$this->isStringColumn()) {
            return true;
        }

        $uidsByValue = [];
        foreach ($this->getRecordsToConvert() as $uid => $value) {
            $uidsByValue[$value][] = $uid;
        }

        foreach ($uidsByValue as $value => $uids) {
            foreach (array_chunk($uids, self::CHUNK_SIZE) as $uidChunk) {
                $this->updateRecords($uidChunk, $value);
            }
        }

        return true;
    }

    /**
     * Only "safe" additions (missing tables and columns) are executed by this
     * prerequisite. Changing the column type is NOT part of it, so it does
     * not run into the strict SQL mode error this wizard is meant to fix.
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    /**
     * Returns false if the table or column does not exist or if the column
     * already is of an integer type. In both cases there is nothing to do.
     */
    private function isStringColumn(): bool
    {
        $schemaManager = $this->connectionPool->getConnectionForTable(self::TABLE_NAME)->createSchemaManager();
        if (!$schemaManager->tablesExist([self::TABLE_NAME])) {
            return false;
        }

        $table = $schemaManager->introspectTable(self::TABLE_NAME);
        if (!$table->hasColumn(self::FIELD_NAME)) {
            return false;
        }

        $type = $table->getColumn(self::FIELD_NAME)->getType();

        return !$type instanceof SmallIntType
            && !$type instanceof IntegerType
            && !$type instanceof BigIntType;
    }

    /**
     * Returns all records whose value is not a valid unsigned SMALLINT.
     * Deleted records are included, as the ALTER statement affects all rows.
     *
     * @return array<int, int> Record UID as key, new integer value as value
     */
    private function getRecordsToConvert(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder
            ->select('uid', self::FIELD_NAME)
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->isNull(self::FIELD_NAME),
                    $queryBuilder->expr()->notIn(
                        self::FIELD_NAME,
                        $queryBuilder->createNamedParameter(['0', '1'], Connection::PARAM_STR_ARRAY),
                    ),
                ),
            )
            ->executeQuery();

        $recordsToConvert = [];
        while ($record = $result->fetchAssociative()) {
            $value = $record[self::FIELD_NAME];
            if (!$this->isValidValue($value)) {
                $recordsToConvert[(int)$record['uid']] = $this->normalizeValue($value);
            }
        }

        return $recordsToConvert;
    }

    private function isValidValue(?string $value): bool
    {
        return $value !== null
            && ctype_digit($value)
            && (int)$value <= self::MAX_VALUE;
    }

    /**
     * Keep numeric values which are only surrounded by whitespace,
     * everything else becomes 0.
     */
    private function normalizeValue(?string $value): int
    {
        $trimmedValue = trim((string)$value);
        if (ctype_digit($trimmedValue) && (int)$trimmedValue <= self::MAX_VALUE) {
            return (int)$trimmedValue;
        }

        return 0;
    }

    /**
     * @param int[] $uids
     */
    private function updateRecords(array $uids, int $value): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update(self::TABLE_NAME)
            ->set(self::FIELD_NAME, $queryBuilder->createNamedParameter((string)$value, Connection::PARAM_STR), false)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY),
                ),
            )
            ->executeStatement();
    }
}
