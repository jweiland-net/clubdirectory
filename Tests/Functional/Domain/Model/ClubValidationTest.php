<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Domain\Model;

use JWeiland\Clubdirectory\Domain\Model\Club;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Validators of the link properties of the club model
 */
class ClubValidationTest extends FunctionalTestCase
{
    private const LINK_PROPERTIES = ['website', 'facebook', 'twitter', 'instagram'];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    private function validateClubWithLink(string $link): Result
    {
        $club = new Club();
        $club->setTitle('Swimming club');
        $club->setWebsite($link);
        $club->setFacebook($link);
        $club->setTwitter($link);
        $club->setInstagram($link);

        return $this->get(ValidatorResolver::class)->getBaseValidatorConjunction(Club::class)->validate($club);
    }

    public static function validLinkDataProvider(): array
    {
        return [
            'empty' => [''],
            'https' => ['https://www.example.org/club?a=1&b=2'],
            'http' => ['http://www.example.org'],
        ];
    }

    #[Test]
    #[DataProvider('validLinkDataProvider')]
    public function validLinksAreAccepted(string $link): void
    {
        $result = $this->validateClubWithLink($link);

        foreach (self::LINK_PROPERTIES as $propertyName) {
            self::assertFalse($result->forProperty($propertyName)->hasErrors(), $propertyName);
        }
    }

    public static function invalidLinkDataProvider(): array
    {
        return [
            'text' => ['no url'],
            'word' => ['garbage'],
            'without scheme' => ['www.example.org'],
            'number' => ['123'],
        ];
    }

    #[Test]
    #[DataProvider('invalidLinkDataProvider')]
    public function invalidLinksAreRejectedWithErrorMessage(string $link): void
    {
        $result = $this->validateClubWithLink($link);

        foreach (self::LINK_PROPERTIES as $propertyName) {
            $errors = $result->forProperty($propertyName)->getErrors();
            self::assertCount(1, $errors, $propertyName);
            self::assertSame('Please enter the complete URL including https://.', $errors[0]->getMessage());
            self::assertSame(1238108078, $errors[0]->getCode());
        }
    }
}
