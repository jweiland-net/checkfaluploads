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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * Test case.
 */
class DynamicUploadValidatorHookTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var FormRuntime|ObjectProphecy
     */
    protected $formRuntimeProphecy;

    /**
     * @var RenderableInterface|Page|ObjectProphecy
     */
    protected $renderableProphecy;

    /**
     * @var DynamicUploadValidatorHook
     */
    protected $subject;

    /**
     * @var array
     */
    protected $elementValue = [
        'foo' => 'bar'
    ];

    /**
     * @var array
     */
    protected $requestArguments = [
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
     *
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'form'
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/checkfaluploads'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $this->renderableProphecy = $this->prophesize(Page::class);

        $this->subject = new DynamicUploadValidatorHook();
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->renderableProphecy,
            $this->formRuntimeProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function afterSubmitWithoutUploadElementsWillNotAddValidator(): void
    {
        /** @var FormElementInterface|ObjectProphecy $formElement */
        $formElement = $this->prophesize(GenericFormElement::class);
        $formElement
            ->getProperties()
            ->willReturn([]);

        $formElement
            ->addValidator(Argument::cetera())
            ->shouldNotBeCalled();

        $this->renderableProphecy = $this->prophesize(Page::class);
        $this->renderableProphecy
            ->getElementsRecursively()
            ->shouldBeCalled()
            ->willReturn([
                0 => $formElement->reveal()
            ]);

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeProphecy->reveal(),
                $this->renderableProphecy->reveal(),
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
        /** @var FormElementInterface|ObjectProphecy $fileUploadProphecy */
        $fileUploadProphecy = $this->prophesize(FileUpload::class);
        $fileUploadProphecy
            ->getProperties()
            ->willReturn([]);
        $fileUploadProphecy
            ->getIdentifier()
            ->willReturn('image-upload');

        /** @var FormElementInterface|ObjectProphecy $checkboxElementProphecy */
        $checkboxElementProphecy = $this->prophesize(GenericFormElement::class);
        $checkboxElementProphecy
            ->getProperties()
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'image-upload'
            ]);
        $checkboxElementProphecy
            ->getIdentifier()
            ->willReturn('upload-rights');

        $checkboxElementProphecy
            ->addValidator(Argument::cetera())
            ->shouldNotBeCalled();

        $this->renderableProphecy = $this->prophesize(Page::class);
        $this->renderableProphecy
            ->getElementsRecursively()
            ->shouldBeCalled()
            ->willReturn([
                0 => $fileUploadProphecy->reveal(),
                1 => $checkboxElementProphecy->reveal()
            ]);

        $requestArguments = $this->requestArguments;
        $requestArguments['image-upload']['error'] = 4;

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeProphecy->reveal(),
                $this->renderableProphecy->reveal(),
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
        /** @var FormElementInterface|ObjectProphecy $fileUploadProphecy */
        $fileUploadProphecy = $this->prophesize(FileUpload::class);
        $fileUploadProphecy
            ->getProperties()
            ->willReturn([]);
        $fileUploadProphecy
            ->getIdentifier()
            ->willReturn('image-upload');

        /** @var FormElementInterface|ObjectProphecy $checkboxElementProphecy */
        $checkboxElementProphecy = $this->prophesize(GenericFormElement::class);
        $checkboxElementProphecy
            ->getProperties()
            ->willReturn([
                'checkboxType' => 'uploadRights',
                'referenceUploadIdentifier' => 'image-upload'
            ]);
        $checkboxElementProphecy
            ->getIdentifier()
            ->willReturn('upload-rights');

        $checkboxElementProphecy
            ->addValidator(Argument::type(NotEmptyValidator::class))
            ->shouldBeCalled();

        $this->renderableProphecy = $this->prophesize(Page::class);
        $this->renderableProphecy
            ->getElementsRecursively()
            ->shouldBeCalled()
            ->willReturn([
                0 => $fileUploadProphecy->reveal(),
                1 => $checkboxElementProphecy->reveal()
            ]);

        self::assertSame(
            $this->elementValue,
            $this->subject->afterSubmit(
                $this->formRuntimeProphecy->reveal(),
                $this->renderableProphecy->reveal(),
                $this->elementValue,
                $this->requestArguments
            )
        );
    }
}
