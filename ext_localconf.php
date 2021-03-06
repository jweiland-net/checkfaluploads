<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

call_user_func(function () {
    // Add checkbox for rights
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
        = \JWeiland\Checkfaluploads\Hooks\PageRendererHook::class . '->replaceDragUploader';

    /** @var $dispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
    );

    // Check if user has checked the checkbox (has rights to upload files)
    $dispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceStorage::class,
        \TYPO3\CMS\Core\Resource\ResourceStorage::SIGNAL_PreFileAdd,
        \JWeiland\Checkfaluploads\Slots\ResourceStorage::class,
        'checkFalUploads'
    );

    // add BE or FE user to FAL record
    $dispatcher->connect(
        \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class,
        'recordCreated',
        \JWeiland\Checkfaluploads\Slots\FileIndexRepository::class,
        'addUserToRecord'
    );
    $dispatcher->connect(
        \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class,
        'recordUpdated',
        \JWeiland\Checkfaluploads\Slots\FileIndexRepository::class,
        'addUserToRecord'
    );

});
