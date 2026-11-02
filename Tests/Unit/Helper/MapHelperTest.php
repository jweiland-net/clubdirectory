<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Helper;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Helper\GeocodingFailure;
use JWeiland\Clubdirectory\Helper\MapHelper;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use JWeiland\Maps2\Domain\Model\GeoCodeResult;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for MapHelper
 */
class MapHelperTest extends UnitTestCase
{
    private const POI_COLLECTION_PID = 7;

    protected GeoCodeService&MockObject $geoCodeServiceMock;

    protected MapService&MockObject $mapServiceMock;

    protected PoiCollectionRepository&MockObject $poiCollectionRepositoryMock;

    protected LoggerInterface&MockObject $loggerMock;

    protected MapHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geoCodeServiceMock = $this->createMock(GeoCodeService::class);
        $this->mapServiceMock = $this->createMock(MapService::class);
        $this->poiCollectionRepositoryMock = $this->createMock(PoiCollectionRepository::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->subject = new MapHelper(
            $this->geoCodeServiceMock,
            $this->mapServiceMock,
            $this->poiCollectionRepositoryMock,
            new ExtConf(poiCollectionPid: self::POI_COLLECTION_PID),
            $this->loggerMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->geoCodeServiceMock,
            $this->mapServiceMock,
            $this->poiCollectionRepositoryMock,
            $this->loggerMock,
        );

        parent::tearDown();
    }

    private function createAddress(string $title = 'organizationAddress', string $zip = '79100', string $city = 'Freiburg'): Address
    {
        $address = new Address();
        $address->setTitle($title);
        $address->setStreet('Main street');
        $address->setHouseNumber('12');
        $address->setZip($zip);
        $address->setCity($city);

        return $address;
    }

    private function createClub(Address ...$addresses): Club
    {
        $club = new Club();
        $club->setTitle('Swimming club');
        foreach ($addresses as $address) {
            $club->addAddress($address);
        }

        return $club;
    }

    private function createPosition(): Position
    {
        $position = new Position();
        $position->setFormattedAddress('Main street 12, 79100 Freiburg, Germany');
        $position->setLatitude(47.99);
        $position->setLongitude(7.84);

        return $position;
    }

    private function createGeoCodeResult(Position ...$positions): GeoCodeResult
    {
        $objectStorage = new ObjectStorage();
        foreach ($positions as $position) {
            $objectStorage->attach($position);
        }

        return new GeoCodeResult($objectStorage);
    }

    private function createPersistedPoiCollection(int $uid): PoiCollection
    {
        $poiCollection = new PoiCollection();
        $poiCollection->_setProperty('uid', $uid);

        return $poiCollection;
    }

