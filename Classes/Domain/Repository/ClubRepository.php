<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Domain\Repository;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to get and search for clubs
 */
class ClubRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'sortTitle' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * charset converter
     * We need some UTF-8 compatible functions for search.
     *
     * @var CharsetConverter
     */
    protected $charsetConverter;

    /**
     * @param CharsetConverter $charsetConverter
     */
    public function injectCharsetConverter(CharsetConverter $charsetConverter)
    {
        $this->charsetConverter = $charsetConverter;
    }

    /**
     * Find clubs
     *
     * @param Search|null $search
     * @return QueryResultInterface
     */
    public function findBySearch(Search $search = null): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [];

        // if a searchWord is set, do not process other filtering methods
        if ($search->getSearchWord()) {
            $constraints[] = $this->getConstraintForSearchWord($query, $search->getSearchWord());
        } elseif ($search->getLetter()) {
            // if a letter is set, do not process other filtering methods
            $constraintOr = [];
            if ($search->getLetter() === '0-9') {
                $constraintOr[] = $query->like('sortTitle', '0%');
                $constraintOr[] = $query->like('sortTitle', '1%');
                $constraintOr[] = $query->like('sortTitle', '2%');
                $constraintOr[] = $query->like('sortTitle', '3%');
                $constraintOr[] = $query->like('sortTitle', '4%');
                $constraintOr[] = $query->like('sortTitle', '5%');
                $constraintOr[] = $query->like('sortTitle', '6%');
                $constraintOr[] = $query->like('sortTitle', '7%');
                $constraintOr[] = $query->like('sortTitle', '8%');
                $constraintOr[] = $query->like('sortTitle', '9%');
            } else {
                $constraintOr[] = $query->like('sortTitle', $search->getLetter() . '%');
            }
            $constraints[] = $query->logicalOr($constraintOr);
        } else {
            // add (Sub-)Category
            if ($search->getSubCategory()) {
                $constraints[] = $query->contains('categories', $search->getSubCategory());
            } elseif ($search->getCategory()) {
                $constraints[] = $query->contains('categories', $search->getCategory());
            }
        }

        // set ordering
        if (in_array($search->getOrderBy(), ['title', 'sortTitle'], true)) {
            if (!in_array($search->getDirection(), [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING], true)) {
                $search->setDirection(QueryInterface::ORDER_ASCENDING);
            }
            $query->setOrderings([
                $search->getOrderBy() => $search->getDirection()
            ]);
        }

        if (!empty($constraints)) {
            return $query->matching($query->logicalAnd($constraints))->execute();
        } else {
            return $query->execute();
        }
    }

    /**
     * Find all records by category and district.
     *
     * @param int $category
     * @param int $district
     * @return QueryResultInterface
     */
    public function findByCategory(int $category, int $district = 0): QueryResultInterface
    {
        $query = $this->createQuery();

        $constraints = [];
        if (!empty($category)) {
            $constraints[] = $query->contains('categories', $category);
        }
        if ($district) {
            $constraints[] = $query->equals('district', $district);
        }
        if (empty($constraints)) {
            return $query->execute();
        }

        return $query->matching($query->logicalAnd($constraints))->execute();
    }

    /**
     * Find all records by feUser.
     *
     * @param int $feUser
     * @return QueryResultInterface
     */
    public function findByFeUser(int $feUser): QueryResultInterface
    {
        $query = $this->createQuery();

        return $query->matching($query->contains('feUsers', $feUser))->execute();
    }

    /**
     * Find all records starting with given letter.
     *
     * @param string $letter
     * @param int $category
     * @param int $district
     * @return QueryResultInterface
     */
    public function findByStartingLetter(string $letter, int $category = 0, int $district = 0): QueryResultInterface
    {
        $query = $this->createQuery();

        $constraintOr = [];
        $constraintAnd = [];

        if ($letter === '0-9') {
            $constraintOr[] = $query->like('sortTitle', '0%');
            $constraintOr[] = $query->like('sortTitle', '1%');
            $constraintOr[] = $query->like('sortTitle', '2%');
            $constraintOr[] = $query->like('sortTitle', '3%');
            $constraintOr[] = $query->like('sortTitle', '4%');
            $constraintOr[] = $query->like('sortTitle', '5%');
            $constraintOr[] = $query->like('sortTitle', '6%');
            $constraintOr[] = $query->like('sortTitle', '7%');
            $constraintOr[] = $query->like('sortTitle', '8%');
            $constraintOr[] = $query->like('sortTitle', '9%');
        } else {
            $constraintOr[] = $query->like('sortTitle', $letter . '%');
        }

        $constraintAnd[] = $query->logicalOr($constraintOr);

        if ($category) {
            $constraintAnd[] = $query->contains('categories', $category);
        }

        if ($district) {
            $constraintAnd[] = $query->equals('district', $district);
        }

        if ($constraintAnd) {
            return $query->matching($query->logicalAnd($constraintAnd))->execute();
        }

        return $query->execute();
    }

    /**
     * Get an array with available starting letters.
     *
     * @param int $category
     * @param int $district
     * @return array
     */
    public function getStartingLetters(int $category = 0, int $district = 0): array
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_domain_model_club');
        $queryBuilder
            ->selectLiteral('UPPER(LEFT(sort_title, 1)) as letter')
            ->from('tx_clubdirectory_domain_model_club', 'c')
            ->add('groupBy', 'letter')
            ->add('orderBy', 'letter ASC');

        if ($category) {
            $queryBuilder
                ->leftJoin(
                    'c',
                    'sys_category_record_mm',
                    'mm',
                    (string)$queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq(
                            'mm.tablenames',
                            $queryBuilder->createNamedParameter('tx_clubdirectory_domain_model_club', \PDO::PARAM_STR)
                        ),
                        $queryBuilder->expr()->eq(
                            'mm.fieldname',
                            $queryBuilder->createNamedParameter('categories', \PDO::PARAM_STR)
                        ),
                        $queryBuilder->expr()->eq(
                            'mm.uid_foreign',
                            $queryBuilder->quoteIdentifier('c.uid')
                        )
                    )
                )
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        'mm.uid_local',
                        $queryBuilder->createNamedParameter($category, \PDO::PARAM_INT)
                    )
                );
        }
        if ($district) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('c.district', $district));
        }

        return $query->statement($queryBuilder)->execute(true);
    }

    /**
     * Get constraint to search clubs by searchWord
     *
     * @param QueryInterface $query
     * @param string $searchWord
     * @return OrInterface
     */
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
            $query->like('sortTitle', '%' . $searchWord . '%'),
            $query->like('addresses.street', '%' . $longStreetSearch . '%'),
            $query->like('addresses.street', '%' . $smallStreetSearch . '%'),
            $query->like('addresses.zip', '%' . $searchWord . '%'),
            $query->like('addresses.city', '%' . $searchWord . '%'),
            $query->like('contactPerson', '%' . $searchWord . '%'),
            $query->like('description', '%' . $searchWord . '%'),
            $query->like('tags', '%' . $searchWord . '%')
        ];

        return $query->logicalOr($logicalOrConstraints);
    }

    /**
     * Find hidden entry by uid.
     *
     * @param int $clubUid
     * @return Club
     */
    public function findHiddenEntryByUid(int $clubUid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled']);

        /** @var Club $club */
        $club = $query->matching($query->equals('uid', (int) $clubUid))->execute()->getFirst();
        return $club;
    }

    /**
     * Find all clubs to export them via CSV
     * only for BE.
     *
     * @return array
     */
    public function findAllForExport(): array
    {
        $clubs = [];
        $clubs[] = ['Title', 'Email', 'Street', 'HouseNumber', 'Zip', 'City', 'Tel'];
        /** @var ObjectStorage|Club[] $clubObjects */
        $clubObjects = $this->findAll();
        if ($clubObjects->count()) {
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
                            $address->getTelephone()
                        ];
                    }
                }
            }
        }

        return $clubs;
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
