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

    protected array $requestArguments = [
        'foo' => 'bar',
        'image-upload' => [
            'error' => 0,
            'name' => 'schlumpf',
            'size' => 123,
            'tmp_name' => '/tmp/nr4378tg',
            'type' => 2,
        ],
        'upload-rights' => '0',
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
            ->getProperties()
            ->willReturn([]);

        $formElement
            ->addValidator(Argument::cetera())
            ->shouldNotBeCalled();

        $this->renderableMock = $this->prophesize(Page::class);
        $this->renderableMock
            ->getElementsRecursively()
            ->shouldBeCalled()
            ->willReturn([
                0 => $formElement->reveal(),
            ]);

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeMock->reveal(),
                $this->renderableMock->reveal(),
                $this->elementValue,
                $this->requestArguments
            )
        );
    }

    /**
     * @test
     */
    public function afterSubmitWithFailedUploadWillNotAddValidator(): void
    {
        /** @var FormElementInterface|MockObject $fileUploadProphecy */
        $fileUploadProphecy = $this->createMock(FileUpload::class);
        $fileUploadProphecy
            ->getProperties()
            ->willReturn([]);
        $fileUploadProphecy
            ->getIdentifier()
            ->willReturn('image-upload');

        /** @var FormElementInterface|MockObject $checkboxElementProphecy */
        $checkboxElementProphecy = $this->createMock(GenericFormElement::class);
        $checkboxElementProphecy
            ->getProperties()
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'image-upload',
            ]);
        $checkboxElementProphecy
            ->getIdentifier()
            ->willReturn('upload-rights');

        $checkboxElementProphecy
            ->addValidator(Argument::cetera())
            ->shouldNotBeCalled();

        $this->renderableMock = $this->createMock(Page::class);
        $this->renderableMock
            ->getElementsRecursively()
            ->shouldBeCalled()
            ->willReturn([
                0 => $fileUploadProphecy->reveal(),
                1 => $checkboxElementProphecy->reveal(),
            ]);

        $requestArguments = $this->requestArguments;
        $requestArguments['image-upload']['error'] = 4;

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeMock->reveal(),
                $this->renderableMock->reveal(),
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
        /** @var FormElementInterface|MockObject $fileUploadProphecy */
        $fileUploadProphecy = $this->createMock(FileUpload::class);
        $fileUploadProphecy
            ->getProperties()
            ->willReturn([]);
        $fileUploadProphecy
            ->getIdentifier()
            ->willReturn('image-upload');

        /** @var FormElementInterface|MockObject $checkboxElementProphecy */
        $checkboxElementProphecy = $this->createMock(GenericFormElement::class);
        $checkboxElementProphecy
            ->getProperties()
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'image-upload',
            ]);
        $checkboxElementProphecy
            ->getIdentifier()
            ->willReturn('upload-rights');

        $checkboxElementProphecy
            ->addValidator(Argument::type(NotEmptyValidator::class))
            ->shouldBeCalled();

        $this->renderableMock = $this->createMock(Page::class);
        $this->renderableMock
            ->getElementsRecursively()
            ->shouldBeCalled()
            ->willReturn([
                0 => $fileUploadProphecy->reveal(),
                1 => $checkboxElementProphecy->reveal(),
            ]);

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeMock->reveal(),
                $this->renderableMock->reveal(),
                $this->elementValue,
                $this->requestArguments
            )
        );
    }
}
