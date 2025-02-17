<?php

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    $GLOBALS['TCA']['tx_clubdirectory_domain_model_club']['columns']['categories'] = [
        'config' => [
            'type' => 'category',
        ],
    ];

    ExtensionManagementUtility::addToAllTCAtypes(
        'tx_clubdirectory_domain_model_club',
        'categories',
        '',
        'before:notes',
    );
});
