<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use JWeiland\Clubdirectory\Event\InitializeControllerActionEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;

#[AsEventListener('clubdirectory/checkFalUploadsValidationEventListener')]
class CheckFalUploadsValidationEventListener extends AbstractControllerEventListener
{
    protected array $allowedControllerActions = [
        'Club' => [
            'create',
            'update',
        ],
    ];

    public function __invoke(InitializeControllerActionEvent $event): void
    {
        if (!$this->isValidRequest($event) || !$this->isCheckFalUploadsAvailable()) {
            return;
        }

        $request = $event->getRequest();
        $club = $request->hasArgument('club') ? $request->getArgument('club') : '';

        // Check Logo File Uploaded
        if (isset($club['logo']) && is_array($club['logo']) && $club['logo'] !== []) {
            $logoUploadError = $this->getFalUploadService()->checkFile($club, 'logo');
            if ($logoUploadError instanceof Error) {
                $this->addErrorToValidationResults($event, $logoUploadError, 'logo');
            }
        }

        // Check Images are Uploaded with Proper Rights
        if (isset($club['images']) && is_array($club['images']) && $club['images'] !== []) {
            $imageUploadError = $this->getFalUploadService()->checkFile($club, 'images');
            if ($imageUploadError instanceof Error) {
                $this->addErrorToValidationResults($event, $imageUploadError, 'images');
            }
        }
    }

    private function isCheckFalUploadsAvailable(): bool
    {
        if (ExtensionManagementUtility::isLoaded('checkfaluploads')) {
            return true;
        }

        return false;
    }

    private function addErrorToValidationResults(
        InitializeControllerActionEvent $event,
        Error $error,
        string $property
    ): void {
        $arguments = $event->getArguments();

        if ($arguments->hasArgument('club')) {
            $arguments->getArgument('club')
                ->getValidationResults()
                ->forProperty($property)
                ->addError($error);
        }
    }

    private function getFalUploadService(): FalUploadService
    {
        return GeneralUtility::makeInstance(FalUploadService::class);
    }
}
