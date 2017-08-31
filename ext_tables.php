<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// hook to replace original TYPO3 DragUploader with my own implementation
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = 'JWeiland\\Checkfaluploads\\Hooks\\PageRenderer->replaceDragUploader';

// Check if user has checked the checkbox (has rights to upload files)
/** @var $dispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
$dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$dispatcher->connect('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', 'preFileAdd', 'JWeiland\\Checkfaluploads\\Hooks\\ResourceStorage', 'checkFalUploads');

// add checkbox for rights in element browser
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preHeaderRenderHook'][] = 'JWeiland\\Checkfaluploads\\Hooks\\DocumentTemplate->addCheckboxForRights';

// add BE or FE user to image record
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_db.php']['queryProcessors'][] = 'JWeiland\\Checkfaluploads\\Hooks\\DatabaseConnection';