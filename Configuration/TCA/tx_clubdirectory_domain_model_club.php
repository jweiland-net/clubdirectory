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
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('clubdirectory').'Resources/Public/Icons/tx_clubdirectory_domain_model_club.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, sort_title, activity, contact_person, contact_times, email, website, members, club_home, description, fe_users, logo, images, facebook, twitter, google, tags, district, addresses'
    ],
    'types' => [
        '1' => [
            'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, title, sort_title,
            activity, contact_person, contact_times, email, website, members, club_home, description, fe_users, logo,
            images, facebook, twitter, google, tags, district, addresses,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access'
        ]
    ],
    'palettes' => [
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ]
    ],

    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_clubdirectory_domain_model_club',
                'foreign_table_where' => 'AND tx_clubdirectory_domain_model_club.pid=###CURRENT_PID### AND tx_clubdirectory_domain_model_club.sys_language_uid IN (-1,0)',
                'showIconTable' => false,
                'default' => 0,
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255'
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:hidden.I.0'
                    ]
                ]
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => '13',
                'eval' => 'datetime',
                'default' => 0
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => '13',
                'eval' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ]
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'sort_title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.sortTitle',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'activity' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.activity',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ]
        ],
        'contact_person' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.contactPerson',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'contact_times' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.contactTimes',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ]
        ],
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim, email'
            ]
        ],
        'website' => [
            'exclude' => true,
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
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.members',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'club_home' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.clubHome',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'description' => [
            'exclude' => true,
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
            'exclude' => true,
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
            'exclude' => true,
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
            'exclude' => true,
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
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.facebook',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'twitter' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.twitter',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'google' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.google',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'tags' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.tags',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'district' => [
            'exclude' => true,
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
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.addresses',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_clubdirectory_domain_model_address',
                'foreign_field' => 'club',
                'minitems' => 0,
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
    ]
];
