<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\EventListener;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Helper\MessageHelper;
use JWeiland\Checkfaluploads\Traits\ApplicationContextTrait;
use JWeiland\Checkfaluploads\Traits\BackendUserAuthenticationTrait;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Resource\Event\BeforeFileAddedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFileReplacedEvent;
use TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Check if user has checked the checkbox which indicates that the user has the rights to upload these files
 */
final readonly class UserMarkedCheckboxForRightsEventListener
{
    use ApplicationContextTrait;
    use BackendUserAuthenticationTrait;

    private const ELEMENT_BROWSER_ROUTE_IDENTIFIER = 'tce_file';

    public function __construct(
        private ExtendedFileUtility $extendedFileUtility,
        private MessageHelper $messageHelper,
        private ExtConf $extConf,
    ) {}

    /**
     * @throws InsufficientUserPermissionsException
     */
    #[AsEventListener(
        identifier: 'checkfaluploads/check-for-added-file',
    )]
    public function checkForAddedFile(BeforeFileAddedEvent $event): void
    {
        // FE uploads are not enforced here: checkfaluploads does not own the
        // frontend upload form, so whichever extension renders it must call
        // FalUploadService / CheckFalUploadValidator itself, see
        // Documentation/Developer/Index.rst.
        if ($this->isBackendRequest() && $this->isUploadRightsCheckEnabledForCurrentRoute()) {
            $fileParts = GeneralUtility::split_fileref($event->getFileName());
            if (!in_array($fileParts['fileext'], ['youtube', 'vimeo'], true)) {
                $userHasRights = (bool)($this->getTypo3Request()->getParsedBody()['userHasRights'] ?? 0);
                if ($userHasRights === false) {
                    $message = LocalizationUtility::translate(
                        'error.uploadFile.missingRights',
                        'Checkfaluploads',
                    );

                    $this->getBackendUserAuthentication()->writeLog(2, 1, 1, 105, $message, []);

                    $this->messageHelper->addFlashMessage($message, '', ContextualFeedbackSeverity::ERROR);

                    throw new InsufficientUserPermissionsException($message, 1396626278);
                }
            }
        }
    }

    /**
     * @throws InsufficientUserPermissionsException
     */
    #[AsEventListener(
        identifier: 'checkfaluploads/check-for-replaced-file',
    )]
    public function checkForReplacedFile(BeforeFileReplacedEvent $event): void
    {
        // FE uploads are not enforced here: checkfaluploads does not own the
        // frontend upload form, so whichever extension renders it must call
        // FalUploadService / CheckFalUploadValidator itself, see
        // Documentation/Developer/Index.rst.
        if ($this->isBackendRequest() && $this->isUploadRightsCheckEnabledForCurrentRoute()) {
            $fileParts = GeneralUtility::split_fileref($event->getFile()->getName());
            if (!in_array($fileParts['fileext'], ['youtube', 'vimeo'], true)) {
                $userHasRights = (bool)($this->getTypo3Request()->getParsedBody()['userHasRights'] ?? false);
                if ($userHasRights === false) {
                    $message = LocalizationUtility::translate(
                        'error.uploadFile.missingRights',
                        'Checkfaluploads',
                    );

                    $this->getBackendUserAuthentication()->writeLog(2, 1, 1, 105, $message, []);

                    $this->messageHelper->addFlashMessage($message, '', ContextualFeedbackSeverity::ERROR);

                    throw new InsufficientUserPermissionsException($message, 1396626278);
                }
            }
        }
    }

    /**
     * The classic, non-AJAX upload form (ElementBrowser popup, and the currently unprotected
     * folder/row upload actions) is submitted through the "tce_file" route and gated by the
     * ElementBrowser rights setting. Every other backend upload path - most notably DragUploader,
     * used in both the File List module and FormEngine's inline "Select & upload files" - is
     * submitted through the same ajax endpoint with nothing in the request telling them apart,
     * so those two share a single combined switch instead, see
     * ExtConf::isDragUploaderUploadRightsCheckEnabled().
     */
    private function isUploadRightsCheckEnabledForCurrentRoute(): bool
    {
        $route = $this->getTypo3Request()?->getAttribute('route');
        if ($route instanceof Route && $route->getOption('_identifier') === self::ELEMENT_BROWSER_ROUTE_IDENTIFIER) {
            return $this->extConf->isElementBrowserUploadRightsCheckEnabled();
        }

        return $this->extConf->isDragUploaderUploadRightsCheckEnabled();
    }
}
