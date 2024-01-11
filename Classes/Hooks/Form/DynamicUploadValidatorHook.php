<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks\Form;

use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Add dynamic validator. Only upload image, if upload-rights are set.
 */
class DynamicUploadValidatorHook
{
    protected array $arguments = [];

    protected RenderableInterface $renderable;

    /**
     * @var FormElementInterface[]
     */
    protected array $elements = [];

    /**
     * This method will be called by Form Framework.
     * It was checked by method_exists() before
     */
    public function afterSubmit(
        FormRuntime $formRuntime,
        RenderableInterface $renderable,
        $elementValue,
        array $requestArguments = []
    ) {
        if ($renderable instanceof Page) {
            $this->initialize($requestArguments, $renderable);
            $this->validateUploadedImages();
        }

        return $elementValue;
    }

    protected function initialize(array $requestArguments, Page $renderable): void
    {
        $this->arguments = $requestArguments;
        $this->renderable = $renderable;

        if ($this->elements === []) {
            $this->elements = $this->renderable->getElementsRecursively();
        }
    }

    protected function validateUploadedImages(): void
    {
        $checkboxElementsForUploadRights = $this->getElementsByProperty('checkboxType', 'uploadRights');
        if ($checkboxElementsForUploadRights === []) {
            // No checkboxes found, nothing to validate
            return;
        }

        foreach ($checkboxElementsForUploadRights as $checkboxElementForUploadRights) {
            if (array_key_exists('referenceUploadIdentifier', $checkboxElementForUploadRights->getProperties())) {
                $uploadIdentifier = $checkboxElementForUploadRights->getProperties()['referenceUploadIdentifier'];
                $uploadElement = $this->getElementByIdentifier($uploadIdentifier);
                if (
                    $uploadElement === null
                    || !$this->isValidUploadArgument($uploadIdentifier)
                    || !$this->hasArgument($checkboxElementForUploadRights->getIdentifier())
                    || $this->getArgument($checkboxElementForUploadRights->getIdentifier()) === '1'
                ) {
                    continue;
                }

                $checkboxElementForUploadRights->addValidator(
                    $this->getNotEmptyValidator()
                );
            }
        }
    }

    protected function isValidUploadArgument(string $identifier): bool
    {
        $argument = $this->getArgument($identifier);
        if ($argument === '') {
            return false;
        }

        if (!$argument instanceof UploadedFile) {
            return false;
        }

        if ($argument->getError() > 0) {
            return false;
        }

        return true;
    }

    protected function getElementByIdentifier(string $identifier): ?FormElementInterface
    {
        foreach ($this->elements as $element) {
            if ($element->getIdentifier() === $identifier) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @return FormElementInterface[]
     */
    protected function getElementsByProperty(string $propertyName, string|int $value): array
    {
        $matchedElements = [];
        foreach ($this->elements as $element) {
            $properties = $element->getProperties();
            if (
                array_key_exists($propertyName, $properties)
                && $properties[$propertyName] === $value
            ) {
                $matchedElements[] = $element;
            }
        }

        return $matchedElements;
    }

    protected function hasArgument(string $argument): bool
    {
        return array_key_exists($argument, $this->arguments) && $this->arguments[$argument] !== null;
    }

    protected function getArgument(string $argument): array|string
    {
        return $this->hasArgument($argument) ? $this->arguments[$argument] : '';
    }

    protected function getNotEmptyValidator(): NotEmptyValidator
    {
        return GeneralUtility::makeInstance(NotEmptyValidator::class);
    }
}
