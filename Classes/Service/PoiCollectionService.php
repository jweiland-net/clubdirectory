<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Stores the position of a club address, which was submitted by the form
 * element "Maps2PoiPosition", in the POI collection of EXT:maps2. New POI
 * collections are created with the API of EXT:maps2 in the storage folder
 * of the extension settings (poiCollectionPid).
 */
readonly class PoiCollectionService
{
    public function __construct(
        private ConnectionPool $connectionPool,
        private ClubFormRecordService $clubFormRecordService,
        private MapService $mapService,
        private ExtConf $extConf,
    ) {}

    /**
     * Returns the UID of the updated or created POI collection. Invalid
     * positions are ignored and the given UID is returned.
     *
     * @param mixed $position The value of "Maps2PoiPosition": ['latitude' => '...', 'longitude' => '...']
     */
    public function savePosition(int $poiCollectionUid, mixed $position, string $title, string $address): int
    {
        $validPosition = $this->createPosition($position, $address);
        if ($validPosition === null) {
            return $poiCollectionUid;
        }

        if ($this->clubFormRecordService->getPoiCollectionRecord($poiCollectionUid) === []) {
            return $this->mapService->createNewPoiCollection(
                $this->extConf->getPoiCollectionPid(),
                $validPosition,
                ['title' => $title],
            );
        }

        $this->updatePosition($poiCollectionUid, $validPosition);

        return $poiCollectionUid;
    }

    public function deletePoiCollection(int $poiCollectionUid): void
    {
        if ($poiCollectionUid <= 0) {
            return;
        }

        $this->connectionPool->getConnectionForTable(ClubFormRecordService::TABLE_POI_COLLECTION)->update(
            ClubFormRecordService::TABLE_POI_COLLECTION,
            ['deleted' => 1, 'tstamp' => time()],
            ['uid' => $poiCollectionUid],
        );
    }

    private function updatePosition(int $poiCollectionUid, Position $position): void
    {
        $this->connectionPool->getConnectionForTable(ClubFormRecordService::TABLE_POI_COLLECTION)->update(
            ClubFormRecordService::TABLE_POI_COLLECTION,
            [
                'latitude' => $position->getLatitude(),
                'longitude' => $position->getLongitude(),
                'address' => $position->getFormattedAddress(),
                'tstamp' => time(),
            ],
            ['uid' => $poiCollectionUid],
        );
    }

    private function createPosition(mixed $position, string $address): ?Position
    {
        $latitude = is_array($position) ? $position['latitude'] ?? null : null;
        $longitude = is_array($position) ? $position['longitude'] ?? null : null;
        if (!is_numeric($latitude) || !is_numeric($longitude)
            || abs((float)$latitude) > 90 || abs((float)$longitude) > 180
            || ((float)$latitude === 0.0 && (float)$longitude === 0.0)
        ) {
            return null;
        }

        $validPosition = new Position();
        $validPosition->setLatitude((float)$latitude);
        $validPosition->setLongitude((float)$longitude);
        $validPosition->setFormattedAddress($address);

        return $validPosition;
    }
}
