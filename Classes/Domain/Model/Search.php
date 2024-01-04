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

    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    /**
     * Helper method to fill selectbox in fluid template {search.direction}
     * Get order directions
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
