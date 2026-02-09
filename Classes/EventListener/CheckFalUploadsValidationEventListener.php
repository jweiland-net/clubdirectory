<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Event\InitializeControllerActionEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\FileUploadConfiguration;
use TYPO3\CMS\Extbase\Validation\Validator\FileExtensionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\FileSizeValidator;
use TYPO3\CMS\Extbase\Validation\Validator\MimeTypeValidator;

class AddFileUploadConfigurationEventListener extends AbstractControllerEventListener
{
    protected array $allowedControllerActions = [
        'Club' => [
            'create',
            'update',
        ],
    ];

    public function __invoke(InitializeControllerActionEvent $event): void
    {
        return;
        if (!$this->isValidRequest($event)) {
            return;
        }

        // Set Required for new action
        $fileRequired = $event->getActionName() === 'create';

        $mimeTypeValidator = $this->getMimeTypeValidator();
        $mimeTypeValidator->setOptions([
            'allowedMimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
        ]);

        $fileSizeValidator = $this->getFileSizeValidator();
        $fileSizeValidator->setOptions([
            'maximum' => '5M',
        ]);

        $fileExtensionValidator = $this->getFileExtensionValidator();
        $fileExtensionValidator->setOptions(['allowedFileExtensions' => ['jpg', 'jpeg', 'png']]);

        // get TypoScript Configuration from event
        $settings = $event->getSettings();

        // get and assign upload path for logo from settings
        $uploadFolder = $settings['new']['uploadFolder'] ?? '';
        if ($uploadFolder === '') {
            throw new \InvalidArgumentException(
                'You have forgotten to set an Upload Folder in TypoScript for clubdirectory',
                1603808777,
            );
        }

        $arguments = $event->getArguments();
        if ($arguments->hasArgument('club')) {
            /**
            $club = $arguments->getArgument('club');
            $fileHandlingServiceConfiguration = $club->getFileHandlingServiceConfiguration();
            $fileHandlingServiceConfiguration->  (
                (new FileUploadConfiguration('logo.0'))
                    ->addValidator($mimeTypeValidator)
                    ->addValidator($fileExtensionValidator)
                    ->addValidator($fileSizeValidator)
                    ->setMaxFiles(1)
                    ->setUploadFolder($uploadFolder)
                    ->setRequired($fileRequired)
            );
            $club->getPropertyMappingConfiguration()->skipProperties('logo.0');
             **/
        }
    }

    protected function getMimeTypeValidator(): MimeTypeValidator
    {
        return GeneralUtility::makeInstance(MimeTypeValidator::class);
    }

    protected function getFileExtensionValidator(): FileExtensionValidator
    {
        return GeneralUtility::makeInstance(FileExtensionValidator::class);
    }

    private function getFileSizeValidator(): FileSizeValidator
    {
        return GeneralUtility::makeInstance(FileSizeValidator::class);
    }
}
