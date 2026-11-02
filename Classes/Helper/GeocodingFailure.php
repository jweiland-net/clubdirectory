<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Helper;

use JWeiland\Clubdirectory\Domain\Model\Address;
use JWeiland\Maps2\Client\ClientError;

/**
 * The address which could not be geocoded and the error of the map provider
 */
final readonly class GeocodingFailure
{
    public function __construct(
        public Address $address,
        public ?ClientError $error = null,
    ) {}
}
