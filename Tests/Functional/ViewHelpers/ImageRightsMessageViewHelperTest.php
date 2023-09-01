<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\ViewHelpers;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\ViewHelpers\ImageRightsMessageViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Test case.
 */
class ImageRightsMessageViewHelperTest extends FunctionalTestCase
{
    protected ?ImageRightsMessageViewHelper $subject = null;

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        /** @var RenderingContextInterface|MockObject $renderingContext */
        $renderingContext = $this->createMock(RenderingContext::class);

        $this->subject = new ImageRightsMessageViewHelper();
        $this->subject->setRenderingContext($renderingContext);
        $this->subject->setArguments(
            [
                'languageKey' => 'frontend.imageUserRights',
                'extensionName' => 'checkfaluploads',
            ]
        );
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
    public function renderStaticInitiallyReturnsPlaceholder(): void
    {
        $message = $this->subject->initializeArgumentsAndRender();

        self::assertStringContainsString(
            'Missing',
            $message
        );
        self::assertStringContainsString(
            'settings',
            $message
        );
        self::assertStringContainsString(
            'checkfaluploads',
            $message
        );
    }

    /**
     * @test
     */
    public function renderStaticReturnsMessageWithOwner(): void
    {
        $extConf = new ExtConf(new ExtensionConfiguration());
        $extConf->setOwner('Stefan Froemken');

        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $message = $this->subject->initializeArgumentsAndRender();

        self::assertStringContainsString(
            'Stefan Froemken',
            $message
        );
        self::assertNotSame(
            'Stefan Froemken',
            $message
        );
    }
}
