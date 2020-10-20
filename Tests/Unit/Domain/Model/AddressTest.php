<?php

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

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
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleWithBooleanResultsInString()
    {
        $this->subject->setTitle(true);
        self::assertSame('1', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getStreetInitiallyReturnsEmptyString()
    {
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getStreet());
    }

    /**
     * @test
     */
    public function setStreetWithBooleanResultsInString()
    {
        $this->subject->setStreet(true);
        self::assertSame('1', $this->subject->getStreet());
    }

    /**
     * @test
     */
    public function getHouseNumberInitiallyReturnsEmptyString()
    {
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getHouseNumber());
    }

    /**
     * @test
     */
    public function setHouseNumberWithBooleanResultsInString()
    {
        $this->subject->setHouseNumber(true);
        self::assertSame('1', $this->subject->getHouseNumber());
    }

    /**
     * @test
     */
    public function getZipInitiallyReturnsEmptyString()
    {
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getZip());
    }

    /**
     * @test
     */
    public function setZipWithBooleanResultsInString()
    {
        $this->subject->setZip(true);
        self::assertSame('1', $this->subject->getZip());
    }

    /**
     * @test
     */
    public function getCityInitiallyReturnsEmptyString()
    {
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function setCityWithBooleanResultsInString()
    {
        $this->subject->setCity(true);
        self::assertSame('1', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function getTelephoneInitiallyReturnsEmptyString()
    {
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getTelephone());
    }

    /**
     * @test
     */
    public function setTelephoneWithBooleanResultsInString()
    {
        $this->subject->setTelephone(true);
        self::assertSame('1', $this->subject->getTelephone());
    }

    /**
     * @test
     */
    public function getFaxInitiallyReturnsEmptyString()
    {
        self::assertSame(
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

        self::assertSame(
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
        self::assertSame('123', $this->subject->getFax());
    }

    /**
     * @test
     */
    public function setFaxWithBooleanResultsInString()
    {
        $this->subject->setFax(true);
        self::assertSame('1', $this->subject->getFax());
    }

    /**
     * @test
     */
    public function getBarrierFreeInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function setBarrierFreeSetsBarrierFree()
    {
        $this->subject->setBarrierFree(true);
        self::assertTrue(
            $this->subject->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function setBarrierFreeWithStringReturnsTrue()
    {
        $this->subject->setBarrierFree('foo bar');
        self::assertTrue($this->subject->getBarrierFree());
    }

    /**
     * @test
     */
    public function setBarrierFreeWithZeroReturnsFalse()
    {
        $this->subject->setBarrierFree(0);
        self::assertFalse($this->subject->getBarrierFree());
    }
}
