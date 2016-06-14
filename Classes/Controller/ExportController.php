<?php

namespace JWeiland\Clubdirectory\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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
     * @var \JWeiland\Clubdirectory\Domain\Repository\ClubRepository
     */
    protected $clubRepository = null;

    /**
     * inject clubRepository.
     *
     * @param \JWeiland\Clubdirectory\Domain\Repository\ClubRepository $clubRepository
     */
    public function injectClubRepository(\JWeiland\Clubdirectory\Domain\Repository\ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    /**
     * action index.
     */
    public function indexAction()
    {
        $this->createDirectoryStructure();
        $this->removePreviousExports();

        $exportFile = PATH_site.$this->exportPath.$this->exportFile;
        $fp = fopen($exportFile, 'w');
        foreach ($this->clubRepository->findAllForExport() as $row) {
            fputcsv($fp, $row, ';', '\'');
        }
        fclose($fp);
    }

    /**
     * check directory and create directory structure if not already created.
     */
    protected function createDirectoryStructure()
    {
        if (!is_dir(PATH_site.$this->exportPath)) {
            GeneralUtility::mkdir_deep(PATH_site.$this->exportPath);
        }
    }

    /**
     * remove previously created exports.
     */
    protected function removePreviousExports()
    {
        $exportFile = PATH_site.$this->exportPath.$this->exportFile;
        if (is_file($exportFile)) {
            unlink($exportFile); // only to be sure
        }
    }
}
