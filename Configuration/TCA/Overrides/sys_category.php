<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['clubdirectory']);
if ($extConf['fallbackIconPath']) {
    $fallbackIconPath = $extConf['fallbackIconPath'];
} else {
    $fallbackIconPath = '/uploads/tx_clubdirectory/';
}

$tempColumns = [
    'icon' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:sys_category.icon',
        'config' => [
            'type' => 'group',
            'internal_type' => 'file',
            'uploadfolder' => $fallbackIconPath,
            'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
            'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
            'show_thumbs' => true,
            'size' => 5,
            'maxitems' => 1,
            'minitems' => 0
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'icon', '1', 'after:description');
unset($tempColumns);
