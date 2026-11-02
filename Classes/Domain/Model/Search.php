<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Model;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Not really a domain model, but a helpful class to simplify our club search
 */
class Search
{
    /**
     * Only these properties are allowed to sort the search result by.
     * The first one is used as fallback for invalid values.
     */
    protected const ALLOWED_ORDER_BY = [
        'title',
    ];

    protected const ALLOWED_ORDER = [
        QueryInterface::ORDER_ASCENDING,
        QueryInterface::ORDER_DESCENDING,
    ];

    protected string $searchWord = '';

    protected int $category = 0;

    protected int $district = 0;

    protected int $subCategory = 0;

    protected string $orderBy = 'title';

    protected string $order = QueryInterface::ORDER_ASCENDING;

    public function getSearchWord(): string
    {
        return $this->searchWord;
    }

    public function setSearchWord(string $searchWord): void
    {
        $this->searchWord = $searchWord;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    public function getDistrict(): int
    {
        return $this->district;
    }

    public function setDistrict(int $district): void
    {
        $this->district = $district;
    }

    public function getSubCategory(): int
    {
        trigger_error('Using getSubCategory is deprecated and will be removed with next major release', E_USER_DEPRECATED);
        return $this->subCategory;
    }

    public function setSubCategory(int $subCategory): void
    {
        trigger_error('Using setSubCategory is deprecated and will be removed with next major release', E_USER_DEPRECATED);
        $this->subCategory = $subCategory;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * The value is used as property name of the query orderings. Prevent
     * sorting by arbitrary properties and invalid property names.
     */
    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = in_array($orderBy, self::ALLOWED_ORDER_BY, true)
            ? $orderBy
            : self::ALLOWED_ORDER_BY[0];
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = in_array($order, self::ALLOWED_ORDER, true)
            ? $order
            : QueryInterface::ORDER_ASCENDING;
    }

    /**
     * Helper method to fill selectbox in fluid template {search.order}
     * Get sorting order
     */
    public function getSortingOrders(): array
    {
        return [
            0 => [
                'key' => QueryInterface::ORDER_ASCENDING,
                'value' => LocalizationUtility::translate('ascending', 'clubdirectory'),
            ],
            1 => [
                'key' => QueryInterface::ORDER_DESCENDING,
                'value' => LocalizationUtility::translate('descending', 'clubdirectory'),
            ],
        ];
    }
}
