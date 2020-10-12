<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Slots;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Check if user has checked the checkbox which indicates that the user has the rights to upload these files
 */
class ResourceStorage
{
    /**
     * @param string $targetFileName
     * @param Folder $targetFolder
     * @param string $sourceFilePath
     * @param \TYPO3\CMS\Core\Resource\ResourceStorage $parentObject
     * @param LocalDriver $driver
     * @return void
     * @throws InsufficientUserPermissionsException
     */
    public function checkFalUploads(
        $targetFileName,
        Folder $targetFolder,
        $sourceFilePath,
        \TYPO3\CMS\Core\Resource\ResourceStorage $parentObject,
        LocalDriver $driver
    ): void {
        // FE will not be checked here. This should be part of the extension itself.
        if (TYPO3_MODE === 'BE') {
            $fileParts = GeneralUtility::split_fileref($targetFileName);
            if (!in_array($fileParts['fileext'], ['youtube', 'vimeo'])) {
                $userHasRights = GeneralUtility::_POST('userHasRights');
                if (empty($userHasRights)) {
                    $message = LocalizationUtility::translate(
                        'error.uploadFile.missingRights',
                        'Checkfaluploads'
                    );

                    $extendedFileUtility = GeneralUtility::makeInstance(ExtendedFileUtility::class);
                    $extendedFileUtility->writeLog(1, 1, 105, $message, []);

                    $this->addMessageToFlashMessageQueue($message);
                    throw new InsufficientUserPermissionsException($message, 1396626278);
                }
            }
        }
    }

    protected function addMessageToFlashMessageQueue(
        string $message,
        int $severity = FlashMessage::ERROR
    ): void {
        if (TYPO3_MODE !== 'BE') {
            return;
        }
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            '',
            $severity,
            true
        );
        $this->addFlashMessage($flashMessage);
    }

    protected function addFlashMessage(FlashMessage $flashMessage): void
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
