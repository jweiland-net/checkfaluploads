<?php
namespace JWeiland\Checkfaluploads\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <sfroemken@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package checkFalUpload
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ResourceStorage {

	/**
	 * Check if user has checked the checkbox, which indicated that the user has the rights to upload these files
	 *
	 * @param string $targetFileName
	 * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
	 * @param string $sourceFilePath
	 * @param \TYPO3\CMS\Core\Resource\ResourceStorage $parentObject
	 * @param \TYPO3\CMS\Core\Resource\Driver\LocalDriver $driver
	 * @return array
	 * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException
	 * @throws \Exception
	 */
	public function checkFalUploads($targetFileName, \TYPO3\CMS\Core\Resource\Folder $targetFolder, $sourceFilePath, \TYPO3\CMS\Core\Resource\ResourceStorage $parentObject, \TYPO3\CMS\Core\Resource\Driver\LocalDriver $driver) {
		// FE will not be checked here. This should be part of the extension itself.
		if (TYPO3_MODE === 'BE') {
			$userHasRights = GeneralUtility::_POST('userHasRights');
			if (empty($userHasRights)) {
				throw new \TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException('You are not allowed to upload files as long as you are not the owner of these files', 1396626278);
			}
		}
	}

}