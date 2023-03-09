<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Controller\Traits\AddressTrait;
use JWeiland\Clubdirectory\Controller\Traits\ControllerInjectionTrait;
use JWeiland\Clubdirectory\Controller\Traits\InitializeControllerTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Model\Search;
use JWeiland\Clubdirectory\Domain\Repository\DistrictRepository;
use JWeiland\Clubdirectory\Event\InitializeControllerActionEvent;
use JWeiland\Clubdirectory\Event\PostProcessFluidVariablesEvent;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to list, show and edit clubs
 */
class ClubController extends ActionController
{
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use AddressTrait;

    /**
     * @var DistrictRepository
     */
    protected $districtRepository;

    /**
     * @var PathSegmentHelper
     */
    protected $pathSegmentHelper;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    public function injectDistrictRepository(DistrictRepository $districtRepository): void
    {
        $this->districtRepository = $districtRepository;
    }

    public function injectPathSegmentHelper(PathSegmentHelper $pathSegmentHelper): void
    {
        $this->pathSegmentHelper = $pathSegmentHelper;
    }

    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function listAction(string $letter = ''): void
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findFilteredBy(
                (int)$this->settings['category'],
                (int)$this->settings['district'],
                $letter
            ),
            'categories' => $this->categoryRepository->getCategories(),
            'districts' => $this->districtRepository->findAll(),
            'search' => GeneralUtility::makeInstance(Search::class),
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);
    }

    public function listMyClubsAction(): void
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findByFeUser($this->frontendUserRepository->getCurrentFrontendUserUid()),
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);
    }

    /**
     * We are using int to prevent calling any Validator
     */
    public function showAction(int $club): void
    {
        $this->postProcessAndAssignFluidVariables([
            'club' => $this->clubRepository->findByIdentifier($club),
        ]);
    }

    public function newAction(): void
    {
        $this->preProcessControllerAction();

        $club = GeneralUtility::makeInstance(Club::class);
        $this->fillAddressesUpToMaximum($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'categories' => $this->categoryRepository->findByParent($this->extConf->getRootCategory()),
            'addressTitles' => $this->getAddressTitles(),
        ]);
    }

    public function initializeCreateAction(): void
    {
        $this->emitInitializeControllerAction();
    }

    /**
     * @Extbase\Validate(param="club", validator="JWeiland\Clubdirectory\Domain\Validator\ClubValidator")
     */
    public function createAction(Club $club): void
    {
        if ($this->frontendUserRepository->getCurrentFrontendUserRecord() !== []) {
            $feUser = $this->frontendUserRepository->findByUid(
                $this->frontendUserRepository->getCurrentFrontendUserUid()
            );
            $club->addFeUser($feUser);
            $club->setHidden(true);
            if ($this->mapHelper->addMapRecordIfPossible($club, $this) === false) {
                $this->errorAction();
            }
            $this->clubRepository->add($club);

            $this->pathSegmentHelper->setGeneratorFields([
                'title',
                'uid',
            ]);
            $this->pathSegmentHelper->updatePathSegmentForClub($club);

            $this->clubRepository->update($club);
            $this->persistenceManager->persistAll();

            $this->redirect('new', 'Map', 'clubdirectory', ['club' => $club]);
        } else {
            $this->addFlashMessage('There is no valid user logged in. So record was not saved');
            $this->redirect('list');
        }
    }

    public function initializeEditAction(): void
    {
        $this->emitInitializeControllerAction();
    }

    /**
     * We are using int to prevent calling any Validator
     *
     * @Extbase\IgnoreValidation("club")
     */
    public function editAction(Club $club): void
    {
        $this->fillAddressesUpToMaximum($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'categories' => $this->categoryRepository->findByParent($this->extConf->getRootCategory()),
            'addressTitles' => $this->getAddressTitles(),
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

        $this->emitInitializeControllerAction();
    }

    /**
     * @Extbase\Validate(param="club", validator="JWeiland\Clubdirectory\Domain\Validator\ClubValidator")
     */
    public function updateAction(Club $club): void
    {
        if ($this->mapHelper->addMapRecordIfPossible($club, $this) === false) {
            $this->errorAction();
        }
        $this->clubRepository->update($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.update', 'clubdirectory')
        );

        $club->setHidden(true);
        $this->addFlashMessage(LocalizationUtility::translate('clubUpdated', 'clubdirectory'));

        $this->redirect('listMyClubs');
    }

    public function initializeSearchAction(): void
    {
        $this->emitInitializeControllerAction();
    }

    public function searchAction(Search $search): void
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findBySearch($search),
            'categories' => $this->categoryRepository->getCategories(),
            'districts' => $this->districtRepository->findAll(),
            'search' => $search,
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);
    }

    public function initializeActivateAction(): void
    {
        $this->emitInitializeControllerAction();
    }

    public function activateAction(Club $club): void
    {
        $club->setHidden(false);
        $this->clubRepository->update($club);
        $this->addFlashMessage(LocalizationUtility::translate('clubActivated', 'clubdirectory'));
        $this->redirect('list', 'Club');
    }

    /**
     * Sometimes Extbase tries to map an empty value like 0 to UID 0.
     * As there is no record with UID 0 a Mapping Error occurs.
     * To prevent that, we remove these kind of properties out of request directly.
     */
    protected function removeEmptyPropertyFromRequest(string $property, array &$requestArgument): void
    {
        if (empty($requestArgument[$property])) {
            unset($requestArgument[$property]);
            $this->request->setArgument('club', $requestArgument);
        }
    }

    protected function emitInitializeControllerAction(): void
    {
        $this->eventDispatcher->dispatch(
            new InitializeControllerActionEvent(
                $this->request,
                $this->arguments,
                $this->settings
            )
        );
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

    protected function preProcessControllerAction(?Club $club = null): void
    {
        $this->eventDispatcher->dispatch(
            new PreProcessControllerActionEvent(
                $this,
                $club,
                $this->settings
            )
        );
    }
}
