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
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Loads the JS module which adds a "user has rights" checkbox to the classic upload form
 * (TYPO3\CMS\Backend\View\FolderUtilityRenderer::uploadForm()) rendered inside the "file" element
 * browser popup (TYPO3\CMS\Filelist\ElementBrowser\FileBrowser), e.g. reached via FormEngine's
 * "Select & upload files" / "Add media file" buttons or the RTE link wizard.
 *
 * That form is plain, non-AJAX, server-rendered HTML with no PSR-14 event around it to hook a
 * checkbox into, so - same reasoning as DragUploaderRightsCheckMiddleware - a middleware loading
 * a small JS module is the only way to add the checkbox without XClassing FolderUtilityRenderer.
 * Unlike DragUploader, this is a plain multipart form: the injected checkbox is a real form field,
 * so its value is simply submitted along with the rest of the form - no FormData/XHR patching
 * needed.
 */
final readonly class ElementBrowserUploadRightsCheckMiddleware implements MiddlewareInterface
{
    private const ROUTE_IDENTIFIER = 'wizard_element_browser';

    public function __construct(
        private PageRenderer $pageRenderer,
        private ExtConf $extConf,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->extConf->isElementBrowserUploadRightsCheckEnabled()) {
            return $handler->handle($request);
        }

        $route = $request->getAttribute('route');
        if ($route instanceof Route && $route->getOption('_identifier') === self::ROUTE_IDENTIFIER) {
            $this->pageRenderer->loadJavaScriptModule('@jweiland/checkfaluploads/element-browser-upload-rights-check.js');
            $this->pageRenderer->addInlineLanguageLabelArray([
                'checkfaluploads.dragUploader.fileRights.title' => $this->extConf->getLabelForUserRights(),
                'checkfaluploads.missingRights.title' => LocalizationUtility::translate(
                    'missingRights.title',
                    'checkfaluploads',
                ),
                'checkfaluploads.missingRights.message' => LocalizationUtility::translate(
                    'error.uploadFile.missingRights',
                    'checkfaluploads',
                ),
            ]);
        }

        return $handler->handle($request);
    }
}
