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
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

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

    #[Test]
    public function initializeArgumentsRegistersExpectedDefaults(): void
    {
        $subject = new ImageRightsMessageViewHelper(new ExtConf());
        $argumentDefinitions = $subject->prepareArguments();

        self::assertSame('frontend.imageUserRights', $argumentDefinitions['languageKey']->getDefaultValue());
        self::assertSame('checkfaluploads', $argumentDefinitions['extensionName']->getDefaultValue());
    }

    #[Test]
    public function renderReturnsMessageWithOwner(): void
    {
        $subject = new ImageRightsMessageViewHelper(new ExtConf(owner: 'Stefan Froemken'));
        $subject->setArguments([
            'languageKey' => 'frontend.imageUserRights',
            'extensionName' => 'checkfaluploads',
        ]);

        self::assertStringContainsString(
            'Stefan Froemken',
            $subject->render(),
        );
    }

    #[Test]
    public function renderReturnsMessageWithPlaceholderWhenOwnerWasNotGiven(): void
    {
        $subject = new ImageRightsMessageViewHelper(new ExtConf());
        $subject->setArguments([
            'languageKey' => 'frontend.imageUserRights',
            'extensionName' => 'checkfaluploads',
        ]);

        self::assertStringContainsString(
            '[Missing owner in ext settings of checkfaluploads]',
            $subject->render(),
        );
    }
}
