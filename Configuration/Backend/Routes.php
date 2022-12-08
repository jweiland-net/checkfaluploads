<?php

/**
 * Overwrite BE file_upload route to show our own Upload Form with an addition
 * checkbox to transfer access rights of a file to another person/company
 */
return [
    // Upload new files
    'file_upload' => [
        'path' => '/file/upload',
        'target' => \JWeiland\Checkfaluploads\Controller\File\FileUploadController::class . '::mainAction',
    ],
];
