<?php
namespace JWeiland\Checkfaluploads\Hooks;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DocumentTemplate
 *
 * @package checkFalUpload
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DocumentTemplate
{

    /**
     * add checkbox where user can decide if he has the rights for the files, he wants to upload
     *
     * @param array $parameters
     * @param \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate
     * @return void
     */
    public function addCheckboxForRights(
        array $parameters,
        \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate
    ) {
        if (GeneralUtility::_GET('route') === '/wizard/record/browse' && GeneralUtility::_GET('mode') === 'file') {
            /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
            $pageRenderer = $parameters['pageRenderer'];
            $pageRenderer->addInlineLanguageLabelFile('EXT:checkfaluploads/Resources/Private/Language/locallang.xlf');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser');
        }
    }

}