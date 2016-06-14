<?php

namespace JWeiland\Clubdirectory\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AbstractController extends ActionController
{
    /**
     * clubRepository.
     *
     * @var \JWeiland\Clubdirectory\Domain\Repository\ClubRepository
     * @inject
     */
    protected $clubRepository;

    /**
     * feUserRepository.
     *
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $feUserRepository;

    /**
     * categoryRepository.
     *
     * @var \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoryRepository;

    /**
     * persistenceManager.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Session
     * @inject
     */
    protected $session;

    /**
     * GeocodeUtility
     *
     * @var \JWeiland\Maps2\Utility\GeocodeUtility
     * @inject
     */
    protected $geocodeUtility;

    /**
     * @var \TYPO3\CMS\Core\Mail\MailMessage
     * @inject
     */
    protected $mail;

    /**
     * extConf.
     *
     * @var \JWeiland\Clubdirectory\Configuration\ExtConf
     * @inject
     */
    protected $extConf;

    protected $letters = '0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';

    /**
     * preprocessing of all actions.
     */
    public function initializeAction()
    {
        // if this value was not set, then it will be filled with 0
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }
    }

    /**
     * get an array with letters as keys for the glossar.
     *
     * @return array Array with starting letters as keys
     */
    protected function getGlossar()
    {
        $glossar = array();
        $availableLetters = $this->clubRepository->getStartingLetters();
        $possibleLetters = GeneralUtility::trimExplode(',', $this->letters);

        // add all letters which we have found in DB
        foreach ($availableLetters as $availableLetter) {
            if (MathUtility::canBeInterpretedAsInteger($availableLetter['letter'])) {
                $availableLetter['letter'] = '0-9';
            }
            // add only letters which are valid (do not add "§$%")
            if (array_search($availableLetter['letter'], $possibleLetters) !== false) {
                $glossar[$availableLetter['letter']] = true;
            }
        }

        // add all valid letters which are not set/found by previous foreach
        foreach ($possibleLetters as $possibleLetter) {
            if (!array_key_exists($possibleLetter, $glossar)) {
                $glossar[$possibleLetter] = false;
            }
        }

        ksort($glossar, SORT_STRING);

        return $glossar;
    }

    /**
     * This is a workaround to help controller actions to find (hidden) posts.
     *
     * @param $argumentName
     */
    protected function registerClubFromRequest($argumentName)
    {
        $argument = $this->request->getArgument($argumentName);
        if (is_array($argument)) {
            // get club from form ($_POST)
            $club = $this->clubRepository->findHiddenEntryByUid($argument['__identity']);
        } elseif (is_object($argument)) {
            // get club from domain model
            $club = $argument;
        } else {
            // get club from UID
            $club = $this->clubRepository->findHiddenEntryByUid($argument);
        }
        $this->session->registerObject($club, $club->getUid());
    }

    /**
     * get titles for select box in address records form.
     *
     * @return array Array containing all allowed address titles
     */
    protected function getAddressTitles()
    {
        $values = GeneralUtility::trimExplode(',', 'organizationAddress, postAddress, clubAddress');
        $titles = array();
        foreach ($values as $value) {
            $title = new \stdClass();
            $title->value = $value;
            $title->label = LocalizationUtility::translate(
                'tx_clubdirectory_domain_model_address.title.'.$value,
                'clubdirectory'
            );
            $titles[] = $title;
        }

        return $titles;
    }

    /**
     * If no poi record was connected with address try to create one.
     *
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     */
    protected function addMapRecordIfPossible(\JWeiland\Clubdirectory\Domain\Model\Club $club)
    {
        /** @var \JWeiland\Clubdirectory\Domain\Model\Address $address */
        foreach ($club->getAddresses() as $address) {
            // add a new poi record if not set allready
            if ($address->getTxMaps2Uid() === null) {
                $results = $this->geocodeUtility->findPositionByAddress($address->getAddress());
                if (count($results)) {
                    $results->rewind();
                    /** @var \JWeiland\Maps2\Domain\Model\RadiusResult $result */
                    $result = $results->current();
                    /** @var \JWeiland\Maps2\Domain\Model\PoiCollection $poi */
                    $poi = $this->objectManager->get('JWeiland\\Maps2\\Domain\\Model\\PoiCollection');
                    $poi->setCollectionType('Point');
                    $poi->setTitle($result->getFormattedAddress());
                    $poi->setAddress($result->getFormattedAddress());
                    $poi->setLatitude($result->getGeometry()->getLocation()->getLatitude());
                    $poi->setLongitude($result->getGeometry()->getLocation()->getLongitude());
                    $poi->setLatitudeOrig($result->getGeometry()->getLocation()->getLatitude());
                    $poi->setLongitudeOrig($result->getGeometry()->getLocation()->getLongitude());
                    $address->setTxMaps2Uid($poi);
                }
            }
        }
    }

    /**
     * send email on new/update.
     *
     * @param string                                    $subjectKey
     * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
     *
     * @return int The amound of email receivers
     */
    public function sendMail($subjectKey, \JWeiland\Clubdirectory\Domain\Model\Club $club)
    {
        $this->view->assign('club', $club);

        $this->mail->setFrom($this->extConf->getEmailFromAddress(), $this->extConf->getEmailFromName());
        $this->mail->setTo($this->extConf->getEmailToAddress(), $this->extConf->getEmailToName());
        $this->mail->setSubject(LocalizationUtility::translate('email.subject.'.$subjectKey, 'clubdirectory'));
        $this->mail->setBody($this->view->render(), 'text/html');

        return $this->mail->send();
    }

    /**
     * A special action which is called if the originally intended action could
     * not be called, for example if the arguments were not valid.
     *
     * The default implementation sets a flash message, request errors and forwards back
     * to the originating action. This is suitable for most actions dealing with form input.
     *
     * We clear the page cache by default on an error as well, as we need to make sure the
     * data is re-evaluated when the user changes something.
     *
     * @return string
     *
     * @api
     */
    protected function errorAction()
    {
        $this->clearCacheOnError();
        /* @var \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument */
        $preparedArguments = array();
        foreach ($this->arguments as $argument) {
            $preparedArguments[$argument->getName()] = $argument->getValue();
        }
        $errorFlashMessage = $this->getErrorFlashMessage();
        if ($errorFlashMessage !== false) {
            $errorFlashMessageObject = new \TYPO3\CMS\Core\Messaging\FlashMessage(
                $errorFlashMessage,
                '',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            $this->controllerContext->getFlashMessageQueue()->enqueue($errorFlashMessageObject);
        }
        $referringRequest = $this->request->getReferringRequest();
        if ($referringRequest !== null) {
            $originalRequest = clone $this->request;
            $this->request->setOriginalRequest($originalRequest);
            $this->request->setOriginalRequestMappingResults($this->arguments->getValidationResults());
            $this->forward(
                $referringRequest->getControllerActionName(),
                $referringRequest->getControllerName(),
                $referringRequest->getControllerExtensionName(),
                $preparedArguments
            );
        }
        $message = 'An error occurred while trying to call ' .
            get_class($this) . '->' . $this->actionMethodName . '().' . PHP_EOL;

        return $message;
    }

    /**
     * files will be uploaded in typeConverter automatically
     * But, if an error occurs we have to remove them.
     *
     * @param string $argument
     */
    protected function deleteUploadedFilesOnValidationErrors($argument)
    {
        if ($this->getControllerContext()->getRequest()->hasArgument($argument)) {
            /** @var \JWeiland\Clubdirectory\Domain\Model\Club $club */
            $club = $this->getControllerContext()->getRequest()->getArgument($argument);
            // in case of realurl $argument can be set, but is empty
            if ($club instanceof \JWeiland\Clubdirectory\Domain\Model\Club) {
                $images = $club->getImages();
                if (count($images)) {
                    /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $image */
                    foreach ($images as $image) {
                        $image->getOriginalResource()->getOriginalFile()->delete();
                    }
                }
            }
        }
    }
}
