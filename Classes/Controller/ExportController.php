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
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller to export clubs as CSV
 */
class ExportController extends ActionController
{
    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * In which directory we want to export club data
     * Needed separately to create folder structure if not exists.
     *
     * @var string
     */
    protected $exportPath = 'typo3temp/tx_clubdirectory/';

    /**
     * In which file we want to export club data.
     *
     * @var string
     */
    protected $exportFile = 'export.csv';

    /**
     * @var ClubRepository
     */
    protected $clubRepository;

    public function injectClubRepository(ClubRepository $clubRepository): void
    {
        $this->clubRepository = $clubRepository;
    }

    public function indexAction(): void
    {
        $this->createDirectoryStructure();
        $this->removePreviousExports();

        $exportFile = $this->getExportPath() . $this->exportFile;
        $fp = \fopen($exportFile, 'wb');
        foreach ($this->clubRepository->findAllForExport() as $row) {
            \fputcsv($fp, $row, ';', '\'');
        }
        \fclose($fp);

        $this->view->assign(
            'exportPath',
            PathUtility::getAbsoluteWebPath($this->getExportPath() . $this->exportFile)
        );
    }

    /**
     * check directory and create directory structure if not already created.
     */
    protected function createDirectoryStructure(): void
    {
        if (!\is_dir($this->getExportPath())) {
            GeneralUtility::mkdir_deep($this->getExportPath());
        }
    }

    /**
     * Remove previously created exports.
     */
    protected function removePreviousExports(): void
    {
        $exportFile = $this->getExportPath() . $this->exportFile;
        if (\is_file($exportFile)) {
            \unlink($exportFile); // only to be sure
        }
    }

    protected function getExportPath(): string
    {
        return  Environment::getPublicPath() . '/' . rtrim($this->exportPath, '/') . '/';
    }
}
