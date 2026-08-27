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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Restrict access to certain controller actions if logged-in user tries to access other user's records.
 */
class RestrictAccessEventListener
{
    /**
     * @var array
     */
    protected $allowedControllerActions = [
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

    /**
     * @var FlashMessageService
     */
    protected $flashMessageService;

    /**
     * @var ClubRepository
     */
    protected $clubRepository;

    /**
     * @var ExtensionService
     */
    protected $extensionService;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(
        FlashMessageService $flashMessageService,
        ClubRepository $clubRepository,
        ExtensionService $extensionService
    ) {
        $this->flashMessageService = $flashMessageService;
        $this->clubRepository = $clubRepository;
        $this->extensionService = $extensionService;
    }

    public function __invoke(InitializeControllerActionEvent $controllerActionEvent): void
    {
        if (!$this->isValidRequest($controllerActionEvent)) {
            return;
        }

        $this->request = $controllerActionEvent->getRequest();

        if ($this->isAccessAllowed($controllerActionEvent)) {
            return;
        }

        $this->request->setControllerActionName('error');

        $controllerActionEvent->setArguments(
            GeneralUtility::makeInstance(Arguments::class)
        );
    }

    protected function isAccessAllowed(InitializeControllerActionEvent $controllerActionEvent): bool
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

    protected function addFlashMessage(string $messageBody): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $messageBody,
            '',
            AbstractMessage::ERROR,
            true
        );

        $this->getFlashMessageQueue()->enqueue($flashMessage);
    }

    protected function getFlashMessageQueue(?string $identifier = null): FlashMessageQueue
    {
        if ($identifier === null) {
            $pluginNamespace = $this->extensionService->getPluginNamespace(
                $this->request->getControllerExtensionName(),
                $this->request->getPluginName()
            );
            $identifier = 'extbase.flashmessages.' . $pluginNamespace;
        }

        return $this->flashMessageService->getMessageQueueByIdentifier($identifier);
    }

    protected function isValidRequest(ControllerActionEventInterface $event): bool
    {
        return
            array_key_exists($event->getControllerName(), $this->allowedControllerActions)
            && in_array(
                $event->getActionName(),
                $this->allowedControllerActions[$event->getControllerName()],
                true
            );
    }
}
