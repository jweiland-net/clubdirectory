<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller to export clubs
 */
class ExportController extends ActionController
{
    /**
     * in which directory we want to export club data
     * Needed separately to create folder structure if not exists.
     *
     * @var string
     */
    protected $exportPath = 'typo3temp/tx_clubdirectory/';

    /**
     * in which file we want to export club data.
     *
     * @var string
     */
    protected $exportFile = 'export.csv';

    /**
     * @var ClubRepository
     */
    protected $clubRepository;

    /**
     * @param ClubRepository $clubRepository
     */
    public function injectClubRepository(ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    public function indexAction()
    {
        $this->createDirectoryStructure();
        $this->removePreviousExports();

        $exportFile = PATH_site . $this->exportPath . $this->exportFile;
        $fp = \fopen($exportFile, 'wb');
        foreach ($this->clubRepository->findAllForExport() as $row) {
            \fputcsv($fp, $row, ';', '\'');
        }
        \fclose($fp);
    }

    /**
     * check directory and create directory structure if not already created.
     */
    protected function createDirectoryStructure()
    {
        if (!\is_dir(PATH_site . $this->exportPath)) {
            GeneralUtility::mkdir_deep(PATH_site . $this->exportPath);
        }
    }

    /**
     * Remove previously created exports.
     */
    protected function removePreviousExports()
    {
        $exportFile = PATH_site . $this->exportPath . $this->exportFile;
        if (\is_file($exportFile)) {
            \unlink($exportFile); // only to be sure
        }
    }
}
