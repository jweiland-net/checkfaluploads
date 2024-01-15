<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\EventListener;

use JWeiland\Checkfaluploads\Helper\MessageHelper;
use JWeiland\Checkfaluploads\Traits\ApplicationContextTrait;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Resource\Event\BeforeFileAddedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFileReplacedEvent;
use TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Check if user has checked the checkbox which indicates that the user has the rights to upload these files
 */
class UserMarkedCheckboxForRightsEventListener
{
    use ApplicationContextTrait;

    /**
     * @var ExtendedFileUtility
     */
    protected $extendedFileUtility;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    public function __construct(
        ExtendedFileUtility $extendedFileUtility,
        MessageHelper $messageHelper
    ) {
        $this->extendedFileUtility = $extendedFileUtility;
        $this->messageHelper = $messageHelper;
    }

    /**
     * @throws InsufficientUserPermissionsException
     */
    public function checkForAddedFile(BeforeFileAddedEvent $event): void
    {
        // FE will not be checked here. This should be part of the extension itself.
        if ($this->isBackendRequest()) {
            $fileParts = GeneralUtility::split_fileref($event->getFileName());
            if (!in_array($fileParts['fileext'], ['youtube', 'vimeo'], true)) {
                $userHasRights = GeneralUtility::_POST('userHasRights');
                if (empty($userHasRights)) {
                    $message = LocalizationUtility::translate(
                        'error.uploadFile.missingRights',
                        'Checkfaluploads'
                    );

                    $this->extendedFileUtility->writeLog(1, 1, 105, $message, []);

                    $this->messageHelper->addFlashMessage($message, '', AbstractMessage::ERROR);

                    throw new InsufficientUserPermissionsException($message, 1396626278);
                }
            }
        }
    }

    /**
     * @throws InsufficientUserPermissionsException
     */
    public function checkForReplacedFile(BeforeFileReplacedEvent $event): void
    {
        // FE will not be checked here. This should be part of the extension itself.
        if ($this->isBackendRequest()) {
            $fileParts = GeneralUtility::split_fileref($event->getFile()->getName());
            if (!in_array($fileParts['fileext'], ['youtube', 'vimeo'])) {
                $userHasRights = GeneralUtility::_POST('userHasRights');
                if (empty($userHasRights)) {
                    $message = LocalizationUtility::translate(
                        'error.uploadFile.missingRights',
                        'Checkfaluploads'
                    );

                    $this->extendedFileUtility->writeLog(1, 1, 105, $message, []);

                    $this->messageHelper->addFlashMessage($message, '', AbstractMessage::ERROR);

                    throw new InsufficientUserPermissionsException($message, 1396626278);
                }
            }
        }
    }
}
