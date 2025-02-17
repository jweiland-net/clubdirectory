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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller to export clubs as CSV
 */
#[AsController]
class ExportModuleController extends ActionController
{
    /**
     * In which directory we want to export club data
     * Needed separately to create folder structure if not exists.
     */
    protected string $exportPath = 'typo3temp/tx_clubdirectory/';

    /**
     * In which file we want to export club data.
     */
    protected string $exportFile = 'export.csv';

    protected ClubRepository $clubRepository;

    protected ModuleTemplateFactory $moduleTemplateFactory;

    private ModuleTemplate $moduleTemplate;

    public function injectClubRepository(ClubRepository $clubRepository): void
    {
        $this->clubRepository = $clubRepository;
    }

    public function injectModuleTemplateFactory(ModuleTemplateFactory $moduleTemplateFactory): void
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function initializeModuleTemplate(ServerRequestInterface $request): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
    }

    public function initializeAction(): void
    {
        $this->initializeModuleTemplate($this->request);
    }

    public function indexAction(): ResponseInterface
    {
        $this->createDirectoryStructure();
        $this->removePreviousExports();

        $clubs = $this->clubRepository->findAllForExport();
        $storagePid = (count($clubs) <= 1) ? $this->clubRepository->getStoragePid() : [];
        $exportFile = $this->getExportPath() . $this->exportFile;
        $fp = \fopen($exportFile, 'wb');
        foreach ($clubs as $row) {
            \fputcsv($fp, $row, ';', "'");
        }

        \fclose($fp);

        $this->moduleTemplate->assign(
            'exportPath',
            PathUtility::getAbsoluteWebPath($this->getExportPath() . $this->exportFile),
        );
        $this->moduleTemplate->assign('clubs', $clubs);
        $this->moduleTemplate->assign('storagePid', $storagePid);

        return $this->moduleTemplate->renderResponse('Index');
    }

    public function showAction(): ResponseInterface
    {
        $clubs = $this->clubRepository->findAllForExport();
        $this->moduleTemplate->assign('clubs', $clubs);

        return $this->moduleTemplate->renderResponse('Show');
    }

    /**
     * Check directory and create directory structure if not already created.
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
        return Environment::getPublicPath() . '/' . rtrim($this->exportPath, '/') . '/';
    }
}
