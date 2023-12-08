<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Club Directory',
    'description' => 'Manage and display clubs',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '7.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.33-12.4.99',
            'maps2' => '10.0.0-0.0.0',
            'glossary2' => '6.0.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [
        ],
    ],
];
