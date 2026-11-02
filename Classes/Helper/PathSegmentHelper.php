<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Helper;

use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper class to generate a unique path segment (slug) for a club record.
 * Used while executing the UpgradeWizard and saving records in frontend.
 */
class PathSegmentHelper
{
    protected const TABLE_NAME = 'tx_clubdirectory_domain_model_club';

    protected const FIELD_NAME = 'path_segment';

    /**
     * Same as DataHandler::checkValueForSlug(): respect the eval options
     * "unique", "uniqueInSite" and "uniqueInPid" of the slug field.
     *
     * @param array<string, mixed> $record Must contain the uid and all fields of generatorOptions
     */
    public function generatePathSegment(array $record, int $pid): string
    {
        $config = $GLOBALS['TCA'][self::TABLE_NAME]['columns'][self::FIELD_NAME]['config'];
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, self::TABLE_NAME, self::FIELD_NAME, $config);

        $slug = $slugHelper->generate($record, $pid);
        $state = RecordStateFactory::forName(self::TABLE_NAME)->fromArray($record, $pid, $record['uid'] ?? null);
        $evalCodes = GeneralUtility::trimExplode(',', (string)($config['eval'] ?? ''), true);
        if (in_array('unique', $evalCodes, true)) {
            $slug = $slugHelper->buildSlugForUniqueInTable($slug, $state);
        }
        if (in_array('uniqueInSite', $evalCodes, true)) {
            $slug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
        }
        if (in_array('uniqueInPid', $evalCodes, true)) {
            $slug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
        }

        return $slug;
    }

    /**
     * The club must be persisted, so that the uniqueness check can exclude
     * the club itself.
     */
    public function updatePathSegmentForClub(Club $club): void
    {
        $club->setPathSegment(
            $this->generatePathSegment($club->getBaseRecordForPathSegment(), (int)$club->getPid()),
        );
    }
}
