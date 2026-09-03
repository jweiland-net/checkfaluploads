<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\EventListener;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\EventListener\UserMarkedCheckboxForRightsEventListener;
use JWeiland\Checkfaluploads\Helper\MessageHelper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\Event\BeforeFileAddedEvent;
use TYPO3\CMS\Core\Resource\Event\BeforeFileReplacedEvent;
use TYPO3\CMS\Core\Resource\Exception\InsufficientUserPermissionsException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class UserMarkedCheckboxForRightsEventListenerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');

        $this->setUpBackendUser(1);
    }

    #[Test]
    public function checkForAddedFileThrowsExceptionOnDragUploaderRouteWhenRightsCheckIsEnabledAndMissingRights(): void
    {
        $this->expectException(InsufficientUserPermissionsException::class);

        $this->setRequestWithRoute(null);

        $this->createSubject(new ExtConf())->checkForAddedFile($this->createFileAddedEvent());
    }

    #[Test]
    public function checkForAddedFileDoesNotThrowOnDragUploaderRouteWhenBothCoveredSurfacesAreDisabled(): void
    {
        $this->setRequestWithRoute(null);

        $this->createSubject(
            new ExtConf(checkFileListUploadRights: false, checkFormEngineUploadRights: false),
        )->checkForAddedFile($this->createFileAddedEvent());

        self::addToAssertionCount(1);
    }

    #[Test]
    public function checkForAddedFileThrowsExceptionOnElementBrowserRouteWhenRightsCheckIsEnabledAndMissingRights(): void
    {
        $this->expectException(InsufficientUserPermissionsException::class);

        $this->setRequestWithRoute('tce_file');

        $this->createSubject(new ExtConf())->checkForAddedFile($this->createFileAddedEvent());
    }

    #[Test]
    public function checkForAddedFileDoesNotThrowOnElementBrowserRouteWhenElementBrowserCheckIsDisabled(): void
    {
        $this->setRequestWithRoute('tce_file');

        $this->createSubject(
            new ExtConf(checkElementBrowserUploadRights: false),
        )->checkForAddedFile($this->createFileAddedEvent());

        self::addToAssertionCount(1);
    }

    #[Test]
    public function checkForReplacedFileThrowsExceptionWhenRightsCheckIsEnabledAndMissingRights(): void
    {
        $this->expectException(InsufficientUserPermissionsException::class);

        $this->setRequestWithRoute(null);

        $file = $this->createMock(FileInterface::class);
        $file->method('getName')->willReturn('image.jpg');

        $this->createSubject(new ExtConf())->checkForReplacedFile(
            new BeforeFileReplacedEvent($file, '/tmp/image.jpg'),
        );
    }

    private function createSubject(ExtConf $extConf): UserMarkedCheckboxForRightsEventListener
    {
        return new UserMarkedCheckboxForRightsEventListener(
            $this->createMock(ExtendedFileUtility::class),
            $this->createMock(MessageHelper::class),
            $extConf,
        );
    }

    private function createFileAddedEvent(): BeforeFileAddedEvent
    {
        return new BeforeFileAddedEvent(
            'image.jpg',
            '/tmp/image.jpg',
            $this->createMock(Folder::class),
            $this->createMock(ResourceStorage::class),
            $this->createMock(DriverInterface::class),
        );
    }

    private function setRequestWithRoute(?string $routeIdentifier): void
    {
        $request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withParsedBody([]);

        if ($routeIdentifier !== null) {
            $request = $request->withAttribute('route', new Route('/some/path', ['_identifier' => $routeIdentifier]));
        }

        $GLOBALS['TYPO3_REQUEST'] = $request;
    }
}
