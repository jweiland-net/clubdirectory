<?php
declare(strict_types=1);
namespace JWeiland\Clubdirectory\Domain\Repository;

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

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class ClubRepository
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
     * inject charsetConverter
     *
     * @param CharsetConverter $charsetConverter
     * @return void
     */
    public function injectCharsetConverter(CharsetConverter $charsetConverter)
    {
        $this->charsetConverter = $charsetConverter;
    }

    /**
     * Find clubs
     *
     * @param Search|null $search
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
     */
    public function findBySearch(Search $search)
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

            // set ordering
            if (in_array($search->getOrderBy(), ['title', 'sortTitle'], true)) {
                if (!in_array($search->getDirection(), [QueryInterface::ORDER_ASCENDING, QueryInterface::ORDER_DESCENDING], true)) {
                    $search->setDirection(QueryInterface::ORDER_ASCENDING);
                }
                $query->setOrderings([
                    $search->getOrderBy() => $search->getDirection()
                ]);
            }
        }

        if (!empty($constraints)) {
            return $query->matching($query->logicalAnd($constraints))->execute();
        } else {
            return $this->findAll();
        }
    }

    /**
     * find all records by category.
     *
     * @param int $category
     * @param int $district
     * @return QueryResultInterface|array
     */
    public function findByCategory($category, $district = 0)
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
     * find all records by feUser.
     *
     * @param int $feUser
     * @return QueryResultInterface|array
     */
    public function findByFeUser($feUser)
    {
        $query = $this->createQuery();

        return $query->matching($query->contains('feUsers', $feUser))->execute();
    }

    /**
     * find all records starting with given letter.
     *
     * @param string $letter
     * @param int $category
     * @param int $district
     * @return QueryResultInterface|array
     */
    public function findByStartingLetter($letter, $category = 0, $district = 0)
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
     * get an array with available starting letters.
     *
     * @param int $category
     * @return array
     */
    public function getStartingLetters(int $category = 0): array
    {
        $query = $this->createQuery();

        if ($category) {
            $where = 'sys_category_record_mm.uid_local=' . $category
                . ' AND tablenames = \'tx_clubdirectory_domain_model_club\''
                . ' AND fieldname = \'categories\'';
        } else {
            $where = '1=1';
        }

        return $query->statement('
			SELECT UPPER(LEFT(sort_title, 1)) as letter
			FROM tx_clubdirectory_domain_model_club
			JOIN sys_category_record_mm ON sys_category_record_mm.uid_foreign = tx_clubdirectory_domain_model_club.uid
			WHERE ' .$where .
            BackendUtility::BEenableFields('tx_clubdirectory_domain_model_club').
            BackendUtility::deleteClause('tx_clubdirectory_domain_model_club').'
			GROUP BY letter
			ORDER by letter;
		')->execute(true);
    }

    /**
     * Get constraint to search clubs by searchWord
     *
     * @param QueryInterface $query
     * @param string $searchWord
     * @return OrInterface
     */
    protected function getConstraintForSearchWord(QueryInterface $query, string $searchWord)
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
            $query->like('title', '%'.$searchWord.'%'),
            $query->like('sortTitle', '%'.$searchWord.'%'),
            $query->like('addresses.street', '%'.$longStreetSearch.'%'),
            $query->like('addresses.street', '%'.$smallStreetSearch.'%'),
            $query->like('addresses.zip', '%'.$searchWord.'%'),
            $query->like('addresses.city', '%'.$searchWord.'%'),
            $query->like('contactPerson', '%'.$searchWord.'%'),
            $query->like('description', '%'.$searchWord.'%'),
            $query->like('tags', '%'.$searchWord.'%')
        ];

        return $query->logicalOr($logicalOrConstraints);
    }

    /**
     * find hidden entry by uid.
     *
     * @param int $clubUid
     * @return Club|object
     */
    public function findHiddenEntryByUid($clubUid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(['disabled']);

        return $query->matching($query->equals('uid', (int) $clubUid))->execute()->getFirst();
    }

    /**
     * find all clubs to export them via CSV
     * only for BE.
     *
     * @return array
     */
    public function findAllForExport(): array
    {
        $clubs = [];
        $clubs[] = ['Title', 'Email', 'Street', 'HouseNumber', 'Zip', 'City', 'Tel'];
        $clubObjects = $this->findAll();
        if ($clubObjects->count()) {
            /** @var \JWeiland\Clubdirectory\Domain\Model\Club $club */
            foreach ($clubObjects as $club) {
                if ($club->getAddresses()->count()) {
                    /** @var \JWeiland\Clubdirectory\Domain\Model\Address $address */
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
        }

        return $clubs;
    }
}
