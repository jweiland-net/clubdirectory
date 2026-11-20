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
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * Without a valid "moderationToken" or for an active club, the moderator
     * is redirected to the club list with a message.
     */
    #[Extbase\IgnoreValidation(['value' => 'club'])]
    public function confirmActivationAction(Club $club): ResponseInterface
    {
        $moderationToken = $this->clubAccessService->getValidModerationToken($this->request, (int)$club->getUid());
        if ($moderationToken === '' || !$club->getHidden()) {
            return $this->redirectToClubList(...$this->getActivationError($moderationToken));
        }

        $this->postProcessAndAssignFluidVariables([
            'club' => $club,
            'moderationToken' => $moderationToken,
        ]);

        return $this->htmlResponse();
    }

    public function initializeActivateAction(): void
    {
        $this->preProcessControllerAction();
    }

    /**
     * The club is activated via POST only. GET requests (e.g. activation
     * links of former mails) are redirected to the confirmation page.
     * The action gets the club UID only, so no property of the club is
     * mapped, even if "__trustedProperties" of another form allow it.
     * Activation changes the "hidden" state only. The club is not validated,
     * so values saved in backend, which do not pass the validators of the
     * frontend form, do not prevent the activation.
     * The result is shown as flash message of the club list.
     */
    public function activateAction(int $clubUid = 0): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('confirmActivation', null, null, [
                'club' => $clubUid,
                'moderationToken' => $this->clubAccessService->getValidModerationToken($this->request, $clubUid),
            ]);
        }

        $club = $this->clubRepository->findHiddenObject($clubUid);
        $moderationToken = $club instanceof Club
            ? $this->clubAccessService->getValidModerationToken($this->request, $clubUid)
            : '';
        if (!$club instanceof Club || $moderationToken === '' || !$club->getHidden()) {
            return $this->redirectToClubList(...$this->getActivationError($moderationToken));
        }

        $this->activateClub($club);

        return $this->redirectToClubList(
            (string)LocalizationUtility::translate('clubActivated', 'clubdirectory'),
        );
    }

    protected function activateClub(Club $club): void
    {
        $club->setHidden(false);
        $this->clubRepository->update($club);
        $this->persistenceManager->persistAll();
        $this->clubCacheService->flushCachesOfClub((int)$club->getUid());
    }

    /**
     * Without a valid token the club can not be activated. Owners never get
     * a token, so they can not bypass the moderation. With a valid token the
     * club is active already.
     *
     * @return array{0: string, 1: ContextualFeedbackSeverity}
     */
    protected function getActivationError(string $moderationToken): array
    {
        if ($moderationToken === '') {
            return [
                (string)LocalizationUtility::translate('activation.invalidToken', 'clubdirectory'),
                ContextualFeedbackSeverity::ERROR,
            ];
        }

        return [
            (string)LocalizationUtility::translate('activation.alreadyActivated', 'clubdirectory'),
            ContextualFeedbackSeverity::INFO,
        ];
    }
}
