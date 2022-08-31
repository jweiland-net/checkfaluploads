<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Hooks;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Hooks\Form\ReplacePlaceholderHook;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * Test case.
 */
class ReplacePlaceholderHookTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var RenderableInterface|GenericFormElement|ObjectProphecy
     */
    protected $renderableProphecy;

    /**
     * @var ExtConf|ObjectProphecy
     */
    protected $extConf;

    /**
     * @var ReplacePlaceholderHook
     */
    protected $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/checkfaluploads'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);

        $this->renderableProphecy = $this->prophesize(GenericFormElement::class);

        $this->extConf = new ExtConf();
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->subject = new ReplacePlaceholderHook();
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->renderableProphecy
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function afterBuildingFinishedWithoutCheckboxTypeWillNotModifyLabel(): void
    {
        /** @var GenericFormElement|ObjectProphecy $formElement */
        $formElement = $this->prophesize(GenericFormElement::class);
        $formElement
            ->getProperties()
            ->willReturn([]);

        $formElement
            ->setLabel(Argument::any())
            ->shouldNotBeCalled();

        $this->subject->afterBuildingFinished($formElement->reveal());
    }

    /**
     * @test
     */
    public function afterBuildingFinishedWithCheckboxTypeWillModifyLabelWithMissingOwner(): void
    {
        $this->extConf->setOwner('');

        /** @var GenericFormElement|ObjectProphecy $formElement */
        $formElement = $this->prophesize(GenericFormElement::class);
        $formElement
            ->getProperties()
            ->willReturn([
                'checkboxType' => 'uploadRights'
            ]);

        $formElement
            ->setLabel(Argument::containingString('[Missing owner in ext settings of checkfaluploads]'))
            ->shouldBeCalled();

        $this->subject->afterBuildingFinished($formElement->reveal());
    }

    /**
     * @test
     */
    public function afterBuildingFinishedWithCheckboxTypeWillModifyLabelWithOwner(): void
    {
        $this->extConf->setOwner('jweiland.net');

        /** @var GenericFormElement|ObjectProphecy $formElement */
        $formElement = $this->prophesize(GenericFormElement::class);
        $formElement
            ->getProperties()
            ->willReturn([
                'checkboxType' => 'uploadRights'
            ]);

        $formElement
            ->setLabel(Argument::containingString('jweiland.net'))
            ->shouldBeCalled();

        $this->subject->afterBuildingFinished($formElement->reveal());
    }
}
