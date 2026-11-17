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
use JWeiland\Clubdirectory\Controller\Traits\PostProcessFluidVariablesTrait;
use JWeiland\Clubdirectory\Controller\Traits\PreProcessControllerActionTrait;
use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Service\ClubCacheService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;

/**
 * Controller to let frontend users create, edit and manage their own clubs
 * listings. Clubs are created and edited with the form based on EXT:form.
 */
class ManagementController extends ActionController
{
    use ClubListRedirectTrait;
    use ControllerInjectionTrait;
    use InitializeControllerTrait;
    use PostProcessFluidVariablesTrait;
    use PreProcessControllerActionTrait;

    public function __construct(
        protected ClubAccessService $clubAccessService,
        protected ClubCacheService $clubCacheService,
        protected PersistenceManagerInterface $persistenceManager,
        protected ErrorController $errorController,
    ) {}

    public function listMyClubsAction(): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'clubs' => $this->clubRepository->findByFeUser($this->frontendUserRepository->getCurrentFrontendUserUid()),
        ]);

        return $this->htmlResponse();
    }

    public function initializePerformAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * Renders the club form based on EXT:form to create a new club or to
     * edit the club of the query parameter "club". The form reads the club,
     * the moderation token and the settings on its own, see
     * ClubAccessService::getClubUidFromQuery().
     */
    public function performAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function initializeConfirmActivationAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * Mail security scanners and link previews open the links of a mail
     * automatically. So the activation link of the mail shows this summary
     * of the club only. Its form activates the club via POST.
     */
    #[Extbase\IgnoreValidation(['value' => 'club'])]
    public function confirmActivationAction(Club $club): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'moderationToken' => $this->clubAccessService->getValidModerationToken($this->request, (int)$club->getUid()),
        ]);

        return $this->htmlResponse();
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * The club is activated via POST only. GET requests are redirected to
     * the confirmation page.
     * The action gets the club UID only, so no property of the club is
     * mapped, even if "__trustedProperties" of another form allow it.
     * Activation changes the "hidden" state only. The club is not validated,
     * so values saved in backend, which do not pass the validators of the
     * frontend form, do not prevent the activation.
     *
     * @throws PropagateResponseException
     */
    public function activateAction(int $clubUid): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('confirmActivation', null, null, [
                'club' => $clubUid,
                'moderationToken' => $this->clubAccessService->getValidModerationToken($this->request, $clubUid),
            ]);
        }

        $club = $this->getClubToActivate($clubUid);
        $club->setHidden(false);
        $this->clubRepository->update($club);
        $this->persistenceManager->persistAll();
        $this->clubCacheService->flushCachesOfClub($clubUid);

        return $this->redirectToClubList(
            (string)LocalizationUtility::translate('clubActivated', 'clubdirectory'),
        );
    }

    /**
     * Unknown clubs result in the 404 error handling of the site
     *
     * @throws PropagateResponseException
     */
    protected function getClubToActivate(int $clubUid): Club
    {
        $club = $this->clubRepository->findHiddenObject($clubUid);
        if (!$club instanceof Club) {
            throw new PropagateResponseException(
                $this->errorController->pageNotFoundAction($this->request, 'Club not found'),
                1762358400,
            );
        }

        return $club;
    }
}
