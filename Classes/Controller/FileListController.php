<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Controller;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Disable CSS class "t3js-drag-uploader-trigger" to prevent loading DragUploader modal.
 */
class FileListController extends \TYPO3\CMS\Filelist\Controller\FileListController
{
    /**
     * Disable CSS class "t3js-drag-uploader-trigger" for TYPO3 11
     */
    protected function registerAdditionalDocHeaderButtons(ServerRequestInterface $request): void
    {
        $lang = $this->getLanguageService();
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        // Refresh
        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref($request->getAttribute('normalizedParams')->getRequestUri())
            ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);

        // Level up
        try {
            $currentStorage = $this->folderObject->getStorage();
            $parentFolder = $this->folderObject->getParentFolder();
            if ($currentStorage->isWithinFileMountBoundaries($parentFolder)
                && $parentFolder->getIdentifier() !== $this->folderObject->getIdentifier()
            ) {
                $levelUpButton = $buttonBar->makeLinkButton()
                    ->setDataAttributes([
                        'tree-update-request' => htmlspecialchars('folder' . GeneralUtility::md5int($parentFolder->getCombinedIdentifier())),
                    ])
                    ->setHref(
                        (string)$this->uriBuilder->buildUriFromRoute(
                            'file_FilelistList',
                            ['id' => $parentFolder->getCombinedIdentifier()]
                        )
                    )
                    ->setShowLabelText(true)
                    ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.upOneLevel'))
                    ->setIcon($this->iconFactory->getIcon('actions-view-go-up', Icon::SIZE_SMALL));
                $buttonBar->addButton($levelUpButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
            }
        } catch (\Exception $e) {
        }

        // Shortcut
        $shortCutButton = $buttonBar->makeShortcutButton()
            ->setRouteIdentifier('file_FilelistList')
            ->setDisplayName(sprintf(
                '%s: %s',
                $lang->sL('LLL:EXT:filelist/Resources/Private/Language/locallang_mod_file_list.xlf:mlang_tabs_tab'),
                $this->folderObject->getName() ?: $this->folderObject->getIdentifier()
            ))
            ->setArguments(array_filter([
                'id' => $this->id,
                'searchTerm' => $this->searchTerm,
            ]));
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT);

        // Upload button (only if upload to this directory is allowed)
        if ($this->folderObject
            && $this->folderObject->checkActionPermission('write')
            && $this->folderObject->getStorage()->checkUserActionPermission('add', 'File')
        ) {
            $uploadButton = $buttonBar->makeLinkButton()
                ->setHref($this->getFileUploadUrl())
                //->setClasses('t3js-drag-uploader-trigger')
                ->setShowLabelText(true)
                ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.upload'))
                ->setIcon($this->iconFactory->getIcon('actions-edit-upload', Icon::SIZE_SMALL));
            $buttonBar->addButton($uploadButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
        }

        // New folder button
        if ($this->folderObject && $this->folderObject->checkActionPermission('write')
            && (
                $this->folderObject->getStorage()->checkUserActionPermission(
                    'add',
                    'File'
                )
                || $this->folderObject->checkActionPermission('add')
            )
        ) {
            $newButton = $buttonBar->makeLinkButton()
                ->setHref((string)$this->uriBuilder->buildUriFromRoute(
                    'file_newfolder',
                    [
                        'target' => $this->folderObject->getCombinedIdentifier(),
                        'returnUrl' => $this->filelist->listURL(),
                    ]
                ))
                ->setShowLabelText(true)
                ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.new'))
                ->setIcon($this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL));
            $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
        }

