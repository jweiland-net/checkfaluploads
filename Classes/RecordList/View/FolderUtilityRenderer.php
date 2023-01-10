<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\RecordList\View;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Add userHasRights checkbox to FileBrowser PopUp
 */
class FolderUtilityRenderer extends \TYPO3\CMS\Recordlist\View\FolderUtilityRenderer
{
    /**
     * Makes an upload form for uploading files to the filemount the user is browsing.
     * The files are uploaded to the tce_file.php script in the core which will handle the upload.
     *
     * @param string[] $allowedExtensions
     */
    public function uploadForm(Folder $folderObject, array $allowedExtensions): string
    {
        if (!$folderObject->checkActionPermission('write')) {
            return '';
        }
        // Read configuration of upload field count
        $count = (int)($this->getBackendUser()->getTSConfig()['options.']['folderTree.']['uploadFieldsInLinkBrowser'] ?? 1);
        if ($count === 0) {
            return '';
        }

        // Create header, showing upload path:
        $header = $folderObject->getIdentifier();
        $lang = $this->getLanguageService();
        // Create a list of allowed file extensions with the readable format "youtube, vimeo" etc.
        $fileExtList = [];
        $fileNameVerifier = GeneralUtility::makeInstance(FileNameValidator::class);
        foreach ($allowedExtensions as $fileExt) {
            if ($fileNameVerifier->isValid('.' . $fileExt)) {
                $fileExtList[] = '<span class="label label-success">'
                    . strtoupper(htmlspecialchars($fileExt)) . '</span>';
            }
        }
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $formAction = (string)$uriBuilder->buildUriFromRoute('tce_file');
        $combinedIdentifier = $folderObject->getCombinedIdentifier();
        $markup = [];
        $markup[] = '<form action="' . htmlspecialchars($formAction)
            . '" method="post" name="editform" enctype="multipart/form-data">';
        $markup[] = '   <h3>' . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:file_upload.php.pagetitle')) . ':</h3>';
        $markup[] = '   <p><strong>' . htmlspecialchars($lang->getLL('path')) . ':</strong>' . htmlspecialchars($header) . '</p>';
        // Traverse the number of upload fields:
        for ($a = 1; $a <= $count; $a++) {
            $markup[] = '<div class="form-group">';
            $markup[] = '<span class="btn btn-default btn-file">';
            $markup[] = '<input type="file" multiple="multiple" name="upload_' . $a . '[]" size="50" />';
            $markup[] = '</span>';
            $markup[] = '</div>';
            $markup[] = '<input type="hidden" name="data[upload][' . $a . '][target]" value="'
                . htmlspecialchars($combinedIdentifier) . '" />';
            $markup[] = '<input type="hidden" name="data[upload][' . $a . '][data]" value="' . $a . '" />';
        }
        $redirectValue = $this->parameterProvider->getScriptUrl() . HttpUtility::buildQueryString(
            $this->parameterProvider->getUrlParameters(['identifier' => $combinedIdentifier]),
            '&'
        );
        $markup[] = '<input type="hidden" name="data[upload][1][redirect]" value="' . htmlspecialchars($redirectValue) . '" />';

        if (!empty($fileExtList)) {
            $markup[] = '<div class="form-group">';
            $markup[] = '    <label>';
            $markup[] = htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.allowedFileExtensions')) . '<br/>';
            $markup[] = '    </label>';
            $markup[] = '    <div>';
            $markup[] = implode(' ', $fileExtList);
            $markup[] = '    </div>';
            $markup[] = '</div>';
        }

        // SF: Add checkbox for FileBrowser PopUp
        $markup[] = '<div class="checkbox">';
        $markup[] = '    <label>';
        $markup[] = '    <input type="checkbox" name="userHasRights" id="userHasRights" value="1" />';
        $markup[] = htmlspecialchars($this->getLabelForUserRights());
        $markup[] = '    </label>';
        $markup[] = '</div>';

        $markup[] = '<div class="checkbox">';
        $markup[] = '    <label>';
        $markup[] = '    <input type="checkbox" name="overwriteExistingFiles" id="overwriteExistingFiles" value="replace" />';
        $markup[] = htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_misc.xlf:overwriteExistingFiles'));
        $markup[] = '    </label>';
        $markup[] = '</div>';

        $markup[] = '<input class="btn btn-default" type="submit" name="submit" value="'
            . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:file_upload.php.submit')) . '" />';

        $markup[] = '</form>';

        $code = implode(LF, $markup);

        // Add online media
        // Create a list of allowed file extensions in a readable format "youtube, vimeo" etc.
        $fileExtList = [];
        $onlineMediaFileExt = OnlineMediaHelperRegistry::getInstance()->getSupportedFileExtensions();
        foreach ($onlineMediaFileExt as $fileExt) {
            if ($fileNameVerifier->isValid('.' . $fileExt)
                && (empty($allowedExtensions) || in_array($fileExt, $allowedExtensions, true))
            ) {
                $fileExtList[] = '<span class="label label-success">'
                    . strtoupper(htmlspecialchars($fileExt)) . '</span>';
            }
        }
        if (!empty($fileExtList)) {
            $formAction = (string)$uriBuilder->buildUriFromRoute('online_media');

            $markup = [];
            $markup[] = '<form action="' . htmlspecialchars($formAction)
                . '" method="post" name="editform1" id="typo3-addMediaForm" enctype="multipart/form-data">';
            $markup[] = '<h3>' . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media')) . ':</h3>';
            $markup[] = '<p><strong>' . htmlspecialchars($lang->getLL('path')) . ':</strong>' . htmlspecialchars($header) . '</p>';
            $markup[] = '<div class="form-group">';
            $markup[] = '<input type="hidden" name="data[newMedia][0][target]" value="'
                . htmlspecialchars($folderObject->getCombinedIdentifier()) . '" />';
            $markup[] = '<input type="hidden" name="data[newMedia][0][allowed]" value="'
                . htmlspecialchars(implode(',', $allowedExtensions)) . '" />';
            $markup[] = '<div class="input-group">';
            $markup[] = '<input type="text" name="data[newMedia][0][url]" class="form-control" placeholder="'
                . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media.placeholder')) . '" />';
            $markup[] = '<div class="input-group-btn">';
            $markup[] = '<button class="btn btn-default">'
                . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media.submit')) . '</button>';
            $markup[] = '</div>';
            $markup[] = '</div>';
            $markup[] = '<div class="form-group">';
            $markup[] = '<label>';
            $markup[] = $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media.allowedProviders') . '<br/>';
            $markup[] = '</label>';
            $markup[] = '<div>';
            $markup[] = implode(' ', $fileExtList);
            $markup[] = '</div>';
            $markup[] = '</div>';
            $markup[] = '<input type="hidden" name="redirect" value="' . htmlspecialchars($redirectValue) . '" />';
            $markup[] = '</form>';

            $code .= implode(LF, $markup);
        }

        return $code;
    }

    protected function getLabelForUserRights(): string
    {
        return LocalizationUtility::translate(
            'dragUploader.fileRights.title',
            'checkfaluploads',
            [0 => $this->getOwner()]
        );
    }

    protected function getOwner(): string
    {
        return $this->getExtConf()->getOwner();
    }

    protected function getExtConf(): ExtConf
    {
        return GeneralUtility::makeInstance(ExtConf::class);
    }
}
