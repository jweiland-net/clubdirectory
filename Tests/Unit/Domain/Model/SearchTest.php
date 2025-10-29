<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

use JWeiland\Clubdirectory\Domain\Model\Search;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for model Search
 */
class SearchTest extends UnitTestCase
{
    protected Search $subject;

    protected function setUp(): void
    {
        $this->subject = new Search();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    #[Test]
    public function getSearchWordInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSearchWord(),
        );
    }

    #[Test]
    public function setSearchWordSetsSearchWord(): void
    {
        $this->subject->setSearchWord('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getSearchWord(),
        );
    }

    #[Test]
    public function getCategoryInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getCategory(),
        );
    }

    #[Test]
    public function setCategorySetsCategory(): void
    {
        $this->subject->setCategory(123456);

        self::assertSame(
            123456,
            $this->subject->getCategory(),
        );
    }

    #[Test]
    public function setOrderBySetsOrderBy(): void
    {
        $this->subject->setOrderBy('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOrderBy(),
        );
    }

    #[Test]
    public function getOrderInitiallyReturnsAscending(): void
    {
        self::assertSame(
            QueryInterface::ORDER_ASCENDING,
            $this->subject->getOrder(),
        );
    }

    #[Test]
    public function setOrderSetsSortingOrder(): void
    {
        $this->subject->setOrder('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOrder(),
        );
    }
}
