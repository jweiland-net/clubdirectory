<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Service;

use JWeiland\Clubdirectory\Helper\GeocodingFailure;
use JWeiland\Clubdirectory\Service\GeocodingService;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for GeocodingService. The map provider of EXT:maps2 is replaced by a mock.
 */
class GeocodingServiceTest extends UnitTestCase
{
    #[Test]
    public function formatAddressReturnsAddressOfAddressModel(): void
    {
        $subject = new GeocodingService(self::createStub(GeoCodeService::class), self::createStub(LoggerInterface::class));

        self::assertSame(
            'Main street 1, 79100 Freiburg',
            $subject->formatAddress(' Main street', '1 ', '79100', 'Freiburg'),
        );
    }

    #[Test]
    public function geocodeReturnsFirstPosition(): void
    {
        $position = new Position();
        $position->setLatitude(47.9);
        $position->setLongitude(7.8);
        /** @var ObjectStorage<Position> $positions */
        $positions = new ObjectStorage();
        $positions->attach($position);

        $geoCodeService = $this->createMock(GeoCodeService::class);
        $geoCodeService
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->with('Main street 1, 79100 Freiburg')
            ->willReturn(new GeoCodeResult($positions));

        $subject = new GeocodingService($geoCodeService, self::createStub(LoggerInterface::class));

        self::assertSame($position, $subject->geocode('Main street 1, 79100 Freiburg'));
    }

    #[Test]
    public function geocodeWithoutPositionReturnsFailureAndLogsProviderMessage(): void
    {
        $error = new ClientError(ClientErrorEnum::QUOTA_EXCEEDED, 'Quota of 1.2.3.4 exceeded', 'Quota');
        $geoCodeService = self::createStub(GeoCodeService::class);
        $geoCodeService->method('getGeoCodeResult')->willReturn(new GeoCodeResult(null, $error));

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(self::anything(), ['error' => 'Quota of 1.2.3.4 exceeded']);

        $failure = (new GeocodingService($geoCodeService, $logger))->geocode('Unknown 1, 00000 Nowhere');

        self::assertInstanceOf(GeocodingFailure::class, $failure);
        self::assertSame('Unknown 1, 00000 Nowhere', $failure->address);
        self::assertSame($error, $failure->error);
    }
}
