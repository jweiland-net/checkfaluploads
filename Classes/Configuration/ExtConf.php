<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/*
 * This class streamlines all settings from extension manager
 */
class ExtConf implements SingletonInterface
{
    /**
     * @var string
     */
    protected $owner = '';

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        // get global configuration
        $extConf = $extensionConfiguration->get('checkfaluploads');
        if (is_array($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        }
    }

    public function getOwner(): string
    {
        if ($this->owner === '') {
            return '[Missing owner in ext settings of checkfaluploads]';
        }

        return $this->owner;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = trim($owner);
    }

    /**
     * Helper method to get the translated label for userHasRights checkbox where
     * the owner was already inserted.
     */
    public function getLabelForUserRights(): string
    {
        return LocalizationUtility::translate(
            'dragUploader.fileRights.title',
            'checkfaluploads',
            [
                0 => $this->getOwner()
            ]
        );
    }
}
