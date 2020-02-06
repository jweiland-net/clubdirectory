<?php
namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

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

use JWeiland\Clubdirectory\Domain\Model\Address;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case for model Address
 */
class AddressTest extends UnitTestCase
{
    /**
     * @var Address
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new Address();
    }

    public function tearDown()
    {
        unset(
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsOrganizationAddress()
    {
        $this->assertSame(
            'organizationAddress',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleWithIntegerResultsInString()
    {
        $this->subject->setTitle(123);
        $this->assertSame('123', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleWithBooleanResultsInString()
    {
        $this->subject->setTitle(true);
        $this->assertSame('1', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getStreetInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function setStreetSetsStreet()
    {
        $this->subject->setStreet('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function setStreetWithIntegerResultsInString()
    {
        $this->subject->setStreet(123);
        $this->assertSame('123', $this->subject->getStreet());
    }

    /**
     * @test
     */
    public function setStreetWithBooleanResultsInString()
    {
        $this->subject->setStreet(true);
        $this->assertSame('1', $this->subject->getStreet());
    }

    /**
     * @test
     */
    public function getHouseNumberInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getHouseNumber()
        );
    }

    /**
     * @test
     */
    public function setHouseNumberSetsHouseNumber()
    {
        $this->subject->setHouseNumber('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getHouseNumber()
        );
    }

    /**
     * @test
     */
    public function setHouseNumberWithIntegerResultsInString()
    {
        $this->subject->setHouseNumber(123);
        $this->assertSame('123', $this->subject->getHouseNumber());
    }

    /**
     * @test
     */
    public function setHouseNumberWithBooleanResultsInString()
    {
        $this->subject->setHouseNumber(true);
        $this->assertSame('1', $this->subject->getHouseNumber());
    }

    /**
     * @test
     */
    public function getZipInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipSetsZip()
    {
        $this->subject->setZip('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipWithIntegerResultsInString()
    {
        $this->subject->setZip(123);
        $this->assertSame('123', $this->subject->getZip());
    }

    /**
     * @test
     */
    public function setZipWithBooleanResultsInString()
    {
        $this->subject->setZip(true);
        $this->assertSame('1', $this->subject->getZip());
    }

    /**
     * @test
     */
    public function getCityInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function setCitySetsCity()
    {
        $this->subject->setCity('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function setCityWithIntegerResultsInString()
    {
        $this->subject->setCity(123);
        $this->assertSame('123', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function setCityWithBooleanResultsInString()
    {
        $this->subject->setCity(true);
        $this->assertSame('1', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function getTelephoneInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getTelephone()
        );
    }

    /**
     * @test
     */
    public function setTelephoneSetsTelephone()
    {
        $this->subject->setTelephone('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTelephone()
        );
    }

    /**
     * @test
     */
    public function setTelephoneWithIntegerResultsInString()
    {
        $this->subject->setTelephone(123);
        $this->assertSame('123', $this->subject->getTelephone());
    }

    /**
     * @test
     */
    public function setTelephoneWithBooleanResultsInString()
    {
        $this->subject->setTelephone(true);
        $this->assertSame('1', $this->subject->getTelephone());
    }

    /**
     * @test
     */
    public function getFaxInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function setFaxSetsFax()
    {
        $this->subject->setFax('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function setFaxWithIntegerResultsInString()
    {
        $this->subject->setFax(123);
        $this->assertSame('123', $this->subject->getFax());
    }

    /**
     * @test
     */
    public function setFaxWithBooleanResultsInString()
    {
        $this->subject->setFax(true);
        $this->assertSame('1', $this->subject->getFax());
    }

    /**
     * @test
     */
    public function getBarrierFreeInitiallyReturnsFalse()
    {
        $this->assertFalse(
            $this->subject->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function setBarrierFreeSetsBarrierFree()
    {
        $this->subject->setBarrierFree(true);
        $this->assertTrue(
            $this->subject->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function setBarrierFreeWithStringReturnsTrue()
    {
        $this->subject->setBarrierFree('foo bar');
        $this->assertTrue($this->subject->getBarrierFree());
    }

    /**
     * @test
     */
    public function setBarrierFreeWithZeroReturnsFalse()
    {
        $this->subject->setBarrierFree(0);
        $this->assertFalse($this->subject->getBarrierFree());
    }
}
