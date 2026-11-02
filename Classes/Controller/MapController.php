<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Controller;

use JWeiland\Clubdirectory\Controller\Traits\ClubListRedirectTrait;
use JWeiland\Clubdirectory\Controller\Traits\ControllerInjectionTrait;
use JWeiland\Clubdirectory\Controller\Traits\InitializeControllerTrait;
use JWeiland\Clubdirectory\Controller\Traits\PreProcessControllerActionTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Service\ClubCacheService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller to confirm the map positions of a new club. Changes of
 * existing clubs incl. their map positions are done in ManagementController.
 */
class MapController extends ActionController
{
    use ClubListRedirectTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected ClubAccessService $clubAccessService,
        protected ClubCacheService $clubCacheService,
        protected PersistenceManagerInterface $persistenceManager,
    ) {}

    public function initializeNewAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * As club was already validated in ClubController create/update there can't be any errors. So ignore validation.
     */
    #[Extbase\IgnoreValidation(['value' => 'club'])]
    public function newAction(Club $club): ResponseInterface
    {
        $this->view->assign('club', $club);

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        $this->preProcessControllerAction();
    }

    public function createAction(Club $club): ResponseInterface
    {
        if (!$this->frontendUserRepository->getCurrentFrontendUserUid()) {
            return $this->redirectToClubList('There is no valid user logged in. So record was not saved');
        }

        $this->clubRepository->update($club);
        // The moderation token of the mail signs the "tstamp" of the persisted club
        $this->persistenceManager->persistAll();
        $this->clubCacheService->flushCachesOfClub((int)$club->getUid());

        $this->view->assignMultiple([
            'club' => $club,
            'moderationToken' => $this->clubAccessService->generateModerationToken((int)$club->getUid()),
        ]);
        $this->mailHelper->sendMail(
            $this->view->render(),
            LocalizationUtility::translate('email.subject.create', 'clubdirectory'),
        );

        return $this->redirectToClubList(
            (string)LocalizationUtility::translate('clubCreated', 'clubdirectory'),
        );
    }
}
