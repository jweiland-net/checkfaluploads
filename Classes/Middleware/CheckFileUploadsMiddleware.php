<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/EXT_KEY.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\UploadedFile;

class CheckFileUploadsMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $uploadedFiles = $request->getUploadedFiles();
        $parsedBody = $request->getParsedBody();

        // no uploads so just pass the request without any changes
        if ($uploadedFiles === []) {
            return $handler->handle($request);
        }

        $cleanUploads = $this->filterUploadedFiles($uploadedFiles, $parsedBody);

        return $handler->handle(
            $request->withUploadedFiles($cleanUploads)
        );
    }

    protected function filterUploadedFiles(array $uploads, array $body): array
    {
        foreach ($uploads as $key => $value) {
            // single UploadedFile
            if ($value instanceof UploadedFile) {
                if (!$this->isAllowedUpload($value, $body)) {
                    unset($uploads[$key]);
                }
                continue;
            }

            // nested structure
            if (is_array($value)) {
                $subBody = is_array($body[$key] ?? null) ? $body[$key] : [];
                $uploads[$key] = $this->filterUploadedFiles($value, $subBody);

                // remove empty branches
                if ($uploads[$key] === []) {
                    unset($uploads[$key]);
                }
            }
        }

        return $uploads;
    }

    public function isAllowedUpload(UploadedFile $uploadedFile, array $rightsConfiguration): bool
    {
        // unwrap numeric index (e.g. [0 => ['rights' => 1]])
        if (isset($rightsConfiguration[0]) && is_array($rightsConfiguration[0])) {
            $rightsConfiguration = $rightsConfiguration[0];
        }

        // upload must be OK
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check if the uploaded file has content (i.e., is not empty)
        if ($uploadedFile->getSize() === null || $uploadedFile->getSize() <= 0) {
            return false;
        }

        // Check the rightsConfigurations set for the field or not
        if (!isset($rightsConfiguration['rights']) || $rightsConfiguration['rights'] === '' || $rightsConfiguration['rights'] === 0) {
            return false;
        }

        return true;
    }
}
