<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'clubdirectory',
        'tx_clubdirectory_domain_model_club'
    );
});
