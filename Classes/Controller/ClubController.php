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

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and edit clubs
 */
class ClubController extends AbstractController
{
    public function listAction()
    {
        $this->view->assign(
            'clubs',
            $this->clubRepository->findByCategory((int)$this->settings['category'], (int)$this->settings['district'])
        );
        $this->view->assign('categories', $this->categoryRepository->getSubCategories());
        $this->view->assign('search', $this->objectManager->get(Search::class));
        $this->view->assign('glossar', $this->getGlossar());
        $this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
        $this->view->assign('fallbackIconPath', $this->extConf->getFallbackIconPath());
    }

    public function listMyClubsAction()
    {
        $clubs = $this->clubRepository->findByFeUser((int) $GLOBALS['TSFE']->fe_user->user['uid']);
        $this->view->assign('clubs', $clubs);
        $this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
        $this->view->assign('fallbackIconPath', $this->extConf->getFallbackIconPath());
    }

    /**
     * We are using int to prevent calling any Validator
     *
     * @param int $club
     */
    public function showAction(int $club)
    {
        $clubObject = $this->clubRepository->findByIdentifier($club);
        $this->view->assign('club', $clubObject);
        $this->view->assign('fallbackIconPath', $this->extConf->getFallbackIconPath());
    }

    public function newAction()
    {
        $this->deleteUploadedFilesOnValidationErrors('club');
        $club = $this->objectManager->get(Club::class);
        $this->fillAddressesUpToMaximum($club);

        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    public function initializeCreateAction()
    {
        $clubMappingConfiguration = $this->arguments
            ->getArgument('club')
            ->getPropertyMappingConfiguration();

        $this->assignMediaTypeConverter('logo', $clubMappingConfiguration);
        $this->assignMediaTypeConverter('images', $clubMappingConfiguration);
    }

    /**
     * @param Club $club
     */
    public function createAction(Club $club)
    {
        if ($GLOBALS['TSFE']->fe_user->user['uid']) {
            /** @var FrontendUser $feUser */
            $feUser = $this->feUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
            $club->addFeUser($feUser);
            $club->setHidden(true);
            $this->addMapRecordIfPossible($club);
            $this->clubRepository->add($club);
            $persistenceManager = $this->objectManager->get(PersistenceManagerInterface::class);
            $persistenceManager->persistAll();

            $this->redirect('new', 'Map', 'clubdirectory', ['club' => $club]);
        } else {
            $this->addFlashMessage('There is no valid user logged in. So record was not saved');
            $this->redirect('list');
        }
    }

    public function initializeEditAction()
    {
        $this->registerClubFromRequest('club');
    }

    /**
     * We are using int to prevent calling any Validator
     *
     * @param int $club
     */
    public function editAction(int $club)
    {
        /** @var Club $clubObject */
        $clubObject = $this->clubRepository->findByIdentifier($club);
        $this->fillAddressesUpToMaximum($clubObject);
        $this->view->assign('club', $clubObject);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    public function initializeUpdateAction()
    {
        if (!$this->request->hasArgument('club')) {
            return;
        }
        $requestArgument = $this->request->getArgument('club');
        $this->removeEmptyPropertyFromRequest('categories', $requestArgument);
        $this->removeEmptyAddressesFromRequest($requestArgument);

        $clubMappingConfiguration = $this->arguments
            ->getArgument('club')
            ->getPropertyMappingConfiguration();

        /** @var Club $persistedClub */
        // Needed to get the previously stored logo and images
        $persistedClub = $this->clubRepository->findByIdentifier($requestArgument['__identity']);
        $this->assignMediaTypeConverter('logo', $clubMappingConfiguration, $persistedClub->getLogo());
        $this->assignMediaTypeConverter('images', $clubMappingConfiguration, $persistedClub->getOriginalImages());

        // we can't work with addresses.* here,
        // because f:form has created addresses.0-3 already, and numbered paths have a higher priority
        /*for ($i = 0; $i < 3; ++$i) {
            $clubMappingConfiguration
                ->forProperty('addresses.' . $i)
                ->allowProperties('txMaps2Uid')
                ->forProperty('txMaps2Uid')
                ->allowProperties('latitude', 'longitude', '__identity');
            $clubMappingConfiguration
                ->allowModificationForSubProperty('addresses.' . $i . '.txMaps2Uid');
        }*/
    }

    /**
     * @param Club $club
     */
    public function updateAction(Club $club)
    {
        $this->addMapRecordIfPossible($club);
        $this->clubRepository->update($club);
        $this->sendMail('update', $club);
        $club->setHidden(true);
        $this->addFlashMessage(LocalizationUtility::translate('clubUpdated', 'clubdirectory'));
        $this->redirect('listMyClubs');
    }

    /**
     * @param Search $search
     */
    public function searchAction(Search $search = null)
    {
        if ($search instanceof Search) {
            $clubs = $this->clubRepository->findBySearch($search);
            if ($search->getCategory()) {
                $this->view->assign('subCategories', $this->categoryRepository->getSubCategories($search->getCategory()));
            }
        } else {
            $clubs = $this->clubRepository->findAll();
        }
        $this->view->assign('clubs', $clubs);
        $this->view->assign('categories', $this->categoryRepository->getSubCategories());
        $this->view->assign('search', $search);
        $this->view->assign('glossar', $this->getGlossar());
        $this->view->assign('allowedUserGroup', $this->extConf->getUserGroup());
        $this->view->assign('fallbackIconPath', $this->extConf->getFallbackIconPath());
    }
}
