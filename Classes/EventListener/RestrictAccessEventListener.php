<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;

/**
 * Restrict access to the actions of the Management and Map controllers.
 * They are allowed for logged-in members of the usergroup configured in
 * "settings.userGroup" only. Frontend users can manage the clubs assigned to
 * them only. Backend administrators can manage existing clubs.
 * A valid "moderationToken" of the signed mail links grants access to the
 * club without login. "confirmActivation" and "activate" are allowed with a
 * valid token only, so owners can not activate their own clubs and bypass
 * the moderation.
 * The map step ("Map" new and create) is allowed for hidden clubs only, as
 * clubs are hidden after Management create and update until they get
 * activated. So the map step can never change an active club. This applies
 * to backend administrators and moderation token holders, too.
 * The listener is called in the initialize*Action methods, so access is
 * denied before Extbase maps the arguments and stores uploaded files.
 * Access will be denied with the 403 error handling of the site. Do not
 * forward to Extbase's errorAction: on POST requests it forwards back to the
 * referring action, which will be denied again (InfiniteLoopException).
 */
#[AsEventListener('clubdirectory/restrictAccess')]
final readonly class RestrictAccessEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ALLOWED_CONTROLLER_ACTIONS = [
        'Management' => [
            'new',
            'create',
            'edit',
            'update',
            'confirmActivation',
            'activate',
        ],
        'Map' => [
            'new',
            'create',
        ],
    ];

    /**
     * These actions are allowed with a valid moderation token only
     */
    private const MODERATION_TOKEN_CONTROLLER_ACTIONS = [
        'Management' => [
            'confirmActivation',
            'activate',
        ],
    ];

    /**
     * Only these actions are allowed to be called with a club which was not persisted yet
     */
    private const NEW_CLUB_CONTROLLER_ACTIONS = [
        'Management' => [
            'new',
            'create',
        ],
    ];

    /**
     * These actions are allowed for hidden clubs only
     */
    private const HIDDEN_CLUB_CONTROLLER_ACTIONS = [
        'Map' => [
            'new',
            'create',
        ],
    ];

    public function __construct(
        private ClubAccessService $clubAccessService,
        private ErrorController $errorController,
    ) {}

    /**
     * @throws PropagateResponseException
     */
    public function __invoke(PreProcessControllerActionEvent $controllerActionEvent): void
    {
        if (!$this->isValidRequest($controllerActionEvent) || $this->isAccessAllowed($controllerActionEvent)) {
            return;
        }

        throw new PropagateResponseException(
            $this->errorController->accessDeniedAction(
                $controllerActionEvent->getRequest(),
                (string)LocalizationUtility::translate('unauthorizedUser', 'clubdirectory'),
            ),
            1762090800,
        );
    }

    private function isAccessAllowed(PreProcessControllerActionEvent $controllerActionEvent): bool
    {
        $request = $controllerActionEvent->getRequest();
        $clubArgument = $request->hasArgument('club') ? $request->getArgument('club') : null;
        $clubUid = $this->clubAccessService->getIdentity($clubArgument);
        $hasModerationToken = $this->clubAccessService->getValidModerationToken($request, $clubUid) !== '';

        if (!$hasModerationToken && !$this->isAuthorizedUser($controllerActionEvent, $clubUid)) {
            return false;
        }

        if ($clubUid === 0 && !$this->isNewClubAllowed($controllerActionEvent)) {
            return false;
        }

        if ($clubUid > 0 && !$hasModerationToken && !$this->isClubAccessAllowed($controllerActionEvent, $clubUid)) {
            return false;
        }

        if ($clubUid > 0 && !$this->isClubStateAllowed($controllerActionEvent, $clubUid)) {
            return false;
        }

        return !is_array($clubArgument) || $this->clubAccessService->hasOnlyRecordsOfClub($clubArgument, $clubUid);
    }

    /**
     * Without a moderation token only members of the configured usergroup are
     * allowed. Backend administrators are allowed to manage existing clubs.
     */
    private function isAuthorizedUser(PreProcessControllerActionEvent $controllerActionEvent, int $clubUid): bool
    {
        $userGroup = (int)($controllerActionEvent->getSettings()['userGroup'] ?? 0);

        return $this->clubAccessService->isMemberOfUserGroup($userGroup)
            || ($clubUid > 0 && $this->clubAccessService->isBackendAdmin());
    }

    private function isClubAccessAllowed(PreProcessControllerActionEvent $controllerActionEvent, int $clubUid): bool
    {
        return !in_array(
            $controllerActionEvent->getActionName(),
            self::MODERATION_TOKEN_CONTROLLER_ACTIONS[$controllerActionEvent->getControllerName()] ?? [],
            true,
        ) && $this->clubAccessService->canManageClub($clubUid);
    }

    private function isNewClubAllowed(PreProcessControllerActionEvent $controllerActionEvent): bool
    {
        return in_array(
            $controllerActionEvent->getActionName(),
            self::NEW_CLUB_CONTROLLER_ACTIONS[$controllerActionEvent->getControllerName()] ?? [],
            true,
        );
    }

    private function isClubStateAllowed(PreProcessControllerActionEvent $controllerActionEvent, int $clubUid): bool
    {
        return !in_array(
            $controllerActionEvent->getActionName(),
            self::HIDDEN_CLUB_CONTROLLER_ACTIONS[$controllerActionEvent->getControllerName()] ?? [],
            true,
        ) || $this->clubAccessService->isClubHidden($clubUid);
    }
}
