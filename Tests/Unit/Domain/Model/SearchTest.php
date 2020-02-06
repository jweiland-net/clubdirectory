<?php
namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

/*
 * This file is part of the clubdirectory project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
        $this->assertSame(
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

        $this->assertSame(
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
        $this->assertSame('123', $this->subject->getLetter());
    }

    /**
     * @test
     */
    public function setLetterWithBooleanResultsInString()
    {
        $this->subject->setLetter(true);
        $this->assertSame('1', $this->subject->getLetter());
    }

    /**
     * @test
     */
    public function getSearchWordInitiallyReturnsEmptyString()
    {
        $this->assertSame(
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

        $this->assertSame(
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
        $this->assertSame('123', $this->subject->getSearchWord());
    }

    /**
     * @test
     */
    public function setSearchWordWithBooleanResultsInString()
    {
        $this->subject->setSearchWord(true);
        $this->assertSame('1', $this->subject->getSearchWord());
    }

    /**
     * @test
     */
    public function getCategoryInitiallyReturnsZero()
    {
        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
            1,
            $this->subject->getCategory()
        );
    }

    /**
     * @test
     */
    public function getSubCategoryInitiallyReturnsZero()
    {
        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
            1,
            $this->subject->getSubCategory()
        );
    }

    /**
     * @test
     */
    public function getOrderByInitiallyReturnsSortTitle()
    {
        $this->assertSame(
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

        $this->assertSame(
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
        $this->assertSame('123', $this->subject->getOrderBy());
    }

    /**
     * @test
     */
    public function setOrderByWithBooleanResultsInString()
    {
        $this->subject->setOrderBy(true);
        $this->assertSame('1', $this->subject->getOrderBy());
    }

    /**
     * @test
     */
    public function getDirectionInitiallyReturnsAscending()
    {
        $this->assertSame(
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

        $this->assertSame(
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
        $this->assertSame('123', $this->subject->getDirection());
    }

    /**
     * @test
     */
    public function setDirectionWithBooleanResultsInString()
    {
        $this->subject->setDirection(true);
        $this->assertSame('1', $this->subject->getDirection());
    }
}
