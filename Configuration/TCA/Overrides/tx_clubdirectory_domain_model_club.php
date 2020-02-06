<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'clubdirectory',
        'tx_clubdirectory_domain_model_club'
    );

    if (version_compare(TYPO3_branch, '9.4', '>=')) {
        // Router configuration can not access sanitize() method of slugs, so we have to create our own column
        $GLOBALS['TCA']['tx_clubdirectory_domain_model_club']['columns']['path_segment'] = [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.path_segment',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    // Default fieldSeparator is / which is not allowed within path_segments
                    'fieldSeparator' => '-',
                    // As pageSlug may contain slashes, we have to remove page slug
                    'prefixParentPageSlug' => false,
                    'replacements' => [
                        '/' => '-'
                    ],
                ],
                'fallbackCharacter' => '-',
                // Do not add / in path_segments, as they are not allowed in RouteEnhancer configuration
                'prependSlash' => false,
                'eval' => 'uniqueInSite',
                'default' => ''
            ]
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'tx_clubdirectory_domain_model_club',
            'path_segment',
            '',
            'after:sort_title'
        );
        $GLOBALS['TCA']['tx_clubdirectory_domain_model_club']['interface']['showRecordFieldList'] .= ',path_segment';
    }
});
