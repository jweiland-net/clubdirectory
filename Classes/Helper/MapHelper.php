<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Helper;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use Psr\Log\LoggerInterface;

/**
 * Helper class to attach POI records of EXT:maps2 to club addresses.
 */
class MapHelper
{
    public function __construct(
        protected readonly GeoCodeService $geoCodeService,
        protected readonly MapService $mapService,
        protected readonly PoiCollectionRepository $poiCollectionRepository,
        protected readonly ExtConf $extConf,
        protected readonly LoggerInterface $logger,
    ) {}

    /**
     * Creates a POI record at the geocoded position for each club address
     * which does not have one yet.
     *
     * @return GeocodingFailure|null The first address which could not be geocoded. NULL, if all addresses have a POI.
     */
    public function addMapRecord(Club $club): ?GeocodingFailure
    {
        foreach ($club->getOriginalAddresses() as $address) {
            if (!$this->isMapRecordRequired($address)) {
                continue;
            }

            $geoCodeResult = $this->geoCodeService->getGeoCodeResult($address->getAddress());
            $position = $geoCodeResult->getFirstPosition();
            if ($position === null) {
                // The provider message may contain sensitive data like the server IP. Log it only.
                $this->logger->warning('Address "' . $address->getAddress() . '" could not be geocoded.', [
                    'error' => $geoCodeResult->error?->message,
                ]);
                return new GeocodingFailure($address, $geoCodeResult->error);
            }

            $address->setTxMaps2Uid($this->poiCollectionRepository->findByIdentifier(
                $this->mapService->createNewPoiCollection(
                    $this->extConf->getPoiCollectionPid(),
                    $position,
                    ['title' => $this->getPoiCollectionTitle($club)],
                ),
            ));
        }

        return null;
    }

    /**
     * Use the club title as POI title, as SetTitleOfPOIToClubTitleEventListener
     * does for clubs saved in backend. Extbase does not call the DataHandler,
     * so set the title with the "overrideFieldValues" API of maps2.
     */
    protected function getPoiCollectionTitle(Club $club): string
    {
        return $club->getTitle();
    }

    /**
     * A POI which was not persisted yet must be replaced, too. It can only
     * originate from property mapping of the maps2 form fields without
     * __identity and contains neither collection type nor a valid position.
     */
    protected function isMapRecordRequired(Address $address): bool
    {
        $poiCollection = $address->getTxMaps2Uid();

        return (!$poiCollection instanceof PoiCollection || $poiCollection->_isNew())
            && $address->getZip() !== ''
            && $address->getCity() !== '';
    }
}
