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

/**
 * @package checkFalUpload
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageRenderer {

	/**
	 * replace DragUploader with own version
	 *
	 * @param array $parameters
	 * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
	 */
	public function replaceDragUploader(array $parameters, \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer) {
		if (isset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader'])) {
			unset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader']);
			$pageRenderer->addInlineLanguageLabelFile('EXT:checkfaluploads/Resources/Private/Language/locallang.xlf');
			$pageRenderer->loadRequireJsModule('TYPO3/CMS/Checkfaluploads/DragUploader');
		}
	}

}