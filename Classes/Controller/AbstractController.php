<?php
declare(strict_types=1);
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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Repository\PoiCollectionRepository;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
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
     * @var PersistenceManager
     */
    protected $persistenceManager;

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
     * inject clubRepository
     *
     * @param ClubRepository $clubRepository
     */
    public function injectClubRepository(ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    /**
     * inject frontendUserRepository
     *
     * @param FrontendUserRepository $feUserRepository
     */
    public function injectFeUserRepository(FrontendUserRepository $feUserRepository)
    {
        $this->feUserRepository = $feUserRepository;
    }

    /**
     * inject categoryRepository
     *
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * inject persistenceManager
     *
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * inject session
     *
     * @param Session $session
     */
    public function injectSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * inject extConf
     *
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
     * send email on new/update.
     *
     * @param string $subjectKey
     * @param Club $club
     * @return int The amount of email receivers
     */
    public function sendMail($subjectKey, Club $club)
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
     * get an array with letters as keys for the glossar.
     *
     * @return array Array with starting letters as keys
     */
    protected function getGlossar()
    {
        $glossar = [];
        if ($this->settings['category']) {
            $availableLetters = $this->clubRepository->getStartingLetters((int)$this->settings['category']);
        } else {
            $availableLetters = $this->clubRepository->getStartingLetters();
        }

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
     * @param $argumentName
     */
    protected function registerClubFromRequest($argumentName)
    {
        $argument = $this->request->getArgument($argumentName);
        if (\is_array($argument)) {
            // get club from form ($_POST)
            $club = $this->clubRepository->findHiddenEntryByUid($argument['__identity']);
        } elseif (\is_object($argument)) {
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
        $mapService = GeneralUtility::makeInstance(MapService::class);
        $poiCollectionRepository = $this->objectManager->get(PoiCollectionRepository::class);
        foreach ($club->getAddresses() as $address) {
            // add a new poi record if empty
            if ($address->getTxMaps2Uid() === null && $address->getZip() && $address->getCity()) {
                $position = $mapService->getFirstFoundPositionByAddress($address->getAddress());
                if ($position instanceof Position) {
                    $poiCollectionUid = $mapService->createNewPoiCollection(
                        $this->extConf->getPoiCollectionPid(),
                        $position,
                        [
                            'title' => $position->getFormattedAddress()
                        ]
                    );
                    /** @var PoiCollection $poiCollection */
                    $poiCollection = $poiCollectionRepository->findByIdentifier($poiCollectionUid);
                    $address->setTxMaps2Uid($poiCollection);
                }
            }
        }
    }

    /**
     * A special action which is called if the originally intended action could
     * not be called, for example if the arguments were not valid.
     * The default implementation sets a flash message, request errors and forwards back
     * to the originating action. This is suitable for most actions dealing with form input.
     * We clear the page cache by default on an error as well, as we need to make sure the
     * data is re-evaluated when the user changes something.
     *
     * @return string
     * @api
     */
    protected function errorAction()
    {
        $this->clearCacheOnError();
        /* @var Argument $argument */
        $preparedArguments = [];
        foreach ($this->arguments as $argument) {
            $preparedArguments[$argument->getName()] = $argument->getValue();
        }
        $errorFlashMessage = $this->getErrorFlashMessage();
        if ($errorFlashMessage !== false) {
            $errorFlashMessageObject = new FlashMessage(
                $errorFlashMessage,
                '',
                FlashMessage::ERROR
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
        $message = 'An error occurred while trying to call ' . \get_class(
            $this
        ) . '->' . $this->actionMethodName . '().' . \PHP_EOL;

        return $message;
    }

    /**
     * files will be uploaded in typeConverter automatically
     * But, if an error occurs we have to remove them.
     *
     * @param string $argument
     * @return void
     */
    protected function deleteUploadedFilesOnValidationErrors($argument)
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