        // Add paste button if clipboard is initialized
        if ($this->filelist->clipObj instanceof Clipboard && $this->folderObject->checkActionPermission('write')) {
            $elFromTable = $this->filelist->clipObj->elFromTable('_FILE');
            if (!empty($elFromTable)) {
                $addPasteButton = true;
                $elToConfirm = [];
                foreach ($elFromTable as $key => $element) {
                    $clipBoardElement = $this->resourceFactory->retrieveFileOrFolderObject($element);
                    if (
                        $clipBoardElement instanceof Folder
                        && $clipBoardElement->getStorage()->isWithinFolder(
                            $clipBoardElement,
                            $this->folderObject
                        )
                    ) {
                        $addPasteButton = false;
                    }
                    $elToConfirm[$key] = $clipBoardElement->getName();
                }
                if ($addPasteButton) {
                    $confirmText = $this->filelist->clipObj
                        ->confirmMsgText('_FILE', $this->folderObject->getReadablePath(), 'into', $elToConfirm);
                    $pastButtonTitle = $lang->sL('LLL:EXT:filelist/Resources/Private/Language/locallang_mod_file_list.xlf:clip_paste');
                    $pasteButton = $buttonBar->makeLinkButton()
                        ->setHref($this->filelist->clipObj
                            ->pasteUrl('_FILE', $this->folderObject->getCombinedIdentifier()))
                        ->setClasses('t3js-modal-trigger')
                        ->setDataAttributes([
                            'severity' => 'warning',
                            'bs-content' => $confirmText,
                            'title' => $pastButtonTitle,
                        ])
                        ->setShowLabelText(true)
                        ->setTitle($pastButtonTitle)
                        ->setIcon($this->iconFactory->getIcon('actions-document-paste-into', Icon::SIZE_SMALL));
                    $buttonBar->addButton($pasteButton, ButtonBar::BUTTON_POSITION_LEFT, 4);
                }
            }
        }
    }

    /**
     * Disable CSS class "t3js-drag-uploader-trigger" for TYPO3 10
     */
    protected function registerButtons()
    {
        /** @var ButtonBar $buttonBar */
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();

        /** @var IconFactory $iconFactory */
        $iconFactory = $this->view->getModuleTemplate()->getIconFactory();

        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

        $lang = $this->getLanguageService();

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        // Refresh page
        $refreshLink = GeneralUtility::linkThisScript(
            [
                'target' => rawurlencode($this->folderObject->getCombinedIdentifier()),
                'imagemode' => $this->filelist->thumbs
            ]
        );
        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref($refreshLink)
            ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);

        // Level up
        try {
            $currentStorage = $this->folderObject->getStorage();
            $parentFolder = $this->folderObject->getParentFolder();
            if ($parentFolder->getIdentifier() !== $this->folderObject->getIdentifier()
                && $currentStorage->isWithinFileMountBoundaries($parentFolder)
            ) {
                $levelUpButton = $buttonBar->makeLinkButton()
                    ->setDataAttributes([
                        'tree-update-request' => htmlspecialchars('folder' . GeneralUtility::md5int($parentFolder->getCombinedIdentifier())),
                    ])
                    ->setHref((string)$uriBuilder->buildUriFromRoute('file_FilelistList', ['id' => $parentFolder->getCombinedIdentifier()]))
                    ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.upOneLevel'))
                    ->setIcon($iconFactory->getIcon('actions-view-go-up', Icon::SIZE_SMALL));
                $buttonBar->addButton($levelUpButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
            }
        } catch (\Exception $e) {
        }

        // Shortcut
        if ($this->getBackendUser()->mayMakeShortcut()) {
            $shortCutButton = $buttonBar->makeShortcutButton()->setModuleName('file_FilelistList');
            $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT);
        }

        // Upload button (only if upload to this directory is allowed)
        if (
            $this->folderObject
            && $this->folderObject->getStorage()->checkUserActionPermission(
                'add',
                'File'
            )
            && $this->folderObject->checkActionPermission('write')
        ) {
            $uploadButton = $buttonBar->makeLinkButton()
                ->setHref((string)$uriBuilder->buildUriFromRoute(
                    'file_upload',
                    [
                        'target' => $this->folderObject->getCombinedIdentifier(),
                        'returnUrl' => $this->filelist->listURL(),
                    ]
                ))
                //->setClasses('t3js-drag-uploader-trigger')
                ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.upload'))
                ->setIcon($iconFactory->getIcon('actions-edit-upload', Icon::SIZE_SMALL));
            $buttonBar->addButton($uploadButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
        }

        // New folder button
        if (
            $this->folderObject
            && $this->folderObject->checkActionPermission('write')
            && (
                $this->folderObject->getStorage()->checkUserActionPermission(
                    'add',
                    'File'
                )
                || $this->folderObject->checkActionPermission('add')
            )
        ) {
            $newButton = $buttonBar->makeLinkButton()
                ->setHref((string)$uriBuilder->buildUriFromRoute(
                    'file_newfolder',
                    [
                        'target' => $this->folderObject->getCombinedIdentifier(),
                        'returnUrl' => $this->filelist->listURL(),
                    ]
                ))
                ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.new'))
                ->setIcon($iconFactory->getIcon('actions-add', Icon::SIZE_SMALL));
            $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
        }

        // Add paste button if clipboard is initialized
        if ($this->filelist->clipObj instanceof Clipboard && $this->folderObject->checkActionPermission('write')) {
            $elFromTable = $this->filelist->clipObj->elFromTable('_FILE');
            if (!empty($elFromTable)) {
                $addPasteButton = true;
                $elToConfirm = [];
                foreach ($elFromTable as $key => $element) {
                    $clipBoardElement = $resourceFactory->retrieveFileOrFolderObject($element);
                    if (
                        $clipBoardElement instanceof Folder
                        && $clipBoardElement->getStorage()->isWithinFolder(
                            $clipBoardElement,
                            $this->folderObject
                        )
                    ) {
                        $addPasteButton = false;
                    }
                    $elToConfirm[$key] = $clipBoardElement->getName();
                }
                if ($addPasteButton) {
                    $confirmText = $this->filelist->clipObj
                        ->confirmMsgText('_FILE', $this->folderObject->getReadablePath(), 'into', $elToConfirm);
                    $pasteButton = $buttonBar->makeLinkButton()
                        ->setHref($this->filelist->clipObj
                            ->pasteUrl('_FILE', $this->folderObject->getCombinedIdentifier()))
                        ->setClasses('t3js-modal-trigger')
                        ->setDataAttributes([
                            'severity' => 'warning',
                            'content' => $confirmText,
                            'title' => $lang->getLL('clip_paste')
                        ])
                        ->setTitle($lang->getLL('clip_paste'))
                        ->setIcon($iconFactory->getIcon('actions-document-paste-into', Icon::SIZE_SMALL));
                    $buttonBar->addButton($pasteButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
                }
            }
        }
    }
}
