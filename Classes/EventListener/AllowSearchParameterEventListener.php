<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 * The search arguments are also transferred by GET (e.g. links of the page browser),
 * so Extbase can not configure the PropertyMappingConfiguration by trusted properties.
 * We have to do it manually here.
 */
#[AsEventListener(
    identifier: 'clubdirectory/allow-search-parameter',
)]
final readonly class AllowSearchParameterEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ALLOWED_CONTROLLER_ACTIONS = [
        'Search' => [
            'listSearchResults',
        ],
    ];

    public function __invoke(PreProcessControllerActionEvent $controllerActionEvent): void
    {
        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        $propertyMappingConfiguration = $controllerActionEvent
            ->getArguments()
            ->getArgument('search')
            ->getPropertyMappingConfiguration();

        $propertyMappingConfiguration->allowProperties(
            'searchWord',
            'district',
            'category',
            'order',
            'orderBy',
        );

        // Ignore unknown arguments like the removed "subCategory" in old links
        $propertyMappingConfiguration->skipUnknownProperties();

        $propertyMappingConfiguration->setTypeConverterOptions(
            PersistentObjectConverter::class,
            [
                PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED => true,
            ],
        );
    }
}
