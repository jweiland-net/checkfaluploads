<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

call_user_func(static function (): void {
    // Add checkbox for rights
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
        = \JWeiland\Checkfaluploads\Hooks\PageRendererHook::class . '->replaceDragUploader';

    // Update (replace placeholders) label and description of Checkboxes
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'][1661929818]
        = \JWeiland\Checkfaluploads\Hooks\Form\ReplacePlaceholderHook::class;
    // Register dynamic validation which depends on other submitted form element values (image -> upload-rights)
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1661258175]
        = \JWeiland\Checkfaluploads\Hooks\Form\DynamicUploadValidatorHook::class;
});
