<?php

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'ctrl' => [
        'title' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club',
        'label' => 'title',
        'descriptionColumn' => 'notes',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'default_sortby' => 'ORDER BY title',
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,activity,contact_person,email,description,user,tags',
        'iconfile' => 'EXT:clubdirectory/Resources/Public/Icons/tx_clubdirectory_domain_model_club.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.palettes.language;language,
             --palette--;;titleAndHidden, path_segment,
             activity, description, members, contact_times,
             --palette--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.palettes.contact;contact,
             --palette--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.palettes.location;location,
             fe_users,
             --div--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.tabs.media,
             logo, images,
             --div--;LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:clubdirectory.tabs.social_media,
             facebook, twitter, instagram, tags,
             --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
             --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
             --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories, categories,
             --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, notes',
        ],
    ],
    'palettes' => [
        'language' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource'],
        'titleAndHidden' => ['showitem' => 'title, hidden'],
        'contact' => ['showitem' => 'contact_person, --linebreak--, email, website'],
        'location' => ['showitem' => 'club_home, district, --linebreak--, addresses'],
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ],
    ],

    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_clubdirectory_domain_model_club',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'cruser_id' => [
            'label' => 'cruser_id',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel',
            'config' => [
                'type' => 'datetime',
                'size' => 16,
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
            'config' => [
                'type' => 'datetime',
                'size' => 16,
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'path_segment' => [
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.path_segment',
            'displayCond' => 'VERSION:IS:false',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    // Do not add pageSlug, as we add pageSlug on our own in RouteEnhancer
                    'prefixParentPageSlug' => false,
                    'fieldSeparator' => '-',
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
                'default' => '',
            ],
        ],
        'activity' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.activity',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'contact_person' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.contactPerson',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'contact_times' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.contactTimes',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim, email',
            ],
        ],
        'website' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'renderType' => 'inputLink',
                'softref' => 'typolink',
            ],
        ],
        'members' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.members',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'club_home' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.clubHome',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
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
            ],
        ],
        'fe_users' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.feUsers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'AND (###PAGE_TSCONFIG_ID### = 0 OR FIND_IN_SET(###PAGE_TSCONFIG_ID###, fe_users.usergroup)) ORDER BY fe_users.username',
                'foreign_sortby' => 'sorting',
                'MM' => 'tx_clubdirectory_club_user_mm',
                'minitems' => 0,
                'maxitems' => 3,
                'size' => 5,
            ],
        ],
        'logo' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.logo',
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'images' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.images',
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 5,
                'allowed' => 'common-image-types',
            ],
        ],
        'facebook' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.facebook',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'twitter' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.twitter',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'instagram' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.instagram',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'tags' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubdirectory/Resources/Private/Language/locallang_db.xlf:tx_clubdirectory_domain_model_club.tags',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
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
                    ['label' => '', 'value' => 0],
                ],
                'default' => 0,
                'minitems' => 0,
                'maxitems' => 1,
            ],
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
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'notes' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 40,
            ],
        ],
    ],
];
