<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    );
    $extConf = $extensionConfiguration->get('clubdirectory');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'JWeiland.clubdirectory',
        'web',    // Make module a submodule of 'web'
        'club',    // Submodule key
        '',    // Position
        [
            'Export' => 'index'
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

    $tsConfig = [];
    $tsConfig[] = 'TCEFORM.tx_clubdirectory_domain_model_club.categories.PAGE_TSCONFIG_ID = ' . (int)$extConf['rootCategory'];
    $tsConfig[] = 'TCEFORM.tx_clubdirectory_domain_model_club.fe_users.PAGE_TSCONFIG_ID = ' . (int)$extConf['userGroup'];

    // following line was not used in current system. So it should not crash somewhere else.
    $tsConfig[] = 'TCEFORM.tt_content.pi_flexform.PAGE_TSCONFIG_ID = ' . (int)$extConf['rootCategory'];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(implode(LF, $tsConfig));
});
