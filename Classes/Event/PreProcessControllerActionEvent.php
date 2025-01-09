<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Event;

use JWeiland\Clubdirectory\Controller\ClubController;
use JWeiland\Clubdirectory\Controller\MapController;
use JWeiland\Clubdirectory\Domain\Model\Club;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Pre process controller actions which does not assign any variables to view.
 * Often used by controller actions like "update" or "create" which redirects after success.
 */
class PreProcessControllerActionEvent implements ControllerActionEventInterface
{
    /**
     * @var ActionController|ClubController|MapController
     */
    protected $controller;

    /**
     * @var Club|null
     */
    protected $club;

    protected array $settings;

    protected Request $request;

    public function __construct(
        ActionController $controller,
        ?Club $club,
        array $settings,
        Request $request
    ) {
        $this->controller = $controller;
        $this->club = $club;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function getController(): ActionController
    {
        return $this->controller;
    }

    public function getClubController(): ClubController
    {
        return $this->controller;
    }

    public function getMapController(): MapController
    {
        return $this->controller;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getControllerName(): string
    {
        return $this->getRequest()->getControllerName();
    }

    public function getActionName(): string
    {
        return $this->getRequest()->getControllerActionName();
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
