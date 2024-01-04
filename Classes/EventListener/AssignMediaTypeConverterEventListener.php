<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Event\InitializeControllerActionEvent;
use JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;

class AssignMediaTypeConverterEventListener extends AbstractControllerEventListener
{
    protected ClubRepository $clubRepository;

    protected array $allowedControllerActions = [
        'Club' => [
            'create',
            'update',
        ],
    ];

    public function __construct(ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    public function __invoke(InitializeControllerActionEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            if ($event->getActionName() === 'create') {
                $this->assignTypeConverterForCreateAction($event);
            } else {
                $this->assignTypeConverterForUpdateAction($event);
            }
        }
    }

    protected function assignTypeConverterForCreateAction(InitializeControllerActionEvent $event): void
    {
        $this->setTypeConverterForProperty('logo', null, $event);
        $this->setTypeConverterForProperty('images', null, $event);
    }

    protected function assignTypeConverterForUpdateAction(InitializeControllerActionEvent $event): void
    {
        // Needed to get the previously stored logo and images
        /** @var Club $persistedClub */
        $persistedClub = $this->clubRepository->findByIdentifier(
            $event->getRequest()->getArgument('club')['__identity']
        );

        $this->setTypeConverterForProperty('logo', $persistedClub->getOriginalLogo(), $event);
        $this->setTypeConverterForProperty('images', $persistedClub->getOriginalImages(), $event);
    }

    protected function setTypeConverterForProperty(
        string $property,
        ?ObjectStorage $persistedFiles,
        InitializeControllerActionEvent $event
    ): void {
        $propertyMappingConfiguration = $this->getPropertyMappingConfigurationForCompany($event)
            ->forProperty($property)
            ->setTypeConverter(GeneralUtility::makeInstance(UploadMultipleFilesConverter::class));

        // Do not use setTypeConverterOptions() as this will remove all existing options
        $this->addOptionToUploadFilesConverter(
            $propertyMappingConfiguration,
            'settings',
            $event->getSettings()
        );

        if ($persistedFiles !== null) {
            $this->addOptionToUploadFilesConverter(
                $propertyMappingConfiguration,
                'IMAGES',
                $persistedFiles
            );
        }
    }

    protected function getPropertyMappingConfigurationForCompany(
        InitializeControllerActionEvent $event
    ): MvcPropertyMappingConfiguration {
        return $event->getArguments()
            ->getArgument('club')
            ->getPropertyMappingConfiguration();
    }

    protected function addOptionToUploadFilesConverter(
        PropertyMappingConfiguration $propertyMappingConfiguration,
        string $optionKey,
        $optionValue
    ): void {
        $propertyMappingConfiguration->setTypeConverterOption(
            UploadMultipleFilesConverter::class,
            $optionKey,
            $optionValue
        );
    }
}
