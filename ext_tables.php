<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}


call_user_func(static function () {
    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    );
    $extConf = $extensionConfiguration->get('clubdirectory');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Clubdirectory',
        'web',    // Make module a submodule of 'web'
        'club',    // Submodule key
        '',    // Position
        [
            \JWeiland\Clubdirectory\Controller\ExportController::class => 'index'
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:clubdirectory/Resources/Public/Icons/Extension.svg',
            'labels' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_export.xlf'
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_clubdirectory_domain_model_club',
        'EXT:clubdirectory/Resources/Private/Language/locallang_csh_tx_clubdirectory_domain_model_club.xlf'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_clubdirectory_domain_model_address',
        'EXT:clubdirectory/Resources/Private/Language/locallang_csh_tx_clubdirectory_domain_model_address.xlf'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_clubdirectory_domain_model_district',
        'EXT:clubdirectory/Resources/Private/Language/locallang_csh_tx_clubdirectory_domain_model_district.xlf'
    );
});
