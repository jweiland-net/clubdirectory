<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Clubdirectory\Domain\Model\Club;
use JWeiland\Clubdirectory\Domain\Repository\ClubRepository;
use JWeiland\Clubdirectory\Event\ControllerActionEventInterface;
use JWeiland\Clubdirectory\Event\InitializeControllerActionEvent;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Restrict access to certain controller actions if logged-in user tries to access other user's records.
 */
#[AsEventListener('clubdirectory/restrictAccess')]
final class RestrictAccessEventListener
{
    protected const ALLOWED_CONTROLLER_ACTIONS = [
        'Club' => [
            'edit',
            'update',
            'activate',
        ],
        'Map' => [
            'edit',
            'update',
        ],
    ];

    private ?ServerRequestInterface $request = null;

    public function __construct(
        private readonly FlashMessageService $flashMessageService,
        private readonly ClubRepository $clubRepository,
        private readonly ExtensionService $extensionService,
    ) {}

    public function __invoke(InitializeControllerActionEvent $controllerActionEvent): void
    {
        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        $this->request = $controllerActionEvent->getRequest();

        if ($this->isAccessAllowed($controllerActionEvent)) {
            return;
        }

        $controllerActionEvent->setRequest(
            $this->request->withControllerActionName('error'),
        );

        $controllerActionEvent->setArguments(
            GeneralUtility::makeInstance(Arguments::class),
        );
    }

    private function isAccessAllowed(InitializeControllerActionEvent $controllerActionEvent): bool
    {
        $request = $controllerActionEvent->getRequest();

        if (!$request->hasArgument('club')) {
            return true;
        }

        $clubArgument = $request->getArgument('club');
        $clubUid = is_array($clubArgument)
            ? (int)($clubArgument['__identity'] ?? 0)
            : (int)$clubArgument;

        if (
            $clubUid > 0
            && ($club = $this->clubRepository->findHiddenObject($clubUid))
            && $club instanceof Club
            && $club->getCurrentUserCanEditClub() === false
        ) {
            $this->addFlashMessage(LocalizationUtility::translate('unauthorizedUser', 'clubdirectory'));

            return false;
        }

        return true;
    }

    private function addFlashMessage(string $messageBody): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $messageBody,
            '',
            ContextualFeedbackSeverity::ERROR,
            true,
        );

        $this->getFlashMessageQueue()->enqueue($flashMessage);
    }

    private function getFlashMessageQueue(?string $identifier = null): FlashMessageQueue
    {
        if ($identifier === null) {
            $pluginNamespace = $this->extensionService->getPluginNamespace(
                $this->request->getControllerExtensionName(),
                $this->request->getPluginName(),
            );
            $identifier = 'extbase.flashmessages.' . $pluginNamespace;
        }

        return $this->flashMessageService->getMessageQueueByIdentifier($identifier);
    }

    protected function isValidRequest(ControllerActionEventInterface $event): bool
    {
        return
            array_key_exists($event->getControllerName(), self::ALLOWED_CONTROLLER_ACTIONS)
            && in_array(
                $event->getActionName(),
                self::ALLOWED_CONTROLLER_ACTIONS[$event->getControllerName()],
                true,
            );
    }
}
