<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Traits;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;

trait ApplicationContextTrait
{
    private function isBackendRequest(): bool
    {
        return ApplicationType::fromRequest($this->getTypo3Request())->isBackend();
    }

    private function isFrontendRequest(): bool
    {
        return ApplicationType::fromRequest($this->getTypo3Request())->isFrontend();
    }

    private function getTypo3Request(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
