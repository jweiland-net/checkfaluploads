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
 * Loads the JS module which adds the same "user has rights" confirmation modal DragUploader gets
 * in the File List module (see DragUploaderRightsCheckMiddleware) to DragUploader's other, inline
 * usage: TYPO3\CMS\Backend\Form\Container\FilesControlContainer renders a ".t3js-drag-uploader"
 * button for every inline file/media relation field (FormEngine's "Select & upload files"), which
 * clicks straight into the native OS file picker with no confirmation of any kind today - and,
 * same as the File List module before, uploads through it are unconditionally rejected server-side
 * by UserMarkedCheckboxForRightsEventListener, since DragUploader never sends "userHasRights".
 *
 * FilesControlContainer is plain PHP string building with no Fluid template and no PSR-14 event
 * around the button markup, so - same reasoning as the other two middlewares - a JS module loaded
 * from here is the only way to add the confirmation without XClassing a core class.
 */
final readonly class FormEngineUploadRightsCheckMiddleware implements MiddlewareInterface
{
    private const ROUTE_IDENTIFIER = 'record_edit';

    public function __construct(
        private PageRenderer $pageRenderer,
        private ExtConf $extConf,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');
        if ($route instanceof Route && $route->getOption('_identifier') === self::ROUTE_IDENTIFIER) {
            $this->pageRenderer->loadJavaScriptModule('@jweiland/checkfaluploads/form-engine-upload-rights-check.js');
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