    #[Test]
    public function addMapRecordWithGeocodedAddressCreatesPoiCollection(): void
    {
        $address = $this->createAddress();
        $position = $this->createPosition();
        $poiCollection = $this->createPersistedPoiCollection(12);

        $this->geoCodeServiceMock
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->with(self::identicalTo('Main street 12, 79100 Freiburg'))
            ->willReturn($this->createGeoCodeResult($position));

        $this->mapServiceMock
            ->expects(self::once())
            ->method('createNewPoiCollection')
            ->with(
                self::identicalTo(self::POI_COLLECTION_PID),
                self::identicalTo($position),
                self::identicalTo(['title' => 'Swimming club']),
            )
            ->willReturn(12);

        $this->poiCollectionRepositoryMock
            ->expects(self::once())
            ->method('findByIdentifier')
            ->with(self::identicalTo(12))
            ->willReturn($poiCollection);

        $this->loggerMock
            ->expects(self::never())
            ->method('warning');

        self::assertNull($this->subject->addMapRecord($this->createClub($address)));
        self::assertSame($poiCollection, $address->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithMultipleAddressesUsesClubTitleForEachPoi(): void
    {
        $organizationAddress = $this->createAddress('organizationAddress');
        $postAddress = $this->createAddress('postAddress');
        $poiCollectionUids = [21, 22];

        $this->geoCodeServiceMock
            ->method('getGeoCodeResult')
            ->willReturn($this->createGeoCodeResult($this->createPosition()));

        // The POI title is the club title only, as in backend. Never the address key.
        $this->mapServiceMock
            ->expects(self::exactly(2))
            ->method('createNewPoiCollection')
            ->with(
                self::identicalTo(self::POI_COLLECTION_PID),
                self::isInstanceOf(Position::class),
                self::identicalTo(['title' => 'Swimming club']),
            )
            ->willReturnCallback(static function () use (&$poiCollectionUids): int {
                return array_shift($poiCollectionUids);
            });

        $this->poiCollectionRepositoryMock
            ->method('findByIdentifier')
            ->willReturnCallback(fn(int $uid): PoiCollection => $this->createPersistedPoiCollection($uid));

        self::assertNull($this->subject->addMapRecord($this->createClub($organizationAddress, $postAddress)));
        self::assertSame(21, $organizationAddress->getTxMaps2Uid()?->getUid());
        self::assertSame(22, $postAddress->getTxMaps2Uid()?->getUid());
    }

    #[Test]
    public function addMapRecordWithoutPositionReturnsGeocodingFailureWithError(): void
    {
        $address = $this->createAddress();
        $clientError = new ClientError(
            ClientErrorEnum::NO_POSITIONS_FOUND,
            'ZERO_RESULTS',
            'No positions found',
        );

        $this->geoCodeServiceMock
            ->method('getGeoCodeResult')
            ->willReturn(new GeoCodeResult(null, $clientError));

        $this->mapServiceMock
            ->expects(self::never())
            ->method('createNewPoiCollection');

        $this->loggerMock
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('Main street 12, 79100 Freiburg'),
                self::identicalTo(['error' => 'ZERO_RESULTS']),
            );

        $geocodingFailure = $this->subject->addMapRecord($this->createClub($address));

        self::assertInstanceOf(GeocodingFailure::class, $geocodingFailure);
        self::assertSame($address, $geocodingFailure->address);
        self::assertSame($clientError, $geocodingFailure->error);
        self::assertNull($address->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithoutPositionAndErrorReturnsGeocodingFailureWithoutError(): void
    {
        $address = $this->createAddress();

        $this->geoCodeServiceMock
            ->method('getGeoCodeResult')
            ->willReturn(new GeoCodeResult());

        $geocodingFailure = $this->subject->addMapRecord($this->createClub($address));

        self::assertInstanceOf(GeocodingFailure::class, $geocodingFailure);
        self::assertSame($address, $geocodingFailure->address);
        self::assertNull($geocodingFailure->error);
    }

    #[Test]
    public function addMapRecordStopsAtFirstAddressWhichCanNotBeGeocoded(): void
    {
        $failingAddress = $this->createAddress('organizationAddress');
        $otherAddress = $this->createAddress('postAddress');
        $clientError = new ClientError(ClientErrorEnum::REQUEST_DENIED, 'API key invalid', 'Request denied');

        $this->geoCodeServiceMock
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->willReturn(new GeoCodeResult(null, $clientError));

        $geocodingFailure = $this->subject->addMapRecord($this->createClub($failingAddress, $otherAddress));

        self::assertSame($failingAddress, $geocodingFailure?->address);
        self::assertSame(ClientErrorEnum::REQUEST_DENIED, $geocodingFailure?->error?->type);
    }

    #[Test]
    public function addMapRecordWithValidAndNonexistentAddressCreatesNoPoiCollection(): void
    {
        $validAddress = $this->createAddress('organizationAddress');
        $nonexistentAddress = $this->createAddress('postAddress', '99999', 'Nowhere');

        $this->geoCodeServiceMock
            ->expects(self::exactly(2))
            ->method('getGeoCodeResult')
            ->willReturnOnConsecutiveCalls(
                $this->createGeoCodeResult($this->createPosition()),
                new GeoCodeResult(),
            );

        // A POI of the valid address would be orphaned, as the club will not be saved
        $this->mapServiceMock
            ->expects(self::never())
            ->method('createNewPoiCollection');

        $this->poiCollectionRepositoryMock
            ->expects(self::never())
            ->method('findByIdentifier');

        $geocodingFailure = $this->subject->addMapRecord($this->createClub($validAddress, $nonexistentAddress));

        self::assertSame($nonexistentAddress, $geocodingFailure?->address);
        self::assertNull($validAddress->getTxMaps2Uid());
        self::assertNull($nonexistentAddress->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithoutZipSkipsAddress(): void
    {
        $this->geoCodeServiceMock
            ->expects(self::never())
            ->method('getGeoCodeResult');

        $address = $this->createAddress('organizationAddress', '', 'Freiburg');

        self::assertNull($this->subject->addMapRecord($this->createClub($address)));
        self::assertNull($address->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithoutCitySkipsAddress(): void
    {
        $this->geoCodeServiceMock
            ->expects(self::never())
            ->method('getGeoCodeResult');

        $address = $this->createAddress('organizationAddress', '79100', '');

        self::assertNull($this->subject->addMapRecord($this->createClub($address)));
        self::assertNull($address->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithPersistedPoiCollectionSkipsAddress(): void
    {
        $this->geoCodeServiceMock
            ->expects(self::never())
            ->method('getGeoCodeResult');

        $poiCollection = $this->createPersistedPoiCollection(5);
        $address = $this->createAddress();
        $address->setTxMaps2Uid($poiCollection);

        self::assertNull($this->subject->addMapRecord($this->createClub($address)));
        self::assertSame($poiCollection, $address->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithUnpersistedPoiCollectionReplacesPoiCollection(): void
    {
        $persistedPoiCollection = $this->createPersistedPoiCollection(13);
        $address = $this->createAddress();
        // Property mapping of the maps2 form fields without __identity
        $address->setTxMaps2Uid(new PoiCollection());

        $this->geoCodeServiceMock
            ->expects(self::once())
            ->method('getGeoCodeResult')
            ->willReturn($this->createGeoCodeResult($this->createPosition()));

        $this->mapServiceMock
            ->method('createNewPoiCollection')
            ->willReturn(13);

        $this->poiCollectionRepositoryMock
            ->method('findByIdentifier')
            ->with(self::identicalTo(13))
            ->willReturn($persistedPoiCollection);

        self::assertNull($this->subject->addMapRecord($this->createClub($address)));
        self::assertSame($persistedPoiCollection, $address->getTxMaps2Uid());
    }

    #[Test]
    public function addMapRecordWithoutAddressesReturnsNull(): void
    {
        $this->geoCodeServiceMock
            ->expects(self::never())
            ->method('getGeoCodeResult');

        self::assertNull($this->subject->addMapRecord($this->createClub()));
    }
}
