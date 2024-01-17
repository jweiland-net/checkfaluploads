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
        $request = $this->getTypo3Request();
        if ($request instanceof ServerRequestInterface) {
            return ApplicationType::fromRequest($request)->isBackend();
        }

        // In CLI context there is no TYPO3_REQUEST
        return false;
    }

    private function isFrontendRequest(): bool
    {
        $request = $this->getTypo3Request();
        if ($request instanceof ServerRequestInterface) {
            return ApplicationType::fromRequest($request)->isFrontend();
        }

        // In CLI context there is no TYPO3_REQUEST
        return false;
    }

    private function getTypo3Request(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
