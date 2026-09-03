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
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This class streamlines all settings from extension manager
 */
#[Autoconfigure(constructor: 'create')]
final readonly class ExtConf
{
    use ApplicationContextTrait;

    private const EXT_KEY = 'checkfaluploads';

    private const DEFAULT_SETTINGS = [
        'owner' => '[Missing owner in ext settings of checkfaluploads]',
        'checkDragUploaderUploadRights' => true,
        'checkElementBrowserUploadRights' => true,
        'storeBackendUploaderUserId' => true,
        'storeFrontendUploaderUserId' => true,
    ];

    public function __construct(
        private string $owner = self::DEFAULT_SETTINGS['owner'],
        private bool $checkDragUploaderUploadRights = self::DEFAULT_SETTINGS['checkDragUploaderUploadRights'],
        private bool $checkElementBrowserUploadRights = self::DEFAULT_SETTINGS['checkElementBrowserUploadRights'],
        private bool $storeBackendUploaderUserId = self::DEFAULT_SETTINGS['storeBackendUploaderUserId'],
        private bool $storeFrontendUploaderUserId = self::DEFAULT_SETTINGS['storeFrontendUploaderUserId'],
    ) {}

    public static function create(ExtensionConfiguration $extensionConfiguration): self
    {
        $extensionSettings = self::DEFAULT_SETTINGS;

        // Overwrite default extension settings with values from EXT_CONF
        try {
            $extensionSettings = array_merge(
                $extensionSettings,
                $extensionConfiguration->get(self::EXT_KEY),
            );
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
        }

        return new self(
            owner: trim((string)$extensionSettings['owner']),
            checkDragUploaderUploadRights: (bool)$extensionSettings['checkDragUploaderUploadRights'],
            checkElementBrowserUploadRights: (bool)$extensionSettings['checkElementBrowserUploadRights'],
            storeBackendUploaderUserId: (bool)$extensionSettings['storeBackendUploaderUserId'],
            storeFrontendUploaderUserId: (bool)$extensionSettings['storeFrontendUploaderUserId'],
        );
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function isElementBrowserUploadRightsCheckEnabled(): bool
    {
        return $this->checkElementBrowserUploadRights;
    }

    /**
     * File List and FormEngine's inline "Select & upload files" both submit through the very
     * same DragUploader ajax endpoint, with no request data distinguishing one from the other.
     * Both the confirmation dialog and its enforcement are therefore controlled by this single
     * switch, covering both surfaces together rather than pretending they can be split further.
     */
    public function isDragUploaderUploadRightsCheckEnabled(): bool
    {
        return $this->checkDragUploaderUploadRights;
    }

    public function isStoreBackendUploaderUserIdEnabled(): bool
    {
        return $this->storeBackendUploaderUserId;
    }

    public function isStoreFrontendUploaderUserIdEnabled(): bool
    {
        return $this->storeFrontendUploaderUserId;
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
            self::EXT_KEY,
            [
                0 => $this->getOwner(),
            ],
        );
    }
}
