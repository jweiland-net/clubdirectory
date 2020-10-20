<?php

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

    public function setUp()
    {
        $this->subject = new Search();
    }

    public function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getLetterInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getLetter()
        );
    }

    /**
     * @test
     */
    public function setLetterSetsLetter()
    {
        $this->subject->setLetter('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getLetter()
        );
    }

    /**
     * @test
     */
    public function setLetterWithIntegerResultsInString()
    {
        $this->subject->setLetter(123);
        self::assertSame('123', $this->subject->getLetter());
    }

    /**
     * @test
     */
    public function setLetterWithBooleanResultsInString()
    {
        $this->subject->setLetter(true);
        self::assertSame('1', $this->subject->getLetter());
    }

    /**
     * @test
     */
    public function getSearchWordInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getSearchWord()
        );
    }

    /**
     * @test
     */
    public function setSearchWordSetsSearchWord()
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
    public function setSearchWordWithIntegerResultsInString()
    {
        $this->subject->setSearchWord(123);
        self::assertSame('123', $this->subject->getSearchWord());
    }

    /**
     * @test
     */
    public function setSearchWordWithBooleanResultsInString()
    {
        $this->subject->setSearchWord(true);
        self::assertSame('1', $this->subject->getSearchWord());
    }

    /**
     * @test
     */
    public function getCategoryInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getCategory()
        );
    }

    /**
     * @test
     */
    public function setCategorySetsCategory()
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
    public function setCategoryWithStringResultsInInteger()
    {
        $this->subject->setCategory('123Test');

        self::assertSame(
            123,
            $this->subject->getCategory()
        );
    }

    /**
     * @test
     */
    public function setCategoryWithBooleanResultsInInteger()
    {
        $this->subject->setCategory(true);

        self::assertSame(
            1,
            $this->subject->getCategory()
        );
    }

    /**
     * @test
     */
    public function getSubCategoryInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getSubCategory()
        );
    }

    /**
     * @test
     */
    public function setSubCategorySetsSubCategory()
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
    public function setSubCategoryWithStringResultsInInteger()
    {
        $this->subject->setSubCategory('123Test');

        self::assertSame(
            123,
            $this->subject->getSubCategory()
        );
    }

    /**
     * @test
     */
    public function setSubCategoryWithBooleanResultsInInteger()
    {
        $this->subject->setSubCategory(true);

        self::assertSame(
            1,
            $this->subject->getSubCategory()
        );
    }

    /**
     * @test
     */
    public function getOrderByInitiallyReturnsSortTitle()
    {
        self::assertSame(
            'sortTitle',
            $this->subject->getOrderBy()
        );
    }

    /**
     * @test
     */
    public function setOrderBySetsOrderBy()
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
    public function setOrderByWithIntegerResultsInString()
    {
        $this->subject->setOrderBy(123);
        self::assertSame('123', $this->subject->getOrderBy());
    }

    /**
     * @test
     */
    public function setOrderByWithBooleanResultsInString()
    {
        $this->subject->setOrderBy(true);
        self::assertSame('1', $this->subject->getOrderBy());
    }

    /**
     * @test
     */
    public function getDirectionInitiallyReturnsAscending()
    {
        self::assertSame(
            QueryInterface::ORDER_ASCENDING,
            $this->subject->getDirection()
        );
    }

    /**
     * @test
     */
    public function setDirectionSetsDirection()
    {
        $this->subject->setDirection('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDirection()
        );
    }

    /**
     * @test
     */
    public function setDirectionWithIntegerResultsInString()
    {
        $this->subject->setDirection(123);
        self::assertSame('123', $this->subject->getDirection());
    }

    /**
     * @test
     */
    public function setDirectionWithBooleanResultsInString()
    {
        $this->subject->setDirection(true);
        self::assertSame('1', $this->subject->getDirection());
    }
}
