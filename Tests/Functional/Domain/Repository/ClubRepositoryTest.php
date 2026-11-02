<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Domain\Repository;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for ClubRepository::findBySearch()
 */
class ClubRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubSearch.csv');
    }

    /**
     * @param int[] $storagePageIds
     */
    private function createSubject(array $storagePageIds = [1]): ClubRepository
    {
        $querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $querySettings->setStoragePageIds($storagePageIds);

        $clubRepository = $this->get(ClubRepository::class);
        $clubRepository->setDefaultQuerySettings($querySettings);

        return $clubRepository;
    }

    private function createSearch(
        string $searchWord = '',
        int $category = 0,
        int $district = 0,
        string $order = QueryInterface::ORDER_ASCENDING,
    ): Search {
        $search = new Search();
        $search->setSearchWord($searchWord);
        $search->setCategory($category);
        $search->setDistrict($district);
        $search->setOrder($order);

        return $search;
    }

    /**
     * @return string[]
     */
    private function findTitlesBySearch(Search $search, ?ClubRepository $clubRepository = null): array
    {
        $clubs = ($clubRepository ?? $this->createSubject())->findBySearch($search)->toArray();

        return array_map(static fn(Club $club): string => $club->getTitle(), $clubs);
    }

    #[Test]
    public function findBySearchWithoutFilterReturnsAllVisibleClubsOfStoragePage(): void
    {
        self::assertSame(
            ['Athletics club', 'Brass band', 'Chess club', 'Drama group'],
            $this->findTitlesBySearch($this->createSearch()),
        );
    }

    #[Test]
    public function findBySearchWithDescendingOrderReturnsClubsOrderedByTitleDescending(): void
    {
        self::assertSame(
            ['Drama group', 'Chess club', 'Brass band', 'Athletics club'],
            $this->findTitlesBySearch($this->createSearch('', 0, 0, QueryInterface::ORDER_DESCENDING)),
        );
    }

    #[Test]
    public function findBySearchWithFilterAndDescendingOrderReturnsClubsOrderedByTitleDescending(): void
    {
        self::assertSame(
            ['Chess club', 'Athletics club'],
            $this->findTitlesBySearch($this->createSearch('', 1, 0, QueryInterface::ORDER_DESCENDING)),
        );
    }

    #[Test]
    public function findBySearchWithSearchWordAndDescendingOrderReturnsClubsOrderedByTitleDescending(): void
    {
        self::assertSame(
            ['Chess club', 'Athletics club'],
            $this->findTitlesBySearch($this->createSearch('club', 0, 0, QueryInterface::ORDER_DESCENDING)),
        );
    }

    public static function categoryDataProvider(): array
    {
        return [
            'category of one club and one club with multiple categories' => [
                1,
                ['Athletics club', 'Chess club'],
            ],
            'category with relations of other tables and fields' => [
                2,
                ['Brass band', 'Chess club'],
            ],
            'category without clubs' => [
                3,
                [],
            ],
            'unknown category' => [
                99,
                [],
            ],
        ];
    }

    /**
     * @param string[] $expectedTitles
     */
    #[Test]
    #[DataProvider('categoryDataProvider')]
    public function findBySearchWithCategoryReturnsClubsOfCategory(int $category, array $expectedTitles): void
    {
        self::assertSame(
            $expectedTitles,
            $this->findTitlesBySearch($this->createSearch('', $category)),
        );
    }

    public static function districtDataProvider(): array
    {
        return [
            'district North' => [1, ['Athletics club', 'Brass band']],
            'district South' => [2, ['Chess club', 'Drama group']],
            'unknown district' => [99, []],
        ];
    }

    /**
     * @param string[] $expectedTitles
     */
    #[Test]
    #[DataProvider('districtDataProvider')]
    public function findBySearchWithDistrictReturnsClubsOfDistrict(int $district, array $expectedTitles): void
    {
        self::assertSame(
            $expectedTitles,
            $this->findTitlesBySearch($this->createSearch('', 0, $district)),
        );
    }

    public static function categoryAndDistrictDataProvider(): array
    {
        return [
            'Sports in North' => [1, 1, ['Athletics club']],
            'Sports in South' => [1, 2, ['Chess club']],
            'Music in North' => [2, 1, ['Brass band']],
            'Music in South' => [2, 2, ['Chess club']],
            'Unused in North' => [3, 1, []],
        ];
    }

    /**
     * @param string[] $expectedTitles
     */
    #[Test]
    #[DataProvider('categoryAndDistrictDataProvider')]
    public function findBySearchWithCategoryAndDistrictReturnsClubsMatchingBoth(
        int $category,
        int $district,
        array $expectedTitles,
    ): void {
        self::assertSame(
            $expectedTitles,
            $this->findTitlesBySearch($this->createSearch('', $category, $district)),
        );
    }

    public static function searchWordDataProvider(): array
    {
        return [
            'title' => ['Athletics', ['Athletics club']],
            'part of multiple titles' => ['club', ['Athletics club', 'Chess club']],
            'street' => ['Hauptstr.', ['Athletics club']],
            'long street name finds abbreviated street' => ['Hauptstraße', ['Athletics club']],
            'abbreviated street name with dot finds long street' => ['Bahnhofstr.', ['Brass band']],
            'abbreviated street name without dot finds long street' => ['Bahnhofstr', ['Brass band']],
            'zip' => ['79312', ['Brass band']],
            'zip of multiple addresses of one club' => ['79100', ['Athletics club']],
            'city' => ['Lahr', ['Drama group']],
            'contact person' => ['Zappa', ['Brass band']],
            'description' => ['tournaments', ['Chess club']],
            'tags' => ['board games', ['Chess club']],
            'no match' => ['Underwater rugby', []],
        ];
    }

    /**
     * @param string[] $expectedTitles
     */
    #[Test]
    #[DataProvider('searchWordDataProvider')]
    public function findBySearchWithSearchWordReturnsMatchingClubs(string $searchWord, array $expectedTitles): void
    {
        self::assertSame(
            $expectedTitles,
            $this->findTitlesBySearch($this->createSearch($searchWord)),
        );
    }

    #[Test]
    public function findBySearchWithSearchWordIgnoresCategoryAndDistrict(): void
    {
        // Brass band is the only club in category Music and district North, but does not match the search word
        self::assertSame(
            ['Athletics club', 'Chess club'],
            $this->findTitlesBySearch($this->createSearch('club', 2, 1)),
        );
    }

    public static function likeWildcardDataProvider(): array
    {
        return [
            'percent sign' => ['%'],
            'underscore' => ['_'],
            'underscore within word' => ['Ch_ss'],
            'percent sign within word' => ['Ath%club'],
        ];
    }

    /**
     * No club contains "%" or "_", so the wildcards must not match anything
     */
    #[Test]
    #[DataProvider('likeWildcardDataProvider')]
    public function findBySearchWithLikeWildcardInSearchWordSearchesLiterally(string $searchWord): void
    {
        self::assertSame(
            [],
            $this->findTitlesBySearch($this->createSearch($searchWord)),
        );
    }

    public static function excludedClubDataProvider(): array
    {
        return [
            'hidden club by title' => ['Hidden sports club'],
            'deleted club by title' => ['Deleted sports club'],
            'expired club by title' => ['Expired sports club'],
            'club of other storage page by title' => ['Foreign storage club'],
            'part of titles of unavailable clubs only' => ['sports club'],
        ];
    }

    #[Test]
    #[DataProvider('excludedClubDataProvider')]
    public function findBySearchWithSearchWordExcludesUnavailableClubs(string $searchWord): void
    {
        self::assertSame(
            [],
            $this->findTitlesBySearch($this->createSearch($searchWord)),
        );
    }

    #[Test]
    public function findBySearchWithCategoryExcludesUnavailableClubs(): void
    {
        // Hidden, deleted, expired and foreign clubs are all assigned to category Sports and district North
        self::assertSame(
            ['Athletics club'],
            $this->findTitlesBySearch($this->createSearch('', 1, 1)),
        );
    }

    #[Test]
    public function findBySearchWithOtherStoragePageReturnsClubsOfThatStoragePage(): void
    {
        self::assertSame(
            ['Foreign storage club'],
            $this->findTitlesBySearch($this->createSearch(), $this->createSubject([2])),
        );
    }

    #[Test]
    public function findBySearchWithMultipleStoragePagesReturnsClubsOfAllStoragePages(): void
    {
        self::assertSame(
            ['Athletics club', 'Chess club', 'Foreign storage club'],
            $this->findTitlesBySearch($this->createSearch('', 1), $this->createSubject([1, 2])),
        );
    }

    #[Test]
    public function findBySearchWithoutConfiguredStoragePageOnlyRespectsRootPage(): void
    {
        // Without a request there is no TypoScript, so Extbase falls back to storage page 0
        self::assertSame(
            [],
            $this->get(ClubRepository::class)->findBySearch($this->createSearch())->toArray(),
        );
    }
}
