<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Service;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class FalUploadServiceTest extends FunctionalTestCase
{
    protected ?FalUploadService $subject = null;

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        $this->subject = new FalUploadService();
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function checkFileWithRightsWillReturnNull(): void
    {
        $uploadedFileMock = $this->createMock(UploadedFile::class);

        // Set up the mock to simulate an uploaded file with the correct "rights" metadata
        $uploadedFileMock
            ->expects($this->atLeastOnce())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);
        $uploadedFileMock
            ->expects($this->atLeastOnce())
            ->method('getSize')
            ->willReturn(100);

        // Set the field name we're checking for ("rights") and other necessary parameters
        $fieldName = 'rights';

        // Run the checkFile method
        $result = $this->subject->checkFile($uploadedFileMock, [$fieldName => 1]);

        // Assert that no error is returned (i.e., it returns null)
        self::assertNull($result);
    }

    #[Test]
    public function checkFileWithNoRightsWillReturnErrorMessage(): void
    {
        // Mocking the UploadedFile instance
        $uploadedFileMock = $this->createMock(UploadedFile::class);

        // Set up the mock to simulate an uploaded file with the correct parameters
        $uploadedFileMock
            ->expects($this->atLeastOnce())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);  // No upload error
        $uploadedFileMock
            ->expects($this->atLeastOnce())
            ->method('getSize')
            ->willReturn(100);  // Non-zero file size

        // Run the checkFile method with parameters that expect "rights"
        $fieldName = 'rights';

        // Run the checkFile method
        $error = $this->subject->checkFile($uploadedFileMock, [$fieldName => 0]);

        // Assert that the error is returned
        self::assertNotNull($error);  // Ensure an error was returned

        // Assert that the error message contains "not allowed"
        self::assertStringContainsString('not allowed', $error->getMessage());

        // Assert that the error message contains "checkbox"
        self::assertStringContainsString('checkbox', $error->getMessage());

        // Assert that the error code is the expected one
        self::assertSame(1604050225, $error->getCode());
    }

    #[Test]
    public function checkFileWithEmptyRightsWillReturnErrorMessage(): void
    {
        // Mocking the UploadedFile instance
        $uploadedFileMock = $this->createMock(UploadedFile::class);

        // Set up the mock to simulate an uploaded file with the correct parameters
        $uploadedFileMock
            ->expects($this->atLeastOnce())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);  // No upload error
        $uploadedFileMock
            ->expects($this->atLeastOnce())
            ->method('getSize')
            ->willReturn(100);  // Non-zero file size

        // Run the checkFile method with the field 'rights' being empty
        $fieldName = 'rights';

        // Run the checkFile method
        $error = $this->subject->checkFile($uploadedFileMock, [$fieldName => '']);

        // Assert that the error is returned (i.e., rights is empty, and error should be triggered)
        self::assertNotNull($error);  // Ensure an error was returned

        // Assert that the error message contains "not allowed"
        self::assertStringContainsString('not allowed', $error->getMessage());

        // Assert that the error message contains "checkbox"
        self::assertStringContainsString('checkbox', $error->getMessage());

        // Assert that the error code is the expected one
        self::assertSame(1604050225, $error->getCode());
    }
}
