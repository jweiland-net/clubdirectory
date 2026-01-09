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
        if (!$this->isValidRequest($event)) {
            return;
        }

        $mimeTypeValidator = $this->getMimeTypeValidator();
        $mimeTypeValidator->setOptions([
            'allowedMimeTypes' => ['image/jpeg', 'image/png'],
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

        $fileHandlingServiceConfiguration = $event
            ->getArguments()
            ->getArgument('club')
            ->getFileHandlingServiceConfiguration();

        $fileUploadConfiguration = (new FileUploadConfiguration('logo'))
            ->addValidator($mimeTypeValidator)
            ->addValidator($fileExtensionValidator)
            ->setMaxFiles(1)
            ->setUploadFolder($uploadFolder);

        // Set Required for new action
        if ($event->getActionName() === 'create') {
            $fileUploadConfiguration->setRequired();
        }

        $fileHandlingServiceConfiguration->addFileUploadConfiguration(
            $fileUploadConfiguration
        );

        $event->getArguments()
            ->getArgument('club')
            ->getPropertyMappingConfiguration()
            ->skipProperties('logo');
    }

    protected function getMimeTypeValidator(): MimeTypeValidator
    {
        return GeneralUtility::makeInstance(MimeTypeValidator::class);
    }

    protected function getFileExtensionValidator(): FileExtensionValidator
    {
        return GeneralUtility::makeInstance(FileExtensionValidator::class);
    }
}
