<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Helper;

use JWeiland\Clubdirectory\Helper\GeocodingFailure;
use JWeiland\Maps2\Client\ClientError;
use JWeiland\Maps2\Client\ClientErrorEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for GeocodingFailure
 */
class GeocodingFailureTest extends UnitTestCase
{
    public static function labelPrefixDataProvider(): array
    {
        return [
            'no positions found' => [ClientErrorEnum::NO_POSITIONS_FOUND, 'error.geocoding.noPositionsFound.'],
            'request denied' => [ClientErrorEnum::REQUEST_DENIED, 'error.geocoding.requestDenied.'],
            'quota exceeded' => [ClientErrorEnum::QUOTA_EXCEEDED, 'error.geocoding.serviceError.'],
            'invalid request' => [ClientErrorEnum::INVALID_REQUEST, 'error.geocoding.serviceError.'],
            'invalid response' => [ClientErrorEnum::INVALID_RESPONSE, 'error.geocoding.serviceError.'],
            'service error' => [ClientErrorEnum::SERVICE_ERROR, 'error.geocoding.serviceError.'],
        ];
    }

    #[Test]
    #[DataProvider('labelPrefixDataProvider')]
    public function getLabelPrefixReturnsPrefixOfErrorType(ClientErrorEnum $type, string $expectedPrefix): void
    {
        $subject = new GeocodingFailure('Main street 1, 79100 Freiburg', new ClientError($type, 'message', 'title'));

        self::assertSame($expectedPrefix, $subject->getLabelPrefix());
    }

    #[Test]
    public function getLabelPrefixWithoutErrorReturnsServiceError(): void
    {
        self::assertSame('error.geocoding.serviceError.', (new GeocodingFailure('Main street'))->getLabelPrefix());
    }
}
