<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Configuration;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Hooks\PageRendererHook;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class PageRendererHookTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var PageRendererHook
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

        $this->subject = new PageRendererHook();
    }

    public function tearDown(): void
    {
        unset(
            $this->subject
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function replaceDragUploaderWillReplaceDragUploader()
    {
        $extConf = new ExtConf();
        $extConf->setOwner('Stefan Froemken');

        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        /** @var PageRenderer|ObjectProphecy $pageRenderer */
        $pageRenderer = $this->prophesize(PageRenderer::class);
        $pageRenderer
            ->addInlineLanguageLabelFile(
                'EXT:checkfaluploads/Resources/Private/Language/locallang.xlf'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->addInlineSetting(
                'checkfaluploads',
                'owner',
                'Stefan Froemken'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/DragUploader'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser'
            )
            ->shouldNotBeCalled();

        $this->subject->replaceDragUploader(
            [
                'jsInline' => [
                    'RequireJS-Module-TYPO3/CMS/Backend/DragUploader' => 'test'
                ]
            ],
            $pageRenderer->reveal()
        );
    }

    /**
     * @test
     */
    public function replaceDragUploaderWillNotLoadJavaScriptForElementBrowser()
    {
        $extConf = new ExtConf();
        $extConf->setOwner('Stefan Froemken');

        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        /** @var PageRenderer|ObjectProphecy $pageRenderer */
        $pageRenderer = $this->prophesize(PageRenderer::class);
        $pageRenderer
            ->addInlineLanguageLabelFile(
                'EXT:checkfaluploads/Resources/Private/Language/locallang.xlf'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->addInlineSetting(
                'checkfaluploads',
                'owner',
                'Stefan Froemken'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/DragUploader'
            )
            ->shouldNotBeCalled();

        $pageRenderer
            ->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser'
            )
            ->shouldNotBeCalled();

        $this->subject->replaceDragUploader([], $pageRenderer->reveal());
    }

    /**
     * @test
     */
    public function replaceDragUploaderWillLoadJavaScriptForElementBrowser()
    {
        $_GET['route'] = '/wizard/record/browse';
        $_GET['mode'] = 'file';

        $extConf = new ExtConf();
        $extConf->setOwner('Stefan Froemken');

        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        /** @var PageRenderer|ObjectProphecy $pageRenderer */
        $pageRenderer = $this->prophesize(PageRenderer::class);
        $pageRenderer
            ->addInlineLanguageLabelFile(
                'EXT:checkfaluploads/Resources/Private/Language/locallang.xlf'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->addInlineSetting(
                'checkfaluploads',
                'owner',
                'Stefan Froemken'
            )
            ->shouldBeCalled();

        $pageRenderer
            ->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/DragUploader'
            )
            ->shouldNotBeCalled();

        $pageRenderer
            ->loadRequireJsModule(
                'TYPO3/CMS/Checkfaluploads/AddCheckboxForRightsInElementBrowser'
            )
            ->shouldBeCalled();

        $this->subject->replaceDragUploader([], $pageRenderer->reveal());
    }
}
