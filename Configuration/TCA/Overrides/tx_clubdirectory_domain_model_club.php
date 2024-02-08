<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    $GLOBALS['TCA']['tx_clubdirectory_domain_model_club']['columns']['categories'] = [
        'config' => [
            'type' => 'category'
        ]
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tx_clubdirectory_domain_model_club',
        'categories',
        '',
        'before:notes'
    );
});
