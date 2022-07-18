<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Repository\CategoryRepository;
use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Domain\Repository\FrontendUserRepository;
use JWeiland\Clubdirectory\Event\PostProcessControllerActionEvent;
use JWeiland\Clubdirectory\Event\PostProcessFluidVariablesEvent;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract controller with useful methods for all other classes
 */
class AbstractController extends ActionController
{
    /**
     * @var ClubRepository
     */
    protected $clubRepository;

    /**
     * @var FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ExtConf
     */
    protected $extConf;

    public function injectClubRepository(ClubRepository $clubRepository): void
    {
        $this->clubRepository = $clubRepository;
    }

    public function injectFeUserRepository(FrontendUserRepository $feUserRepository): void
    {
        $this->frontendUserRepository = $feUserRepository;
    }

    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function injectSession(Session $session): void
    {
        $this->session = $session;
    }

    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    public function initializeAction(): void
    {
        // if this value was not set, then it will be filled with 0
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }
        if ($this->arguments->hasArgument('search')) {
            $this->arguments->getArgument('search')
                ->getPropertyMappingConfiguration()
                ->allowProperties(
                    'searchWord',
                    'letter',
                    'category',
                    'subCategory'
                );
        }
    }

    protected function initializeView(ViewInterface $view): void
    {
        $view->assign('data', $this->configurationManager->getContentObject()->data);
        $view->assign('extConf', $this->extConf);
    }

    /**
     * Send email on new/update.
     */
    public function sendMail(string $subjectKey, Club $club): bool
    {
        $this->view->assign('club', $club);

        /** @var MailMessage $mail */
        $mail = $this->objectManager->get(MailMessage::class);
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'clubdirectory'));
        $mail->html($this->view->render());

        return $mail->send();
    }

    /**
     * Get titles for select box in address records form.
     *
     * @return array Array containing all allowed address titles
     */
    protected function getAddressTitles(): array
    {
        $values = GeneralUtility::trimExplode(',', 'organizationAddress, postAddress, clubAddress');
        $titles = [];
        foreach ($values as $value) {
            $title = new \stdClass();
            $title->value = $value;
            $title->label = LocalizationUtility::translate(
                'tx_clubdirectory_domain_model_address.title.' . $value,
                'clubdirectory'
            );
            $titles[] = $title;
        }

        return $titles;
    }

    /**
     * If no poi record was connected with address try to create one.
     *
     * @param Club $club
     */
    protected function addMapRecordIfPossible(Club $club): void
    {
        $geocodeService = GeneralUtility::makeInstance(GeoCodeService::class);
        $mapService = GeneralUtility::makeInstance(MapService::class);
        $poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);
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
                            )
                        ]
                    );
                    /** @var PoiCollection $poiCollection */
                    $poiCollection = $poiCollectionRepository->findByIdentifier($poiCollectionUid);
                    $address->setTxMaps2Uid($poiCollection);
                } else {
                    foreach ($geocodeService->getErrors() as $error) {
                        $this->addFlashMessage($error->getMessage(), $error->getTitle(), $error->getSeverity());
                        $this->errorAction();
                    }
                }
            }
        }
    }

    /**
     * Currently only "logo" and "images" are allowed properties.
     *
     * @param string $property
     * @param MvcPropertyMappingConfiguration $propertyMappingConfigurationForClub
     * @param mixed $converterOptionValue
     */
    protected function assignMediaTypeConverter(
        string $property,
        MvcPropertyMappingConfiguration $propertyMappingConfigurationForClub,
        $converterOptionValue
    ): void {
        if ($property === 'logo' || $property === 'images') {
            $className = UploadMultipleFilesConverter::class;
            $converterOptionName = 'IMAGES';
        } else {
            return;
        }

        /** @var TypeConverterInterface $typeConverter */
        $typeConverter = $this->objectManager->get($className);
        $propertyMappingConfigurationForMediaFiles = $propertyMappingConfigurationForClub
            ->forProperty($property)
            ->setTypeConverter($typeConverter);

        $propertyMappingConfigurationForMediaFiles->setTypeConverterOption(
            $className,
            'settings',
            $this->settings
        );

        if (!empty($converterOptionValue)) {
            // Do not use setTypeConverterOptions() as this will remove all existing options
            $propertyMappingConfigurationForMediaFiles->setTypeConverterOption(
                $className,
                $converterOptionName,
                $converterOptionValue
            );
        }
    }

    /**
     * Sometimes Extbase tries to map an empty value like 0 to UID 0.
     * As there is no record with UID 0 a Mapping Error occurs.
     * To prevent that, we remove these kind of properties out of request directly.
     *
     * @param string $property
     * @param array $requestArgument
     */
    protected function removeEmptyPropertyFromRequest(string $property, array &$requestArgument): void
    {
        if (empty($requestArgument[$property])) {
            unset($requestArgument[$property]);
            $this->request->setArgument('club', $requestArgument);
        }
    }

    /**
     * As we have a Checkbox in Address Model, we have to fill
     * Club with the maximum of Address Models to prevent Rendering Errors.
     *
     * Never put that into Club Model as we don't want all these empty addresses in DB.
     * So this Method will only correct the rendering of frontend.
     *
     * @param Club $club
     */
    protected function fillAddressesUpToMaximum(Club $club): void
    {
        for ($i = \count($club->getAddresses()); $i < 3; ++$i) {
            $club->addAddress(GeneralUtility::makeInstance(Address::class));
        }
    }

    /**
     * Remove empty addresses from request before Property Mapping starts,
     * to prevent inserting empty addresses into DB
     *
     * @param array $requestArgument
     */
    protected function removeEmptyAddressesFromRequest(array &$requestArgument): void
    {
        if (isset($requestArgument['addresses']) && is_array($requestArgument['addresses'])) {
            foreach ($requestArgument['addresses'] as $key => $address) {
                // Only remove addresses which were not persisted before.
                // We will remove persisted addresses later on in editAction()
                if (
                    !isset($address['__identity'])
                    && empty($address['street'])
                    && empty($address['house_number'])
                    && empty($address['zip'])
                    && empty($address['city'])
                ) {
                    unset($requestArgument['addresses'][$key]);
                }
            }
        }

        $this->request->setArgument('club', $requestArgument);
    }
    /**
     * Files will be uploaded in typeConverter.
     * But, if an error occurs we have to remove these files.
     *
     * @param string $argument
     */
    protected function deleteUploadedFilesOnValidationErrors(string $argument): void
    {
        if ($this->getControllerContext()->getRequest()->hasArgument($argument)) {
            $club = $this->getControllerContext()->getRequest()->getArgument($argument);
            if ($club instanceof Club) {
                $images = $club->getImages();
                if (!empty($images)) {
                    foreach ($images as $image) {
                        $image->getOriginalResource()->getOriginalFile()->delete();
                    }
                }
            }
        }
    }

    protected function postProcessAndAssignFluidVariables(array $variables = []): void
    {
        /** @var PostProcessFluidVariablesEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PostProcessFluidVariablesEvent(
                $this->request,
                $this->settings,
                $variables
            )
        );

        $this->view->assignMultiple($event->getFluidVariables());
    }

    protected function postProcessControllerAction(?Club $club): void
    {
        $this->eventDispatcher->dispatch(
            new PostProcessControllerActionEvent(
                $this,
                $club,
                $this->settings
            )
        );
    }

    protected function preProcessControllerAction(): void
    {
        $this->eventDispatcher->dispatch(
            new PreProcessControllerActionEvent(
                $this->request,
                $this->arguments,
                $this->settings
            )
        );
    }
}
