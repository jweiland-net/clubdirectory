<?php

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Clubdirectory\Backend\Preview\ClubDirectoryPluginPreview;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::registerPlugin(
    'Clubdirectory',
    'Clubdirectory',
    'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:plugin.clubdirectory.title',
    'ext-clubdirectory-wizard-icon',
    'plugins',
    'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:plugin.clubdirectory.description',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:clubdirectory/Configuration/FlexForms/ClubDirectory.xml',
    'clubdirectory_clubdirectory',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Configuration,pi_flexform',
    'clubdirectory_clubdirectory',
    'after:subheader',
);

$GLOBALS['TCA']['tt_content']['types']['clubdirectory_clubdirectory']['previewRenderer'] = ClubDirectoryPluginPreview::class;
