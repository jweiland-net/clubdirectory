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
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        ManagementController::class => 'listMyClubs, new, create, edit, update, activate',
        MapController::class => 'new, create, edit, update',
    ],
    // non-cacheable actions
    [
        ManagementController::class => 'listMyClubs, edit, create, update, activate',
        MapController::class => 'new, create, edit, update',
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


try {
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $rootCategory = (int)$extConf->get('clubdirectory', 'rootCategory');
    $userGroup = (int)$extConf->get('clubdirectory', 'userGroup');

    $tsConfig = [
        'TCEFORM.tt_content.pi_flexform.clubdirectory_clubdirectory.sDEFAULT.settings\.category.PAGE_TSCONFIG_ID = ' . $rootCategory,
        'TCEFORM.tx_clubdirectory_domain_model_club.categories.config.treeConfig.startingPoints = ' . $rootCategory,
        'TCEFORM.tx_clubdirectory_domain_model_club.fe_users.PAGE_TSCONFIG_ID = ' . $userGroup,
    ];
    ExtensionManagementUtility::addPageTSConfig(implode(LF, $tsConfig));
} catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $exception) {
}

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1000] = 'EXT:clubdirectory/Resources/Private/Templates/Email';
