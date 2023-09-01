<?php

use JWeiland\Avalex\Utility\Typo3Utility;

if (!defined('TYPO3')) {
    die ('Access denied.');
}

call_user_func(static function (): void {
    // Disable CSS class "t3js-drag-uploader-trigger" to prevent loading DragUploader modal.
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Filelist\Controller\FileListController::class]['className']
        = \JWeiland\Checkfaluploads\Controller\FileListController::class;

    // Add Checkbox for image rights into replace file form
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Filelist\Controller\File\ReplaceFileController::class]['className']
        = \JWeiland\Checkfaluploads\Controller\File\ReplaceFileController::class;

    // Add userHasRights checkbox to FileBrowser PopUp
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Recordlist\View\FolderUtilityRenderer::class]['className']
        = \JWeiland\Checkfaluploads\RecordList\View\FolderUtilityRenderer::class;

    // Update (replace placeholders) label and description of Checkboxes
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'][1661929818]
        = \JWeiland\Checkfaluploads\Hooks\Form\ReplacePlaceholderHook::class;
    // Register dynamic validation which depends on other submitted form element values (image -> upload-rights)
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1661258175]
        = \JWeiland\Checkfaluploads\Hooks\Form\DynamicUploadValidatorHook::class;
});
