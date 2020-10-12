<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add checkbox to filelist and ElementBrowser where user can decide,
 * if he has the rights for the files he wants to upload.
 *
 * We will replace the DragUploader of TYPO3 completely.
 * We have to check, if JS of Core has changed in the meanwhile.
 */
class PageRenderer
{
    public function replaceDragUploader(
        array $parameters,
        \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
    ): void {
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

    protected function addLanguageFile(\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer): void
    {
        $pageRenderer->addInlineLanguageLabelFile(
            'EXT:checkfaluploads/Resources/Private/Language/locallang.xlf'
        );
    }

    protected function addOwnerToTypo3Settings(\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer): void
    {
        $pageRenderer->addInlineSetting(
            'checkfaluploads',
            'owner',
            $this->getOwner()
        );
    }

    protected function getOwner(): string
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        return (string)$extensionConfiguration->get('checkfaluploads', 'owner');
    }
}
