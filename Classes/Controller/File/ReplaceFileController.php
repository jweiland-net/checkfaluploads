<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Controller\File;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Override renderContent method of ReplaceFileController
 * to add checkbox for image rights
 */
class ReplaceFileController extends \TYPO3\CMS\Filelist\Controller\File\ReplaceFileController
{
    protected function renderContent(): void
    {
        // Assign variables used by the fluid template
        $assigns = [];
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $assigns['moduleUrlTceFile'] = (string)$uriBuilder->buildUriFromRoute('tce_file');
        $assigns['uid'] = $this->uid;
        $assigns['returnUrl'] = $this->returnUrl;

        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        // csh button
        $cshButton = $buttonBar->makeHelpButton()
            ->setModuleName('xMOD_csh_corebe')
            ->setFieldName('file_rename');
        $buttonBar->addButton($cshButton);

        // Back button
        if ($this->returnUrl) {
            $returnButton = $buttonBar->makeLinkButton()
                ->setHref($this->returnUrl)
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.goBack'))
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-view-go-back', Icon::SIZE_SMALL));
            $buttonBar->addButton($returnButton);
        }

        // Rendering of the output via fluid
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths([GeneralUtility::getFileAbsFileName('EXT:backend/Resources/Private/Templates')]);
        $view->setPartialRootPaths([GeneralUtility::getFileAbsFileName('EXT:backend/Resources/Private/Partials')]);

        // SF: Modify path to our needs
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:checkfaluploads/Resources/Private/Templates/File/ReplaceFile.html'
        ));
        // SF: end

        $view->assignMultiple($assigns);
        $this->moduleTemplate->setContent($view->render());
    }
}
