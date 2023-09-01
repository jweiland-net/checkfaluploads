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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ReplacePlaceholderHookTest extends FunctionalTestCase
{
    /**
     * @var RenderableInterface|GenericFormElement|MockObject
     */
    protected $renderableMock;

    protected ExtConf $extConf;

    protected ?ReplacePlaceholderHook $subject = null;

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

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        $this->renderableMock = $this->createMock(GenericFormElement::class);

        $this->extConf = new ExtConf(new ExtensionConfiguration());
        GeneralUtility::setSingletonInstance(ExtConf::class, $this->extConf);

        $this->subject = new ReplacePlaceholderHook($this->extConf);
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->renderableMock
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function afterBuildingFinishedWithoutCheckboxTypeWillNotModifyLabel(): void
    {
        /** @var GenericFormElement|MockObject $formElement */
        $formElement = $this->createMock(GenericFormElement::class);
        $formElement
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([]);

        $formElement
            ->expects(self::never())
            ->method('setLabel');

        $this->subject->afterBuildingFinished($formElement);
    }

    /**
     * @test
     */
    public function afterBuildingFinishedWithCheckboxTypeWillModifyLabelWithMissingOwner(): void
    {
        $this->extConf->setOwner('');

        /** @var GenericFormElement|MockObject $formElement */
        $formElement = $this->createMock(GenericFormElement::class);
        $formElement
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
            ]);

        $formElement
            ->expects(self::atLeastOnce())
            ->method('setLabel')
            ->with(self::stringContains('[Missing owner in ext settings of checkfaluploads]'));

        $this->subject->afterBuildingFinished($formElement);
    }

    /**
     * @test
     */
    public function afterBuildingFinishedWithCheckboxTypeWillModifyLabelWithOwner(): void
    {
        $this->extConf->setOwner('jweiland.net');

        /** @var GenericFormElement|MockObject $formElement */
        $formElement = $this->createMock(GenericFormElement::class);
        $formElement
            ->expects(self::atLeastOnce())
            ->method('getProperties')
            ->willReturn([
                'checkboxType' => 'uploadRights',
            ]);

        $formElement
            ->expects(self::atLeastOnce())
            ->method('setLabel')
            ->with(self::stringContains('jweiland.net'));

        $this->subject->afterBuildingFinished($formElement);
    }
}
