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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Test case.
 */
class ImageRightsMessageViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function renderStaticReturnsMessageWithOwner(): void
    {
        $extConf = new ExtConf(new ExtensionConfiguration());
        $extConf->setOwner('Stefan Froemken');

        GeneralUtility::setSingletonInstance(ExtConf::class, $extConf);

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource(
            '<html lang="en"
                xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                xmlns:c="http://typo3.org/ns/JWeiland/Checkfaluploads/ViewHelpers"
                data-namespace-typo3-fluid="true">

                {c:imageRightsMessage(languageKey: \'frontend.imageUserRights\', extensionName: \'checkfaluploads\')}
            </html>'
        );

        self::assertStringContainsString(
            'Stefan Froemken',
            (new TemplateView($context))->render(),
        );
    }
}
