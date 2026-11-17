<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Hook\Form;

use Doctrine\DBAL\ArrayParameterType;
use JWeiland\Clubdirectory\Configuration\ExtConf;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * Adds the options of the elements "categories" and "district" of the club
 * form based on EXT:form:
 *
 * - categories: The direct children of the root category of the extension
 *   settings (rootCategory), like the category filter of the list view
 * - district: All districts
 *
 * The options are translated into the current language and sorted by label.
 * SaveClubFinisher stores submitted values only, if they are an option.
 */
readonly class PrefillOptionsHook
{
    private const FORM_IDENTIFIER = 'newClub';

    public function __construct(
        private ConnectionPool $connectionPool,
        private PageRepository $pageRepository,
        private ExtConf $extConf,
    ) {}

    /**
     * Called by EXT:form for each element, after it was created
     */
    public function initializeFormElement(RenderableInterface $renderable): void
    {
        if (!$renderable instanceof FormElementInterface) {
            return;
        }

        $options = match ($renderable->getUniqueIdentifier()) {
            self::FORM_IDENTIFIER . '-categories'
                => $this->getOptions('sys_category', 'title', $this->extConf->getRootCategory()),
            self::FORM_IDENTIFIER . '-district' => $this->getOptions('tx_clubdirectory_domain_model_district', 'district'),
            default => null,
        };

        if ($options !== null) {
            // setProperty() merges arrays. Remove options of the form definition first.
            $renderable->setProperty('options', null);
            $renderable->setProperty('options', $options);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function getOptions(string $table, string $labelColumn, ?int $parentCategory = null): array
    {
        $queryBuilder = $this->getQueryBuilder($table);
        if ($parentCategory !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq(
                'parent',
                $queryBuilder->createNamedParameter($parentCategory, Connection::PARAM_INT),
            ));
        }

        $options = [];
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $record) {
            $translatedRecord = $this->pageRepository->getLanguageOverlay($table, $record);
            if (is_array($translatedRecord)) {
                $options[(int)$record['uid']] = (string)$translatedRecord[$labelColumn];
            }
        }

        asort($options, SORT_LOCALE_STRING);

        return $options;
    }

    /**
     * Records of the default language, which are visible in frontend
     */
    private function getQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(new FrontendRestrictionContainer());

        return $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->in(
                'sys_language_uid',
                $queryBuilder->createNamedParameter([0, -1], ArrayParameterType::INTEGER),
            ));
    }
}
