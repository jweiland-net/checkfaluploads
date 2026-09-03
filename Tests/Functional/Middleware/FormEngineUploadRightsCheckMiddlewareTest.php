<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Middleware;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Middleware\FormEngineUploadRightsCheckMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class FormEngineUploadRightsCheckMiddlewareTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    #[Test]
    public function processSkipsLoadingJavaScriptModuleWhenFormEngineUploadRightsCheckIsDisabled(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects($this->never())->method('loadJavaScriptModule');

        $subject = new FormEngineUploadRightsCheckMiddleware(
            $pageRenderer,
            new ExtConf(checkFormEngineUploadRights: false),
        );

        $subject->process($this->createRequestForRecordEditRoute(), $this->createRequestHandler());
    }

    #[Test]
    public function processLoadsJavaScriptModuleForRecordEditRouteWhenEnabled(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer
            ->expects($this->once())
            ->method('loadJavaScriptModule')
            ->with('@jweiland/checkfaluploads/form-engine-upload-rights-check.js');

        $subject = new FormEngineUploadRightsCheckMiddleware(
            $pageRenderer,
            new ExtConf(),
        );

        $subject->process($this->createRequestForRecordEditRoute(), $this->createRequestHandler());
    }

    #[Test]
    public function processSkipsLoadingJavaScriptModuleForUnrelatedRoute(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects($this->never())->method('loadJavaScriptModule');

        $request = (new ServerRequest())->withAttribute(
            'route',
            new Route('/some/path', ['_identifier' => 'wizard_element_browser']),
        );

        $subject = new FormEngineUploadRightsCheckMiddleware(
            $pageRenderer,
            new ExtConf(),
        );

        $subject->process($request, $this->createRequestHandler());
    }

    private function createRequestForRecordEditRoute(): ServerRequestInterface
    {
        return (new ServerRequest())->withAttribute(
            'route',
            new Route('/some/path', ['_identifier' => 'record_edit']),
        );
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($this->createMock(ResponseInterface::class));

        return $handler;
    }
}
