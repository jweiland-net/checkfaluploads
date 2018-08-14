<?php
namespace JWeiland\Checkfaluploads\Slots;

/*
 * This file is part of the checkfaluploads project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Slot for ResourceStorage
 *
 * @package checkFalUpload
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ResourceStorage
{
    /**
     * Check if user has checked the checkbox, which indicated that the user has the rights to upload these files
     *
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
    ) {
        // FE will not be checked here. This should be part of the extension itself.
        if (TYPO3_MODE === 'BE') {
            $fileParts = GeneralUtility::split_fileref($targetFileName);
            if (!in_array($fileParts['fileext'], ['youtube', 'vimeo'])) {
                $userHasRights = GeneralUtility::_POST('userHasRights');
                if (empty($userHasRights)) {
                    $message = 'It is not allowed to upload files, as long as the checkbox for file rights is not checked';

                    /** @var ExtendedFileUtility $extendedFileUtility */
                    $extendedFileUtility = GeneralUtility::makeInstance(ExtendedFileUtility::class);
                    $extendedFileUtility->writeLog(1, 1, 105, $message, []);
                    $this->addMessageToFlashMessageQueue($message);

                    throw new InsufficientUserPermissionsException($message, 1396626278);
                }
            }
        }
    }

    /**
     * Adds a localized FlashMessage to the message queue
     *
     * @param string $message
     * @param int $severity
     * @throws \InvalidArgumentException
     */
    protected function addMessageToFlashMessageQueue($message, $severity = FlashMessage::ERROR)
    {
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

    /**
     * Add flash message to message queue
     *
     * @param FlashMessage $flashMessage
     */
    protected function addFlashMessage(FlashMessage $flashMessage)
    {
        /** @var $flashMessageService FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);

        /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
