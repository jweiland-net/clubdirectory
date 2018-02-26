<?php
declare(strict_types=1);
namespace JWeiland\Clubdirectory\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
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

use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Address
 *
 * @package JWeiland\Clubdirectory\Domain\Model
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     * @return void
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
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Sets the street.
     *
     * @param string $street
     * @return void
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
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * Sets the houseNumber.
     *
     * @param string $houseNumber
     * @return void
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
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Sets the zip.
     *
     * @param string $zip
     * @return void
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
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Sets the city.
     *
     * @param string $city
     * @return void
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
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * Sets the telephone.
     *
     * @param string $telephone
     * @return void
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
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * Sets the fax.
     *
     * @param string $fax
     * @return void
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
    public function getBarrierFree(): bool
    {
        return $this->barrierFree;
    }

    /**
     * Sets the barrierFree.
     *
     * @param bool $barrierFree
     * @return void
     */
    public function setBarrierFree($barrierFree)
    {
        $this->barrierFree = (boolean) $barrierFree;
    }

    /**
     * Returns the txMaps2Uid.
     *
     * @return PoiCollection|null $txMaps2Uid
     */
    public function getTxMaps2Uid()
    {
        return $this->txMaps2Uid;
    }

    /**
     * Sets the txMaps2Uid.
     *
     * @param PoiCollection $txMaps2Uid
     * @return void
     */
    public function setTxMaps2Uid(PoiCollection $txMaps2Uid = null)
    {
        $this->txMaps2Uid = $txMaps2Uid;
    }

    /**
     * helper method to get the address of the record
     * this is needed by google maps api geocode.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->getStreet() . ' ' . $this->getHouseNumber() . ', '.$this->getZip() . ' ' . $this->getCity();
    }
}
