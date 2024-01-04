<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Clubdirectory',
        'Clubdirectory',
        [
            \JWeiland\Clubdirectory\Controller\ClubController::class => 'list, listMyClubs, show, new, create, edit, update, search, activate',
            \JWeiland\Clubdirectory\Controller\MapController::class => 'new, create, edit, update',
        ],
        // non-cacheable actions
        [
            \JWeiland\Clubdirectory\Controller\ClubController::class => 'create, update, search, activate',
            \JWeiland\Clubdirectory\Controller\MapController::class => 'create, update',
        ]
    );

    // Add clubdirectory plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:clubdirectory/Configuration/TSconfig/ContentElementWizard.txt">'
    );

    try {
        $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
        $rootCategory = (int)$extConf->get('clubdirectory', 'rootCategory');
        $userGroup = (int)$extConf->get('clubdirectory', 'userGroup');

        $tsConfig = [
            'TCEFORM.tt_content.pi_flexform.clubdirectory_clubdirectory.sDEFAULT.settings\.category.PAGE_TSCONFIG_ID = ' . $rootCategory,
            'TCEFORM.tx_clubdirectory_domain_model_club.categories.config.treeConfig.startingPoints = ' . $rootCategory,
            'TCEFORM.tx_clubdirectory_domain_model_club.fe_users.PAGE_TSCONFIG_ID = ' . $userGroup,
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(implode(LF, $tsConfig));
    } catch (\TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException | \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException $exception) {
        // do nothing
    }

    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1000] = 'EXT:clubdirectory/Resources/Private/Templates/Email';
});
