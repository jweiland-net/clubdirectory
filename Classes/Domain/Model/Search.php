<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Domain\Model;

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

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Not really a domain model, but a helpful class to simplify our club search
 */
class Search
{
    /**
     * @var string
     */
    protected $letter = '';

    /**
     * @var string
     */
    protected $searchWord = '';

    /**
     * @var int
     */
    protected $category = 0;

    /**
     * @var int
     */
    protected $subCategory = 0;

    /**
     * @var string
     */
    protected $orderBy = '';

    /**
     * @var string
     */
    protected $direction = QueryInterface::ORDER_ASCENDING;

    /**
     * @return string
     */
    public function getLetter(): string
    {
        return $this->letter;
    }

    /**
     * @param string $letter
     */
    public function setLetter(string $letter)
    {
        $this->letter = $letter;
    }

    /**
     * @return string
     */
    public function getSearchWord(): string
    {
        return $this->searchWord;
    }

    /**
     * @param string $searchWord
     */
    public function setSearchWord(string $searchWord)
    {
        $this->searchWord = $searchWord;
    }

    /**
     * @return int
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * @param int $category
     */
    public function setCategory(int $category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getSubCategory(): int
    {
        return $this->subCategory;
    }

    /**
     * @param int $subCategory
     */
    public function setSubCategory(int $subCategory)
    {
        $this->subCategory = $subCategory;
    }

    /**
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy(string $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection(string $direction)
    {
        $this->direction = $direction;
    }

    /**
     * Helper method to fill selectbox
     * Get fieldNames to sort by
     *
     * @return array
     */
    public function getFieldNames(): array
    {
        return [
            0 => [
                'key' => 'title',
                'value' => LocalizationUtility::translate('tx_clubdirectory_domain_model_club.title', 'clubdirectory')
            ],
            1 => [
                'key' => 'sortTitle',
                'value' => LocalizationUtility::translate('tx_clubdirectory_domain_model_club.sortTitle', 'clubdirectory')
            ],
        ];
    }

    /**
     * Helper method to fill selectbox
     * Get order directions
     *
     * @return array
     */
    public function getDirections(): array
    {
        return [
            0 => [
                'key' => QueryInterface::ORDER_ASCENDING,
                'value' => LocalizationUtility::translate('ascending', 'clubdirectory')
            ],
            1 => [
                'key' => QueryInterface::ORDER_DESCENDING,
                'value' => LocalizationUtility::translate('descending', 'clubdirectory')
            ],
        ];
    }
}
