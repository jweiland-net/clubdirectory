<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Pagination;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class ClubPagination implements PaginationInterface
{
    protected string $pluginNamespace = 'tx_clubdirectory_clubdirectory';

    protected PaginatorInterface $paginator;

    protected array $arguments = [];

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
        $pluginArguments = $this->getPluginArguments($this->pluginNamespace);

        foreach ($pluginArguments as $argumentName => $argument) {
            if ($argumentName[0] === '_' && $argumentName[1] === '_') {
                continue;
            }

            if (in_array($argumentName, ['@extension', '@subpackage', '@controller', '@action', '@format'], true)) {
                continue;
            }

            if (in_array($argumentName, ['extension', 'plugin', 'controller', 'action'], true)) {
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

    public function getPluginArguments($pluginNamespace): array
    {
        $request = $this->getRequestFromGlobalScope();
        $getMergedWithPost = $request->getQueryParams()[$pluginNamespace] ?? [];
        $postArgument = $request->getParsedBody()[$pluginNamespace] ?? [];
        ArrayUtility::mergeRecursiveWithOverrule($getMergedWithPost, $postArgument);

        return $getMergedWithPost;
    }

    public function getRequestFromGlobalScope(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
