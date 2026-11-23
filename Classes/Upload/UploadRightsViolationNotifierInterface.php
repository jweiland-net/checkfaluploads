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
 * Informs the frontend user about uploads which were rejected because the
 * "rights" field was not checked.
 */
interface UploadRightsViolationNotifierInterface
{
    public function notify(UploadRightsViolation $violation, ServerRequestInterface $request): void;
}
