<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Tests\Functional\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\Exception\UploadException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Submits the Extbase upload form of the fixture extension "checkfaluploads_test".
 * That extension uses #[FileUpload] and contains no checkfaluploads code.
 */
final class FrontendUploadRightsCheckMiddlewareTest extends FunctionalTestCase
{
    private const PLUGIN_NAMESPACE = 'tx_checkfaluploadstest_upload';

    private const UPLOAD_FOLDER = 'fileadmin/user_upload/checkfaluploads_test/';

    protected array $coreExtensionsToLoad = [
        'extbase',
        'fluid',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/checkfaluploads',
        __DIR__ . '/../Fixtures/Extensions/checkfaluploads_test',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_file_storage.csv');
        $this->setUpFrontendRootPage(1, ['EXT:checkfaluploads_test/Configuration/TypoScript/setup.typoscript']);
        $this->writeSiteConfiguration();
        GeneralUtility::mkdir_deep($this->instancePath . '/fileadmin/user_upload');
    }

    #[Test]
    public function uploadWithCheckedRightsFieldIsPassedToFileStorage(): void
    {
        // In CLI, is_uploaded_file() is always false. Therefore, the core rejects the
        // file at the very last step. Reaching this step proves, that the upload passed
        // checkfaluploads and Extbase validation and was handed over to FAL for storing.
        $this->expectException(UploadException::class);
        $this->expectExceptionCode(1322110455);

        $this->submitForm(['rights' => '1'], true);
    }

    #[Test]
    public function uploadWithUncheckedRightsFieldIsRejectedWithErrorMessage(): void
    {
        $response = $this->submitForm(['rights' => ''], true);

        $content = (string)$response->getBody();
        self::assertStringContainsString('<p id="created-images">0</p>', $content);
        self::assertStringContainsString(
            'The file(s) "document.txt" were not uploaded, because the checkbox for file rights was not checked.',
            $content,
        );
        self::assertFileDoesNotExist($this->instancePath . '/' . self::UPLOAD_FOLDER . 'document.txt');
        self::assertSame(0, $this->countRecords('sys_file_reference'));
        self::assertSame(0, $this->countRecords('sys_file'));
    }

    #[Test]
    public function uploadWithoutRightsFieldIsPassedToFileStorage(): void
    {
        // In CLI, is_uploaded_file() is always false. Therefore, the core rejects the
        // file at the very last step. Reaching this step proves, that the upload passed
        // checkfaluploads and Extbase validation and was handed over to FAL for storing.
        $this->expectException(UploadException::class);
        $this->expectExceptionCode(1322110455);

        $this->submitForm([], true);
    }

    #[Test]
    public function formWithUncheckedRightsFieldAndWithoutUploadIsStoredWithoutMessage(): void
    {
        $response = $this->submitForm(['rights' => ''], false);

        $content = (string)$response->getBody();
        self::assertStringContainsString('<p id="created-item">My item</p>', $content);
        self::assertStringContainsString('<p id="created-images">0</p>', $content);
        self::assertStringNotContainsString('were not uploaded', $content);
        self::assertSame(1, $this->countRecords('tx_checkfaluploadstest_domain_model_item'));
    }

    /**
     * @param array<string, string> $imagesFields Additional fields of item[images], e.g. the rights field
     */
    private function submitForm(array $imagesFields, bool $withUpload): ResponseInterface
    {
        $form = $this->fetchForm();

        $parsedBody = $form['hiddenFields'];
        $parsedBody[self::PLUGIN_NAMESPACE]['item']['title'] = 'My item';
        // Replaces the hidden field of the rights checkbox, if any
        unset($parsedBody[self::PLUGIN_NAMESPACE]['item']['images']);
        if ($imagesFields !== []) {
            $parsedBody[self::PLUGIN_NAMESPACE]['item']['images'] = $imagesFields;
        }

        $uploadedFiles = [];
        if ($withUpload) {
            $uploadedFiles[self::PLUGIN_NAMESPACE]['item']['images'] = [$this->createUploadedFile()];
        }

        // The testing framework can not build a body of nested arrays itself
        $body = new Stream('php://temp', 'rw');
        $body->write(http_build_query($parsedBody));

        $request = (new InternalRequest('https://example.com' . $form['action']))
            ->withMethod('POST')
            ->withBody($body)
            ->withParsedBody($parsedBody)
            ->withUploadedFiles($uploadedFiles);

        return $this->executeFrontendSubRequest($request);
    }

    /**
     * Renders the form to get its action and the signed hidden fields
     * (__referrer, __trustedProperties) of Extbase.
     *
     * @return array{action: string, hiddenFields: array<string, mixed>}
     */
    private function fetchForm(): array
    {
        $content = (string)$this->executeFrontendSubRequest(
            (new InternalRequest('https://example.com/'))->withPageId(1),
        )->getBody();

        self::assertSame(1, preg_match('/<form[^>]+action="([^"]+)"/', $content, $actionMatch));
        preg_match_all('/<input type="hidden" name="([^"]+)" value="([^"]*)"/', $content, $hiddenMatches, PREG_SET_ORDER);

        $query = '';
        foreach ($hiddenMatches as $hiddenMatch) {
            $query .= '&' . urlencode(html_entity_decode($hiddenMatch[1])) . '=' . urlencode(html_entity_decode($hiddenMatch[2]));
        }
        parse_str(ltrim($query, '&'), $hiddenFields);

        return [
            'action' => html_entity_decode($actionMatch[1]),
            'hiddenFields' => $hiddenFields,
        ];
    }

    private function createUploadedFile(): UploadedFile
    {
        $temporaryFile = GeneralUtility::tempnam('checkfaluploads_');
        file_put_contents($temporaryFile, 'Content of an uploaded document');

        return new UploadedFile($temporaryFile, (int)filesize($temporaryFile), UPLOAD_ERR_OK, 'document.txt', 'text/plain');
    }

    private function countRecords(string $tableName): int
    {
        return $this->getConnectionPool()
            ->getConnectionForTable($tableName)
            ->count('*', $tableName, []);
    }

    private function writeSiteConfiguration(): void
    {
        $configuration = <<<'YAML'
rootPageId: 1
base: 'https://example.com/'
languages:
  -
    languageId: 0
    title: English
    locale: en_US.UTF-8
    base: /
YAML;
        GeneralUtility::mkdir_deep($this->instancePath . '/typo3conf/sites/test');
        file_put_contents($this->instancePath . '/typo3conf/sites/test/config.yaml', $configuration);
    }
}
