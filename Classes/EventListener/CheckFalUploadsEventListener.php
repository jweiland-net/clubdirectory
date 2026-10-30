<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Checkfaluploads\Validation\Validator\CheckFalUploadValidator;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * If EXT:checkfaluploads is loaded, add its validator to the file upload
 * configuration of logo and images. The validator checks the "rights"
 * checkbox rendered next to the upload fields.
 */
#[AsEventListener(
    identifier: 'clubdirectory/check-fal-uploads',
)]
final readonly class CheckFalUploadsEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ALLOWED_CONTROLLER_ACTIONS = [
        'Management' => [
            'create',
            'update',
        ],
    ];

    private const VALIDATOR = CheckFalUploadValidator::class;

    private const PROPERTY_NAMES = [
        'logo',
        'images',
    ];

    public function __construct(
        private ValidatorResolver $validatorResolver,
    ) {}

    public function __invoke(PreProcessControllerActionEvent $controllerActionEvent): void
    {
        if (!ExtensionManagementUtility::isLoaded('checkfaluploads')) {
            return;
        }

        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        foreach (self::PROPERTY_NAMES as $propertyName) {
            $this->assignValidatorToFileUploadConfiguration($controllerActionEvent, $propertyName);
        }
    }

    private function assignValidatorToFileUploadConfiguration(
        PreProcessControllerActionEvent $controllerActionEvent,
        string $propertyName,
    ): void {
        // The request is mandatory, as the validator reads the "rights" checkbox from the parsed body
        $checkFalUploadsValidator = $this->validatorResolver->createValidator(
            self::VALIDATOR,
            ['propertyPath' => 'club.' . $propertyName],
            $controllerActionEvent->getRequest(),
        );

        $controllerActionEvent
            ->getArguments()
            ->getArgument('club')
            ->getFileHandlingServiceConfiguration()
            ->getFileUploadConfigurationForProperty($propertyName)
            ?->addValidator($checkFalUploadsValidator);
    }
}
