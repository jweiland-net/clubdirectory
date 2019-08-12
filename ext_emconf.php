<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Club Directory',
    'description' => 'clubdirectory',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '1',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
            'maps2' => '5.0.0-5.99.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
