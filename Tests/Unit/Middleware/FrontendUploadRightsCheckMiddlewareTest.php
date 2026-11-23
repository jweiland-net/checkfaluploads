<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Unit\Middleware;

use JWeiland\Checkfaluploads\Middleware\FrontendUploadRightsCheckMiddleware;
use JWeiland\Checkfaluploads\Upload\FrontendUploadRightsChecker;
use JWeiland\Checkfaluploads\Upload\UploadRightsViolation;
use JWeiland\Checkfaluploads\Upload\UploadRightsViolationNotifierInterface;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
final class FrontendUploadRightsCheckMiddlewareTest extends UnitTestCase
{
    #[Test]
    public function processPassesGetRequestUnchanged(): void
    {
        $notifier = $this->createMock(UploadRightsViolationNotifierInterface::class);
        $notifier->expects($this->never())->method('notify');

        $handler = $this->createRequestHandler();
        $request = $this->createRequest('GET', '');
        (new FrontendUploadRightsCheckMiddleware(new FrontendUploadRightsChecker(), $notifier))
            ->process($request, $handler);

        self::assertSame($request, $handler->handledRequest);
    }

    #[Test]
    public function processPassesRequestWithCheckedRightsFieldUnchanged(): void
    {
        $notifier = $this->createMock(UploadRightsViolationNotifierInterface::class);
        $notifier->expects($this->never())->method('notify');

        $handler = $this->createRequestHandler();
        $request = $this->createRequest('POST', '1');
        (new FrontendUploadRightsCheckMiddleware(new FrontendUploadRightsChecker(), $notifier))
            ->process($request, $handler);

        self::assertSame($request, $handler->handledRequest);
    }

    #[Test]
    public function processRemovesUploadsAndNotifiesIfRightsFieldIsNotChecked(): void
    {
        $notifier = $this->createMock(UploadRightsViolationNotifierInterface::class);
        $notifier
            ->expects($this->once())
            ->method('notify')
            ->with(new UploadRightsViolation('tx_myext_myplugin', 'record', 'images', ['image.jpg']));

        $handler = $this->createRequestHandler();
        (new FrontendUploadRightsCheckMiddleware(new FrontendUploadRightsChecker(), $notifier))
            ->process($this->createRequest('POST', ''), $handler);

        self::assertSame(
            ['tx_myext_myplugin' => ['record' => []]],
            $handler->handledRequest->getUploadedFiles(),
        );
        self::assertEquals(
            [new UploadRightsViolation('tx_myext_myplugin', 'record', 'images', ['image.jpg'])],
            $handler->handledRequest->getAttribute(FrontendUploadRightsCheckMiddleware::REQUEST_ATTRIBUTE),
        );
    }

    private function createRequest(string $method, string $rightsValue): ServerRequest
    {
        return (new ServerRequest('https://example.com/', $method))
            ->withParsedBody(['tx_myext_myplugin' => ['record' => ['images' => ['rights' => $rightsValue]]]])
            ->withUploadedFiles(['tx_myext_myplugin' => ['record' => ['images' => [
                new UploadedFile('php://memory', 100, UPLOAD_ERR_OK, 'image.jpg', 'image/jpeg'),
            ]]]]);
    }

    private function createRequestHandler(): object
    {
        return new class () implements RequestHandlerInterface {
            public ?ServerRequestInterface $handledRequest = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->handledRequest = $request;

                return new Response();
            }
        };
    }
}
