<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add checkbox to ElementBrowser where user can decide, if he has the rights for the files he wants to upload
 */
class DocumentTemplate
{
    public function addCheckboxForRights(
        array $parameters,
        \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate
    ): void {
        if (
            GeneralUtility::_GET('route') === '/wizard/record/browse'
            && GeneralUtility::_GET('mode') === 'file'
        ) {
            /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
            $pageRenderer = $parameters['pageRenderer'];
            $pageRenderer->addInlineLanguageLabelFile(
                'EXT:checkfaluploads/Resources/Private/Language/locallang.xlf'
            );
            $pageRenderer->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser'
            );
        }
    }
}
