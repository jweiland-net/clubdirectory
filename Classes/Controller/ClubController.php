<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and edit clubs
 */
class ClubController extends AbstractController
{
    public function listAction(): void
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

    public function listMyClubsAction(): void
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
    public function showAction(int $club): void
    {
        $clubObject = $this->clubRepository->findByIdentifier($club);
        $this->view->assign('club', $clubObject);
        $this->view->assign('fallbackIconPath', $this->extConf->getFallbackIconPath());
    }

    public function newAction(): void
    {
        $this->deleteUploadedFilesOnValidationErrors('club');
        $club = $this->objectManager->get(Club::class);
        $this->fillAddressesUpToMaximum($club);

        $this->view->assign('club', $club);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    public function initializeCreateAction(): void
    {
        $clubMappingConfiguration = $this->arguments
            ->getArgument('club')
            ->getPropertyMappingConfiguration();

        $this->assignMediaTypeConverter('logo', $clubMappingConfiguration);
        $this->assignMediaTypeConverter('images', $clubMappingConfiguration);
    }

    /**
     * @param Club $club
     * @Extbase\Validate(param="club", validator="JWeiland\Clubdirectory\Domain\Validator\ClubValidator")
     */
    public function createAction(Club $club): void
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

    public function initializeEditAction(): void
    {
        $this->registerClubFromRequest('club');
    }

    /**
     * We are using int to prevent calling any Validator
     *
     * @param int $club
     */
    public function editAction(int $club): void
    {
        /** @var Club $clubObject */
        $clubObject = $this->clubRepository->findByIdentifier($club);
        $this->fillAddressesUpToMaximum($clubObject);
        $this->view->assign('club', $clubObject);
        $this->view->assign('categories', $this->categoryRepository->findByParent($this->extConf->getRootCategory()));
        $this->view->assign('addressTitles', $this->getAddressTitles());
    }

    public function initializeUpdateAction(): void
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

        // Needed to get the previously stored logo and images
        /** @var Club $persistedClub */
        $persistedClub = $this->clubRepository->findByIdentifier($requestArgument['__identity']);
        $this->assignMediaTypeConverter('logo', $clubMappingConfiguration, $persistedClub->getLogo());
        $this->assignMediaTypeConverter('images', $clubMappingConfiguration, $persistedClub->getOriginalImages());
    }

    /**
     * @param Club $club
     * @Extbase\Validate(param="club", validator="JWeiland\Clubdirectory\Domain\Validator\ClubValidator")
     */
    public function updateAction(Club $club): void
    {
        $this->addMapRecordIfPossible($club);
        $this->clubRepository->update($club);
        $this->sendMail('update', $club);
        $club->setHidden(true);
        $this->addFlashMessage(LocalizationUtility::translate('clubUpdated', 'clubdirectory'));
        $this->redirect('listMyClubs');
    }

    /**
     * @param Search|null $search
     */
    public function searchAction(?Search $search): void
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

    /**
     * @param int $club
     */
    public function activateAction(int $club): void
    {
        /** @var Club $clubObject */
        $clubObject = $this->clubRepository->findHiddenEntryByUid($club);
        $clubObject->setHidden(false);
        $this->clubRepository->update($clubObject);
        $this->addFlashMessage(LocalizationUtility::translate('clubActivated', 'clubdirectory'));
        $this->redirect('list', 'Club');
    }
}
