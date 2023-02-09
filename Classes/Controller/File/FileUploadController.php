<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Controller\File;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add Checkbox to give usage rights to another person/company
 */
class FileUploadController extends \TYPO3\CMS\Filelist\Controller\File\FileUploadController
{
    /**
     * Add Checkbox to original upload form of TYPO3
     */
    protected function renderUploadFormInternal(): string
    {
        return '
            <div class="form-group">
                <input type="file" multiple="multiple" class="form-control" name="upload_1[]" />
                <input type="hidden" name="data[upload][1][target]" value="' . htmlspecialchars($this->folderObject->getCombinedIdentifier()) . '" />
                <input type="hidden" name="data[upload][1][data]" value="1" />
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="userHasRights" id="userHasRights" value="1" />
                <label class="form-check-label" for="userHasRights"> ' . htmlspecialchars($this->getExtConf()->getLabelForUserRights()) . '</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="overwriteExistingFiles" id="overwriteExistingFiles" value="replace" />
                <label class="form-check-label" for="overwriteExistingFiles"> ' . htmlspecialchars($this->getLanguageService()->getLL('overwriteExistingFiles')) . '</label>
            </div>
            <div>
                <input type="hidden" name="data[upload][1][redirect]" value="' . $this->returnUrl . '" />
                <input class="btn btn-primary" type="submit" value="' . htmlspecialchars($this->getLanguageService()->sL('LLL:EXT:filelist/Resources/Private/Language/locallang.xlf:file_upload.php.submit')) . '" />
            </div>
            <div class="callout callout-warning">
              ' . htmlspecialchars($this->getLanguageService()->getLL('uploadMultipleFilesInfo')) . '
            </div>
        ';
    }

    protected function getExtConf(): ExtConf
    {
        return GeneralUtility::makeInstance(ExtConf::class);
    }
}
