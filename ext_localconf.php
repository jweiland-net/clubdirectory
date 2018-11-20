<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JWeiland.clubdirectory',
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
