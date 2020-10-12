<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks;

/**
 * Replace DragUploader with our own version.
 * We have to check, if JS of Core has changed in the meanwhile.
 */
class PageRenderer
{
    public function replaceDragUploader(
        array $parameters,
        \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
    ): void {
        if (isset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader'])) {
            unset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader']);
            $pageRenderer->addInlineLanguageLabelFile('EXT:checkfaluploads/Resources/Private/Language/locallang.xlf');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Checkfaluploads/DragUploader');
        }
    }
}
