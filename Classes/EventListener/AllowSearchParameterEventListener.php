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
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/*
 * We have build our own form-tag for search plugin, so extbase will not configure PropertyMappingConfiguration
 * automatically. We have to do it manually here.
 * With fluid-form-VHs the $_GET request in browser URL will get extremely long.
 */
class AllowSearchParameterEventListener extends AbstractControllerEventListener
{
    protected array $allowedControllerActions = [
        'Club' => [
            'search',
        ],
    ];

    public function __invoke(InitializeControllerActionEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $pmc = $event->getArguments()
                ->getArgument('search')
                ->getPropertyMappingConfiguration();

            // ToDo: Remove subCategory with next major version
            $pmc->allowProperties(
                'searchWord',
                'letter',
                'district',
                'category',
                'subCategory',
                'direction',
                'orderBy',
            );

            $pmc->setTypeConverterOptions(
                PersistentObjectConverter::class,
                [
                    PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED => true,
                ],
            );
        }
    }
}
