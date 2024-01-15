<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Helper;

use JWeiland\Checkfaluploads\Helper\MessageHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;

/**
 * Test case.
 */
class MessageHelperTest extends FunctionalTestCase
{
    /**
     * @var MessageHelper
     */
    protected $subject;

    /**
     * @var array|string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/checkfaluploads'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);

        $this->subject = new MessageHelper(
            new FlashMessageService()
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function addFlashMessageWithMessageCallsEnqueue(): void
    {
        $expectedFlashMessage = new FlashMessage(
            'Hello',
            '',
            AbstractMessage::OK,
            true
        );

        $this->subject->addFlashMessage('Hello');

        self::assertEquals(
            [$expectedFlashMessage],
            $this->subject->getAllFlashMessages()
        );
    }

    /**
     * @test
     */
    public function addFlashMessageWithMessageAndSubjectCallsEnqueue(): void
    {
        $expectedFlashMessage = new FlashMessage(
            'Hello',
            'Subject',
            AbstractMessage::OK,
            true
        );

        $this->subject->addFlashMessage('Hello', 'Subject');

        self::assertEquals(
            [$expectedFlashMessage],
            $this->subject->getAllFlashMessages()
        );
    }

    /**
     * @test
     */
    public function addFlashMessageWithAllArgumentsCallsEnqueue(): void
    {
        $expectedFlashMessage = new FlashMessage(
            'Hello',
            'Subject',
            AbstractMessage::ERROR,
            true
        );

        $this->subject->addFlashMessage('Hello', 'Subject', AbstractMessage::ERROR);

        self::assertEquals(
            [$expectedFlashMessage],
            $this->subject->getAllFlashMessages()
        );
    }

    /**
     * @test
     */
    public function getAllFlashMessagesReturnsAllFlashMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        // Test two times, to be safe that messages were NOT flushed
        self::assertCount(
            3,
            $this->subject->getAllFlashMessages(false)
        );
        self::assertCount(
            3,
            $this->subject->getAllFlashMessages(false)
        );
    }

    /**
     * @test
     */
    public function getAllFlashMessagesReturnsAllFlashMessagesAndFlush(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        // Test two times, to be safe that messages were flushed
        self::assertCount(
            3,
            $this->subject->getAllFlashMessages()
        );
        self::assertCount(
            0,
            $this->subject->getAllFlashMessages()
        );
    }

    /**
     * @test
     */
    public function hasMessagesChecksQueueIfThereAreAnyMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        self::assertTrue(
            $this->subject->hasMessages()
        );
    }

    /**
     * @test
     */
    public function getFlashMessagesBySeverityAndFlushReturnsFlashMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        // Test two times, to be save that messages were flushed
        self::assertCount(
            1,
            $this->subject->getFlashMessagesBySeverityAndFlush(AbstractMessage::ERROR)
        );
        self::assertCount(
            0,
            $this->subject->getFlashMessagesBySeverityAndFlush(AbstractMessage::ERROR)
        );

        // Test two times, to be save that messages were flushed
        self::assertCount(
            2,
            $this->subject->getFlashMessagesBySeverityAndFlush(AbstractMessage::WARNING)
        );
        self::assertCount(
            0,
            $this->subject->getFlashMessagesBySeverityAndFlush(AbstractMessage::WARNING)
        );
    }

    /**
     * @test
     */
    public function hasErrorMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::ERROR);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        self::assertTrue(
            $this->subject->hasErrorMessages()
        );
    }

    /**
     * @test
     */
    public function hasErrorMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all');
        $this->subject->addFlashMessage('together', 'together');

        self::assertFalse(
            $this->subject->hasErrorMessages()
        );
    }

    /**
     * @test
     */
    public function getErrorMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::ERROR);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        self::assertCount(
            2,
            $this->subject->getErrorMessages()
        );
    }

    /**
     * @test
     */
    public function hasWarningMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::WARNING);

        self::assertTrue(
            $this->subject->hasWarningMessages()
        );
    }

    /**
     * @test
     */
    public function hasWarningMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::ERROR);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::ERROR);

        self::assertFalse(
            $this->subject->hasWarningMessages()
        );
    }

    /**
     * @test
     */
    public function getWarningMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello');
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::WARNING);

        self::assertCount(
            2,
            $this->subject->getWarningMessages()
        );
    }

    /**
     * @test
     */
    public function hasOkMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all');
        $this->subject->addFlashMessage('together', 'together');

        self::assertTrue(
            $this->subject->hasOkMessages()
        );
    }

    /**
     * @test
     */
    public function hasOkMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::ERROR);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::WARNING);

        self::assertFalse(
            $this->subject->hasOkMessages()
        );
    }

    /**
     * @test
     */
    public function getOkMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all');
        $this->subject->addFlashMessage('together', 'together');

        self::assertCount(
            2,
            $this->subject->getOkMessages()
        );
    }

    /**
     * @test
     */
    public function hasInfoMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::INFO);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::INFO);

        self::assertTrue(
            $this->subject->hasInfoMessages()
        );
    }

    /**
     * @test
     */
    public function hasInfoMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::ERROR);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::WARNING);

        self::assertFalse(
            $this->subject->hasInfoMessages()
        );
    }

    /**
     * @test
     */
    public function getInfoMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::INFO);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::INFO);

        self::assertCount(
            2,
            $this->subject->getInfoMessages()
        );
    }

    /**
     * @test
     */
    public function hasNoticeMessagesReturnsTrue(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::NOTICE);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::NOTICE);

        self::assertTrue(
            $this->subject->hasNoticeMessages()
        );
    }

    /**
     * @test
     */
    public function hasNoticeMessagesReturnsFalse(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::ERROR);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::WARNING);

        self::assertFalse(
            $this->subject->hasNoticeMessages()
        );
    }

    /**
     * @test
     */
    public function getNoticeMessagesReturnsErrorMessages(): void
    {
        $this->subject->addFlashMessage('Hello', 'Hello', AbstractMessage::WARNING);
        $this->subject->addFlashMessage('all', 'all', AbstractMessage::NOTICE);
        $this->subject->addFlashMessage('together', 'together', AbstractMessage::NOTICE);

        self::assertCount(
            2,
            $this->subject->getNoticeMessages()
        );
    }
}