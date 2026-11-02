<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Controller;

use JWeiland\Clubdirectory\Service\ClubCacheService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Cached list and detail pages are tagged with the club records, so they
 * are flushed, when clubs get changed in frontend or backend.
 */
class ClubPageCacheTest extends FunctionalTestCase
{
    private const TAG_TABLE = 'tx_clubdirectory_domain_model_club';

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    /**
     * Automatic cache tagging is disabled, as in instances upgraded from
     * TYPO3 v12. Then the cache tags of the extension are the only ones.
     */
    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'features' => [
                'frontend.cache.autoTagging' => false,
            ],
            'caching' => [
                'cacheConfigurations' => [
                    'pages' => [
                        'backend' => Typo3DatabaseBackend::class,
                    ],
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/PageCache.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => [
                    'EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Constants.typoscript',
                    'EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/PageCache.typoscript',
                ],
                'setup' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Setup.typoscript'],
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

    private function requestListAction(): ResponseInterface
    {
        return $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.com/'))->withQueryParameters(['id' => 1]),
        );
    }

    private function requestShowAction(int $clubUid): ResponseInterface
    {
        $queryParameters = [
            'id' => 1,
            'tx_clubdirectory_clubdirectory[action]' => 'show',
            'tx_clubdirectory_clubdirectory[controller]' => 'Club',
            'tx_clubdirectory_clubdirectory[club]' => $clubUid,
        ];
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        return $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters),
        );
    }

    /**
     * @return string[]
     */
    private function findCachedPagesByTag(string $tag): array
    {
        $backend = $this->get(CacheManager::class)->getCache('pages')->getBackend();
        self::assertInstanceOf(TaggableBackendInterface::class, $backend);

        return $backend->findIdentifiersByTag($tag);
    }

    private function updateClubTitleInBackend(int $clubUid, string $title): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([self::TAG_TABLE => [$clubUid => ['title' => $title]]], []);
        $dataHandler->process_datamap();

        self::assertSame([], $dataHandler->errorLog);
    }

    #[Test]
    public function listActionTagsPageWithClubTable(): void
    {
        $response = $this->requestListAction();

        self::assertStringContainsString('Swimming club', (string)$response->getBody());
        self::assertCount(1, $this->findCachedPagesByTag(self::TAG_TABLE));
    }

    #[Test]
    public function showActionTagsPageWithClubRecord(): void
    {
        $this->requestShowAction(1);

        self::assertCount(1, $this->findCachedPagesByTag(self::TAG_TABLE . '_1'));
        self::assertSame([], $this->findCachedPagesByTag(self::TAG_TABLE . '_2'));
        self::assertSame([], $this->findCachedPagesByTag(self::TAG_TABLE));
    }

    #[Test]
    public function flushCachesOfClubFlushesListAndDetailPageOfClub(): void
    {
        $this->requestListAction();
        $this->requestShowAction(1);
        $this->requestShowAction(2);

        $this->get(ClubCacheService::class)->flushCachesOfClub(1);

        self::assertSame([], $this->findCachedPagesByTag(self::TAG_TABLE));
        self::assertSame([], $this->findCachedPagesByTag(self::TAG_TABLE . '_1'));
        self::assertCount(1, $this->findCachedPagesByTag(self::TAG_TABLE . '_2'));
    }

    #[Test]
    public function backendChangeOfClubShowsNewTitleOnListAndDetailPage(): void
    {
        $this->requestListAction();
        $this->requestShowAction(1);

        $this->updateClubTitleInBackend(1, 'Diving club');

        self::assertStringContainsString('Diving club', (string)$this->requestListAction()->getBody());
        self::assertStringContainsString('Diving club', (string)$this->requestShowAction(1)->getBody());
    }
}
