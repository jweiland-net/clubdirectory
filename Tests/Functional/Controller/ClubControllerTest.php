<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Frontend requests to the show action of ClubController
 */
class ClubControllerTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Setup.typoscript'],
            ],
        );
        $this->get(SiteWriter::class)->write('clubdirectory', [
            'rootPageId' => 1,
            'base' => 'https://www.example.com/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'locale' => 'en_US.UTF-8',
                    'base' => '/',
                ],
            ],
        ]);
    }

    private function requestShowAction(int $clubUid): ResponseInterface
    {
        $queryParameters = [
            'id' => 1,
            'tx_clubdirectory_clubdirectory[action]' => 'show',
            'tx_clubdirectory_clubdirectory[controller]' => 'Club',
            'tx_clubdirectory_clubdirectory[club]' => $clubUid,
        ];
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        return $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.com/'))->withQueryParameters($queryParameters),
        );
    }

    #[Test]
    public function showActionWithVisibleClubRendersClub(): void
    {
        $response = $this->requestShowAction(1);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Swimming club', (string)$response->getBody());
    }

    public static function unavailableClubDataProvider(): array
    {
        return [
            'hidden club' => [2],
            'deleted club' => [3],
            'unknown club' => [999],
        ];
    }

    #[Test]
    #[DataProvider('unavailableClubDataProvider')]
    public function showActionWithUnavailableClubRespondsWithPageNotFound(int $clubUid): void
    {
        $response = $this->requestShowAction($clubUid);

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('Club not found', (string)$response->getBody());
        self::assertStringNotContainsString('Hidden club', (string)$response->getBody());
    }
}
