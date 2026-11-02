<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace MyVendor\MySitePackage\EventListener;

use JWeiland\Clubdirectory\Event\PostProcessFluidVariablesEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'my-site-package/add-club-list-variable')]
final readonly class AddClubListVariableEventListener
{
    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($event->getControllerName() !== 'Club'
            || $event->getActionName() !== 'list'
        ) {
            return;
        }

        $event->addFluidVariable('myVariable', 'myValue');
    }
}
