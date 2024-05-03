<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'checkFalUploads',
    'description' => 'Displays a checkbox that must be checked to upload files in backend',
    'category' => 'be',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '3.0.7',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.33-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
