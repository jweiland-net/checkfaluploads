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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/*
 * This VH renders an image user rights message incl. the owner who will retrieve the image rights.
 */

class ImageRightsMessageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        // Registering arguments with clear types and defaults
        $this->registerArgument(
            'languageKey',
            'string',
            'The language key of a locallang.xml file to use/translate',
            false,
            'frontend.imageUserRights',
        );
        $this->registerArgument(
            'extensionName',
            'string',
            'Use locallang.xml of given extension name',
            false,
            'checkfaluploads',
        );
    }

    /**
     * Implements a ViewHelper to get values from current logged in fe_user.
     *
     * @return string
     */
    public function render(): string
    {
        $arguments = $this->arguments;

        $owner = self::getOwnerFromConfiguration();

        return LocalizationUtility::translate(
            $arguments['languageKey'],
            $arguments['extensionName'],
            [
                0 => $owner,
            ],
        );
    }

    /**
     * Fetches the owner from the extension configuration.
     *
     * @return string The owner name or an empty string if not configured
     */
    private static function getOwnerFromConfiguration(): string
    {
        $extConf = self::getExtConf();
        return $extConf->getOwner() ?: '';  // Return an empty string if owner is not set
    }

    /**
     * Retrieves the extension configuration instance.
     *
     * @return ExtConf The configuration object
     */
    protected static function getExtConf(): ExtConf
    {
        return GeneralUtility::makeInstance(ExtConf::class);
    }
}
