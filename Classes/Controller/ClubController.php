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
use JWeiland\Clubdirectory\Helper\HiddenObjectHelper;
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and edit clubs
 */
class ClubController extends AbstractController
{
    /**
     * @param string $letter
     */
    public function listAction(string $letter = ''): void
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findFilteredBy(
                (int)$this->settings['category'],
                (int)$this->settings['district'],
                $letter
            ),
            'categories' => $this->categoryRepository->getSubCategories(),
            'search' => $this->objectManager->get(Search::class),
            'allowedUserGroup' => $this->extConf->getUserGroup()
        ]);
    }

    public function listMyClubsAction(): void
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findByFeUser($this->frontendUserRepository->getCurrentFrontendUserUid()),
            'allowedUserGroup' => $this->extConf->getUserGroup()
        ]);
    }

    /**
     * We are using int to prevent calling any Validator
     *
     * @param int $club
     */
    public function showAction(int $club): void
    {
        $this->postProcessAndAssignFluidVariables([
            'club' => $this->clubRepository->findByIdentifier($club)
        ]);
    }

    public function newAction(): void
    {
        $this->deleteUploadedFilesOnValidationErrors('club');
        $club = $this->objectManager->get(Club::class);
        $this->fillAddressesUpToMaximum($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'categories' => $this->categoryRepository->findByParent($this->extConf->getRootCategory()),
            'addressTitles' => $this->getAddressTitles()
        ]);
    }

    public function initializeCreateAction(): void
    {
        $clubMappingConfiguration = $this->arguments
            ->getArgument('club')
            ->getPropertyMappingConfiguration();

        $this->assignMediaTypeConverter('logo', $clubMappingConfiguration, null);
        $this->assignMediaTypeConverter('images', $clubMappingConfiguration, null);

        $this->preProcessControllerAction();
    }

    /**
     * @param Club $club
     * @Extbase\Validate(param="club", validator="JWeiland\Clubdirectory\Domain\Validator\ClubValidator")
     */
    public function createAction(Club $club): void
    {
        if ($this->frontendUserRepository->getCurrentFrontendUserRecord() !== []) {
            /** @var FrontendUser $feUser */
            $feUser = $this->frontendUserRepository->findByUid(
                $this->frontendUserRepository->getCurrentFrontendUserUid()
            );
            $club->addFeUser($feUser);
            $club->setHidden(true);
            $this->addMapRecordIfPossible($club);
            $this->clubRepository->add($club);

            $pathSegmentHelper = GeneralUtility::makeInstance(PathSegmentHelper::class);
            $pathSegmentHelper->setGeneratorFields([
                'title',
                'uid'
            ]);
            $pathSegmentHelper->updatePathSegmentForClub($club);
            $this->clubRepository->update($club);

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
        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->clubRepository,
            $this->request,
            'club'
        );

        $this->preProcessControllerAction();
    }

    /**
     * We are using int to prevent calling any Validator
     *
     * @param Club $club
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("club")
     */
    public function editAction(Club $club): void
    {
        $this->fillAddressesUpToMaximum($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'categories' => $this->categoryRepository->findByParent($this->extConf->getRootCategory()),
            'addressTitles' => $this->getAddressTitles()
        ]);
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
        $persistedClub = $this->clubRepository->findHiddenObject($requestArgument['__identity']);
        $this->assignMediaTypeConverter('logo', $clubMappingConfiguration, $persistedClub->getOriginalLogo());
        $this->assignMediaTypeConverter('images', $clubMappingConfiguration, $persistedClub->getOriginalImages());

        $this->preProcessControllerAction();
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

    public function initializeSearchAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * @param Search|null $search
     */
    public function searchAction(?Search $search = null): void
    {
        if ($search instanceof Search) {
            $clubs = $this->clubRepository->findBySearch($search);
            if ($search->getCategory()) {
                $this->view->assign('subCategories', $this->categoryRepository->getSubCategories($search->getCategory()));
            }
        } else {
            $clubs = $this->clubRepository->findAll();
        }

        $this->postProcessAndAssignFluidVariables([
            'clubs' => $clubs,
            'categories' => $this->categoryRepository->getSubCategories(),
            'search' => $search,
            'allowedUserGroup' => $this->extConf->getUserGroup()
        ]);
    }

    public function initializeActivateAction()
    {
        $hiddenObjectHelper = $this->objectManager->get(HiddenObjectHelper::class);
        $hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->clubRepository,
            $this->request,
            'club'
        );

        $this->preProcessControllerAction();
    }

    /**
     * @param Club $club
     */
    public function activateAction(Club $club): void
    {
        $club->setHidden(false);
        $this->clubRepository->update($club);
        $this->addFlashMessage(LocalizationUtility::translate('clubActivated', 'clubdirectory'));
        $this->redirect('list', 'Club');
    }
}
