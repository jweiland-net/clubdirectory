<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Clubdirectory\Controller\ExportController;

/**
 * Definitions for modules provided by EXT:clubdirectory
 */
return [
    'web_clubdirectory' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/web/club',
        'labels' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_export.xlf',
        'iconIdentifier' => 'ext-clubdirectory-be-module-icon',
        'extensionName' => 'Clubdirectory',
        'controllerActions' => [
            ExportController::class => 'index, show',
        ],
    ],
];
