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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ExtConfTest extends FunctionalTestCase
{
    protected ExtConf $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);

        $this->subject = new ExtConf(new ExtensionConfiguration());
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
    public function getOwnerInitiallyReturnsPlaceholder(): void
    {
        self::assertSame(
            '[Missing owner in ext settings of checkfaluploads]',
            $this->subject->getOwner()
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
            $this->subject->getOwner()
        );
    }

    /**
     * @test
     */
    public function getLabelForUserRightsContainsOwner(): void
    {
        $this->subject->setOwner('foo bar');

        self::assertStringContainsString(
            'foo bar',
            $this->subject->getLabelForUserRights()
        );
    }
}
