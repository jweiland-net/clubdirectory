<?php

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-clubdirectory-wizard-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:clubdirectory/Resources/Public/Icons/plugin_wizard.svg',
    ],
    'ext-clubdirectory-be-module-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:clubdirectory/Resources/Public/Icons/backend_module_icon.svg',
    ],
];
