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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Tree\View\LinkParameterProviderInterface;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add the userHasRights checkbox to FileBrowser PopUp
 */
final class FolderUtilityRenderer extends \TYPO3\CMS\Backend\View\FolderUtilityRenderer
{
    public function __construct(
        LinkParameterProviderInterface $parameterProvider,
        private ExtConf $extConf,
    ) {
        parent::__construct($parameterProvider);
    }

    /**
     * Makes an upload form for uploading files to the file mount the user is browsing.
     * The files are uploaded to the tce_file.php script in the core which will handle the upload.
     *
     * @return string HTML for an upload form.
     */
    public function uploadForm(ServerRequestInterface $request, Folder $folderObject, ?FileExtensionFilter $fileExtensionFilter = null)
    {
        if (!$folderObject->checkActionPermission('write')) {
            return '';
        }
        // Read configuration of upload field count
        $allowUpload = (bool)($this->getBackendUser()->getTSConfig()['options.']['folderTree.']['uploadFieldsInLinkBrowser'] ?? true);
        if (!$allowUpload) {
            return '';
        }

        $list = ['*'];
        $denyList = false;
        $allowedOnlineMediaList = [];
        $lang = $this->getLanguageService();

        if ($fileExtensionFilter !== null) {
            $resolvedFileExtensions = $fileExtensionFilter->getFilteredFileExtensions();
            if (($resolvedFileExtensions['allowedFileExtensions'] ?? []) !== []) {
                $list = $resolvedFileExtensions['allowedFileExtensions'];
            } elseif (($resolvedFileExtensions['disallowedFileExtensions'] ?? []) !== []) {
                $denyList = true;
                $list = $resolvedFileExtensions['disallowedFileExtensions'];
            }
        }

        $fileNameVerifier = GeneralUtility::makeInstance(FileNameValidator::class);
        foreach ($list as $fileExt) {
            if (($fileExt === '*' && !$denyList) || $fileNameVerifier->isValid('.' . $fileExt)) {
                $allowedOnlineMediaList[] = '<li class="badge badge-' . ($denyList ? 'danger' : 'success') . '">' . strtoupper(htmlspecialchars($fileExt)) . '</li>';
            }
        }
        $markup = [];
        if (!empty($allowedOnlineMediaList)) {
            $markup[] = '<div class="row">';
            $markup[] = '    <label>';
            $markup[] = htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.' . ($denyList ? 'disallowedFileExtensions' : 'allowedFileExtensions'))) . '<br/>';
            $markup[] = '    </label>';
            $markup[] = '    <ul>' . implode(' ', $allowedOnlineMediaList) . '</ul>';
            $markup[] = '</div>';
        }

        $formAction = (string)$this->uriBuilder->buildUriFromRoute('tce_file');
        $combinedIdentifier = $folderObject->getCombinedIdentifier();
        $redirectValue = (string)$this->uriBuilder->buildUriFromRequest($request, $this->parameterProvider->getUrlParameters(['identifier' => $combinedIdentifier]));
        $markup[] = '<form class="pt-3 pb-3" action="' . htmlspecialchars($formAction) . '" method="post" name="editform" enctype="multipart/form-data">';
        $markup[] = '<input type="hidden" name="data[upload][0][target]" value="' . htmlspecialchars($combinedIdentifier) . '" />';
        $markup[] = '<input type="hidden" name="data[upload][0][data]" value="0" />';
        $markup[] = '<input type="hidden" name="redirect" value="' . htmlspecialchars($redirectValue) . '" />';
        $markup[] = '<div class="row">';
        $markup[] = '<div class="col-auto me-auto">';
        $markup[] = '   <h4>' . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:file_upload.php.pagetitle')) . '</h4>';
        $markup[] = '</div>';

        // SF: Add checkbox for FileBrowser PopUp
        $markup[] = '<div class="col-auto">';
        $markup[] = '<div class="form-check form-switch">';
        $markup[] = '    <input class="form-check-input" type="checkbox" name="userHasRights" id="userHasRights" value="1" />';
        $markup[] = '    <label>';
        $markup[] =          htmlspecialchars($this->extConf->getLabelForUserRights());
        $markup[] = '    </label>';
        $markup[] = '</div>';
        $markup[] = '</div>';

        $markup[] = '<div class="col-auto">';
        $markup[] = '<div class="form-check form-switch">';
        $markup[] = '    <input class="form-check-input" type="checkbox" name="overwriteExistingFiles" id="overwriteExistingFiles" value="replace" />';
        $markup[] = '    <label class="form-check-label" for="overwriteExistingFiles">';
        $markup[] =          htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_misc.xlf:overwriteExistingFiles'));
        $markup[] = '    </label>';
        $markup[] = '</div>';
        $markup[] = '</div>';
        $markup[] = '<div class="col-12">';
        $markup[] = '<div class="input-group">';
        $markup[] = '<input type="file" multiple="multiple" name="upload_0[]" class="form-control" />';
        $markup[] = '<input class="btn btn-default" type="submit" name="submit" value="'
            . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:file_upload.php.submit')) . '" />';
        $markup[] = '</div>';
        $markup[] = '</div>';

        $markup[] = '</div>';
        $markup[] = '</form>';

        $code = implode(LF, $markup);

        // Add online media
        // Create a list of allowed file extensions in a readable format "youtube, vimeo" etc.
        $allowedOnlineMediaList = [];
        foreach (GeneralUtility::makeInstance(OnlineMediaHelperRegistry::class)->getSupportedFileExtensions() as $supportedFileExtension) {
            if ($fileNameVerifier->isValid('.' . $supportedFileExtension)
                && ($fileExtensionFilter === null || $fileExtensionFilter->isAllowed($supportedFileExtension))
            ) {
                $allowedOnlineMediaList[$supportedFileExtension] = '<li class="badge badge-success">' . strtoupper(htmlspecialchars($supportedFileExtension)) . '</li>';
            }
        }
        if (!empty($allowedOnlineMediaList)) {
            $formAction = (string)$this->uriBuilder->buildUriFromRoute('online_media');

            $markup = [];
            $markup[] = '<form class="pt-3 pb-3" action="' . htmlspecialchars($formAction) . '" method="post" name="editform1" id="typo3-addMediaForm" enctype="multipart/form-data">';
            $markup[] = '<input type="hidden" name="redirect" value="' . htmlspecialchars($redirectValue) . '" />';
            $markup[] = '<input type="hidden" name="data[newMedia][0][target]" value="' . htmlspecialchars($folderObject->getCombinedIdentifier()) . '" />';
            $markup[] = '<input type="hidden" name="data[newMedia][0][allowed]" value="' . htmlspecialchars(implode(',', array_keys($allowedOnlineMediaList))) . '" />';
            $markup[] = '<h4>' . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media')) . '</h4>';
            $markup[] = '<div class="row">';
            $markup[] = '<div class="col">';
            $markup[] = '<div class="input-group">';
            $markup[] = '<input type="url" name="data[newMedia][0][url]" class="form-control" placeholder="'
                . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media.placeholder')) . '" />';
            $markup[] = '<button class="btn btn-default">'
                . htmlspecialchars($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media.submit')) . '</button>';
            $markup[] = '</div>';
            $markup[] = '</div>';
            $markup[] = '<div class="row mt-1">';
            $markup[] = '<div class="col-auto">';
            $markup[] = $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:online_media.new_media.allowedProviders');
            $markup[] = '</div>';
            $markup[] = '<ul>';
            $markup[] = implode(' ', $allowedOnlineMediaList);
            $markup[] = '</ul>';
            $markup[] = '</div>';
            $markup[] = '</div>';
            $markup[] = '</form>';

            $code .= implode(LF, $markup);
        }

        return $code;
    }
}
