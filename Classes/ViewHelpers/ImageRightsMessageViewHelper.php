<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\ViewHelpers;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/*
 * This VH renders an image user rights message incl. the owner who will retrieve the image rights.
 */
class ImageRightsMessageViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Implements a ViewHelper to get values from current logged in fe_user.
     *
     * @param array $arguments
     * @param \Closure $childClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $childClosure,
        RenderingContextInterface $renderingContext
    ) {
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        return LocalizationUtility::translate(
            'frontend.imageUserRights',
            'checkfaluploads',
            [
                0 => $extConf->getOwner()
            ]
        );
    }
}
