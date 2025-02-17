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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * EXT:checkfaluploads checks, if the user of the image upload has marked the checkbox to transfer the user rights
 * of the image to the owner of the website.
 * Instead of implementing this code in many of our JW extensions we simply check, if checkfaluploads is loaded
 * and call this Service to return an Extbase Error, if checkbox was not marked.
 */
class FalUploadService
{
    public function checkFile(
        UploadedFile $uploadedFile,
        string $fieldName = 'rights',
        string $langKey = 'error.uploadFile.missingRights',
        string $extensionName = 'checkfaluploads',
    ): ?Error {
        // Check if the file has an upload error
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return new Error(
                LocalizationUtility::translate('error.uploadFile.invalidFile', $extensionName) . ': ' . $this->getUploadErrorMessage($uploadedFile->getError()),
                1604050226,
            );
        }

        // Check if the uploaded file has content (i.e., is not empty)
        if ($uploadedFile->getSize() === 0) {
            return new Error(
                LocalizationUtility::translate('error.uploadFile.emptyFile', $extensionName),
                1604050227,
            );
        }

        // Check if the file contains the required "rights" metadata
        // Since UploadedFile doesn't contain arbitrary fields like `$fieldName`, adapt logic accordingly
        if ($fieldName === 'rights') {
            // Example logic for checking rights; adjust based on your needs
            $stream = $uploadedFile->getStream();
            $stream->rewind();
            $fileContents = $stream->getContents();
            $stream->close();

            if (!str_contains($fileContents, 'rights')) {
                return new Error(
                    LocalizationUtility::translate($langKey, $extensionName),
                    1604050225,
                );
            }
        }

        return null;
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload.';
            default:
                return 'Unknown upload error.';
        }
    }
}
