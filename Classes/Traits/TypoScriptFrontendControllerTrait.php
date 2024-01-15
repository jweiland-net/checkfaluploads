<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Traits;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Trait which returns the current TypoScriptFrontendController in frontend context
 */
trait TypoScriptFrontendControllerTrait
{
    /**
     * We don't check against FE context as the calling classes already check against that context.
     */
    private function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
