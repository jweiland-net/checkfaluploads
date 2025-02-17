<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Configuration;

use JWeiland\Checkfaluploads\Traits\ApplicationContextTrait;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This class streamlines all settings from extension manager
 */
class ExtConf implements SingletonInterface
{
    use ApplicationContextTrait;
    use LoggerAwareTrait;

    private const EXTENSION_KEY = 'checkfaluploads';
    private const DEFAULT_OWNER = '[Missing owner in ext settings of checkfaluploads]';
    private string $owner = self::DEFAULT_OWNER;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        try {
            $extConf = $extensionConfiguration->get(self::EXTENSION_KEY);
            if (is_array($extConf)) {
                $this->mapConfiguration($extConf);
            }
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $exception) {
            $this->logger?->error(
                sprintf('Failed to load configuration for extension "%s": %s', self::EXTENSION_KEY, $exception->getMessage()),
            );
        }
    }

    /**
     * Maps the configuration array to class properties.
     *
     * @param array<string, mixed> $config
     */
    private function mapConfiguration(array $config): void
    {
        foreach ($config as $key => $value) {
            $setterMethod = 'set' . ucfirst($key);
            if (method_exists($this, $setterMethod)) {
                $this->$setterMethod((string)$value);
            }
        }
    }

    public function getOwner(): string
    {
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
        $langKey = 'dragUploader.fileRights.title';
        if ($this->isFrontendRequest()) {
            $langKey = 'frontend.imageUserRights';
        }

        return LocalizationUtility::translate(
            $langKey,
            self::EXTENSION_KEY,
            [
                0 => $this->getOwner(),
            ],
        );
    }
}
