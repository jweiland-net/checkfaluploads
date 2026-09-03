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
use JWeiland\Checkfaluploads\Middleware\DragUploaderRightsCheckMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Module\ModuleInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class DragUploaderRightsCheckMiddlewareTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    #[Test]
    public function processSkipsLoadingJavaScriptModuleWhenDragUploaderUploadRightsCheckIsDisabled(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects($this->never())->method('loadJavaScriptModule');

        $subject = new DragUploaderRightsCheckMiddleware(
            $pageRenderer,
            new ExtConf(checkDragUploaderUploadRights: false),
        );

        $subject->process($this->createRequestForMediaManagementModule(), $this->createRequestHandler());
    }

    #[Test]
    public function processLoadsJavaScriptModuleForMediaManagementModuleWhenEnabled(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer
            ->expects($this->once())
            ->method('loadJavaScriptModule')
            ->with('@jweiland/checkfaluploads/drag-uploader-rights-check.js');

        $subject = new DragUploaderRightsCheckMiddleware(
            $pageRenderer,
            new ExtConf(),
        );

        $subject->process($this->createRequestForMediaManagementModule(), $this->createRequestHandler());
    }

    #[Test]
    public function processSkipsLoadingJavaScriptModuleForUnrelatedModule(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects($this->never())->method('loadJavaScriptModule');

        $module = $this->createMock(ModuleInterface::class);
        $module->method('getIdentifier')->willReturn('some_other_module');

        $request = (new ServerRequest())->withAttribute(
            'route',
            new Route('/some/path', ['module' => $module]),
        );

        $subject = new DragUploaderRightsCheckMiddleware(
            $pageRenderer,
            new ExtConf(),
        );

        $subject->process($request, $this->createRequestHandler());
    }

    private function createRequestForMediaManagementModule(): ServerRequestInterface
    {
        $module = $this->createMock(ModuleInterface::class);
        $module->method('getIdentifier')->willReturn('media_management');

        return (new ServerRequest())->withAttribute(
            'route',
            new Route('/some/path', ['module' => $module]),
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
