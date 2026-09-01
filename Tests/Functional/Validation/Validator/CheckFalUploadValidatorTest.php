<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Validation\Validator;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use JWeiland\Checkfaluploads\Validation\Validator\CheckFalUploadValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class CheckFalUploadValidatorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    private ExtensionService $extensionServiceStub;

    private CheckFalUploadValidator $subject;

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        $this->extensionServiceStub = self::createStub(ExtensionService::class);
        $this->extensionServiceStub->method('getPluginNamespace')->willReturn('tx_pforum_forum');

        $this->subject = new CheckFalUploadValidator(new FalUploadService(), $this->extensionServiceStub);
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extensionServiceStub,
        );

        parent::tearDown();
    }

    #[Test]
    public function validateReturnsNoErrorsWhenRightsCheckboxIsConfirmed(): void
    {
        $this->subject->setOptions(['propertyPath' => 'topic.images']);
        $this->subject->setRequest($this->createRequestStub([
            'tx_pforum_forum' => [
                'topic' => [
                    'images' => [
                        'rights' => '1',
                    ],
                ],
            ],
        ]));

        $result = $this->subject->validate($this->createUploadedFileMock());

        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function validateAddsValidationErrorWhenRightsCheckboxIsNotConfirmed(): void
    {
        $this->subject->setOptions(['propertyPath' => 'topic.images']);
        $this->subject->setRequest($this->createRequestStub([
            'tx_pforum_forum' => [
                'topic' => [
                    'images' => [
                        'rights' => '',
                    ],
                ],
            ],
        ]));

        $result = $this->subject->validate($this->createUploadedFileMock());

        self::assertTrue($result->hasErrors());
        self::assertSame(1604050225, $result->getFirstError()->getCode());
    }

    #[Test]
    public function validateUsesConfiguredFieldNameToLookUpRightsConfiguration(): void
    {
        $this->subject->setOptions([
            'propertyPath' => 'topic.images',
            'fieldName' => 'uploadRights',
        ]);
        $this->subject->setRequest($this->createRequestStub([
            'tx_pforum_forum' => [
                'topic' => [
                    'images' => [
                        // "rights" is unrelated here on purpose, "uploadRights" is the configured fieldName
                        'rights' => '',
                        'uploadRights' => '1',
                    ],
                ],
            ],
        ]));

        $result = $this->subject->validate($this->createUploadedFileMock());

        self::assertFalse($result->hasErrors());
    }

    #[Test]
    #[DataProvider('nonUploadedFileValueDataProvider')]
    public function validateThrowsExceptionWhenValueIsNotAnUploadedFile(mixed $value): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1788269915);

        $this->subject->validate($value);
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function nonUploadedFileValueDataProvider(): array
    {
        return [
            'string value' => ['not-a-file'],
            'integer value' => [123],
            'array value' => [['not', 'a', 'file']],
            'boolean true value' => [true],
            'plain object value' => [new \stdClass()],
        ];
    }

    #[Test]
    public function validateThrowsExceptionWhenRequestWasNotSet(): void
    {
        $this->subject->setOptions(['propertyPath' => 'topic.images']);

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1788269828);

        $this->subject->validate($this->createUploadedFileMock());
    }

    #[Test]
    public function validateThrowsExceptionWhenPropertyPathDoesNotExistInParsedBody(): void
    {
        $this->subject->setOptions(['propertyPath' => 'topic.nonExistingProperty']);
        $this->subject->setRequest($this->createRequestStub([
            'tx_pforum_forum' => [
                'topic' => [
                    'images' => [
                        'rights' => '1',
                    ],
                ],
            ],
        ]));

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1788270392);

        $this->subject->validate($this->createUploadedFileMock());
    }

    #[Test]
    public function validatePrependsResolvedPluginNamespaceBeforeResolvingPropertyPath(): void
    {
        $extensionServiceStub = self::createStub(ExtensionService::class);
        $extensionServiceStub->method('getPluginNamespace')->willReturn('tx_wrong_namespace');

        $subject = new CheckFalUploadValidator(new FalUploadService(), $extensionServiceStub);
        $subject->setOptions(['propertyPath' => 'topic.images']);
        $subject->setRequest($this->createRequestStub([
            'tx_pforum_forum' => [
                'topic' => [
                    'images' => [
                        'rights' => '1',
                    ],
                ],
            ],
        ]));

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1788270392);

        $subject->validate($this->createUploadedFileMock());
    }

    private function createRequestStub(array $parsedBody): Request
    {
        $requestStub = self::createStub(Request::class);
        $requestStub->method('getControllerExtensionName')->willReturn('Pforum');
        $requestStub->method('getPluginName')->willReturn('Forum');
        $requestStub->method('getParsedBody')->willReturn($parsedBody);

        return $requestStub;
    }

    private function createUploadedFileMock(): UploadedFile
    {
        $uploadedFileMock = self::createMock(UploadedFile::class);
        $uploadedFileMock->method('getError')->willReturn(UPLOAD_ERR_OK);
        $uploadedFileMock->method('getSize')->willReturn(100);

        return $uploadedFileMock;
    }
}
