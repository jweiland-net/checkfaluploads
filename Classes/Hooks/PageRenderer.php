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

/**
 * Class PageRenderer
 *
 * @package checkFalUpload
 */
class PageRenderer
{
    /**
     * replace DragUploader with own version
     *
     * @param array $parameters
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
     */
    public function replaceDragUploader(array $parameters, \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer)
    {
        if (isset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader'])) {
            unset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader']);
            $pageRenderer->addInlineLanguageLabelFile('EXT:checkfaluploads/Resources/Private/Language/locallang.xlf');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Checkfaluploads/DragUploader');
        }
    }
}
