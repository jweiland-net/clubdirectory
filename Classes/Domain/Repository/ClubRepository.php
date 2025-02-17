<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Repository;

use Doctrine\DBAL\Exception;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use JWeiland\Clubdirectory\Traits\ConnectionPoolTrait;
use JWeiland\Glossary2\Service\GlossaryService;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to get and search for clubs
 */
class ClubRepository extends Repository implements HiddenRepositoryInterface
{
    use ConnectionPoolTrait;

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Charset converter
     * We need some UTF-8 compatible functions for search.
     */
    protected CharsetConverter $charsetConverter;

    protected GlossaryService $glossaryService;

    public function injectCharsetConverter(CharsetConverter $charsetConverter): void
    {
        $this->charsetConverter = $charsetConverter;
    }

    public function inject(GlossaryService $glossaryService): void
    {
        $this->glossaryService = $glossaryService;
    }

    public function findFilteredBy(int $category, int $district = 0, string $letter = ''): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [];

        if ($category !== 0) {
            $constraints[] = $query->contains('categories', $category);
        }

        if ($district !== 0) {
            $constraints[] = $query->equals('district', $district);
        }

        if ($letter !== '' && $letter !== '0') {
            $constraints[] = $this->glossaryService->getLetterConstraintForExtbaseQuery(
                $query,
                'title',
                $letter,
            );
        }

        if (count($constraints) === 1) {
            $query->matching(reset($constraints));
        } elseif (count($constraints) >= 2) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        if ($constraints === []) {
            return $query->execute();
        }

        return $query->matching($query->logicalAnd(...$constraints))->execute();
    }

    public function findBySearch(Search $search): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [];

        // If a searchWord is set, do not process other filtering methods
        if ($search->getSearchWord()) {
            $constraints[] = $this->getConstraintForSearchWord($query, $search->getSearchWord());
        } else {
            if ($search->getSubCategory()) {
                // @ToDo: getSubCategory is deprecated. Remove with next major release
                $constraints[] = $query->contains('categories', $search->getSubCategory());
            } elseif ($search->getCategory()) {
                $constraints[] = $query->contains('categories', $search->getCategory());
            }

            if ($search->getDistrict()) {
                $constraints[] = $query->equals('district', $search->getDistrict());
            }
        }

        // Set ordering
        if ($search->getOrderBy()) {
            if (!in_array(
                $search->getOrder(),
                [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING],
                true,
            )) {
                $search->setOrderBy(QueryInterface::ORDER_ASCENDING);
            }

            $query->setOrderings([
                $search->getOrderBy() => $search->getOrder(),
            ]);
        }

        return $constraints !== [] ? $query->matching($query->logicalAnd(...$constraints))->execute() : $query->execute();
    }

    public function findByFeUser(int $feUser): QueryResultInterface
    {
        $query = $this->createQuery();

        return $query->matching($query->contains('feUsers', $feUser))->execute();
    }

    public function getQueryBuilderToFindAllEntries(int $category = 0, int $district = 0): QueryBuilder
    {
        $table = 'tx_clubdirectory_domain_model_club';
        $query = $this->createQuery();
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        // Do not set any SELECT, ORDER BY, GROUP BY statement. It will be set by glossary2 API
        $queryBuilder
            ->from($table, 'c')
            ->where(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(
                        $query->getQuerySettings()->getStoragePageIds(),
                        Connection::PARAM_INT_ARRAY,
                    ),
                ),
            );

        if ($category !== 0) {
            $queryBuilder
                ->leftJoin(
                    'c',
                    'sys_category_record_mm',
                    'mm',
                    (string)$queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq(
                            'mm.tablenames',
                            $queryBuilder->createNamedParameter('tx_clubdirectory_domain_model_club'),
                        ),
                        $queryBuilder->expr()->eq(
                            'mm.fieldname',
                            $queryBuilder->createNamedParameter('categories'),
                        ),
                        $queryBuilder->expr()->eq(
                            'mm.uid_foreign',
                            $queryBuilder->quoteIdentifier('c.uid'),
                        ),
                    ),
                )
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        'mm.uid_local',
                        $queryBuilder->createNamedParameter($category, Connection::PARAM_INT),
                    ),
                );
        }

        if ($district !== 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('c.district', $district));
        }

        return $queryBuilder;
    }

    protected function getConstraintForSearchWord(QueryInterface $query, string $searchWord): OrInterface
    {
        // strtolower is not UTF-8 compatible
        $longStreetSearch = $searchWord;
        $smallStreetSearch = $searchWord;

        // unify street search
        if (\strtolower(mb_substr($searchWord, -6)) === 'straße') {
            $smallStreetSearch = \str_ireplace('straße', 'str', $searchWord);
        }

        if (\strtolower(mb_substr($searchWord, -4)) === 'str.') {
            $longStreetSearch = \str_ireplace('str.', 'straße', $searchWord);
            $smallStreetSearch = \str_ireplace('str.', 'str', $searchWord);
        }

        if (\strtolower(mb_substr($searchWord, -3)) === 'str') {
            $longStreetSearch = \str_ireplace('str', 'straße', $searchWord);
        }

        $logicalOrConstraints = [
            $query->like('title', '%' . $searchWord . '%'),
            $query->like('addresses.street', '%' . $longStreetSearch . '%'),
            $query->like('addresses.street', '%' . $smallStreetSearch . '%'),
            $query->like('addresses.zip', '%' . $searchWord . '%'),
            $query->like('addresses.city', '%' . $searchWord . '%'),
            $query->like('contactPerson', '%' . $searchWord . '%'),
            $query->like('description', '%' . $searchWord . '%'),
            $query->like('tags', '%' . $searchWord . '%'),
        ];

        return $query->logicalOr(...$logicalOrConstraints);
    }

    public function findHiddenObject($value, string $property = 'uid'): ?Club
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled']);
        $query->getQuerySettings()->setRespectStoragePage(false);

        /** @var Club $club */
        $club = $query->matching($query->equals($property, $value))->execute()->getFirst();

        return $club;
    }

    /**
     * Find all clubs to export them via CSV
     * only for BE.
     */
    public function findAllForExport(): array
    {
        $clubs = [];
        $clubs[] = ['Title', 'Email', 'Street', 'HouseNumber', 'Zip', 'City', 'Distrct', 'Tel'];

        /** @var ObjectStorage|Club[] $clubObjects */
        $clubObjects = $this->findAll();
        foreach ($clubObjects as $club) {
            foreach ($club->getAddresses() as $address) {
                if ($address->getTitle() === 'postAddress') {
                    $clubs[] = [
                        $club->getTitle(),
                        $club->getEmail(),
                        $address->getStreet(),
                        $address->getHouseNumber(),
                        $address->getZip(),
                        $address->getCity(),
                        $club->getDistrict() ? $club->getDistrict()->getDistrict() : '',
                        $address->getTelephone(),
                    ];
                }
            }
        }

        return $clubs;
    }

    public function getStoragePid(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_club');
        $queryBuilder
            ->select('pid')
            ->from('tx_clubdirectory_domain_model_club')
            ->groupBy('pid');

        try {
            return $queryBuilder
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (Exception $exception) {
            return [];
        }
    }
}
