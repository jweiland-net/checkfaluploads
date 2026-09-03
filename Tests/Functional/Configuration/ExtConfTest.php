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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ExtConfTest extends FunctionalTestCase
{
    public ExtensionConfiguration|MockObject $extensionConfigurationMock;

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->extensionConfigurationMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function getOwnerInitiallyReturnsPlaceholder(): void
    {
        $subject = new ExtConf();

        self::assertSame(
            '[Missing owner in ext settings of checkfaluploads]',
            $subject->getOwner(),
        );
    }

    #[Test]
    public function constructorSetsOwner(): void
    {
        $subject = new ExtConf(owner: 'foo bar');

        self::assertSame(
            'foo bar',
            $subject->getOwner(),
        );
    }

    #[Test]
    public function createMapsOwnerFromExtensionConfiguration(): void
    {
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('get')
            ->with('checkfaluploads')
            ->willReturn([
                'owner' => 'foo bar',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            'foo bar',
            $subject->getOwner(),
        );
    }

    #[Test]
    public function createTrimsOwnerFromExtensionConfiguration(): void
    {
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('get')
            ->with('checkfaluploads')
            ->willReturn([
                'owner' => '  foo bar  ',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            'foo bar',
            $subject->getOwner(),
        );
    }

    #[Test]
    public function createFallsBackToPlaceholderWhenExtensionIsNotConfigured(): void
    {
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('get')
            ->with('checkfaluploads')
            ->willThrowException(
                new ExtensionConfigurationExtensionNotConfiguredException('not configured', 1788363531),
            );

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertSame(
            '[Missing owner in ext settings of checkfaluploads]',
            $subject->getOwner(),
        );
    }

    #[Test]
    public function allUploadRightsChecksAndUserIdStorageAreEnabledByDefault(): void
    {
        $subject = new ExtConf();

        self::assertTrue($subject->isDragUploaderUploadRightsCheckEnabled());
        self::assertTrue($subject->isElementBrowserUploadRightsCheckEnabled());
        self::assertTrue($subject->isStoreBackendUploaderUserIdEnabled());
        self::assertTrue($subject->isStoreFrontendUploaderUserIdEnabled());
    }

    #[Test]
    public function constructorSetsUploadRightsChecksAndUserIdStorage(): void
    {
        $subject = new ExtConf(
            checkDragUploaderUploadRights: false,
            checkElementBrowserUploadRights: false,
            storeBackendUploaderUserId: false,
            storeFrontendUploaderUserId: false,
        );

        self::assertFalse($subject->isDragUploaderUploadRightsCheckEnabled());
        self::assertFalse($subject->isElementBrowserUploadRightsCheckEnabled());
        self::assertFalse($subject->isStoreBackendUploaderUserIdEnabled());
        self::assertFalse($subject->isStoreFrontendUploaderUserIdEnabled());
    }

    #[Test]
    public function createMapsUploadRightsChecksAndUserIdStorageFromExtensionConfiguration(): void
    {
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('get')
            ->with('checkfaluploads')
            ->willReturn([
                'checkDragUploaderUploadRights' => '0',
                'checkElementBrowserUploadRights' => '0',
                'storeBackendUploaderUserId' => '0',
                'storeFrontendUploaderUserId' => '0',
            ]);

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertFalse($subject->isDragUploaderUploadRightsCheckEnabled());
        self::assertFalse($subject->isElementBrowserUploadRightsCheckEnabled());
        self::assertFalse($subject->isStoreBackendUploaderUserIdEnabled());
        self::assertFalse($subject->isStoreFrontendUploaderUserIdEnabled());
    }

    #[Test]
    public function createFallsBackToEnabledUploadRightsChecksAndUserIdStorageWhenExtensionIsNotConfigured(): void
    {
        $this->extensionConfigurationMock
            ->expects($this->once())
            ->method('get')
            ->with('checkfaluploads')
            ->willThrowException(
                new ExtensionConfigurationExtensionNotConfiguredException('not configured', 1788363531),
            );

        $subject = ExtConf::create($this->extensionConfigurationMock);

        self::assertTrue($subject->isDragUploaderUploadRightsCheckEnabled());
        self::assertTrue($subject->isElementBrowserUploadRightsCheckEnabled());
        self::assertTrue($subject->isStoreBackendUploaderUserIdEnabled());
        self::assertTrue($subject->isStoreFrontendUploaderUserIdEnabled());
    }

    #[Test]
    public function getLabelForUserRightsInFrontendContextContainsOwner(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->createRequest(SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $subject = new ExtConf(owner: 'foo bar');

        self::assertStringContainsString(
            'foo bar',
            $subject->getLabelForUserRights(),
        );
    }

    #[Test]
    public function getLabelForUserRightsInBackendContextContainsOwner(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->createRequest(SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $subject = new ExtConf(owner: 'foo bar');

        self::assertStringContainsString(
            'foo bar',
            $subject->getLabelForUserRights(),
        );
    }

    private function createRequest(int $applicationType): ServerRequestInterface
    {
        $language = new SiteLanguage(0, 'en_US.UTF-8', new Uri('/'), []);
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);

        return (new ServerRequest())
            ->withAttribute('applicationType', $applicationType)
            ->withAttribute('language', $language)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
    }
}
