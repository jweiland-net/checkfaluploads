<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Validation\Validator;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validates that a frontend user has confirmed the "rights" checkbox for an uploaded file, before the
 * file is accepted by Extbase's native file upload handling (TYPO3 >= 13.3). Custom validators cannot be
 * configured via the #[FileUpload] attribute, so this must be added manually to the property's
 * FileUploadConfiguration, e.g. in the controller's initialize*Action(); the request MUST be passed to
 * ValidatorResolver::createValidator(), as this validator reads the parsed request body and resolves the
 * current plugin namespace via ExtensionService::getPluginNamespace().
 *
 * If the request does not contain the "rights" field, the validator does not report an error, so the
 * upload works like without checkfaluploads. In the frontend, the generic FrontendUploadRightsCheckMiddleware
 * already removes uploads with an unchecked field named "rights" before Extbase is called. Configure another
 * "fieldName" (e.g. "uploadRights") to get a validation error at the upload field from this validator instead.
 *
 * See Documentation/Developer/Index.rst ("CheckFalUploadValidator") for usage, the available options and
 * how "propertyPath" is resolved against the raw parsed request body.
 */
final class CheckFalUploadValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'propertyPath' => [null, 'Path in Extbase notation (divided by "."), without the plugin namespace, pointing to the array that holds the "rights" key, e.g. "topic.images"', 'string'],
        'fieldName' => ['rights', 'Key within the rights configuration array that must be set and not empty for the upload to be allowed. Configurable to avoid collisions with an unrelated field of the same name in a foreign form', 'string'],
        'langKey' => ['error.uploadFile.missingRights', 'Language key (LLL) of the validation error message shown when "fieldName" is empty', 'string'],
        'extensionName' => ['checkfaluploads', 'Extension key used to resolve the language file for "langKey" and the other error messages of FalUploadService::checkFile()', 'string'],
    ];

    public function __construct(
        private readonly FalUploadService $falUploadService,
        private readonly ExtensionService $extensionService,
    ) {}

    protected function isValid(mixed $value): void
    {
        if (!$value instanceof UploadedFile) {
            throw new \InvalidArgumentException('CheckFalUploadValidator can only validate instances of UploadedFile', 1788269915);
        }

        if (!$this->request instanceof Request) {
            throw new \InvalidArgumentException('CheckFalUploadValidator can only be used within an Extbase request context', 1788269828);
        }

        $rightsConfiguration = $this->getRightsConfiguration($this->request);

        // Same rule as for the generic frontend check: the rights check only applies,
        // if the form contains the rights field. Without it, there is nothing to check.
        if (!is_array($rightsConfiguration) || !array_key_exists($this->options['fieldName'], $rightsConfiguration)) {
            return;
        }

        $error = $this->falUploadService->checkFile(
            $value,
            $rightsConfiguration,
            $this->options['fieldName'],
            $this->options['langKey'],
            $this->options['extensionName'],
        );

        if ($error instanceof Error) {
            $this->addError(
                $error->getMessage(),
                $error->getCode(),
                $error->getArguments(),
                $error->getTitle(),
            );
        }
    }

    private function getRightsConfiguration(Request $request): mixed
    {
        if ((string)($this->options['propertyPath'] ?? '') === '') {
            throw new \InvalidArgumentException(
                'checkfaluploads: CheckFalUploadValidator was registered without a "propertyPath" option. '
                . 'The option "propertyPath" must not be empty.',
                1788270484,
            );
        }

        $pluginNamespace = $this->extensionService->getPluginNamespace(
            $request->getControllerExtensionName(),
            $request->getPluginName(),
        );

        // PropertyPath of Extbase is divided by ".", to use it in ArrayUtility we need "/"
        $propertyPath = str_replace('.', '/', $pluginNamespace . '.' . $this->options['propertyPath']);

        try {
            return ArrayUtility::getValueByPath((array)$request->getParsedBody(), $propertyPath);
        } catch (MissingArrayPathException) {
            return null;
        }
    }
}
