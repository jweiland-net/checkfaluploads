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
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownDivider;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownRadio;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownToggle;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Filelist\ElementBrowser\CreateFolderBrowser;
use TYPO3\CMS\Filelist\Type\ViewMode;

/**
 * Disable CSS class "t3js-drag-uploader-trigger" to prevent loading DragUploader modal.
 */
class FileListController extends \TYPO3\CMS\Filelist\Controller\FileListController
{
    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     */
    protected function registerAdditionalDocHeaderButtons(ServerRequestInterface $request): void
    {
        $lang = $this->getLanguageService();
        $buttonBar = $this->view->getDocHeaderComponent()->getButtonBar();

        // Refresh
        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref($request->getAttribute('normalizedParams')->getRequestUri())
            ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);

        // ViewMode
        $viewModeItems = [];
        $viewModeItems[] = GeneralUtility::makeInstance(DropDownRadio::class)
            ->setActive($this->moduleData->get('viewMode') === ViewMode::TILES->value)
            ->setHref($this->filelist->createModuleUri(['viewMode' => ViewMode::TILES->value]))
            ->setLabel($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.view.tiles'))
            ->setIcon($this->iconFactory->getIcon('actions-viewmode-tiles'));
        $viewModeItems[] = GeneralUtility::makeInstance(DropDownRadio::class)
            ->setActive($this->moduleData->get('viewMode') === ViewMode::LIST->value)
            ->setHref($this->filelist->createModuleUri(['viewMode' => ViewMode::LIST->value]))
            ->setLabel($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.view.list'))
            ->setIcon($this->iconFactory->getIcon('actions-viewmode-list'));
        $viewModeItems[] = GeneralUtility::makeInstance(DropDownDivider::class);
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['thumbnails'] && ($this->getBackendUser()->getTSConfig()['options.']['file_list.']['enableDisplayThumbnails'] ?? '') === 'selectable') {
            $viewModeItems[] = GeneralUtility::makeInstance(DropDownToggle::class)
                ->setActive((bool)$this->moduleData->get('displayThumbs'))
                ->setHref($this->filelist->createModuleUri(['displayThumbs' => $this->moduleData->get('displayThumbs') ? 0 : 1]))
                ->setLabel($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.view.showThumbnails'))
                ->setIcon($this->iconFactory->getIcon('actions-image'));
        }
        $viewModeItems[] = GeneralUtility::makeInstance(DropDownToggle::class)
            ->setActive((bool)$this->moduleData->get('clipBoard'))
            ->setHref($this->filelist->createModuleUri(['clipBoard' => $this->moduleData->get('clipBoard') ? 0 : 1]))
            ->setLabel($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.view.showClipboard'))
            ->setIcon($this->iconFactory->getIcon('actions-clipboard'));
        if (($this->getBackendUser()->getTSConfig()['options.']['file_list.']['displayColumnSelector'] ?? true)
            && $this->moduleData->get('viewMode') === ViewMode::LIST->value) {
            $viewModeItems[] = GeneralUtility::makeInstance(DropDownDivider::class);
            $viewModeItems[] = GeneralUtility::makeInstance(DropDownItem::class)
                ->setTag('typo3-backend-column-selector-button')
                ->setLabel($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.view.selectColumns'))
                ->setAttributes([
                    'data-url' => $this->uriBuilder->buildUriFromRoute(
                        'ajax_show_columns_selector',
                        ['id' => $this->id, 'table' => '_FILE']
                    ),
                    'data-target' => $this->filelist->createModuleUri(),
                    'data-title' => sprintf(
                        $lang->sL('LLL:EXT:backend/Resources/Private/Language/locallang_column_selector.xlf:showColumnsSelection'),
                        $lang->sL($GLOBALS['TCA']['sys_file']['ctrl']['title'] ?? ''),
                    ),
                    'data-button-ok' => $lang->sL('LLL:EXT:backend/Resources/Private/Language/locallang_column_selector.xlf:updateColumnView'),
                    'data-button-close' => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.cancel'),
                    'data-error-message' => $lang->sL('LLL:EXT:backend/Resources/Private/Language/locallang_column_selector.xlf:updateColumnView.error'),
                ])
                ->setIcon($this->iconFactory->getIcon('actions-options'));
        }
        $viewModeButton = $buttonBar->makeDropDownButton()
            ->setLabel($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.view'))
            ->setShowLabelText(true);
        foreach ($viewModeItems as $viewModeItem) {
            $viewModeButton->addItem($viewModeItem);
        }
        $buttonBar->addButton($viewModeButton, ButtonBar::BUTTON_POSITION_RIGHT, 2);

        // Level up
        try {
            $currentStorage = $this->folderObject->getStorage();
            $parentFolder = $this->folderObject->getParentFolder();
            if ($currentStorage->isWithinFileMountBoundaries($parentFolder)
                && $parentFolder->getIdentifier() !== $this->folderObject->getIdentifier()
                && $parentFolder instanceof Folder
            ) {
                $levelUpButton = $buttonBar->makeLinkButton()
                    ->setDataAttributes([
                        'tree-update-request' => htmlspecialchars('folder' . GeneralUtility::md5int($parentFolder->getCombinedIdentifier())),
                    ])
                    ->setHref(
                        (string)$this->uriBuilder->buildUriFromRoute(
                            'media_management',
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
            ->setRouteIdentifier('media_management')
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
        if ($this->folderObject && $this->folderObject->checkActionPermission('write') && $this->folderObject->checkActionPermission('add')) {
            $newButton = $buttonBar->makeLinkButton()
                ->setClasses('t3js-element-browser')
                ->setHref((string)$this->uriBuilder->buildUriFromRoute('wizard_element_browser'))
                ->setDataAttributes([
                    'identifier' => $this->folderObject->getCombinedIdentifier(),
                    'mode' => CreateFolderBrowser::IDENTIFIER,
                ])
                ->setShowLabelText(true)
                ->setTitle($lang->sL('LLL:EXT:filelist/Resources/Private/Language/locallang.xlf:actions.create_folder'))
                ->setIcon($this->iconFactory->getIcon('actions-folder-add', Icon::SIZE_SMALL));
            $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 3);
        }

        // New file button
        if ($this->folderObject && $this->folderObject->checkActionPermission('write')
            && $this->folderObject->getStorage()->checkUserActionPermission('add', 'File')
        ) {
            $newButton = $buttonBar->makeLinkButton()
                ->setHref((string)$this->uriBuilder->buildUriFromRoute(
                    'file_create',
                    [
                        'target' => $this->folderObject->getCombinedIdentifier(),
                        'returnUrl' => $this->filelist->createModuleUri(),
                    ]
                ))
                ->setShowLabelText(true)
                ->setTitle($lang->sL('LLL:EXT:filelist/Resources/Private/Language/locallang.xlf:actions.create_file'))
                ->setIcon($this->iconFactory->getIcon('actions-file-add', Icon::SIZE_SMALL));
            $buttonBar->addButton($newButton, ButtonBar::BUTTON_POSITION_LEFT, 4);
        }

        // Add paste button if clipboard is initialized
        if ($this->filelist->clipObj instanceof Clipboard && $this->folderObject->checkActionPermission('write')) {
            $elFromTable = $this->filelist->clipObj->elFromTable('_FILE');
            if (!empty($elFromTable)) {
                $addPasteButton = true;
                $elToConfirm = [];
                foreach ($elFromTable as $key => $element) {
                    $clipBoardElement = $this->resourceFactory->retrieveFileOrFolderObject($element);
                    if ($clipBoardElement instanceof Folder && $clipBoardElement->getStorage()->isWithinFolder(
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
                    $buttonBar->addButton($pasteButton, ButtonBar::BUTTON_POSITION_LEFT, 10);
                }
            }
        }
    }
}
