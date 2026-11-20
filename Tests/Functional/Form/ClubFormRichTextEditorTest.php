<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Form;

use JWeiland\Clubdirectory\Hook\Form\RichTextEditorHook;
use JWeiland\Clubdirectory\Tests\Functional\Fixtures\Hook\ForeignRichTextEditorHook;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Renders the club form with enabled rich text editor in frontend, while
 * another extension registers its own rich text editor for its own forms.
 * The description must get the editor of clubdirectory only once.
 */
class ClubFormRichTextEditorTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
        'typo3/cms-rte-ckeditor',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected array $configurationToUseInTestInstance = [
        'SC_OPTIONS' => [
            'ext/form' => [
                'beforeRendering' => [
                    1763999901 => ForeignRichTextEditorHook::class,
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(ExtensionManagementUtility::getExtensionVersion('maps2'), '12.3.0', '<')) {
            self::markTestSkipped('The form element "Maps2PoiPosition" of EXT:maps2 >= 12.3.0 is not available.');
        }

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubForm.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/ClubFormRichText.typoscript'],
            ],
        );
        $this->get(SiteWriter::class)->write('clubdirectory', [
            'rootPageId' => 1,
            'base' => 'https://www.example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'locale' => 'en_US.UTF-8',
                    'base' => '/',
                ],
            ],
        ]);
    }

    private function renderClubForm(): string
    {
        $queryParameters = [
            'id' => 1,
            self::PLUGIN_NAMESPACE . '[action]' => 'perform',
            self::PLUGIN_NAMESPACE . '[controller]' => 'Management',
        ];
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters),
            (new InternalRequestContext())->withFrontendUserId(self::OWNER),
        );
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    #[Test]
    public function descriptionGetsRichTextEditorOfClubdirectoryOnlyOnce(): void
    {
        $content = $this->renderClubForm();

        self::assertSame(1, substr_count($content, RichTextEditorHook::DATA_ATTRIBUTE . '="'));
        self::assertStringNotContainsString(ForeignRichTextEditorHook::DATA_ATTRIBUTE, $content);
        self::assertMatchesRegularExpression(
            '/<textarea[^>]*name="' . preg_quote(self::PLUGIN_NAMESPACE, '/') . '\[newClub\]\[description\]"[^>]*>/',
            $content,
        );
        preg_match('/<textarea[^>]*\[newClub\]\[description\][^>]*>/', $content, $matches);
        self::assertStringContainsString(RichTextEditorHook::DATA_ATTRIBUTE . '="', $matches[0] ?? '');
    }

    #[Test]
    public function richTextElementLoadsAssetsOfPartial(): void
    {
        $content = $this->renderClubForm();

        self::assertStringContainsString('@jweiland/clubdirectory/rich-text-editor.js', $content);
        self::assertMatchesRegularExpression('#<link[^>]*rte_ckeditor/Resources/Public/Css/editor\.css#', $content);
        self::assertMatchesRegularExpression('#<link[^>]*clubdirectory/Resources/Public/Css/RichTextEditor\.css#', $content);
    }

    #[Test]
    public function activityIsPlainTextarea(): void
    {
        $content = $this->renderClubForm();

        preg_match('/<textarea[^>]*\[newClub\]\[activity\][^>]*>/', $content, $matches);
        self::assertNotSame([], $matches);
        self::assertStringNotContainsString('rich-text', $matches[0]);
    }
}
