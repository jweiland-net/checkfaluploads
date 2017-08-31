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
class DocumentTemplate {

	/**
	 * add checkbox where user can decide if he has the rights for the files, he wants to upload
	 *
	 * @param array $parameters
	 * @param \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate
	 * @return void
	 */
	public function addCheckboxForRights(array $parameters, \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate) {
		if ($_SERVER['PHP_SELF'] === '/typo3/mod.php' && GeneralUtility::_GET('M') === 'wizard_element_browser' && GeneralUtility::_GET('mode') === 'file') {
			$documentTemplate->getPageRenderer()->addInlineLanguageLabelFile('EXT:checkfaluploads/Resources/Private/Language/locallang.xlf');
			$documentTemplate->getPageRenderer()->loadJquery();
			$documentTemplate->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser');
		}
	}

}