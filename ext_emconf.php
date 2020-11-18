<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'checkFalUploads',
    'description' => 'Displays a checkbox that must be checked to upload files in backend',
    'category' => 'be',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.20-10.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
