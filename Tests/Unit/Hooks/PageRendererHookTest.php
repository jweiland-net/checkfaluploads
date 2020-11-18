<?php

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Unit\Configuration;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Hooks\PageRendererHook;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class PageRendererHookTest extends UnitTestCase
{
    /**
     * @var PageRendererHook
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new PageRendererHook();
    }

    public function tearDown()
    {
        unset(
            $this->subject
        );
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
