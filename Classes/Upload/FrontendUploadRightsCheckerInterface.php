<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Upload;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Detects and removes uploaded files of a frontend request whose upload field
 * comes with a "rights" field which was not checked.
 */
interface FrontendUploadRightsCheckerInterface
{
    /**
     * @return list<UploadRightsViolation>
     */
    public function findViolations(ServerRequestInterface $request): array;

    /**
     * @param list<UploadRightsViolation> $violations
     */
    public function removeViolatingUploads(ServerRequestInterface $request, array $violations): ServerRequestInterface;
}
