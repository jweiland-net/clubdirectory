<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Domain\Model;

use JWeiland\Clubdirectory\Domain\Model\District;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for model District
 */
class DistrictTest extends UnitTestCase
{
    /**
     * @var District
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new District();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getDistrictInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDistrict(),
        );
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict(): void
    {
        $this->subject->setDistrict('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDistrict(),
        );
    }
}
