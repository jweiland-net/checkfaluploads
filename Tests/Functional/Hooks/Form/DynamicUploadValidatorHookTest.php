<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Hooks\Form;

use JWeiland\Checkfaluploads\Hooks\Form\DynamicUploadValidatorHook;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Test case.
 */
class DynamicUploadValidatorHookTest extends FunctionalTestCase
{
    /**
     * @var FormRuntime|MockObject
     */
    protected $formRuntimeMock;

    /**
     * @var RenderableInterface|Page|MockObject
     */
    protected $renderableMock;

    /**
     * @var DynamicUploadValidatorHook|null
     */
    protected $subject = null;

    /**
     * Core extensions to load.
     *
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'form',
    ];

    /**
     * @var array|string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/checkfaluploads'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->formRuntimeMock = $this->createMock(FormRuntime::class);
        $this->renderableMock = $this->createMock(Page::class);

        $this->subject = new DynamicUploadValidatorHook();
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->renderableMock,
            $this->formRuntimeMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function afterSubmitWithoutFileUploadWillReturnOriginalElementValue(): void
    {
        self::assertSame(
            'Test',
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->createMock(Page::class),
                'Test'
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithNullValueWillReturnNull(): void
    {
        self::assertNull(
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->createMock(FileUpload::class),
                null
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithUploadedFileErrorWillReturnNull(): void
    {
        self::assertNull(
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->createMock(FileUpload::class),
                [
                    'error' => 1,
                ]
            )
        );
    }

    public function invalidResourcePointerDataProvider(): array
    {
        return [
            'empty array' => [[]],
            'invalid array structure' => [['eat' => 'apple']],
            'empty submitted file' => [['submittedFile' => []]],
            'empty resource pointer' => [['submittedFile' => ['resourcePointer' => '']]],
        ];
    }

    /**
     * @test
     *
     * @dataProvider invalidResourcePointerDataProvider
     */
    public function afterSubmitWithEmptyResourcePointerWillReturnNull($invalidResourcePointer): void
    {
        self::assertNull(
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->createMock(FileUpload::class),
                $invalidResourcePointer
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithNoChildElementsReturnsOriginalEmptyValue(): void
    {
        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([]);

        /** @var FileUpload|MockObject $fileUploadMock */
        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithNoCheckboxElementReturnsOriginalEmptyValue(): void
    {
        /** @var GenericFormElement|MockObject $genericFormElementMock */
        $genericFormElementMock = $this->createMock(GenericFormElement::class);
        $genericFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Text');

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $genericFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithEmptyCheckboxPropertiesReturnsOriginalEmptyValue(): void
    {
        /** @var GenericFormElement|MockObject $checkboxFormElementMock */
        $checkboxFormElementMock = $this->createMock(GenericFormElement::class);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Checkbox');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([]);

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $checkboxFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithEmptyUploadIdentifierReturnsOriginalEmptyValue(): void
    {
        /** @var GenericFormElement|MockObject $checkboxFormElementMock */
        $checkboxFormElementMock = $this->createMock(GenericFormElement::class);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Checkbox');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
            ]);

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $checkboxFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithDifferentIdentifiersReturnsOriginalEmptyValue(): void
    {
        /** @var GenericFormElement|MockObject $checkboxFormElementMock */
        $checkboxFormElementMock = $this->createMock(GenericFormElement::class);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Checkbox');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'Different Identifier',
            ]);

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $checkboxFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('Other Identifier');

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithMissingCheckboxRequestReturnsOriginalEmptyValue(): void
    {
        /** @var GenericFormElement|MockObject $checkboxFormElementMock */
        $checkboxFormElementMock = $this->createMock(GenericFormElement::class);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Checkbox');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'CorrectIdentifier',
            ]);

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $checkboxFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('CorrectIdentifier');

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithActivatedCheckboxReturnsOriginalEmptyValue(): void
    {
        /** @var GenericFormElement|MockObject $checkboxFormElementMock */
        $checkboxFormElementMock = $this->createMock(GenericFormElement::class);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Checkbox');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'FileUploadIdentifier',
            ]);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('CheckboxIdentifier');

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $checkboxFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('FileUploadIdentifier');

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertSame(
            $uploadedFile,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile,
                [
                    'CheckboxIdentifier' => '1',
                ]
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithDeactivatedCheckboxWillAddNotEmptyValidator(): void
    {
        /** @var GenericFormElement|MockObject $checkboxFormElementMock */
        $checkboxFormElementMock = $this->createMock(GenericFormElement::class);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getType')
            ->willReturn('Checkbox');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'FileUploadIdentifier',
            ]);
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('CheckboxIdentifier');
        $checkboxFormElementMock
            ->expects(self::atLeastOnce())
            ->method('addValidator')
            ->with(
                self::isInstanceOf(NotEmptyValidator::class)
            );

        /** @var Page|MockObject $pageMock */
        $pageMock = $this->createMock(Page::class);
        $pageMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                $checkboxFormElementMock,
            ]);

        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getParentRenderable')
            ->willReturn($pageMock);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('FileUploadIdentifier');

        $uploadedFile = [
            'error' => 0,
            'path' => '/tmp/cth8w9mth'
        ];

        self::assertNull(
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $fileUploadMock,
                $uploadedFile,
                [
                    'CheckboxIdentifier' => '',
                ]
            )
        );
    }
}
