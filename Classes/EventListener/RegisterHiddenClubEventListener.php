<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use JWeiland\Clubdirectory\Helper\HiddenObjectHelper;
use JWeiland\Clubdirectory\Traits\IsValidEventListenerRequestTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * New and updated clubs are hidden until they get activated. Register the
 * hidden club in the Extbase session, so that the property mapper can resolve it.
 *
 * This EventListener has to be executed after the RestrictAccessEventListener,
 * which stops the request on denied access, so that a hidden club will never
 * be registered for unauthorized users.
 * Public actions like Club->show must never be listed here.
 */
#[AsEventListener(
    identifier: 'clubdirectory/register-hidden-club',
    after: 'clubdirectory/restrictAccess',
)]
final readonly class RegisterHiddenClubEventListener
{
    use IsValidEventListenerRequestTrait;

    private const ALLOWED_CONTROLLER_ACTIONS = [
        'Management' => [
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

    public function __construct(
        private HiddenObjectHelper $hiddenObjectHelper,
        private ClubRepository $clubRepository,
    ) {}

    public function __invoke(PreProcessControllerActionEvent $controllerActionEvent): void
    {
        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        $this->hiddenObjectHelper->registerHiddenObjectInExtbaseSession(
            $this->clubRepository,
            $controllerActionEvent->getRequest(),
            'club',
        );
    }
}
