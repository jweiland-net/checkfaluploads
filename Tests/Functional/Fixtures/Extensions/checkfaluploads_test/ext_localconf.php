<?php

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\CheckfaluploadsTest\Controller\UploadController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionUtility::configurePlugin(
    'CheckfaluploadsTest',
    'Upload',
    [UploadController::class => ['new', 'create']],
    [UploadController::class => ['new', 'create']],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);
