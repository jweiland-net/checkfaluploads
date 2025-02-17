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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ExtConfTest extends FunctionalTestCase
{
    protected ExtConf $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
            ->create('default');

        $this->subject = new ExtConf(new ExtensionConfiguration());
    }

    public function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    private function getRequestForContext(int $applicationType): ServerRequestInterface
    {
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

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');

        // Request to default page
        $request = new ServerRequest('https://example.com', 'GET');
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', $applicationType);
        $request = $request->withAttribute('frontend.typoscript', $frontendTypoScript);

        return $request->withAttribute('language', $site->getDefaultLanguage());
    }

    /**
     * @test
     */
    public function getOwnerInitiallyReturnsPlaceholder(): void
    {
        self::assertSame(
            '', // It's set as '' in default TypoScript
            $this->subject->getOwner(),
        );
    }

    /**
     * @test
     */
    public function setOwnerSetsOwner(): void
    {
        $this->subject->setOwner('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOwner(),
        );
    }

    /**
     * @test
     */
    public function getLabelForUserRightsInFrontendContextContainsOwner(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->getRequestForContext(SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $this->subject->setOwner('foo bar');

        self::assertStringContainsString(
            'foo bar',
            $this->subject->getLabelForUserRights(),
        );
    }

    /**
     * @test
     */
    public function getLabelForUserRightsInBackendContextContainsOwner(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->getRequestForContext(SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $this->subject->setOwner('foo bar');

        self::assertStringContainsString(
            'foo bar',
            $this->subject->getLabelForUserRights(),
        );
    }
}
