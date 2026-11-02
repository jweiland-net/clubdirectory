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
use JWeiland\Clubdirectory\Controller\Traits\PostProcessControllerActionTrait;
use JWeiland\Clubdirectory\Controller\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Clubdirectory\Controller\Traits\PreProcessControllerActionTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Repository\DistrictRepository;
use JWeiland\Clubdirectory\Domain\Validator\ClubValidator;
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to let frontend users create, edit and manage their own clubs listings
 */
class ManagementController extends ActionController
{
    use AddressTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PostProcessFluidVariablesTrait;
    use PostProcessControllerActionTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected DistrictRepository $districtRepository,
        protected PathSegmentHelper $pathSegmentHelper,
        protected PersistenceManagerInterface $persistenceManager,
    ) {}

    public function listMyClubsAction(): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findByFeUser($this->frontendUserRepository->getCurrentFrontendUserUid()),
            'allowedUserGroup' => $this->extConf->getUserGroup(),
        ]);

        return $this->htmlResponse();
    }

    public function newAction(): ResponseInterface
    {
        $this->preProcessControllerAction();

        $club = GeneralUtility::makeInstance(Club::class);
        $this->fillAddressesUpToMaximum($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'categories' => $this->categoryRepository->findByParent($this->extConf->getRootCategory()),
            'addressTitles' => $this->getAddressTitles(),
        ]);

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        if ($this->request->hasArgument('club')) {
            $requestArgument = $this->request->getArgument('club');
            $this->removeEmptyAddressesFromRequest($requestArgument);
        }

        $this->preProcessControllerAction();
    }

    #[Extbase\Validate(['param' => 'club', 'validator' => ClubValidator::class])]
    public function createAction(Club $club): ResponseInterface
    {
        if ($this->frontendUserRepository->getCurrentFrontendUserRecord() !== []) {
            $feUser = $this->frontendUserRepository->findByUid(
                $this->frontendUserRepository->getCurrentFrontendUserUid(),
            );
            $club->addFeUser($feUser);
            $club->setHidden(true);

            $geocodingFailure = $this->mapHelper->addMapRecord($club);
            if ($geocodingFailure !== null) {
                return $this->forwardBackWithGeocodingError($geocodingFailure);
            }

            $this->persistNewClub($club);

            return $this->redirect('new', 'Map', 'clubdirectory', ['club' => $club]);
        }

        $this->addFlashMessage('There is no valid user logged in. So record was not saved');
        return $this->redirect('list');
    }

    /**
     * The club UID and PID are required for the unique path segment and the
     * POI titles. They are available after the first persistAll() only.
     */
    protected function persistNewClub(Club $club): void
    {
        $this->clubRepository->add($club);
        $this->persistenceManager->persistAll();

        $this->pathSegmentHelper->updatePathSegmentForClub($club);
        $this->mapHelper->updatePoiCollectionTitles($club);

        $this->clubRepository->update($club);
        $this->persistenceManager->persistAll();
    }

    public function initializeEditAction(): void
    {
        $this->preProcessControllerAction();
    }

    #[Extbase\IgnoreValidation(['value' => 'club'])]
    public function editAction(Club $club): ResponseInterface
    {
        $this->fillAddressesUpToMaximum($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'categories' => $this->categoryRepository->findByParent($this->extConf->getRootCategory()),
            'addressTitles' => $this->getAddressTitles(),
        ]);

        return $this->htmlResponse();
    }

    public function initializeUpdateAction(): void
    {
        if (!$this->request->hasArgument('club')) {
            return;
        }

        $requestArgument = $this->request->getArgument('club');
        $this->removeEmptyAddressesFromRequest($requestArgument);

        $this->preProcessControllerAction();
    }

    #[Extbase\Validate(['param' => 'club', 'validator' => ClubValidator::class])]
    public function updateAction(Club $club): ResponseInterface
    {
        $geocodingFailure = $this->mapHelper->addMapRecord($club);
        if ($geocodingFailure !== null) {
            return $this->forwardBackWithGeocodingError($geocodingFailure);
        }

        $this->clubRepository->update($club);

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
        ]);

        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.update', 'clubdirectory'),
        );

        $club->setHidden(true);
        $this->addFlashMessage(LocalizationUtility::translate('clubUpdated', 'clubdirectory'));

        return $this->redirect('listMyClubs');
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function activateAction(Club $club): ResponseInterface
    {
        $club->setHidden(false);
        $this->clubRepository->update($club);
        $this->addFlashMessage(LocalizationUtility::translate('clubActivated', 'clubdirectory'));

        return $this->redirect('list', 'Club');
    }
}
