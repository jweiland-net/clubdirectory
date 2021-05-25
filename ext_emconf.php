<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Club Directory',
    'description' => 'Manage and display clubs',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '5.0.7',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'maps2' => '9.3.0-0.0.0',
            'glossary2' => '4.0.0-0.0.0'
        ],
        'conflicts' => [],
        'suggests' => [
            'checkfaluploads' => ''
        ]
    ]
];
