<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\EventListener;

use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;


class SetTitleOfPOIToClubTitleEventListener
{
    public function __invoke(PostProcessPoiCollectionRecordEvent $event): void
    {

    }
}
