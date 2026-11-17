<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Helper;

use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;

/**
 * The address which could not be geocoded and the error of the map provider
 */
final readonly class GeocodingFailure
{
    private const LABEL_PREFIX = 'error.geocoding.';

    public function __construct(
        public string $address,
        public ?ClientError $error = null,
    ) {}

    /**
     * Only "no positions found" is caused by the user input. For all other
     * errors the user can not do anything, so the label asks to inform the
     * administrator. Append "title" or "body" to get the label key.
     */
    public function getLabelPrefix(): string
    {
        return self::LABEL_PREFIX . match ($this->error?->type) {
            ClientErrorEnum::NO_POSITIONS_FOUND => 'noPositionsFound',
            ClientErrorEnum::REQUEST_DENIED => 'requestDenied',
            default => 'serviceError',
        } . '.';
    }
}
