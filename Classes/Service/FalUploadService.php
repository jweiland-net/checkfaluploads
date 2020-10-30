<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Service;

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * EXT:checkfaluploads checks, if the image uploader has marked the checkbox to transfer the user rights of the image
 * to the owner of the website.
 * Instead of implementing this code in many of our JW extensions we simply check, if checkfaluploads is loaded
 * and call this Service to return an Extbase Error, if checkbox was not marked.
 */
class FalUploadService
{
    public function checkFile(
        array $uploadedFile,
        string $fieldName = 'rights',
        string $langKey = 'error.uploadFile.missingRights',
        string $extensionName = 'checkfaluploads'
    ): ?Error {
        // When checkFile was called, we already have a pre-validated uploaded file
        if (!isset($uploadedFile[$fieldName]) || empty($uploadedFile[$fieldName])) {
            return new Error(
                LocalizationUtility::translate($langKey, $extensionName),
                1604050225
            );
        }
        return null;
    }
}
