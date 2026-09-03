<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Middleware;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Module\ModuleInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Loads the JS module which extends the backend DragUploader with a "user has rights" check.
 *
 * TYPO3's "id" request parameter is overloaded: in most backend modules it is a page uid, but
 * the File List module reuses it for the currently opened folder (e.g. "1:/Extensions/events2/").
 * TYPO3\CMS\Backend\View\BackendViewFactory::create() only resolves page TSconfig - and with it
 * any "templates.typo3/cms-filelist" override - when "id" is numeric; for every other value it
 * skips that lookup entirely. Its own source comment names ext:filelist explicitly as one of the
 * modules affected by this. A Fluid template override therefore never reaches the File List
 * module, no matter how it is configured in page.tsconfig.
 *
 * With no PSR-14 event available in this area of the File List module either, a middleware
 * hooking into the request is the only way left to load the JS module for this one route,
 * without XClassing the core FileListController.
 */
final readonly class DragUploaderRightsCheckMiddleware implements MiddlewareInterface
{
    private const MODULE_IDENTIFIER = 'media_management';

    public function __construct(
        private PageRenderer $pageRenderer,
        private ExtConf $extConf,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->extConf->isFileListUploadRightsCheckEnabled()) {
            return $handler->handle($request);
        }

        $route = $request->getAttribute('route');
        $module = $route instanceof Route ? $route->getOption('module') : null;
        if ($module instanceof ModuleInterface && $module->getIdentifier() === self::MODULE_IDENTIFIER) {
            $this->pageRenderer->loadJavaScriptModule('@jweiland/checkfaluploads/drag-uploader-rights-check.js');
            $this->pageRenderer->addInlineLanguageLabelArray([
                'checkfaluploads.dragUploader.fileRights.title' => $this->extConf->getLabelForUserRights(),
                'checkfaluploads.dragUploader.modalTitle' => LocalizationUtility::translate(
                    'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:cm.upload',
                ),
                'checkfaluploads.dragUploader.modalCancel' => LocalizationUtility::translate(
                    'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.cancel',
                ),
                'checkfaluploads.dragUploader.modalConfirm' => LocalizationUtility::translate(
                    'dragUploader.modal.browseFiles',
                    'checkfaluploads',
                ),
            ]);
        }

        return $handler->handle($request);
    }
}
