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
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Test case for model Search
 */
class SearchTest extends UnitTestCase
{
    /**
     * @var Search
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Search();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getSearchWordInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getSearchWord()
        );
    }

    /**
     * @test
     */
    public function setSearchWordSetsSearchWord(): void
    {
        $this->subject->setSearchWord('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getSearchWord()
        );
    }

    /**
     * @test
     */
    public function getCategoryInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getCategory()
        );
    }

    /**
     * @test
     */
    public function setCategorySetsCategory(): void
    {
        $this->subject->setCategory(123456);

        self::assertSame(
            123456,
            $this->subject->getCategory()
        );
    }

    /**
     * @test
     */
    public function getSubCategoryInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getSubCategory()
        );
    }

    /**
     * @test
     */
    public function setSubCategorySetsSubCategory(): void
    {
        $this->subject->setSubCategory(123456);

        self::assertSame(
            123456,
            $this->subject->getSubCategory()
        );
    }

    /**
     * @test
     */
    public function getOrderByInitiallyReturnsSortTitle(): void
    {
        self::assertSame(
            'sortTitle',
            $this->subject->getOrderBy()
        );
    }

    /**
     * @test
     */
    public function setOrderBySetsOrderBy(): void
    {
        $this->subject->setOrderBy('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOrderBy()
        );
    }

    /**
     * @test
     */
    public function getDirectionInitiallyReturnsAscending(): void
    {
        self::assertSame(
            QueryInterface::ORDER_ASCENDING,
            $this->subject->getDirection()
        );
    }

    /**
     * @test
     */
    public function setDirectionSetsDirection(): void
    {
        $this->subject->setDirection('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDirection()
        );
    }
}
