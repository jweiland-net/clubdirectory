<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Clubdirectory\Domain\Model\FrontendUser;

return [
    FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
];
