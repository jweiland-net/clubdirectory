<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Clubdirectory',
        'Clubdirectory',
        [
            \JWeiland\Clubdirectory\Controller\ClubController::class => 'list, listMyClubs, show, new, create, edit, update, search, activate',
            \JWeiland\Clubdirectory\Controller\MapController::class => 'new, create, edit, update'
        ],
        // non-cacheable actions
        [
            \JWeiland\Clubdirectory\Controller\ClubController::class => 'create, update, search, activate',
            \JWeiland\Clubdirectory\Controller\MapController::class => 'create, update'
        ]
    );

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-clubdirectory-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:clubdirectory/Resources/Public/Icons/' . $fileName]
        );
    }

    // Add clubdirectory plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:clubdirectory/Configuration/TSconfig/ContentElementWizard.txt">'
    );

    try {
        $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
        $rootCategory = (int)$extConf->get('clubdirectory', 'rootCategory');
        $userGroup = (int)$extConf->get('clubdirectory', 'userGroup');

        // @ToDo: rootUid is deprecated since TYPO3 11. Replace while removing TYPO3 10 compatibility
        $tsConfig = [
            'TCEFORM.tt_content.pi_flexform.clubdirectory_clubdirectory.sDEFAULT.settings\.category.PAGE_TSCONFIG_ID = ' . $rootCategory,
            'TCEFORM.tx_clubdirectory_domain_model_club.categories.config.treeConfig.rootUid = ' . $rootCategory,
            'TCEFORM.tx_clubdirectory_domain_model_club.fe_users.PAGE_TSCONFIG_ID = ' . $userGroup
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(implode(LF, $tsConfig));
    } catch (\TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException | \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException $exception) {
        // do nothing
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['clubdirectoryUpdateSlug']
        = \JWeiland\Clubdirectory\Updater\ClubdirectorySlugUpdater::class;

    $typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
    if (version_compare($typo3Version->getBranch(), '11.0', '<')) {
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );

        // update poiCollection record while saving club records
        $signalSlotDispatcher->connect(
            \JWeiland\Maps2\Hook\CreateMaps2RecordHook::class,
            'postUpdatePoiCollection',
            \JWeiland\Clubdirectory\Hook\UpdateMaps2RecordHook::class,
            'postUpdatePoiCollection'
        );
    }
});
