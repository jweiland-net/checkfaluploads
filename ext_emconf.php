<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'checkFalUploads',
    'description' => 'Displays a checkbox that must be checked to upload files in backend',
    'category' => 'be',
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '2.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.29-10.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
