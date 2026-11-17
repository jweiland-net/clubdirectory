<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

use JWeiland\Clubdirectory\Helper\GeocodingFailure;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use Psr\Log\LoggerInterface;

/**
 * Geocodes the addresses of clubs with the configured map provider of EXT:maps2
 */
readonly class GeocodingService
{
    public function __construct(
        private GeoCodeService $geoCodeService,
        private LoggerInterface $logger,
    ) {}

    /**
     * Same format as Address::getAddress()
     */
    public function formatAddress(string $street, string $houseNumber, string $zip, string $city): string
    {
        return trim($street) . ' ' . trim($houseNumber) . ', ' . trim($zip) . ' ' . trim($city);
    }

    public function geocode(string $address): Position|GeocodingFailure
    {
        $geoCodeResult = $this->geoCodeService->getGeoCodeResult($address);
        $position = $geoCodeResult->getFirstPosition();
        if ($position === null) {
            // The provider message may contain sensitive data like the server IP. Log it only.
            $this->logger->warning('Address "' . $address . '" could not be geocoded.', [
                'error' => $geoCodeResult->error?->message,
            ]);

            return new GeocodingFailure($address, $geoCodeResult->error);
        }

        return $position;
    }
}
