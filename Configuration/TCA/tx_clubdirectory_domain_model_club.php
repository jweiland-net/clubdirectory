<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'default_sortby' => 'ORDER BY title',

        'versioningWS' => 2,
        'versioning_followPages' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ],
        'searchFields' => 'title,activity,contact_person,email,website,members,club_home,description,user,logo,images,facebook,twitter,google,tags,',
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('clubdirectory').'Configuration/TCA/Club.php',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('clubdirectory').'Resources/Public/Icons/tx_clubdirectory_domain_model_club.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, sort_title, activity, contact_person, contact_times, email, website, members, club_home, description, fe_users, logo, images, facebook, twitter, google, tags, district, addresses'
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ]
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => 'tx_clubdirectory_domain_model_club',
                'foreign_table_where' => 'AND tx_clubdirectory_domain_model_club.pid=###CURRENT_PID### AND tx_clubdirectory_domain_model_club.sys_language_uid IN (-1,0)'
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check'
            ]
        ],
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ]
            ]
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ]
            ]
        ],
        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'sort_title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.sortTitle',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'activity' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.activity',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ]
        ],
        'contact_person' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.contactPerson',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'contact_times' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.contactTimes',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ]
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim, email'
            ]
        ],
        'website' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'actions-wizard-link',
                        'module' => [
                            'name' => 'wizard_link'
                        ],
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ]
        ],
        'members' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.members',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'club_home' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.clubHome',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'wizards' => [
                    'RTE' => [
                        'icon' => 'wizard_rte2.gif',
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'module' => 'wizard_rte',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:bodytext.W.RTE',
                        'type' => 'script'
                    ]
                ]
            ],
            'defaultExtras' => 'richtext:rte_transform[flag=rte_enabled|mode=ts]'
        ],
        'fe_users' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.feUsers',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'AND FIND_IN_SET(###PAGE_TSCONFIG_ID###, fe_users.usergroup) ORDER BY fe_users.username',
                'foreign_sortby' => 'sorting',
                'MM' => 'tx_clubdirectory_club_user_mm',
                'minitems' => 0,
                'maxitems' => 3,
                'size' => 5
            ]
        ],
        'logo' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.logo',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'logo',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
                    'foreign_match_fields' => [
                        'fieldname' => 'logo',
                        'tablenames' => 'tx_clubdirectory_domain_model_club',
                        'table_local' => 'sys_file'
                    ]
                ]
            )
        ],
        'images' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.images',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'images',
                [
                    'minitems' => 0,
                    'maxitems' => 5,
                    'foreign_match_fields' => [
                        'fieldname' => 'images',
                        'tablenames' => 'tx_clubdirectory_domain_model_club',
                        'table_local' => 'sys_file'
                    ]
                ]
            )
        ],
        'facebook' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.facebook',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'twitter' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.twitter',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'google' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.google',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'tags' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.tags',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'district' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.district',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'tx_clubdirectory_domain_model_district',
                'foreign_table_where' => 'ORDER BY tx_clubdirectory_domain_model_district.district',
                'items' => [
                    ['', '']
                ],
                'minitems' => 0,
                'maxitems' => 1
            ]
        ],
        'addresses' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.addresses',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_clubdirectory_domain_model_address',
                'foreign_field' => 'club',
                'minitems' => 3,
                'maxitems' => 3,
                'appearance' => [
                    'collapseAll' => true,
                    'newRecordLinkAddTitle' => true,
                    'levelLinksPosition' => 'both',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ]
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, title, sort_title, activity, contact_person, contact_times, email, website, members, club_home, description, fe_users, logo, images, facebook, twitter, google, tags, district, addresses,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime']
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ]
];
