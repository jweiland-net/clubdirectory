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
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Helper class to attach POI records of EXT:maps2 to club addresses.
 */
class MapHelper
{
    protected GeoCodeService $geoCodeService;

    protected MapService $mapService;

    protected PoiCollectionRepository $poiCollectionRepository;

    protected ExtConf $extConf;

    public function __construct(
        GeoCodeService $geoCodeService,
        MapService $mapService,
        PoiCollectionRepository $poiCollectionRepository,
        ExtConf $extConf
    ) {
        $this->geoCodeService = $geoCodeService;
        $this->mapService = $mapService;
        $this->poiCollectionRepository = $poiCollectionRepository;
        $this->extConf = $extConf;
    }

    /**
     * If no POI record was connected with a club address we try to create one.
     * If no POI record could be created or address could not be found, this method returns false.
     */
    public function addMapRecordIfPossible(Club $club, ActionController $actionController): bool
    {
        foreach ($club->getOriginalAddresses() as $address) {
            // add a new poi record if empty
            if ($address->getTxMaps2Uid() === null && $address->getZip() && $address->getCity()) {
                $position = $this->geoCodeService->getFirstFoundPositionByAddress($address->getAddress());
                if ($position instanceof Position) {
                    $poiCollectionUid = $this->mapService->createNewPoiCollection(
                        $this->extConf->getPoiCollectionPid(),
                        $position,
                        [
                            'title' => sprintf(
                                '%s (%d) - %s',
                                $club->getTitle(),
                                $club->getUid(),
                                $address->getTitle()
                            ),
                        ]
                    );
                    /** @var PoiCollection $poiCollection */
                    $poiCollection = $this->poiCollectionRepository->findByIdentifier($poiCollectionUid);
                    $address->setTxMaps2Uid($poiCollection);
                } else {
                    foreach ($this->geoCodeService->getErrors() as $error) {
                        $actionController->addFlashMessage($error->getMessage(), $error->getTitle(), $error->getSeverity()->value);
                    }
                    return false;
                }
            }
        }

        return true;
    }
}
