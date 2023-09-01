<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Hooks;

use JWeiland\Checkfaluploads\Hooks\Form\DynamicUploadValidatorHook;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

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

    protected ?DynamicUploadValidatorHook $subject = null;

    protected array $elementValue = [
        'foo' => 'bar',
    ];

    /**
     * Core extensions to load.
     */
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
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
    public function afterSubmitWithoutUploadElementsWillNotAddValidator(): void
    {
        /** @var FormElementInterface|MockObject $formElement */
        $formElement = $this->createMock(GenericFormElement::class);
        $formElement
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([]);

        $formElement
            ->expects(self::never())
            ->method('addValidator');

        $this->renderableMock = $this->createMock(Page::class);
        $this->renderableMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                0 => $formElement,
            ]);

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->renderableMock,
                $this->elementValue,
                [
                    'foo' => 'bar',
                    'image-upload' => new UploadedFile(
                        'schlumpf.png',
                        123,
                        0,
                        '/tmp/nr4378tg',
                        'image/png'
                    ),
                    'upload-rights' => '0',
                ]
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithFailedUploadWillNotAddValidator(): void
    {
        /** @var FormElementInterface|MockObject $fileUploadMock */
        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([]);

        $fileUploadMock
            ->method('getIdentifier')
            ->willReturn('image-upload');

        /** @var FormElementInterface|MockObject $checkboxElementMock */
        $checkboxElementMock = $this->createMock(GenericFormElement::class);
        $checkboxElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'image-upload',
            ]);

        $checkboxElementMock
            ->method('getIdentifier')
            ->willReturn('upload-rights');

        $checkboxElementMock
            ->expects(self::never())
            ->method('addValidator');

        $this->renderableMock = $this->createMock(Page::class);
        $this->renderableMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                0 => $fileUploadMock,
                1 => $checkboxElementMock,
            ]);

        $requestArguments = [
            'foo' => 'bar',
            'image-upload' => new UploadedFile(
                'schlumpf.png',
                123,
                4,
                '/tmp/nr4378tg',
                'image/png'
            ),
            'upload-rights' => '0',
        ];

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->renderableMock,
                $this->elementValue,
                $requestArguments
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithUploadElementsWillAddValidator(): void
    {
        /** @var FormElementInterface|MockObject $fileUploadMock */
        $fileUploadMock = $this->createMock(FileUpload::class);
        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([]);

        $fileUploadMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('image-upload');

        /** @var FormElementInterface|MockObject $checkboxElementMock */
        $checkboxElementMock = $this->createMock(GenericFormElement::class);
        $checkboxElementMock
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'image-upload',
            ]);

        $checkboxElementMock
            ->expects(self::atLeastOnce())
            ->method('getIdentifier')
            ->willReturn('upload-rights');

        $checkboxElementMock
            ->expects(self::atLeastOnce())
            ->method('addValidator')
            ->with(self::isInstanceOf(NotEmptyValidator::class));

        $this->renderableMock = $this->createMock(Page::class);
        $this->renderableMock
            ->expects(self::atLeastOnce())
            ->method('getElementsRecursively')
            ->willReturn([
                0 => $fileUploadMock,
                1 => $checkboxElementMock,
            ]);

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeMock,
                $this->renderableMock,
                $this->elementValue,
                [
                    'foo' => 'bar',
                    'image-upload' => new UploadedFile(
                        'schlumpf.png',
                        123,
                        0,
                        '/tmp/nr4378tg',
                        'image/png'
                    ),
                    'upload-rights' => '0',
                ]
            )
        );
    }
}
