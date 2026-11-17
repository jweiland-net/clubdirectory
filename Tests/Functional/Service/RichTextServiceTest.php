<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Service;

use JWeiland\Clubdirectory\Service\RichTextService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for RichTextService
 */
class RichTextServiceTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
        'typo3/cms-rte-ckeditor',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    private const TABLE = 'tx_clubdirectory_domain_model_club';

    private const CLUB = 1;

    private RichTextService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->subject = $this->get(RichTextService::class);
    }

    /**
     * @param array<string, string> $values
     */
    private function updateClub(array $values): void
    {
        $this->getConnectionPool()->getConnectionForTable(self::TABLE)->update(self::TABLE, $values, ['uid' => self::CLUB]);
    }

    public static function editorHtmlDataProvider(): array
    {
        return [
            'example of CKEditor' => [
                '<p>Erste Zeile der Beschreibung.<br>Zweite Zeile direkt darunter.</p>'
                . '<p>Neuer Absatz nach einer Leerzeile.<br>Letzte Zeile mit Umlauten: äöü ß &amp; "Anführungszeichen".</p>',
                '<p>Erste Zeile der Beschreibung.<br />Zweite Zeile direkt darunter.</p>' . "\r\n"
                . '<p>Neuer Absatz nach einer Leerzeile.<br />Letzte Zeile mit Umlauten: äöü ß &amp; "Anführungszeichen".</p>',
            ],
            'headings, lists and formatting' => [
                '<h2>Programm</h2><ul><li><strong>Fett</strong></li><li><em>Kursiv</em></li></ul>'
                . '<h3>Preise</h3><ol><li>10&nbsp;Euro</li></ol>',
                "<h2>Programm</h2>\r\n<ul><li><strong>Fett</strong></li><li><em>Kursiv</em></li></ul>\r\n"
                . "<h3>Preise</h3>\r\n<ol><li>10&nbsp;Euro</li></ol>",
            ],
            'link opening in new tab' => [
                '<p><a href="https://example.com" target="_blank" rel="noopener noreferrer">Link</a></p>',
                '<p><a href="https://example.com" target="_blank" rel="noopener noreferrer">Link</a></p>',
            ],
            'forbidden markup is removed' => [
                '<p onclick="alert(1)" style="color:red">Text<script>alert(1)</script></p><img src="x.png">',
                '<p>Text</p>',
            ],
            'raw POST with loose inline content keeps formatting' => [
                '<h1>H1</h1><h4>H4</h4><b>fett</b> <i>kursiv</i> <u>unter</u> <span style="font-size:40px">gross</span>'
                . '<table><tr><td>Zelle</td></tr></table><!-- kommentar -->',
                "<p>H1</p>\r\n<p>H4</p>\r\n<p><strong>fett</strong> <em>kursiv</em> unter gross</p>\r\n<p>Zelle</p>",
            ],
            'loose b and i are kept as strong and em' => [
                '<b class="x">a</b> <i>b</i>',
                '<p><strong>a</strong> <em>b</em></p>',
            ],
            'plain text without JavaScript is converted like the plain textarea' => [
                "Erste Zeile\r\nZweite Zeile & mehr\r\n\r\nAbsatz",
                "<p>Erste Zeile<br />Zweite Zeile &amp; mehr</p>\r\n<p>Absatz</p>",
            ],
        ];
    }

    #[Test]
    #[DataProvider('editorHtmlDataProvider')]
    public function prepareForPersistenceReturnsHtmlOfBackend(string $html, string $expected): void
    {
        self::assertSame(
            $expected,
            $this->subject->prepareForPersistence($html, self::TABLE, 'description', 0, 1),
        );
    }

    #[Test]
    public function prepareForPersistenceKeepsUnchangedT3LinkOfStoredClub(): void
    {
        // RteHtmlParser removes links to not existing records. Page 1 exists.
        $this->updateClub(['description' => '<p><a href="t3://page?uid=1">Program</a></p>']);

        self::assertSame(
            '<p><a href="t3://page?uid=1">Program</a> and new</p>',
            $this->subject->prepareForPersistence(
                '<p><a href="t3://page?uid=1">Program</a> and <a href="t3://page?uid=2">new</a></p>',
                self::TABLE,
                'description',
                self::CLUB,
            ),
        );
    }

    #[Test]
    public function prepareForPersistenceRemovesT3LinkOfNewClub(): void
    {
        self::assertSame(
            '<p>Program</p>',
            $this->subject->prepareForPersistence('<p><a href="t3://page?uid=1">Program</a></p>', self::TABLE, 'description', 0),
        );
    }

    #[Test]
    public function prepareForPersistenceComparesT3LinksWithStoredValueOfSameColumn(): void
    {
        $this->updateClub([
            'activity' => '<p><a href="t3://page?uid=1">Activity</a></p>',
            'description' => '<p>Description</p>',
        ]);

        self::assertStringContainsString(
            '<p>Activity</p>',
            $this->subject->prepareForPersistence(
                '<p><a href="t3://page?uid=1">Activity</a></p>',
                self::TABLE,
                'description',
                self::CLUB,
            ),
        );
    }

    #[Test]
    public function prepareForPersistenceWithUnknownColumnRemovesT3Links(): void
    {
        $this->updateClub(['description' => '<p><a href="t3://page?uid=1">Link</a></p>']);

        self::assertStringContainsString(
            '<p>Link</p>',
            $this->subject->prepareForPersistence('<p><a href="t3://page?uid=1">Link</a></p>', self::TABLE, 'not_existing', self::CLUB),
        );
    }
}
