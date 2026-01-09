<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Service;

use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Extbase\Error\Error;

/**
 * EXT:checkfaluploads checks, if the user of the image upload has marked the checkbox to transfer the user rights
 * of the image to the owner of the website.
 * Instead of implementing this code in many of our JW extensions we simply check, if checkfaluploads is loaded
 * and call this Service to return an Extbase Error, if checkbox was not marked.
 *
 * @deprecated
 * FalUploadService is deprecated. File rights are now handled via PSR-15 middleware in EXT:checkfaluploads.
 */
class FalUploadService
{
    public function __construct()
    {
        trigger_error(
            'FalUploadService is deprecated. File rights are now handled via PSR-15 middleware in EXT:checkfaluploads.',
            E_USER_DEPRECATED
        );
    }

    public function checkFile(
        UploadedFile $uploadedFile,
        ?array $rightsConfiguration,
        string $fieldName = 'rights',
        string $langKey = 'error.uploadFile.missingRights',
        string $extensionName = 'checkfaluploads',
    ): ?Error {

        return null;
    }
}
