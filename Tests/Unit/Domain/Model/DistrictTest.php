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

use JWeiland\Clubdirectory\Domain\Model\District;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case for model District
 */
class DistrictTest extends UnitTestCase
{
    /**
     * @var District
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new District();
    }

    public function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getDistrictInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict()
    {
        $this->subject->setDistrict('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function setDistrictWithIntegerResultsInString()
    {
        $this->subject->setDistrict(123);
        $this->assertSame('123', $this->subject->getDistrict());
    }

    /**
     * @test
     */
    public function setDistrictWithBooleanResultsInString()
    {
        $this->subject->setDistrict(true);
        $this->assertSame('1', $this->subject->getDistrict());
    }
}
