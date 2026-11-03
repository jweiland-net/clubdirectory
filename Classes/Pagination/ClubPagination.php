<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Pagination;

use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;

/**
 * Pagination of the club list and the search results. The page links keep
 * all arguments of the current plugin request, like "letter" or "search".
 */
class ClubPagination implements PaginationInterface
{
    protected const IGNORED_ARGUMENTS = [
        '@extension',
        '@subpackage',
        '@controller',
        '@action',
        '@format',
        'extension',
        'plugin',
        'controller',
        'action',
    ];

    protected PaginatorInterface $paginator;

    /**
     * @var array<string, mixed>
     */
    protected array $arguments = [];

    /**
     * PaginationInterface defines the constructor with the paginator only,
     * so the arguments of the Extbase request (GET merged with POST) are optional.
     *
     * @param array<string, mixed> $pluginArguments
     */
    public function __construct(PaginatorInterface $paginator, array $pluginArguments = [])
    {
        $this->paginator = $paginator;

        foreach ($pluginArguments as $argumentName => $argument) {
            $argumentName = (string)$argumentName;
            if (str_starts_with($argumentName, '__')
                || in_array($argumentName, self::IGNORED_ARGUMENTS, true)
            ) {
                continue;
            }

            $this->arguments[$argumentName] = $argument;
        }
    }

    public function getPreviousPageNumber(): ?int
    {
        $previousPage = $this->paginator->getCurrentPageNumber() - 1;

        if ($previousPage > $this->paginator->getNumberOfPages()) {
            return null;
        }

        return $previousPage >= $this->getFirstPageNumber() ? $previousPage : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPreviousPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getPreviousPageNumber();

        return $arguments;
    }

    public function getNextPageNumber(): ?int
    {
        $nextPage = $this->paginator->getCurrentPageNumber() + 1;

        return $nextPage <= $this->paginator->getNumberOfPages() ? $nextPage : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getNextPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getNextPageNumber();

        return $arguments;
    }

    public function getFirstPageNumber(): int
    {
        return 1;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFirstPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getFirstPageNumber();

        return $arguments;
    }

    public function getLastPageNumber(): int
    {
        return $this->paginator->getNumberOfPages();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastPageArguments(): ?array
    {
        $arguments = $this->arguments;
        $arguments['currentPage'] = $this->getLastPageNumber();

        return $arguments;
    }

    public function getStartRecordNumber(): int
    {
        if ($this->paginator->getCurrentPageNumber() > $this->paginator->getNumberOfPages()) {
            return 0;
        }

        return $this->paginator->getKeyOfFirstPaginatedItem() + 1;
    }

    public function getEndRecordNumber(): int
    {
        if ($this->paginator->getCurrentPageNumber() > $this->paginator->getNumberOfPages()) {
            return 0;
        }

        return $this->paginator->getKeyOfLastPaginatedItem() + 1;
    }

    public function getAllPageNumbers(): array
    {
        return range($this->getFirstPageNumber(), $this->getLastPageNumber());
    }
}
