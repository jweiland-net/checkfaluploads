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
    /**
     * Checks a collection (logo or images array) for valid uploads and rights.
     * Validates file upload permissions for a specified field in the provided data array.
     *
     * @param array $modelOrFieldDataArray An array containing the model or field data.
     * @param string $propertyName The name of the property in the data array that contains file information.
     * @param string $fieldName The name of the field to validate file permissions for. Default is 'rights'.
     * @param string $langKey A language key for the error message to be returned in case of a validation failure.
     * Default is 'error.uploadFile.missingRights'.
     * @param string $extensionName The name of the extension used for validation services.
     * Default is 'checkfaluploads'.
     * @return ?Error Returns an Error object if validation fails, or null if all validations pass.
     */
    public function checkFile(
        array $modelOrFieldDataArray,
        string $propertyName,
        string $fieldName = 'rights',
        string $langKey = 'error.uploadFile.missingRights',
        string $extensionName = 'checkfaluploads',
    ): ?Error {
        // Here it will be either logo or images
        $fileCollectionProperty = $modelOrFieldDataArray[$propertyName] ?? [];
        // Verify the checkbox is checked
        $rightsConfiguration = $this->getFileUploadRightsConfiguration($fileCollectionProperty);

        // Get all UploadedFile objects (For Logo it is single, but images it is multiple)
        $uploadedFiles = $this->getUploadedFiles($fileCollectionProperty);

        // Here the UploadedFile and Rights Configuration validated with EXT:checkfalupload service
        foreach ($uploadedFiles as $uploadedFileObject) {
            $error = $this->checkFileUploadPermissions(
                $uploadedFileObject,
                $rightsConfiguration,
                $fieldName,
                $langKey,
                $extensionName,
            );

            if ($error instanceof Error) {
                return $error;
            }
        }

        return null;
    }

    public function checkFileUploadPermissions(
        UploadedFile $uploadedFile,
        ?array $rightsConfiguration,
        string $fieldName = 'rights',
        string $langKey = 'error.uploadFile.missingRights',
        string $extensionName = 'checkfaluploads',
    ): ?Error {
        // Check if the file has an upload error
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return new Error(
                sprintf(
                    '%s: %s',
                    LocalizationUtility::translate('error.uploadFile.invalidFile', $extensionName) ?? 'Invalid file',
                    $this->getUploadErrorMessage($uploadedFile->getError()),
                ),
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

        // Check the rightsConfigurations set for the field or not
        if (!isset($rightsConfiguration[$fieldName])
            || $rightsConfiguration[$fieldName] === ''
            || $rightsConfiguration[$fieldName] === 0
        ) {
            return new Error(
                LocalizationUtility::translate($langKey, $extensionName),
                1604050225,
            );
        }

        return null;
    }

    private function getUploadedFiles(array $uploadedDataArray): array
    {
        return array_filter($uploadedDataArray, static fn($item) => $item instanceof UploadedFile);
    }

    private function getFileUploadRightsConfiguration(array $uploadedDataArray): ?array
    {
        foreach ($uploadedDataArray as $uploadedDataRight) {
            if (is_array($uploadedDataRight)
                && (isset($uploadedDataRight['rights']) && $uploadedDataRight['rights'])
            ) {
                return $uploadedDataRight;
            }
        }

        return null;
    }

    public function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }
}
