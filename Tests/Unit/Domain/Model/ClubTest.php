<?php

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
 *  the Free Software Foundation; either version 2 of the License, or
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

/**
 * Test case for class Tx_Clubdirectory_Domain_Model_Club.
 *
 * @version $Id$
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author Stefan Froemken <projects@jweiland.net>
 */
class Tx_Clubdirectory_Domain_Model_ClubTest extends Tx_Extbase_Tests_Unit_BaseTestCase
{
    /**
     * @var Tx_Clubdirectory_Domain_Model_Club
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new Tx_Clubdirectory_Domain_Model_Club();
    }

    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function getNameReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setNameForStringSetsName()
    {
        $this->fixture->setName('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getName()
        );
    }

    /**
     * @test
     */
    public function getActivityReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setActivityForStringSetsActivity()
    {
        $this->fixture->setActivity('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getActivity()
        );
    }

    /**
     * @test
     */
    public function getStreetReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setStreetForStringSetsStreet()
    {
        $this->fixture->setStreet('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getStreet()
        );
    }

    /**
     * @test
     */
    public function getHouseNumberReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setHouseNumberForStringSetsHouseNumber()
    {
        $this->fixture->setHouseNumber('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getHouseNumber()
        );
    }

    /**
     * @test
     */
    public function getZipReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setZipForStringSetsZip()
    {
        $this->fixture->setZip('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getZip()
        );
    }

    /**
     * @test
     */
    public function getCityReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setCityForStringSetsCity()
    {
        $this->fixture->setCity('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getCity()
        );
    }

    /**
     * @test
     */
    public function getTelephoneReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setTelephoneForStringSetsTelephone()
    {
        $this->fixture->setTelephone('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getTelephone()
        );
    }

    /**
     * @test
     */
    public function getFaxReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setFaxForStringSetsFax()
    {
        $this->fixture->setFax('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getFax()
        );
    }

    /**
     * @test
     */
    public function getContactPersonReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setContactPersonForStringSetsContactPerson()
    {
        $this->fixture->setContactPerson('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getContactPerson()
        );
    }

    /**
     * @test
     */
    public function getContactTimesReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setContactTimesForStringSetsContactTimes()
    {
        $this->fixture->setContactTimes('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getContactTimes()
        );
    }

    /**
     * @test
     */
    public function getEmailReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setEmailForStringSetsEmail()
    {
        $this->fixture->setEmail('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getEmail()
        );
    }

    /**
     * @test
     */
    public function getWebsiteReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setWebsiteForStringSetsWebsite()
    {
        $this->fixture->setWebsite('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getWebsite()
        );
    }

    /**
     * @test
     */
    public function getDistrictReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setDistrictForStringSetsDistrict()
    {
        $this->fixture->setDistrict('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getDistrict()
        );
    }

    /**
     * @test
     */
    public function getBarrierFreeReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setBarrierFreeForStringSetsBarrierFree()
    {
        $this->fixture->setBarrierFree('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function getMembersReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setMembersForStringSetsMembers()
    {
        $this->fixture->setMembers('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getMembers()
        );
    }

    /**
     * @test
     */
    public function getClubHomeReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setClubHomeForStringSetsClubHome()
    {
        $this->fixture->setClubHome('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getClubHome()
        );
    }

    /**
     * @test
     */
    public function getDescriptionReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setDescriptionForStringSetsDescription()
    {
        $this->fixture->setDescription('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function getTxMaps2UidReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setTxMaps2UidForStringSetsTxMaps2Uid()
    {
        $this->fixture->setTxMaps2Uid('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getTxMaps2Uid()
        );
    }

    /**
     * @test
     */
    public function getUserReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setUserForStringSetsUser()
    {
        $this->fixture->setUser('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getUser()
        );
    }

    /**
     * @test
     */
    public function getLogoReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setLogoForStringSetsLogo()
    {
        $this->fixture->setLogo('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getLogo()
        );
    }

    /**
     * @test
     */
    public function getImagesReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setImagesForStringSetsImages()
    {
        $this->fixture->setImages('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getImages()
        );
    }

    /**
     * @test
     */
    public function getFacebookReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setFacebookForStringSetsFacebook()
    {
        $this->fixture->setFacebook('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getFacebook()
        );
    }

    /**
     * @test
     */
    public function getTwitterReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setTwitterForStringSetsTwitter()
    {
        $this->fixture->setTwitter('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getTwitter()
        );
    }

    /**
     * @test
     */
    public function getGoogleReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setGoogleForStringSetsGoogle()
    {
        $this->fixture->setGoogle('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getGoogle()
        );
    }

    /**
     * @test
     */
    public function getTagsReturnsInitialValueForString()
    {
    }

    /**
     * @test
     */
    public function setTagsForStringSetsTags()
    {
        $this->fixture->setTags('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->fixture->getTags()
        );
    }
}
