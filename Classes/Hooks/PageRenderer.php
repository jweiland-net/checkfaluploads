<?php
namespace JWeiland\Checkfaluploads\Hooks;

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/**
 * Class PageRenderer
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
