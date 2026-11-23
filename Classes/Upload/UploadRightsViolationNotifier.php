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
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Adds an error flash message to the Extbase flash message queue of the plugin
 * which owns the upload field ("extbase.flashmessages.<pluginNamespace>").
 * The message is stored in the session, so it is rendered by <f:flashMessages />
 * of that plugin, even if the action redirects to another page.
 */
#[AsAlias(UploadRightsViolationNotifierInterface::class)]
final readonly class UploadRightsViolationNotifier implements UploadRightsViolationNotifierInterface
{
    private const LANGUAGE_FILE = 'LLL:EXT:checkfaluploads/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        private FlashMessageService $flashMessageService,
        private LanguageServiceFactory $languageServiceFactory,
    ) {}

    public function notify(UploadRightsViolation $violation, ServerRequestInterface $request): void
    {
        $languageService = $this->getLanguageService($request);

        $flashMessage = new FlashMessage(
            sprintf(
                $languageService->sL(self::LANGUAGE_FILE . 'error.uploadFile.rightsNotConfirmed'),
                implode('", "', $violation->fileNames),
            ),
            $languageService->sL(self::LANGUAGE_FILE . 'missingRights.title'),
            ContextualFeedbackSeverity::ERROR,
            true,
        );

        $this->flashMessageService
            ->getMessageQueueByIdentifier('extbase.flashmessages.' . $violation->pluginNamespace)
            ->enqueue($flashMessage);
    }

    private function getLanguageService(ServerRequestInterface $request): LanguageService
    {
        $siteLanguage = $request->getAttribute('language');
        if ($siteLanguage instanceof SiteLanguage) {
            return $this->languageServiceFactory->createFromSiteLanguage($siteLanguage);
        }

        return $this->languageServiceFactory->create('default');
    }
}
