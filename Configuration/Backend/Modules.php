<?php

declare(strict_types=1);

use JWeiland\Clubdirectory\Controller\ExportModuleController;

/**
 * Definitions for modules provided by EXT:clubdirectory
 */
return [
    'club' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/web/club',
        'labels' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_export.xlf',
        'iconIdentifier' => 'ext-clubdirectory-be-module-icon',
        'extensionName' => 'clubdirectory',
        'controllerActions' => [
            ExportModuleController::class => [
                'index', 'show',
            ],
        ],
    ],
];
