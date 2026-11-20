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
 * Restrict access to the club form (action "perform" of the Management
 * controller). Without a club it creates a new club, which is allowed for
 * logged-in members of the usergroup configured in "settings.userGroup"
 * only. Members can edit the clubs assigned to them only. Backend
 * administrators can edit existing clubs. A valid "moderationToken" of the
 * signed mail links grants access to the club without login.
 * The SaveClubFinisher and the PrefillForEditUsageHook check the same rules
 * with ClubAccessService::isFormAccessAllowed() again.
 * The actions "confirmActivation" and "activate" check the moderation token
 * on their own and redirect to the club list with a message.
 * The listener is called in the initialize*Action methods, so access is
 * denied before Extbase maps the arguments.
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
            'perform',
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
        if ($this->clubAccessService->getValidModerationToken($request, $clubUid) !== '') {
            return true;
        }

        if (!$this->isAuthorizedUser($controllerActionEvent, $clubUid)) {
            return false;
        }

        return $clubUid === 0 || $this->clubAccessService->canManageClub($clubUid);
    }

    /**
     * Without a moderation token only members of the configured usergroup are
     * allowed. Backend administrators are allowed to edit existing clubs.
     */
    private function isAuthorizedUser(PreProcessControllerActionEvent $controllerActionEvent, int $clubUid): bool
    {
        $userGroup = (int)($controllerActionEvent->getSettings()['userGroup'] ?? 0);

        return $this->clubAccessService->isMemberOfUserGroup($userGroup)
            || ($clubUid > 0 && $this->clubAccessService->isBackendAdmin());
    }
}
