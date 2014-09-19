<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Clubdirectory',
	'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:plugin.title'
);

// load tt_content to $TCA array and add flexform
$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName) . '_clubdirectory';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/ClubDirectory.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Club Directory');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_clubdirectory_domain_model_club', 'EXT:clubdirectory/Resources/Private/Language/locallang_csh_tx_clubdirectory_domain_model_club.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_clubdirectory_domain_model_club');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_clubdirectory_domain_model_address', 'EXT:clubdirectory/Resources/Private/Language/locallang_csh_tx_clubdirectory_domain_model_address.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_clubdirectory_domain_model_address');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_clubdirectory_domain_model_district', 'EXT:clubdirectory/Resources/Private/Language/locallang_csh_tx_clubdirectory_domain_model_district.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_clubdirectory_domain_model_district');

$extConf = unserialize($_EXTCONF);
$tsConfig = array();
$tsConfig[] = 'ext.clubdirectory.pid = ' . (integer) $extConf['poiCollectionPid'];
$tsConfig[] = 'TCEFORM.tx_clubdirectory_domain_model_club.categories.PAGE_TSCONFIG_ID = ' . (integer) $extConf['rootCategory'];
$tsConfig[] = 'TCEFORM.tx_clubdirectory_domain_model_club.fe_users.PAGE_TSCONFIG_ID = ' . (integer) $extConf['userGroup'];
// following line was not used in current system. So it should not crash somewhere else.
$tsConfig[] = 'TCEFORM.tt_content.pi_flexform.PAGE_TSCONFIG_ID = ' . (integer) $extConf['rootCategory'];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(implode(LF, $tsConfig));