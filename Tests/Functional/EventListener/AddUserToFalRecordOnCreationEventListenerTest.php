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
use JWeiland\Checkfaluploads\EventListener\AddUserToFalRecordOnCreationEventListener;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedToIndexEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AddUserToFalRecordOnCreationEventListenerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_file.csv');
    }

    #[Test]
    public function invokeStoresBackendUserOnFileWhenBackendStorageIsEnabled(): void
    {
        $this->setUpBackendRequest();

        $subject = new AddUserToFalRecordOnCreationEventListener(
            new Context(),
            new ExtConf(storeBackendUploaderUserId: true),
        );

        $subject->__invoke(new AfterFileAddedToIndexEvent(1, []));

        self::assertSame(1, $this->getCruserIdOfFile());
    }

    #[Test]
    public function invokeDoesNotStoreBackendUserOnFileWhenBackendStorageIsDisabled(): void
    {
        $this->setUpBackendRequest();

        $subject = new AddUserToFalRecordOnCreationEventListener(
            new Context(),
            new ExtConf(storeBackendUploaderUserId: false),
        );

        $subject->__invoke(new AfterFileAddedToIndexEvent(1, []));

        self::assertSame(0, $this->getCruserIdOfFile());
    }

    #[Test]
    public function invokeStoresFrontendUserOnFileWhenFrontendStorageIsEnabled(): void
    {
        $subject = new AddUserToFalRecordOnCreationEventListener(
            $this->createFrontendUserContext(5),
            new ExtConf(storeFrontendUploaderUserId: true),
        );

        $subject->__invoke(new AfterFileAddedToIndexEvent(1, []));

        self::assertSame(5, $this->getFeCruserIdOfFile());
    }

    #[Test]
    public function invokeDoesNotStoreFrontendUserOnFileWhenFrontendStorageIsDisabled(): void
    {
        $subject = new AddUserToFalRecordOnCreationEventListener(
            $this->createFrontendUserContext(5),
            new ExtConf(storeFrontendUploaderUserId: false),
        );

        $subject->__invoke(new AfterFileAddedToIndexEvent(1, []));

        self::assertSame(0, $this->getFeCruserIdOfFile());
    }

    private function setUpBackendRequest(): void
    {
        $this->setUpBackendUser(1);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
    }

    private function createFrontendUserContext(int $userId): Context
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $frontendUser = $this->createMock(FrontendUserAuthentication::class);
        $frontendUser->user = ['uid' => $userId];

        $context = new Context();
        $context->setAspect('frontend.user', new UserAspect($frontendUser));

        return $context;
    }

    private function getCruserIdOfFile(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');

        return (int)$connection->select(['cruser_id'], 'sys_file', ['uid' => 1])->fetchOne();
    }

    private function getFeCruserIdOfFile(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');

        return (int)$connection->select(['fe_cruser_id'], 'sys_file', ['uid' => 1])->fetchOne();
    }
}
