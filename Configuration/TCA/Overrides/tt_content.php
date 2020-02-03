<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['clubdirectory_clubdirectory'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['clubdirectory_clubdirectory'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'clubdirectory_clubdirectory',
    'FILE:EXT:clubdirectory/Configuration/FlexForms/ClubDirectory.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JWeiland.clubdirectory',
    'Clubdirectory',
    'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:plugin.clubdirectory.title'
);
