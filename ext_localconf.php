<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JWeiland.clubdirectory',
        'Clubdirectory',
        [
            'Club' => 'list, listMyClubs, show, new, create, edit, update, search, activate',
            'Map' => 'new, create, edit, update'
        ],
        // non-cacheable actions
        [
            'Club' => 'create, update, search, activate',
            'Map' => 'create, update'
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

    // add clubdirectory plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:clubdirectory/Configuration/TSconfig/ContentElementWizard.txt">');

    if (version_compare(TYPO3_branch, '9.4', '>=')) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['clubdirectoryUpdateSlug'] = \JWeiland\Clubdirectory\Updater\ClubdirectorySlugUpdater::class;
    }

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    // update poiCollection record while saving club records
    $signalSlotDispatcher->connect(
        \JWeiland\Maps2\Hook\CreateMaps2RecordHook::class,
        'postUpdatePoiCollection',
        \JWeiland\Clubdirectory\Hook\UpdateMaps2RecordHook::class,
        'postUpdatePoiCollection'
    );
});
