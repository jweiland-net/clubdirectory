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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package clubdirectory
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClubController extends AbstractController {

	/**
	 * action list
	 *
	 * @param string $letter Show only records starting with this letter
	 * @validate $letter String, StringLength(minimum=0,maximum=3)
	 * @return void
	 */
	public function listAction($letter = NULL) {
		if ($letter === NULL) {
			if ($this->settings['category'] || $this->settings['district']) {
				$clubs = $this->clubRepository->findByCategory((int)$this->settings['category'], (int)$this->settings['district']);
			} else {
				$clubs = $this->clubRepository->findAll();
			}
		} else {
			$clubs = $this->clubRepository->findByStartingLetter($letter, (int)$this->settings['category'], (int)$this->settings['district']);
		}
		$this->view->assign('clubs', $clubs);
		$this->view->assign('glossar', $this->getGlossar());
		$this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
	}

	/**
	 * action listMyClubs
	 *
	 * @return void
	 */
	public function listMyClubsAction() {
		$clubs = $this->clubRepository->findByFeUser((int)$GLOBALS['TSFE']->fe_user->user['uid']);
		$this->view->assign('clubs', $clubs);
		$this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
	}

	/**
	 * action show
	 *
	 * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
	 * @return void
	 */
	public function showAction(\JWeiland\Clubdirectory\Domain\Model\Club $club) {
		$isValidUser = FALSE;
		if (is_array($GLOBALS['TSFE']->fe_user->user) && count($club->getFeUsers())) {
			/** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser */
			foreach ($club->getFeUsers() as $feUser) {
				if ($feUser->getUid() === (integer) $GLOBALS['TSFE']->fe_user->user['uid']) {
					$isValidUser = TRUE;
					break;
				}
			}
		}
		$this->view->assign('club', $club);
		$this->view->assign('isValidUser', $isValidUser);
	}

	/**
	 * action new
	 *
	 * @return void
	 */
	public function newAction() {
		$this->deleteUploadedFilesOnValidationErrors('club');
		/** @var \JWeiland\Clubdirectory\Domain\Model\Club $club */
		$club = $this->objectManager->get('JWeiland\\Clubdirectory\\Domain\\Model\\Club');
		for ($i = 0; $i < 3; $i++) {
			/** @var \JWeiland\Clubdirectory\Domain\Model\Address $address */
			$address = $this->objectManager->get('JWeiland\\Clubdirectory\\Domain\\Model\\Address');
			$club->addAddress($address);
		}
		$this->view->assign('club', $club);
		$this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
		$this->view->assign('addressTitles', $this->getAddressTitles());
	}

	/**
	 * initialized create action
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		/** @var \JWeiland\Clubdirectory\Property\TypeConverter\UploadOneFileConverter $oneFileTypeConverter */
		$oneFileTypeConverter = $this->objectManager->get('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter');
		$this->arguments->getArgument('club')->getPropertyMappingConfiguration()->forProperty('logo')->setTypeConverter($oneFileTypeConverter)->setTypeConverterOption('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter', 'TABLENAME', 'tx_clubdirectory_domain_model_club');
		/** @var \JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter $multipleFilesTypeConverter */
		$multipleFilesTypeConverter = $this->objectManager->get('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadMultipleFilesConverter');
		$this->arguments->getArgument('club')->getPropertyMappingConfiguration()->forProperty('images')->setTypeConverter($multipleFilesTypeConverter)->setTypeConverterOption('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadMultipleFilesConverter', 'TABLENAME', 'tx_clubdirectory_domain_model_club');
	}

	/**
	 * action create
	 *
	 * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
	 * @return void
	 */
	public function createAction(\JWeiland\Clubdirectory\Domain\Model\Club $club) {
		if ($GLOBALS['TSFE']->fe_user->user['uid']) {
			/** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser */
			$feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
			$club->addFeUser($feUser);
			$this->addMapRecordIfPossible($club);
			$club->setHidden(TRUE);
			$this->clubRepository->add($club);
			$this->persistenceManager->persistAll();
			//$this->flashMessageContainer->add(LocalizationUtility::translate('clubCreated', 'clubdirectory'));
			$this->redirect('new', 'Map', 'clubdirectory', array('club' => $club));
		} else {
			$this->flashMessageContainer->add('There is no valid user logged in. So record was not saved');
			$this->redirect('list');
		}
	}

	/**
	 * initialized edit action
	 *
	 * @return void
	 */
	public function initializeEditAction() {
		$this->registerClubFromRequest('club');
	}

	/**
	 * action edit
	 *
	 * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
	 * @return void
	 */
	public function editAction(\JWeiland\Clubdirectory\Domain\Model\Club $club) {
		// this is something very terrible of extbase
		// because of the checkboxes in address records we have to add all 3 addresses. Filled or not filled.
		$i = count($club->getAddresses());
		for ($i; $i < 3; $i++ ) {
			$club->getAddresses()->attach(new \JWeiland\Clubdirectory\Domain\Model\Address());
		}

		$this->view->assign('club', $club);
		$this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
		$this->view->assign('addressTitles', $this->getAddressTitles());
	}

	/**
	 * initialized update action
	 *
	 * @return void
	 */
	public function initializeUpdateAction() {
		$this->registerClubFromRequest('club');

		$argument = $this->request->getArgument('club');
		/** @var \JWeiland\Clubdirectory\Domain\Model\Club $club */
		$car = $this->clubRepository->findByIdentifier($argument['__identity']);
		/** @var \JWeiland\Clubdirectory\Property\TypeConverter\UploadOneFileConverter $oneFileTypeConverter */
		$oneFileTypeConverter = $this->objectManager->get('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter');
		$this->arguments->getArgument('club')
			->getPropertyMappingConfiguration()
			->forProperty('logo')
			->setTypeConverter($oneFileTypeConverter)
			->setTypeConverterOptions('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadOneFileConverter', array(
				'TABLENAME' => 'tx_clubdirectory_domain_model_club',
				'IMAGE' => $car->getLogo()
			));
		/** @var \JWeiland\Clubdirectory\Property\TypeConverter\UploadMultipleFilesConverter $multipleFilesTypeConverter */
		$multipleFilesTypeConverter = $this->objectManager->get('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadMultipleFilesConverter');
		$this->arguments->getArgument('club')
			->getPropertyMappingConfiguration()
			->forProperty('images')
			->setTypeConverter($multipleFilesTypeConverter)
			->setTypeConverterOptions('JWeiland\\Clubdirectory\\Property\\TypeConverter\\UploadMultipleFilesConverter', array(
				'TABLENAME' => 'tx_clubdirectory_domain_model_club',
				'IMAGES' => $car->getImages()
			));

		// we can't work with addresses.* here, because f:form has created addresses.0-3 already, and numbered paths have a higher priority
		for ($i = 0; $i < 3; $i++) {
			$this->arguments->getArgument('club')->getPropertyMappingConfiguration()
				->forProperty('addresses.' . $i)->allowProperties('txMaps2Uid')
				->forProperty('txMaps2Uid')->allowProperties('latitude', 'longitude', '__identity');
			$this->arguments->getArgument('club')->getPropertyMappingConfiguration()->allowModificationForSubProperty('addresses.' . $i . '.txMaps2Uid');
		}
	}

	/**
	 * action update
	 *
	 * @param \JWeiland\Clubdirectory\Domain\Model\Club $club
	 * @return void
	 */
	public function updateAction(\JWeiland\Clubdirectory\Domain\Model\Club $club) {
		$this->addMapRecordIfPossible($club);
		$this->clubRepository->update($club);
		$this->sendMail('update', $club);
		$club->setHidden(TRUE);
		$this->flashMessageContainer->add(LocalizationUtility::translate('clubUpdated', 'clubdirectory'));
		$this->redirect('list', NULL, NULL, array('club' => $club));
	}

	/**
	 * $search isn't a domainmodel, so we have to do htmlspecialchars on our own
	 *
	 * @return void
	 */
	public function initializeSearchAction() {
		if ($this->request->hasArgument('search')) {
			$search = htmlspecialchars($this->request->getArgument('search'));
			$this->request->setArgument('search', $search);
		}
	}

	/**
	 * search show
	 *
	 * @param string $search
	 * @return void
	 */
	public function searchAction($search) {
		$clubs = $this->clubRepository->searchClubs($search);
		$this->view->assign('search', $search);
		$this->view->assign('clubs', $clubs);
	}

}