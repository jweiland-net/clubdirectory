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
use JWeiland\Clubdirectory\Controller\SearchController;
use JWeiland\Clubdirectory\Hook\Form\GeocodeAddressesHook;
use JWeiland\Clubdirectory\Hook\Form\PrefillForEditUsageHook;
use JWeiland\Clubdirectory\Hook\Form\PrefillOptionsHook;
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
        ManagementController::class => 'listMyClubs, perform, confirmActivation, activate',
    ],
    // non-cacheable actions
    [
        ManagementController::class => 'listMyClubs, perform, confirmActivation, activate',
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

// Add the options of categories and districts to the club form
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['initializeFormElement'][1763337600]
    = PrefillOptionsHook::class;
// Prefill the club form with the values of the club to edit
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'][1763337601]
    = PrefillForEditUsageHook::class;
// Geocode the addresses of the club form, before the map positions are shown
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1763337602]
    = GeocodeAddressesHook::class;
