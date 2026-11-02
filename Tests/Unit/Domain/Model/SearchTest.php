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
use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->subject->setOrderBy('title');

        self::assertSame(
            'title',
            $this->subject->getOrderBy(),
        );
    }

    #[Test]
    public function setOrderByWithInvalidValueSetsTitle(): void
    {
        $this->subject->setOrderBy('feUsers.password');

        self::assertSame(
            'title',
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
        $this->subject->setOrder(QueryInterface::ORDER_DESCENDING);

        self::assertSame(
            QueryInterface::ORDER_DESCENDING,
            $this->subject->getOrder(),
        );
    }

    #[Test]
    public function setOrderWithInvalidValueSetsAscending(): void
    {
        $this->subject->setOrder('foo bar');

        self::assertSame(
            QueryInterface::ORDER_ASCENDING,
            $this->subject->getOrder(),
        );
    }

    #[Test]
    public function getOrderByInitiallyReturnsTitle(): void
    {
        self::assertSame(
            'title',
            $this->subject->getOrderBy(),
        );
    }

    public static function invalidOrderByDataProvider(): array
    {
        return [
            'property of relation' => ['feUsers.password'],
            'other property of club' => ['email'],
            'column name instead of property name' => ['path_segment'],
            'uppercase title' => ['Title'],
            'title with whitespace' => [' title'],
            'SQL fragment' => ['title; DROP TABLE fe_users'],
            'empty string' => [''],
        ];
    }

    #[Test]
    #[DataProvider('invalidOrderByDataProvider')]
    public function setOrderByWithInvalidValueFallsBackToTitle(string $orderBy): void
    {
        $this->subject->setOrderBy($orderBy);

        self::assertSame(
            'title',
            $this->subject->getOrderBy(),
        );
    }

    public static function validOrderDataProvider(): array
    {
        return [
            'ascending' => [QueryInterface::ORDER_ASCENDING],
            'descending' => [QueryInterface::ORDER_DESCENDING],
        ];
    }

    #[Test]
    #[DataProvider('validOrderDataProvider')]
    public function setOrderWithValidValueSetsOrder(string $order): void
    {
        $this->subject->setOrder($order);

        self::assertSame(
            $order,
            $this->subject->getOrder(),
        );
    }

    public static function invalidOrderDataProvider(): array
    {
        return [
            'lowercase ascending' => ['asc'],
            'lowercase descending' => ['desc'],
            'descending with whitespace' => ['DESC '],
            'SQL fragment' => ['DESC, uid'],
            'empty string' => [''],
        ];
    }

    #[Test]
    #[DataProvider('invalidOrderDataProvider')]
    public function setOrderWithInvalidValueFallsBackToAscending(string $order): void
    {
        $this->subject->setOrder(QueryInterface::ORDER_DESCENDING);
        $this->subject->setOrder($order);

        self::assertSame(
            QueryInterface::ORDER_ASCENDING,
            $this->subject->getOrder(),
        );
    }
}
