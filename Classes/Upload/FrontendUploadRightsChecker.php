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
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Generic upload rights check for Extbase frontend forms.
 *
 * Extbase forms post their values as <pluginNamespace>[<argument>][<property>].
 * An upload field is only checked, if the parsed body contains a "rights" field
 * as sibling of the uploaded files:
 *
 *   uploaded files: tx_myext_myplugin[record][images][0]
 *   rights field:   tx_myext_myplugin[record][images][rights]
 *
 * If the rights field is missing, the upload field is not touched at all.
 * The rights field counts as checked, if its value is neither empty nor "0".
 */
#[AsAlias(FrontendUploadRightsCheckerInterface::class)]
final readonly class FrontendUploadRightsChecker implements FrontendUploadRightsCheckerInterface
{
    private const RIGHTS_FIELD_NAME = 'rights';

    public function findViolations(ServerRequestInterface $request): array
    {
        $parsedBody = $request->getParsedBody();
        if (!is_array($parsedBody)) {
            return [];
        }

        $violations = [];
        foreach ($parsedBody as $pluginNamespace => $arguments) {
            $uploadedFiles = $request->getUploadedFiles()[$pluginNamespace] ?? [];
            if (!is_array($arguments) || !is_array($uploadedFiles)) {
                continue;
            }

            foreach ($arguments as $argumentName => $properties) {
                if (!is_array($properties)) {
                    continue;
                }

                array_push($violations, ...$this->findViolationsForArgument(
                    (string)$pluginNamespace,
                    (string)$argumentName,
                    $properties,
                    $uploadedFiles[$argumentName] ?? [],
                ));
            }
        }

        return $violations;
    }

    public function removeViolatingUploads(ServerRequestInterface $request, array $violations): ServerRequestInterface
    {
        if ($violations === []) {
            return $request;
        }

        $uploadedFiles = $request->getUploadedFiles();
        foreach ($violations as $violation) {
            unset($uploadedFiles[$violation->pluginNamespace][$violation->argumentName][$violation->propertyName]);
        }

        return $request->withUploadedFiles($uploadedFiles);
    }

    /**
     * @param array<array-key, mixed> $properties
     * @return list<UploadRightsViolation>
     */
    private function findViolationsForArgument(
        string $pluginNamespace,
        string $argumentName,
        array $properties,
        mixed $uploadedFiles,
    ): array {
        $violations = [];
        foreach ($properties as $propertyName => $propertyValue) {
            $violation = $this->findViolationForProperty(
                $pluginNamespace,
                $argumentName,
                (string)$propertyName,
                $propertyValue,
                is_array($uploadedFiles) ? ($uploadedFiles[$propertyName] ?? []) : [],
            );
            if ($violation instanceof UploadRightsViolation) {
                $violations[] = $violation;
            }
        }

        return $violations;
    }

    private function findViolationForProperty(
        string $pluginNamespace,
        string $argumentName,
        string $propertyName,
        mixed $propertyValue,
        mixed $uploadedFiles,
    ): ?UploadRightsViolation {
        if (!is_array($propertyValue)
            || !array_key_exists(self::RIGHTS_FIELD_NAME, $propertyValue)
            || $this->isRightsFieldChecked($propertyValue[self::RIGHTS_FIELD_NAME])
        ) {
            return null;
        }

        $fileNames = $this->getFileNamesOfSuccessfulUploads($uploadedFiles);
        if ($fileNames === []) {
            return null;
        }

        return new UploadRightsViolation($pluginNamespace, $argumentName, $propertyName, $fileNames);
    }

    private function isRightsFieldChecked(mixed $value): bool
    {
        return is_scalar($value)
            && trim((string)$value) !== ''
            && (string)$value !== '0';
    }

    /**
     * Uploads with an error (e.g. exceeded upload_max_filesize) can not be stored
     * anyway. They are left to the error handling of the extension.
     *
     * @return list<string>
     */
    private function getFileNamesOfSuccessfulUploads(mixed $uploadedFiles): array
    {
        if ($uploadedFiles instanceof UploadedFileInterface) {
            return $uploadedFiles->getError() === UPLOAD_ERR_OK
                ? [(string)$uploadedFiles->getClientFilename()]
                : [];
        }

        if (!is_array($uploadedFiles)) {
            return [];
        }

        $fileNames = [];
        foreach ($uploadedFiles as $uploadedFile) {
            array_push($fileNames, ...$this->getFileNamesOfSuccessfulUploads($uploadedFile));
        }

        return $fileNames;
    }
}
