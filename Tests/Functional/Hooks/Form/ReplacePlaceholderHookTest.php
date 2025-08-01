<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Hooks\Form;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Hooks\Form\ReplacePlaceholderHook;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ReplacePlaceholderHookTest extends FunctionalTestCase
{
    protected RenderableInterface|GenericFormElement|MockObject $renderableMock;

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

        $site = new Site('https://example.com', 1, [
            'base' => '/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'locale' => 'en_US.UTF-8',
                    'base' => '/en/',
                    'enabled' => false,
                ],
            ],
        ]);

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'page' => 'PAGE',
            'page.' => [
                'config.' => [
                    'disableAllHeaderCode' => '1',
                ],
                '10' => 'TEXT',
                '10.' => [
                    'value' => '<p>I like apples</p>',
                ],
            ],
        ]);

        // Request to default page
        $request = new ServerRequest('https://example.com', 'GET');
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);
        $GLOBALS['TYPO3_REQUEST'] = $request->withAttribute('language', $site->getDefaultLanguage());

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');

        $this->renderableMock = $this->createMock(GenericFormElement::class);

        $this->extConf = new ExtConf(new ExtensionConfiguration());

        $this->subject = new ReplacePlaceholderHook($this->extConf);
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
            $this->renderableMock,
        );

        parent::tearDown();
    }

    #[Test]
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

    #[Test]
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
            ->with(self::stringContains('You have to confirm that  has unrestricted rights to use the files you will upload'));

        $this->subject->afterBuildingFinished($formElement);
    }

    #[Test]
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
