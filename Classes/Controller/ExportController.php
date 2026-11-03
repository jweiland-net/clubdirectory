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
use JWeiland\Clubdirectory\Service\CsvExportService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to export clubs as CSV. The CSV contains personal data, so it
 * is streamed as download and never stored in a public directory. Clubs are
 * exported from the selected page only, if the backend user has access to it.
 */
class ExportController extends ActionController
{
    private const EXPORT_FILE_NAME = 'clubdirectory-export.csv';

    protected ClubRepository $clubRepository;

    protected CsvExportService $csvExportService;

    protected ModuleTemplateFactory $moduleTemplateFactory;

    private ModuleTemplate $moduleTemplate;

    public function injectClubRepository(ClubRepository $clubRepository): void
    {
        $this->clubRepository = $clubRepository;
    }

    public function injectCsvExportService(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
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
        $clubs = $this->getClubsOfCurrentPage();

        $this->moduleTemplate->assign('clubs', $clubs);
        $this->moduleTemplate->assign('storagePid', count($clubs) <= 1 ? $this->getAccessibleStoragePids() : []);

        return $this->moduleTemplate->renderResponse('Export/Index');
    }

    public function showAction(): ResponseInterface
    {
        $this->moduleTemplate->assign('clubs', $this->getClubsOfCurrentPage());

        return $this->moduleTemplate->renderResponse('Export/Show');
    }

    public function downloadAction(): ResponseInterface
    {
        if (!$this->hasPageAccess($this->getCurrentPageUid())) {
            return $this->responseFactory->createResponse(403);
        }

        $csv = $this->csvExportService->createCsv($this->clubRepository->findAllForExport());

        return $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/csv; charset=utf-8; header=present')
            ->withHeader('Content-Disposition', 'attachment; filename="' . self::EXPORT_FILE_NAME . '"')
            ->withHeader('Cache-Control', 'no-store, private')
            ->withBody($this->streamFactory->createStream($csv));
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected function getClubsOfCurrentPage(): array
    {
        if ($this->hasPageAccess($this->getCurrentPageUid())) {
            return $this->clubRepository->findAllForExport();
        }

        $this->addFlashMessage(
            (string)LocalizationUtility::translate('LLL:EXT:clubdirectory/Resources/Private/Language/locallang_export.xlf:noPageAccess'),
            '',
            ContextualFeedbackSeverity::WARNING,
        );

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getAccessibleStoragePids(): array
    {
        return array_values(array_filter(
            $this->clubRepository->getStoragePid(),
            fn(array $storage): bool => $this->hasPageAccess((int)($storage['pid'] ?? 0)),
        ));
    }

    /**
     * Without a selected page Extbase falls back to the first root page, so
     * a page has to be selected explicitly.
     */
    protected function hasPageAccess(int $pageUid): bool
    {
        if ($pageUid <= 0) {
            return false;
        }

        $permissionClause = $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW);

        return is_array(BackendUtility::readPageAccess($pageUid, $permissionClause));
    }

    /**
     * Same page UID as used by Extbase's BackendConfigurationManager as storage PID
     */
    protected function getCurrentPageUid(): int
    {
        $pageUid = $this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? 0;

        return MathUtility::canBeInterpretedAsInteger($pageUid) ? (int)$pageUid : 0;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
