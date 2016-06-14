<?php

namespace JWeiland\Clubdirectory\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClubRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'sortTitle' => QueryInterface::ORDER_ASCENDING,
    );

    /**
     * charset converter
     * We need some UTF-8 compatible functions for search.
     *
     * @var \TYPO3\CMS\Core\Charset\CharsetConverter
     * @inject
     */
    protected $charsetConverter;

    /**
     * find all records by category.
     *
     * @param int $category
     * @param int $district
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByCategory($category, $district = 0)
    {
        $query = $this->createQuery();

        $constraints = array();
        if (!empty($category)) {
            $constraints[] = $query->contains('categories', $category);
        }
        if ($district) {
            $constraints[] = $query->equals('district', $district);
        }
        if (!empty($constraints)) {
            return $query->matching($query->logicalAnd($constraints))->execute();
        } else {
            return $query->execute();
        }
    }

    /**
     * find all records by feUser.
     *
     * @param int $feUser
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
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
     * @param int    $district
     * @param string $city
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByStartingLetter($letter, $category = 0, $district = 0)
    {
        $query = $this->createQuery();

        $constraintOr = array();
        $constraintAnd = array();

        if ($letter == '0-9') {
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
            $constraintOr[] = $query->like('sortTitle', $letter.'%');
        }

        $constraintAnd[] = $query->logicalOr($constraintOr);

        if ($category) {
            $constraintAnd[] = $query->equals('categories.uid', $category);
        }

        if (!empty($district)) {
            $constraintAnd[] = $query->equals('district.uid', $district);
        }

        if ($constraintAnd) {
            return $query->matching($query->logicalAnd($constraintAnd))->execute();
        } else {
            return $query->matching()->execute();
        }
    }

    /**
     * get an array with available starting letters.
     *
     * @return array
     */
    public function getStartingLetters()
    {
        $query = $this->createQuery();

        return $query->statement('
			SELECT UPPER(LEFT(sort_title, 1)) as letter
			FROM tx_clubdirectory_domain_model_club
			WHERE 1=1'.
            BackendUtility::BEenableFields('tx_clubdirectory_domain_model_club').
            BackendUtility::deleteClause('tx_clubdirectory_domain_model_club').'
			GROUP BY letter
			ORDER by letter;
		')->execute(true);
    }

    /**
     * search records.
     *
     * @param string $search
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function searchClubs($search)
    {
        // strtolower is not UTF-8 compatible
        // $search = strtolower($search);
        $longStreetSearch = $search;
        $smallStreetSearch = $search;

        // unify street search
        if (strtolower($this->charsetConverter->utf8_substr($search, -6) === 'straße')) {
            $smallStreetSearch = str_ireplace('straße', 'str', $search);
        }
        if (strtolower($this->charsetConverter->utf8_substr($search, -4)) === 'str.') {
            $longStreetSearch = str_ireplace('str.', 'straße', $search);
            $smallStreetSearch = str_ireplace('str.', 'str', $search);
        }
        if (strtolower($this->charsetConverter->utf8_substr($search, -3)) === 'str') {
            $longStreetSearch = str_ireplace('str', 'straße', $search);
        }

        $query = $this->createQuery();

        return $query->matching(
            $query->logicalOr(
                $query->like('title', '%'.$search.'%'),
                $query->like('sortTitle', '%'.$search.'%'),
                $query->like('addresses.street', '%'.$longStreetSearch.'%'),
                $query->like('addresses.street', '%'.$smallStreetSearch.'%'),
                $query->like('addresses.zip', '%'.$search.'%'),
                $query->like('addresses.city', '%'.$search.'%'),
                $query->like('contactPerson', '%'.$search.'%'),
                $query->like('description', '%'.$search.'%'),
                $query->like('tags', '%'.$search.'%')
            )
        )->execute();
    }

    /**
     * find hidden entry by uid.
     *
     * @param int $clubUid
     *
     * @return \JWeiland\Clubdirectory\Domain\Model\Club
     */
    public function findHiddenEntryByUid($clubUid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setEnableFieldsToBeIgnored(array('disabled'));

        return $query->matching($query->equals('uid', (int) $clubUid))->execute()->getFirst();
    }

    /**
     * find all clubs to export them via CSV
     * only for BE.
     *
     * @return array
     */
    public function findAllForExport()
    {
        $clubs = array();
        $clubs[] = array('Title', 'Email', 'Street', 'HouseNumber', 'Zip', 'City', 'Tel');
        $clubObjects = $this->findAll();
        if ($clubObjects->count()) {
            /** @var \JWeiland\Clubdirectory\Domain\Model\Club $club */
            foreach ($clubObjects as $club) {
                if ($club->getAddresses()->count()) {
                    /** @var \JWeiland\Clubdirectory\Domain\Model\Address $address */
                    foreach ($club->getAddresses() as $address) {
                        if ($address->getTitle() === 'postAddress') {
                            $clubs[] = array($club->getTitle(), $club->getEmail(), $address->getStreet(), $address->getHouseNumber(), $address->getZip(), $address->getCity(), $address->getTelephone());
                        }
                    }
                }
            }
        }

        return $clubs;
    }
}
