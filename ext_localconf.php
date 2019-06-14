<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
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
