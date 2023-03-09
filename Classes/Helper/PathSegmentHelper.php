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
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/*
 * Helper class to generate a path segment (slug) for a club record.
 * Used while executing the UpgradeWizard and saving records in frontend.
 */
class PathSegmentHelper
{
    /**
     * @var array
     */
    protected $generatorFields = [];

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    public function __construct(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function generatePathSegment(array $baseRecord, int $pid): string
    {
        return $this->getSlugHelper()->generate($baseRecord, $pid);
    }

    public function updatePathSegmentForClub(Club $club): void
    {
        // First of all, we have to check, if an UID is available
        if (!$club->getUid()) {
            $this->persistenceManager->persistAll();
        }

        $club->setPathSegment(
            $this->generatePathSegment(
                $club->getBaseRecordForPathSegment(),
                $club->getPid()
            )
        );
    }

    protected function getSlugHelper(): SlugHelper
    {
        $config = $GLOBALS['TCA']['tx_clubdirectory_domain_model_club']['columns']['path_segment']['config'];

        if ($this->generatorFields) {
            $config['generatorOptions']['fields'] = $this->generatorFields;
        }

        return GeneralUtility::makeInstance(
            SlugHelper::class,
            'tx_clubdirectory_domain_model_club',
            'path_segment',
            $config
        );
    }

    public function setGeneratorFields(array $generatorFields): void
    {
        $this->generatorFields = $generatorFields;
    }
}
