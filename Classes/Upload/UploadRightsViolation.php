<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Upload;

/**
 * Describes the files of one upload field in a frontend request which were
 * submitted together with an unchecked "rights" field.
 *
 * The upload field is addressed in Extbase notation:
 * <pluginNamespace>[<argumentName>][<propertyName>]
 */
final readonly class UploadRightsViolation
{
    /**
     * @param list<string> $fileNames Client file names of the rejected uploads
     */
    public function __construct(
        public string $pluginNamespace,
        public string $argumentName,
        public string $propertyName,
        public array $fileNames,
    ) {}

    /**
     * Property path in Extbase notation, e.g. "post.images"
     */
    public function getPropertyPath(): string
    {
        return $this->argumentName . '.' . $this->propertyName;
    }
}
