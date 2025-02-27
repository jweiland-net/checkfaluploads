<?php

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

use JWeiland\Checkfaluploads\Controller\FileListController as CheckfaluploadsFileListController;
use JWeiland\Checkfaluploads\Hooks\Form\DynamicUploadValidatorHook;
use JWeiland\Checkfaluploads\Hooks\Form\ReplacePlaceholderHook;
use JWeiland\Checkfaluploads\RecordList\View\FolderUtilityRenderer as CheckfaluploadsFolderUtilityRenderer;
use TYPO3\CMS\Backend\View\FolderUtilityRenderer;
use TYPO3\CMS\Filelist\Controller\FileListController;

call_user_func(static function (): void {
    // Disable CSS class "t3js-drag-uploader-trigger" to prevent loading DragUploader modal.
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FileListController::class]['className'] = CheckfaluploadsFileListController::class;

    // Add userHasRights checkbox to FileBrowser PopUp
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FolderUtilityRenderer::class]['className'] = CheckfaluploadsFolderUtilityRenderer::class;

    // Update (replace placeholders) label and description of Checkboxes
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'][1661929818] = ReplacePlaceholderHook::class;
    // Register dynamic validation which depends on other submitted form element values (image -> upload-rights)
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1661258175] = DynamicUploadValidatorHook::class;
});
