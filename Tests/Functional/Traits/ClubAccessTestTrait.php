<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Traits;

use JWeiland\Clubdirectory\Event\PreProcessControllerActionEvent;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Records of Fixtures/ClubAccess.csv and helpers to simulate logged-in
 * users and Extbase requests of the club management plugin.
 */
trait ClubAccessTestTrait
{
    /**
     * fe_users record assigned to the clubs 1, 2 and 3
     */
    private const OWNER = 1;

    /**
     * fe_users record assigned to club 4
     */
    private const FOREIGN_USER = 2;

    /**
     * fe_users record of another usergroup than "settings.userGroup"
     */
    private const OTHER_GROUP_USER = 3;

    /**
     * Value of "settings.userGroup"
     */
    private const USER_GROUP = 1;

    /**
     * Usergroup of the fe_users records of Fixtures/ClubAccess.csv
     */
    private const USER_GROUP_OF_FRONTEND_USER = [
        self::OWNER => self::USER_GROUP,
        self::FOREIGN_USER => self::USER_GROUP,
        self::OTHER_GROUP_USER => 2,
    ];

    private const VISIBLE_CLUB = 1;

    private const HIDDEN_CLUB = 2;

    private const DELETED_CLUB = 3;

    private const FOREIGN_CLUB = 4;

    /**
     * Hidden club assigned to OTHER_GROUP_USER
     */
    private const OTHER_GROUP_CLUB = 5;

    private function createContext(
        int $frontendUserUid = 0,
        bool $isBackendAdmin = false,
        bool $isBackendUser = false,
    ): Context {
        $context = new Context();
        $this->setUserAspects($context, $frontendUserUid, $isBackendAdmin, $isBackendUser);

        return $context;
    }

    /**
     * Always set both user aspects, so that no user of a previous test survives in the Context singleton
     */
    private function setUserAspects(
        Context $context,
        int $frontendUserUid = 0,
        bool $isBackendAdmin = false,
        bool $isBackendUser = false,
    ): void {
        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->user = $frontendUserUid > 0 ? ['uid' => $frontendUserUid, 'username' => 'user'] : null;
        $userGroup = self::USER_GROUP_OF_FRONTEND_USER[$frontendUserUid] ?? 0;
        $frontendUser->userGroups = $userGroup > 0 ? [$userGroup => ['uid' => $userGroup]] : [];
        $context->setAspect('frontend.user', new UserAspect($frontendUser));

        $backendUser = null;
        if ($isBackendAdmin || $isBackendUser) {
            $backendUser = new BackendUserAuthentication();
            $backendUser->user = ['uid' => 1, 'username' => 'editor', 'admin' => $isBackendAdmin ? 1 : 0];
        }
        $context->setAspect('backend.user', new UserAspect($backendUser));
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function createExtbaseRequest(string $controllerName, string $actionName, array $arguments = []): Request
    {
        $extbaseRequestParameters = (new ExtbaseRequestParameters())
            ->setPluginName('Management')
            ->setControllerExtensionName('Clubdirectory')
            ->setControllerName($controllerName)
            ->setControllerActionName($actionName)
            ->setArguments($arguments);

        $serverRequest = (new ServerRequest('https://www.example.com/', 'POST'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('extbase', $extbaseRequestParameters);

        return new Request($serverRequest);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function createEvent(
        string $controllerName,
        string $actionName,
        array $arguments = [],
        int $userGroup = self::USER_GROUP,
    ): PreProcessControllerActionEvent {
        return new PreProcessControllerActionEvent(
            $this->createExtbaseRequest($controllerName, $actionName, $arguments),
            new Arguments(),
            ['userGroup' => (string)$userGroup],
        );
    }
}
