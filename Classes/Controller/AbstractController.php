<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\Controller;

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

use JWeiland\Clubdirectory\Configuration\ExtConf;
use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Repository\CategoryRepository;
use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter;
use JWeiland\Clubdirectory\Property\TypeConverter\UploadOneFileConverter;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
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
     * @var Session
     */
    protected $session;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var string
     */
    protected $letters = '0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';

    /**
     * @param ClubRepository $clubRepository
     */
    public function injectClubRepository(ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    /**
     * @param FrontendUserRepository $feUserRepository
     */
    public function injectFeUserRepository(FrontendUserRepository $feUserRepository)
    {
        $this->feUserRepository = $feUserRepository;
    }

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Session $session
     */
    public function injectSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param ExtConf $extConf
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * Pre processing of all actions.
     */
    public function initializeAction()
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

    /**
     * Initializes the view before invoking an action method.
     *
     * Override this method to solve assign variables common for all actions
     * or prepare the view in another way before the action is called.
     *
     * @param ViewInterface $view The view to be initialized
     */
    protected function initializeView(ViewInterface $view)
    {
        $this->view->assign('data', $this->configurationManager->getContentObject()->data);
        $this->view->assign('extConf', $this->extConf);
    }

    /**
     * Send email on new/update.
     *
     * @param string $subjectKey
     * @param Club $club
     * @return int The amount of email receivers
     */
    public function sendMail(string $subjectKey, Club $club): int
    {
        $this->view->assign('club', $club);
        /** @var MailMessage $mail */
        $mail = $this->objectManager->get(MailMessage::class);
        $mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $mail->setSubject(LocalizationUtility::translate('email.subject.' . $subjectKey, 'clubdirectory'));
        $mail->setBody($this->view->render(), 'text/html');

        return $mail->send();
    }

    /**
     * Get an array with letters as keys for the glossar.
     *
     * @return array Array with starting letters as keys
     */
    protected function getGlossar(): array
    {
        $glossar = [];
        $availableLetters = $this->clubRepository->getStartingLetters(
            (int)$this->settings['category'],
            (int)$this->settings['district']
        );
        $possibleLetters = GeneralUtility::trimExplode(',', $this->letters);
        // add all letters which we have found in DB
        foreach ($availableLetters as $availableLetter) {
            if (MathUtility::canBeInterpretedAsInteger($availableLetter['letter'])) {
                $availableLetter['letter'] = '0-9';
            }
            // add only letters which are valid (do not add "§$%")
            if (\in_array($availableLetter['letter'], $possibleLetters, true)) {
                $glossar[$availableLetter['letter']] = true;
            }
        }
        // add all valid letters which are not set/found by previous foreach
        foreach ($possibleLetters as $possibleLetter) {
            if (!\array_key_exists($possibleLetter, $glossar)) {
                $glossar[$possibleLetter] = false;
            }
        }
        \ksort($glossar, \SORT_STRING);

        return $glossar;
    }

    /**
     * This is a workaround to help controller actions to find (hidden) posts.
     *
     * @param string $argumentName
     */
    protected function registerClubFromRequest(string $argumentName)
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
    protected function addMapRecordIfPossible(Club $club)
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
        $converterOptionValue = null
    ) {
        if ($property === 'logo') {
            $className = UploadOneFileConverter::class;
            $converterOptionName = 'IMAGE';
        } elseif ($property === 'images') {
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
    protected function removeEmptyPropertyFromRequest(string $property, array &$requestArgument)
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
    protected function fillAddressesUpToMaximum(Club $club)
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
    protected function removeEmptyAddressesFromRequest(array &$requestArgument)
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
    protected function deleteUploadedFilesOnValidationErrors(string $argument)
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
