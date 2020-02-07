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
        'searchFields' => 'title,activity,contact_person,email,description,user,tags',
        'iconfile' => 'EXT:clubdirectory/Resources/Public/Icons/tx_clubdirectory_domain_model_club.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, sort_title, activity, contact_person, contact_times, email, website, members, club_home, description, fe_users, logo, images, facebook, twitter, google, tags, district, addresses'
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.palettes.language;language,
             --palette--;;titleAndHidden,
             members, description, activity, contact_times, 
             --palette--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.palettes.contact;contact,
             --palette--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.palettes.location;location,
             fe_users,
             --div--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.tabs.media,
             logo, images,
             --div--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.tabs.social_media,
             facebook, twitter, google, tags,
             --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
             --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access'
        ]
    ],
    'palettes' => [
        'language' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource'],
        'titleAndHidden' => ['showitem' => 'title, hidden, --linebreak--, sort_title'],
        'contact' => ['showitem' => 'contact_person, --linebreak--, email, website'],
        'location' => ['showitem' => 'club_home, district, --linebreak--, addresses'],
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ]
    ],

    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => false,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_clubdirectory_domain_model_club',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'cruser_id' => [
            'label' => 'cruser_id',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 16,
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 16,
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
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
                'renderType' => 'inputLink',
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
                'softref' => 'typolink_tag,images,email[subst],url',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],
        'fe_users' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.feUsers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
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
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_clubdirectory_domain_model_district',
                'foreign_table_where' => 'ORDER BY tx_clubdirectory_domain_model_district.district',
                'items' => [
                    ['', 0]
                ],
                'default' => 0,
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
