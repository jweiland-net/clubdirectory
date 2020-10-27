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
use JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
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
    protected $feUserRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var GlossaryService
     */
    protected $glossaryService;

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
        $this->feUserRepository = $feUserRepository;
    }

    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function injectGlossaryService(GlossaryService $glossaryService): void
    {
        $this->glossaryService = $glossaryService;
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

    protected function addGlossarToView()
    {
        $this->view->assign(
            'glossar',
            $this->glossaryService->buildGlossary(
                $this->clubRepository->getQueryBuilderToFindAllEntries(
                    (int)$this->settings['category'],
                    (int)$this->settings['district']
                ),
                [
                    'settings' => $this->settings,
                    'extensionName' => 'Clubdirectory',
                    'pluginName' => 'Clubdirectory',
                    'controllerName' => 'Club',
                    'actionName' => 'list',
                ]
            )
        );
    }

    /**
     * Send email on new/update.
     *
     * @param string $subjectKey
     * @param Club $club
     * @return bool
     */
    public function sendMail(string $subjectKey, Club $club): bool
    {
        $this->view->assign('club', $club);
        /** @var MailMessage $mail */
        $mail = $this->objectManager->get(MailMessage::class);
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'clubdirectory'));
        if (version_compare(TYPO3_branch, '10.0', '>=')) {
            $mail->html($this->view->render());
        } else {
            $mail->setBody($this->view->render(), 'text/html');
        }

        return (bool)$mail->send();
    }

    /**
     * This is a workaround to help controller actions to find (hidden) posts.
     *
     * @param string $argumentName
     */
    protected function registerClubFromRequest(string $argumentName): void
    {
        $argument = $this->request->getArgument($argumentName);
        if (\is_array($argument)) {
            // get club from form ($_POST)
            $club = $this->clubRepository->findHiddenEntryByUid((int)$argument['__identity']);
        } elseif (\is_object($argument)) {
            // get club from domain model
            $club = $argument;
        } else {
            // get club from UID
            $club = $this->clubRepository->findHiddenEntryByUid((int)$argument);
        }
        $this->session->registerObject($club, $club->getUid());
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
     * files will be uploaded in typeConverter automatically
     * But, if an error occurs we have to remove them.
     *
     * @param string $argument
     */
    protected function deleteUploadedFilesOnValidationErrors(string $argument): void
    {
        if ($this->getControllerContext()->getRequest()->hasArgument($argument)) {
            /** @var Club $club */
            $club = $this->getControllerContext()->getRequest()->getArgument($argument);
            // in case of realurl $argument can be set, but is empty
            if ($club instanceof Club) {
                $images = $club->getImages();
                if (\count($images)) {
                    /** @var FileReference $image */
                    foreach ($images as $image) {
                        $image->getOriginalResource()->getOriginalFile()->delete();
                    }
                }
            }
        }
    }
}
