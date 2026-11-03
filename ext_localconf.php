<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

use JWeiland\Clubdirectory\Controller\ClubController;
use JWeiland\Clubdirectory\Controller\ManagementController;
use JWeiland\Clubdirectory\Controller\MapController;
use JWeiland\Clubdirectory\Controller\SearchController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::configurePlugin(
    'Clubdirectory',
    'Clubdirectory',
    [
        ClubController::class => 'list, show',
    ],
    // non-cacheable actions
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'Clubdirectory',
    'Management',
    [
        ManagementController::class => 'listMyClubs, new, create, edit, update, confirmActivation, activate',
        MapController::class => 'new, create',
    ],
    // non-cacheable actions
    [
        ManagementController::class => 'listMyClubs, edit, create, update, confirmActivation, activate',
        MapController::class => 'new, create',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'Clubdirectory',
    'Search',
    [
        SearchController::class => 'show, listSearchResults',
    ],
    // non-cacheable actions
    [
        SearchController::class => 'listSearchResults',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1000] = 'EXT:clubdirectory/Resources/Private/Templates/Email';
