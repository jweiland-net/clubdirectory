<?php

namespace JWeiland\Clubdirectory\Domain\Model;

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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Address extends AbstractEntity
{
    /**
     * Title.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title = 'organizationAddress';

    /**
     * Street.
     *
     * @var string
     */
    protected $street = '';

    /**
     * House number.
     *
     * @var string
     */
    protected $houseNumber = '';

    /**
     * Zip.
     *
     * @var string
     * @validate RegularExpression(regularExpression='/^[0-9]{4,5}$/')
     */
    protected $zip = '';

    /**
     * City.
     *
     * @var string
     */
    protected $city = '';

    /**
     * Telephone.
     *
     * @var string
     */
    protected $telephone = '';

    /**
     * Fax.
     *
     * @var string
     */
    protected $fax = '';

    /**
     * Barrier-free.
     *
     * @var bool
     */
    protected $barrierFree = false;

    /**
     * Map.
     *
     * @var \JWeiland\Maps2\Domain\Model\PoiCollection
     */
    protected $txMaps2Uid;

    /**
     * Returns the title.
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * Returns the street.
     *
     * @return string $street
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the street.
     *
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = (string) $street;
    }

    /**
     * Returns the houseNumber.
     *
     * @return string $houseNumber
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     * Sets the houseNumber.
     *
     * @param string $houseNumber
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = (string) $houseNumber;
    }

    /**
     * Returns the zip.
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the zip.
     *
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = (string) $zip;
    }

    /**
     * Returns the city.
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city.
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = (string) $city;
    }

    /**
     * Returns the telephone.
     *
     * @return string $telephone
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Sets the telephone.
     *
     * @param string $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = (string) $telephone;
    }

    /**
     * Returns the fax.
     *
     * @return string $fax
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Sets the fax.
     *
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = (string) $fax;
    }

    /**
     * Returns the barrierFree.
     *
     * @return bool $barrierFree
     */
    public function getBarrierFree()
    {
        return $this->barrierFree;
    }

    /**
     * Sets the barrierFree.
     *
     * @param bool $barrierFree
     */
    public function setBarrierFree($barrierFree)
    {
        $this->barrierFree = (boolean) $barrierFree;
    }

    /**
     * Returns the txMaps2Uid.
     *
     * @return \JWeiland\Maps2\Domain\Model\PoiCollection $txMaps2Uid
     */
    public function getTxMaps2Uid()
    {
        return $this->txMaps2Uid;
    }

    /**
     * Sets the txMaps2Uid.
     *
     * @param \JWeiland\Maps2\Domain\Model\PoiCollection $txMaps2Uid
     */
    public function setTxMaps2Uid(\JWeiland\Maps2\Domain\Model\PoiCollection $txMaps2Uid)
    {
        $this->txMaps2Uid = $txMaps2Uid;
    }

    /**
     * helper method to get the address of the record
     * this is needed by google maps api geocode.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getStreet().' '.$this->getHouseNumber().', '.$this->getZip().' '.$this->getCity();
    }
}
