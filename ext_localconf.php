<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function($extKey) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JWeiland.' . $extKey,
        'Clubdirectory',
        [
            'Club' => 'list, listMyClubs, show, new, create, edit, update, search',
            'Map' => 'new, create, edit, update'
        ],
        // non-cacheable actions
        [
            'Club' => 'create, update, search',
            'Map' => 'create, update'
        ]
    );

    // use hook to automatically add a map record to current yellow page
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \JWeiland\Clubdirectory\Tca\CreateMap::class;
}, $_EXTKEY);
