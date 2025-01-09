<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['clubdirectory_clubdirectory'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['clubdirectory_clubdirectory'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'clubdirectory_clubdirectory',
    'FILE:EXT:clubdirectory/Configuration/FlexForms/ClubDirectory.xml'
);

ExtensionUtility::registerPlugin(
    'Clubdirectory',
    'Clubdirectory',
    'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:plugin.clubdirectory.title'
);
