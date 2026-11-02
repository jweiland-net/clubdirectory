<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Pagination;

use JWeiland\Clubdirectory\Pagination\ClubPagination;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for ClubPagination
 */
class ClubPaginationTest extends UnitTestCase
{
    private function createPagination(int $currentPage, array $pluginArguments = []): ClubPagination
    {
        return new ClubPagination(
            new ArrayPaginator(range(1, 25), $currentPage, 10),
            $pluginArguments,
        );
    }

    #[Test]
    public function nextPageArgumentsContainSearchArgumentOfRequest(): void
    {
        $search = [
            'searchWord' => 'rowing',
            'category' => '2',
            'order' => 'DESC',
        ];
        $subject = $this->createPagination(1, [
            'search' => $search,
            '__referrer' => ['@action' => 'show'],
            '__trustedProperties' => 'hmac',
            'controller' => 'Search',
            'action' => 'listSearchResults',
        ]);

        self::assertSame(
            [
                'search' => $search,
                'currentPage' => 2,
            ],
            $subject->getNextPageArguments(),
        );
    }

    #[Test]
    public function pageArgumentsOverrideCurrentPageOfRequest(): void
    {
        $subject = $this->createPagination(2, [
            'letter' => 'G',
            'currentPage' => '2',
        ]);

        self::assertSame(['letter' => 'G', 'currentPage' => 1], $subject->getPreviousPageArguments());
        self::assertSame(['letter' => 'G', 'currentPage' => 1], $subject->getFirstPageArguments());
        self::assertSame(['letter' => 'G', 'currentPage' => 3], $subject->getLastPageArguments());
    }

    #[Test]
    public function pageArgumentsWithoutRequestArgumentsContainCurrentPageOnly(): void
    {
        self::assertSame(['currentPage' => 2], $this->createPagination(1)->getNextPageArguments());
    }

    #[Test]
    public function nextPageNumberOnLastPageIsNull(): void
    {
        $subject = $this->createPagination(3);

        self::assertNull($subject->getNextPageNumber());
        self::assertSame(3, $subject->getLastPageNumber());
    }
}
