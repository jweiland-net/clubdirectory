<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Traits;

use JWeiland\Clubdirectory\Event\ControllerActionEventInterface;

/**
 * Trait to validate if the incoming request is valid for a specific EventListener.
 * The using class has to define the constant ALLOWED_CONTROLLER_ACTIONS.
 */
trait IsValidEventListenerRequestTrait
{
    private function isValidRequest(ControllerActionEventInterface $event): bool
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
