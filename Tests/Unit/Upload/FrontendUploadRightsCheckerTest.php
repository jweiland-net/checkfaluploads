<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Unit\Upload;

use JWeiland\Checkfaluploads\Upload\FrontendUploadRightsChecker;
use JWeiland\Checkfaluploads\Upload\UploadRightsViolation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
final class FrontendUploadRightsCheckerTest extends UnitTestCase
{
    private FrontendUploadRightsChecker $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontendUploadRightsChecker();
    }

    #[Test]
    public function findViolationsReturnsEmptyArrayWithoutParsedBody(): void
    {
        $request = (new ServerRequest('https://example.com/', 'POST'))
            ->withUploadedFiles(['tx_myext_myplugin' => ['record' => ['images' => [$this->createUploadedFile()]]]]);

        self::assertSame([], $this->subject->findViolations($request));
    }

    #[Test]
    public function findViolationsReturnsEmptyArrayWithoutRightsField(): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['title' => 'Foo']]],
            ['tx_myext_myplugin' => ['record' => ['images' => [$this->createUploadedFile()]]]],
        );

        self::assertSame([], $this->subject->findViolations($request));
    }

    #[Test]
    public function findViolationsIgnoresRightsFieldWithOtherName(): void
    {
        // Field name of an extension which attaches CheckFalUploadValidator with option "fieldName"
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['images' => ['uploadRights' => '']]]],
            ['tx_myext_myplugin' => ['record' => ['images' => [$this->createUploadedFile()]]]],
        );

        self::assertSame([], $this->subject->findViolations($request));
    }

    #[Test]
    public function findViolationsReturnsEmptyArrayWithoutUploadedFile(): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['images' => ['rights' => '']]]],
            [],
        );

        self::assertSame([], $this->subject->findViolations($request));
    }

    #[Test]
    public function findViolationsIgnoresUploadsWithUploadError(): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['images' => ['rights' => '']]]],
            ['tx_myext_myplugin' => ['record' => ['images' => [$this->createUploadedFile(UPLOAD_ERR_INI_SIZE)]]]],
        );

        self::assertSame([], $this->subject->findViolations($request));
    }

    #[Test]
    public function findViolationsReturnsEmptyArrayIfRightsFieldIsChecked(): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['images' => ['rights' => '1']]]],
            ['tx_myext_myplugin' => ['record' => ['images' => [$this->createUploadedFile()]]]],
        );

        self::assertSame([], $this->subject->findViolations($request));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function uncheckedRightsValueDataProvider(): array
    {
        return [
            'empty string (hidden field of checkbox)' => [''],
            'zero as string' => ['0'],
            'whitespace' => [' '],
            'null' => [null],
            'array' => [['1']],
        ];
    }

    #[Test]
    #[DataProvider('uncheckedRightsValueDataProvider')]
    public function findViolationsReturnsViolationIfRightsFieldIsNotChecked(mixed $rightsValue): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['title' => 'Foo', 'images' => ['rights' => $rightsValue]]]],
            ['tx_myext_myplugin' => ['record' => ['images' => [
                $this->createUploadedFile(UPLOAD_ERR_OK, 'a.jpg'),
                $this->createUploadedFile(UPLOAD_ERR_OK, 'b.jpg'),
            ]]]],
        );

        $violations = $this->subject->findViolations($request);

        self::assertEquals(
            [new UploadRightsViolation('tx_myext_myplugin', 'record', 'images', ['a.jpg', 'b.jpg'])],
            $violations,
        );
        self::assertSame('record.images', $violations[0]->getPropertyPath());
    }

    #[Test]
    public function findViolationsSupportsSingleUploadedFile(): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => ['image' => ['rights' => '']]]],
            ['tx_myext_myplugin' => ['record' => ['image' => $this->createUploadedFile(UPLOAD_ERR_OK, 'single.png')]]],
        );

        self::assertEquals(
            [new UploadRightsViolation('tx_myext_myplugin', 'record', 'image', ['single.png'])],
            $this->subject->findViolations($request),
        );
    }

    #[Test]
    public function findViolationsChecksEachUploadFieldSeparately(): void
    {
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => [
                'images' => ['rights' => '1'],
                'documents' => ['rights' => ''],
            ]]],
            ['tx_myext_myplugin' => ['record' => [
                'images' => [$this->createUploadedFile(UPLOAD_ERR_OK, 'image.jpg')],
                'documents' => [$this->createUploadedFile(UPLOAD_ERR_OK, 'document.pdf')],
            ]]],
        );

        self::assertEquals(
            [new UploadRightsViolation('tx_myext_myplugin', 'record', 'documents', ['document.pdf'])],
            $this->subject->findViolations($request),
        );
    }

    #[Test]
    public function removeViolatingUploadsRemovesOnlyFilesOfViolatingUploadField(): void
    {
        $image = $this->createUploadedFile(UPLOAD_ERR_OK, 'image.jpg');
        $request = $this->createRequest(
            ['tx_myext_myplugin' => ['record' => [
                'images' => ['rights' => '1'],
                'documents' => ['rights' => ''],
            ]]],
            ['tx_myext_myplugin' => ['record' => [
                'images' => [$image],
                'documents' => [$this->createUploadedFile(UPLOAD_ERR_OK, 'document.pdf')],
            ]]],
        );

        $result = $this->subject->removeViolatingUploads($request, $this->subject->findViolations($request));

        self::assertSame(
            ['tx_myext_myplugin' => ['record' => ['images' => [$image]]]],
            $result->getUploadedFiles(),
        );
        self::assertSame($request->getParsedBody(), $result->getParsedBody());
    }

    #[Test]
    public function removeViolatingUploadsReturnsSameRequestWithoutViolations(): void
    {
        $request = $this->createRequest([], []);

        self::assertSame($request, $this->subject->removeViolatingUploads($request, []));
    }

    /**
     * @param array<string, mixed> $parsedBody
     * @param array<string, mixed> $uploadedFiles
     */
    private function createRequest(array $parsedBody, array $uploadedFiles): ServerRequest
    {
        return (new ServerRequest('https://example.com/', 'POST'))
            ->withParsedBody($parsedBody)
            ->withUploadedFiles($uploadedFiles);
    }

    private function createUploadedFile(int $error = UPLOAD_ERR_OK, string $fileName = 'image.jpg'): UploadedFile
    {
        return new UploadedFile('php://memory', 100, $error, $fileName, 'image/jpeg');
    }
}
