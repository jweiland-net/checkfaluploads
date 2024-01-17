<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'checkFalUploads',
    'description' => 'Displays a checkbox that must be checked to upload files in backend',
    'category' => 'be',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '4.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.8-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
