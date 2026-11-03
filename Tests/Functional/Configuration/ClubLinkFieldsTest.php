<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The link fields of the club allow external URLs only, when they are
 * saved in backend.
 */
class ClubLinkFieldsTest extends FunctionalTestCase
{
    private const TABLE_CLUB = 'tx_clubdirectory_domain_model_club';

    private const LINK_FIELDS = ['website', 'facebook', 'twitter', 'instagram'];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/PageCache.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    /**
     * @return array<string, string>
     */
    private function saveLinkFieldsInBackend(string $link): array
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([self::TABLE_CLUB => [1 => array_fill_keys(self::LINK_FIELDS, $link)]], []);
        $dataHandler->process_datamap();

        $record = $this->getConnectionPool()
            ->getConnectionForTable(self::TABLE_CLUB)
            ->select(self::LINK_FIELDS, self::TABLE_CLUB, ['uid' => 1])
            ->fetchAssociative();

        return is_array($record) ? $record : [];
    }

    public static function externalUrlDataProvider(): array
    {
        return [
            'https' => ['https://www.example.org/club'],
            'without scheme' => ['www.example.org'],
        ];
    }

    #[Test]
    #[DataProvider('externalUrlDataProvider')]
    public function saveInBackendKeepsExternalUrls(string $link): void
    {
        self::assertSame(array_fill_keys(self::LINK_FIELDS, $link), $this->saveLinkFieldsInBackend($link));
    }

    public static function disallowedLinkDataProvider(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
            'page' => ['t3://page?uid=1'],
            'email' => ['mailto:club@example.org'],
        ];
    }

    #[Test]
    #[DataProvider('disallowedLinkDataProvider')]
    public function saveInBackendRejectsOtherLinks(string $link): void
    {
        self::assertSame(array_fill_keys(self::LINK_FIELDS, ''), $this->saveLinkFieldsInBackend($link));
    }
}
