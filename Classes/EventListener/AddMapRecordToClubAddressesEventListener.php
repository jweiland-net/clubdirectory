<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Event\PostProcessFluidVariablesEvent;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * If no EXT:maps2 POI record was connected with club addresses try to create one.
 */
class AddMapRecordToClubAddressesEventListener extends AbstractControllerEventListener
{
    /**
     * @var GlossaryService
     */
    protected $glossaryService;

    /**
     * @var ClubRepository
     */
    protected $clubRepository;

    protected $allowedControllerActions = [
        'Club' => [
            'list',
            'search',
        ],
    ];

    public function __construct(GlossaryService $glossaryService, ClubRepository $clubRepository)
    {
        $this->glossaryService = $glossaryService;
        $this->clubRepository = $clubRepository;
    }

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $geocodeService = GeneralUtility::makeInstance(GeoCodeService::class);
            $mapService = GeneralUtility::makeInstance(MapService::class);
            $poiCollectionRepository = GeneralUtility::makeInstance(PoiCollectionRepository::class);
            foreach ($club->getOriginalAddresses() as $address) {
                // add a new poi record if empty
                if ($address->getTxMaps2Uid() === null && $address->getZip() && $address->getCity()) {
                    $position = $geocodeService->getFirstFoundPositionByAddress($address->getAddress());
                    if ($position instanceof Position) {
                        $poiCollectionUid = $mapService->createNewPoiCollection(
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
                        $poiCollection = $poiCollectionRepository->findByIdentifier($poiCollectionUid);
                        $address->setTxMaps2Uid($poiCollection);
                    } else {
                        foreach ($geocodeService->getErrors() as $error) {
                            $this->addFlashMessage($error->getMessage(), $error->getTitle(), $error->getSeverity());
                        }
                        $this->errorAction();
                    }
                }
            }
        }
    }
}
