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
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Add dynamic validator. Only upload image, if upload-rights are set.
 */
class DynamicUploadValidatorHook
{
    protected array $requestArguments = [];

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
        if ($renderable instanceof FileUpload) {
            $this->requestArguments = $requestArguments;
            $elementValue = $this->updateElementValueOnError($elementValue, $renderable);
        }

        return $elementValue;
    }

    /**
     * In normal cases $elementValue should never be an array, as we remove the file from request, if checkbox
     * for user-rights was not checked.
     *
     * But in some cases it might be an array:
     * - You have uploaded a file, marked the checkbox, but a validator of another element throws an error, and you send the form again after solving the issue
     * - You upload the file on Page 1, but add the checkbox for file-rights to Page 2. OK, in that special case this code here will not find the checkbox ;-)
     * - You are using the form to edit records which has already an image assigned, and send the form to update the record.
     *
     * @param array|UploadedFile|null $elementValue UploadedFile on initial upload, array on next upload after error, null on no upload
     */
    protected function updateElementValueOnError(
        array|UploadedFile|null $elementValue,
        FileUpload $fileUpload
    ): array|UploadedFile|null {
        // Early return, if no file was uploaded
        if ($elementValue === null) {
            return null;
        }

        // Early return, if upload has failed
        if (!$this->isValidElementValue($elementValue)) {
            return null;
        }

        // Early return, if there is no checkbox configured for user-rights
        $relatedCheckboxForFileUpload = $this->getRelatedCheckboxElementForFileUpload($fileUpload);
        if (!$relatedCheckboxForFileUpload instanceof FormElementInterface) {
            return $elementValue;
        }

        // Early return, if checkbox for user-rights is already set or not in request
        if (
            !$this->isCheckboxElementPartOfRequest($relatedCheckboxForFileUpload->getIdentifier())
            || $this->isCheckboxElementActivated($relatedCheckboxForFileUpload->getIdentifier())
        ) {
            return $elementValue;
        }

        // Checkbox not activated: Add NotEmpty validator to inform the user
        $relatedCheckboxForFileUpload->addValidator(
            $this->getNotEmptyValidator()
        );

        // Checkbox not activated: Remove the uploaded file from request by returning an empty value
        return null;
    }

    protected function getRelatedCheckboxElementForFileUpload(FileUpload $fileUpload): ?FormElementInterface
    {
        $possibleCheckboxElements = $this->getCheckboxElementsByProperty(
            'checkboxType',
            'uploadRights',
            $this->getPageElement($fileUpload)
        );

        foreach ($possibleCheckboxElements as $possibleCheckboxElement) {
            $elementProperties = $possibleCheckboxElement->getProperties();
            if (!array_key_exists('referenceUploadIdentifier', $elementProperties)) {
                continue;
            }

            if ($elementProperties['referenceUploadIdentifier'] === $fileUpload->getIdentifier()) {
                return $possibleCheckboxElement;
            }
        }

        return null;
    }

    /**
     * We need access to all elements of current view to access the related checkbox element of our FileUpload
     * element. This is only possible on Page element as there is the property "renderables" which can access
     * all available elements.
     */
    protected function getPageElement(RenderableInterface $element): Page
    {
        $parentElement = $element->getParentRenderable();
        if ($parentElement instanceof Page) {
            return $parentElement;
        }

        return $this->getPageElement($parentElement);
    }

    protected function isValidElementValue(UploadedFile|array $elementValue): bool
    {
        // Process value on initial upload
        if ($elementValue instanceof UploadedFile) {
            return $elementValue->getError() === 0;
        }

        // Process value on further uploads after error.
        // In that case "value" is an array containing a related resourcePointer
        $resourcePointer = (string)($elementValue['submittedFile']['resourcePointer'] ?? '');

        return $resourcePointer !== '';
    }

    protected function isCheckboxElementPartOfRequest(string $identifier): bool
    {
        return $this->hasArgument($identifier);
    }

    protected function isCheckboxElementActivated(string $identifier): bool
    {
        return $this->isCheckboxElementPartOfRequest($identifier)
            && $this->getArgument($identifier) === '1';
    }

    /**
     * @return FormElementInterface[]
     */
    protected function getCheckboxElementsByProperty(string $propertyName, string $value, Page $page): array
    {
        $checkboxElements = [];
        foreach ($page->getElementsRecursively() as $element) {
            if ($element->getType() !== 'Checkbox') {
                continue;
            }

            $properties = $element->getProperties();
            if (
                array_key_exists($propertyName, $properties)
                && $properties[$propertyName] === $value
            ) {
                $checkboxElements[] = $element;
            }
        }

        return $checkboxElements;
    }

    protected function hasArgument(string $argument): bool
    {
        return array_key_exists($argument, $this->requestArguments)
            && $this->requestArguments[$argument] !== null;
    }

    protected function getArgument(string $argument): array|string|UploadedFile
    {
        return $this->hasArgument($argument)
            ? $this->requestArguments[$argument]
            : '';
    }

    protected function getNotEmptyValidator(): NotEmptyValidator
    {
        return GeneralUtility::makeInstance(NotEmptyValidator::class);
    }
}
