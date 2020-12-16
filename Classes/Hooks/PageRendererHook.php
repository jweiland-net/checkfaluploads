<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add checkbox to filelist and ElementBrowser where user can decide,
 * if he has the rights for the files he wants to upload.
 *
 * We will replace the DragUploader of TYPO3 completely.
 * We have to check, if JS of Core has changed in the meanwhile.
 */
class PageRendererHook
{
    public function replaceDragUploader(
        array $parameters,
        PageRenderer $pageRenderer
    ): void {
        if (TYPO3_MODE === 'FE') {
            return;
        }

        $this->addLanguageFile($pageRenderer);
        $this->addOwnerToTypo3Settings($pageRenderer);

        // Replace TYPO3's DragUploader
        if (isset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader'])) {
            unset($parameters['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/DragUploader']);
            $pageRenderer->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/DragUploader'
            );
        }

        // Add Checkbox for owner rights to ElementBrowser
        if (
            GeneralUtility::_GET('route') === '/wizard/record/browse'
            && GeneralUtility::_GET('mode') === 'file'
        ) {
            $pageRenderer->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser'
            );
        }
    }

    protected function addLanguageFile(PageRenderer $pageRenderer): void
    {
        $pageRenderer->addInlineLanguageLabelFile(
            'EXT:checkfaluploads/Resources/Private/Language/locallang.xlf'
        );
    }

    protected function addOwnerToTypo3Settings(PageRenderer $pageRenderer): void
    {
        $pageRenderer->addInlineSetting(
            'checkfaluploads',
            'owner',
            $this->getOwner()
        );
    }

    protected function getOwner(): string
    {
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        return $extConf->getOwner();
    }
}
