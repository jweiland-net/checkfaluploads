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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 */
class ExtConfTest extends FunctionalTestCase
{
    /**
     * @var ExtConf
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

        $this->subject = new ExtConf();
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
    public function getOwnerInitiallyReturnsPlaceholder()
    {
        self::assertSame(
            '[Missing owner in ext settings of checkfaluploads]',
            $this->subject->getOwner()
        );
    }

    /**
     * @test
     */
    public function setOwnerSetsOwner()
    {
        $this->subject->setOwner('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOwner()
        );
    }

    /**
     * @test
     */
    public function setOwnerWithIntegerResultsInString()
    {
        $this->subject->setOwner(123);
        self::assertSame('123', $this->subject->getOwner());
    }

    /**
     * @test
     */
    public function setOwnerWithBooleanResultsInString()
    {
        $this->subject->setOwner(true);
        self::assertSame('1', $this->subject->getOwner());
    }
}
