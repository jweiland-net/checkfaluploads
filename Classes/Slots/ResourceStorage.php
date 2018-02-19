<?php
namespace JWeiland\Checkfaluploads\Slots;

/*
 * This file is part of the TYPO3 CMS project.
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

use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
                    throw new InsufficientUserPermissionsException(
                        'You are not allowed to upload files as long as you are not the owner of these files',
                        1396626278
                    );
                }
            }
        }
    }
}
