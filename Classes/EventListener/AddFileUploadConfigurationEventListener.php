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
        $mimeTypeValidator->setOptions(['allowedMimeTypes' => ['image/jpeg']]);

        $fileExtensionValidator = $this->getFileExtensionValidator();
        $fileExtensionValidator->setOptions(['allowedFileExtensions' => ['jpg', 'jpeg', 'png']]);

        $fileHandlingServiceConfiguration = $this->arguments->getArgument('myArgument')->getFileHandlingServiceConfiguration();
        $fileHandlingServiceConfiguration->addFileUploadConfiguration(
            (new FileUploadConfiguration('myPropertyName'))
                ->setRequired()
                ->addValidator($mimeTypeValidator)
                ->addValidator($fileExtensionValidator)
                ->setMaxFiles(1)
                ->setUploadFolder('1:/user_upload/files/')
        );

        $this->arguments->getArgument('myArgument')->getPropertyMappingConfiguration()->skipProperties('myPropertyName');
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
