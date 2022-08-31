<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Service;

use JWeiland\Checkfaluploads\Service\FalUploadService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class FalUploadServiceTest extends FunctionalTestCase
{
    /**
     * @var FalUploadService
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

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);

        $this->subject = new FalUploadService();
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
    public function checkFileWithRightsWillReturnNull(): void
    {
        $file = [
            'name' => 'test',
            'rights' => '1'
        ];

        self::assertNull(
            $this->subject->checkFile($file)
        );
    }

    /**
     * @test
     */
    public function checkFileWithNoRightsWillReturnErrorMessage(): void
    {
        $file = [
            'name' => 'test'
        ];

        $error = $this->subject->checkFile($file);

        self::assertStringContainsString(
            'not allowed',
            $error->getMessage()
        );

        self::assertStringContainsString(
            'checkbox',
            $error->getMessage()
        );

        self::assertSame(
            1604050225,
            $error->getCode()
        );
    }

    /**
     * @test
     */
    public function checkFileWithEmptyRightsWillReturnErrorMessage(): void
    {
        $file = [
            'name' => 'test',
            'rights' => ''
        ];

        $error = $this->subject->checkFile($file);

        self::assertStringContainsString(
            'not allowed',
            $error->getMessage()
        );

        self::assertStringContainsString(
            'checkbox',
            $error->getMessage()
        );

        self::assertSame(
            1604050225,
            $error->getCode()
        );
    }
}
