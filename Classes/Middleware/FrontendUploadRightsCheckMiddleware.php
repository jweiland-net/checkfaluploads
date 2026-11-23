<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Middleware;

use JWeiland\Checkfaluploads\Upload\FrontendUploadRightsCheckerInterface;
use JWeiland\Checkfaluploads\Upload\UploadRightsViolationNotifierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Generic upload rights check for all Extbase frontend forms.
 *
 * Extbase offers no public hook to add a validator to the arguments of a
 * foreign controller, so the check happens on the request itself: uploaded
 * files of an upload field with an unchecked "rights" field are removed from
 * the request before any plugin is rendered. Therefore, they can not be stored
 * by any upload mechanism (#[FileUpload], custom TypeConverter, ...).
 * The frontend user is informed by an error flash message of the plugin.
 *
 * The rejected uploads are available in the request attribute
 * "checkfaluploads.uploadRightsViolations" (list of UploadRightsViolation).
 */
final readonly class FrontendUploadRightsCheckMiddleware implements MiddlewareInterface
{
    public const REQUEST_ATTRIBUTE = 'checkfaluploads.uploadRightsViolations';

    public function __construct(
        private FrontendUploadRightsCheckerInterface $uploadRightsChecker,
        private UploadRightsViolationNotifierInterface $uploadRightsViolationNotifier,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== 'POST' || $request->getUploadedFiles() === []) {
            return $handler->handle($request);
        }

        $violations = $this->uploadRightsChecker->findViolations($request);
        if ($violations === []) {
            return $handler->handle($request);
        }

        $request = $this->uploadRightsChecker->removeViolatingUploads($request, $violations);
        foreach ($violations as $violation) {
            $this->uploadRightsViolationNotifier->notify($violation, $request);
        }

        return $handler->handle($request->withAttribute(self::REQUEST_ATTRIBUTE, $violations));
    }
}
