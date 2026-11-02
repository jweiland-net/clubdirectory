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
 * Restrict access to controller actions, if the logged-in user tries to
 * access records of clubs which are not assigned to him.
 * A valid "moderationToken" of the signed mail links grants access to the
 * club without login. "activate" is allowed with a valid token only, so
 * owners can not activate their own clubs and bypass the moderation.
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
            'create',
            'edit',
            'update',
            'activate',
        ],
        'Map' => [
            'new',
            'create',
        ],
    ];

    /**
     * Only these actions are allowed to be called with a club which was not persisted yet
     */
    private const NEW_CLUB_CONTROLLER_ACTIONS = [
        'Management' => [
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

        if ($clubUid === 0 && !$this->isNewClubAllowed($controllerActionEvent)) {
            return false;
        }

        if ($clubUid > 0 && !$this->isClubAccessAllowed($controllerActionEvent, $clubUid)) {
            return false;
        }

        return !is_array($clubArgument) || $this->clubAccessService->hasOnlyRecordsOfClub($clubArgument, $clubUid);
    }

    private function isClubAccessAllowed(PreProcessControllerActionEvent $controllerActionEvent, int $clubUid): bool
    {
        if ($this->clubAccessService->getValidModerationToken($controllerActionEvent->getRequest(), $clubUid) !== '') {
            return true;
        }

        return $controllerActionEvent->getActionName() !== 'activate'
            && $this->clubAccessService->canManageClub($clubUid);
    }

    private function isNewClubAllowed(PreProcessControllerActionEvent $controllerActionEvent): bool
    {
        return in_array(
            $controllerActionEvent->getActionName(),
            self::NEW_CLUB_CONTROLLER_ACTIONS[$controllerActionEvent->getControllerName()] ?? [],
            true,
        );
    }
}
