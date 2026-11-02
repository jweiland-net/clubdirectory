<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Helper;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for PathSegmentHelper
 */
class PathSegmentHelperTest extends FunctionalTestCase
{
    protected PathSegmentHelper $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/PathSegment.csv');

        $this->subject = new PathSegmentHelper();
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    public static function recordDataProvider(): array
    {
        return [
            'new club with unique title' => [
                ['uid' => 0, 'title' => 'Football club'],
                1,
                'football-club',
            ],
            'new club with title of existing club' => [
                ['uid' => 0, 'title' => 'Tennis club'],
                1,
                'tennis-club-1',
            ],
            'new club with title of two existing clubs' => [
                ['uid' => 0, 'title' => 'Swimming club'],
                1,
                'swimming-club-2',
            ],
            'new club on another page is unique in table' => [
                ['uid' => 0, 'title' => 'Tennis club'],
                2,
                'tennis-club-1',
            ],
            'new club with title of hidden club' => [
                ['uid' => 0, 'title' => 'Hockey club'],
                1,
                'hockey-club-1',
            ],
            'new club with title of deleted club' => [
                ['uid' => 0, 'title' => 'Golf club'],
                1,
                'golf-club',
            ],
            'existing club keeps own path segment' => [
                ['uid' => 3, 'title' => 'Tennis club'],
                1,
                'tennis-club',
            ],
            'existing club with suffix keeps own path segment' => [
                ['uid' => 2, 'title' => 'Swimming club'],
                1,
                'swimming-club-1',
            ],
            'existing club renamed to title of another club' => [
                ['uid' => 3, 'title' => 'Swimming club'],
                1,
                'swimming-club-2',
            ],
            'slash is replaced' => [
                ['uid' => 0, 'title' => 'Tennis/Squash'],
                1,
                'tennis-squash',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $record
     */
    #[Test]
    #[DataProvider('recordDataProvider')]
    public function generatePathSegmentReturnsUniquePathSegment(array $record, int $pid, string $expectedPathSegment): void
    {
        self::assertSame(
            $expectedPathSegment,
            $this->subject->generatePathSegment($record, $pid),
        );
    }

    #[Test]
    public function updatePathSegmentForClubSetsUniquePathSegment(): void
    {
        $club = new Club();
        $club->_setProperty('uid', 6);
        $club->setPid(1);
        $club->setTitle('Swimming club');

        $this->subject->updatePathSegmentForClub($club);

        self::assertSame('swimming-club-2', $club->getPathSegment());
    }

    #[Test]
    public function updatePathSegmentForClubExcludesClubItself(): void
    {
        $club = new Club();
        $club->_setProperty('uid', 1);
        $club->setPid(1);
        $club->setTitle('Swimming club');

        $this->subject->updatePathSegmentForClub($club);

        self::assertSame('swimming-club', $club->getPathSegment());
    }
}
