<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Controller;

use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * The map step (Map->create) saves the map positions of a hidden club.
 * The trusted properties of all forms of the plugin are signed for the
 * argument "club", so the submitted fields must not decide what is mapped.
 */
class MapCreateTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;

    private const PLUGIN_NAMESPACE = 'tx_clubdirectory_management';

    private const TABLE_CLUB = 'tx_clubdirectory_domain_model_club';

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected array $configurationToUseInTestInstance = [
        'MAIL' => [
            'transport' => 'null',
        ],
        'EXTENSIONS' => [
            'clubdirectory' => [
                'emailFromAddress' => 'clubdirectory@example.com',
                'emailFromName' => 'Club directory',
                'emailToAddress' => 'moderation@example.com',
                'emailToName' => 'Moderation',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/MapCreate.csv');
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Constants.typoscript'],
                'setup' => ['EXT:clubdirectory/Tests/Functional/Fixtures/Frontend/Management.typoscript'],
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

    /**
     * Submits the club argument with a valid trusted properties token, which
     * allows all submitted fields, as the edit form of the plugin does.
     *
     * @param array<string, mixed> $club
     */
    private function postCreate(array $club, int $frontendUserUid = self::OWNER): ResponseInterface
    {
        $queryParameters = [
            'id' => 1,
            self::PLUGIN_NAMESPACE . '[action]' => 'create',
            self::PLUGIN_NAMESPACE . '[controller]' => 'Map',
        ];
        $queryParameters['cHash'] = $this->get(CacheHashCalculator::class)
            ->generateForParameters(HttpUtility::buildQueryString($queryParameters));

        $parsedBody = [
            self::PLUGIN_NAMESPACE => [
                'club' => $club,
                '__trustedProperties' => $this->get(MvcPropertyMappingConfigurationService::class)
                    ->generateTrustedPropertiesToken($this->getFormFieldNames(['club' => $club])),
            ],
        ];
        $request = (new InternalRequest('https://www.example.com/'))
            ->withMethod('POST')
            ->withQueryParameters($queryParameters)
            ->withParsedBody($parsedBody);
        // The testing framework can not build a body of nested arrays
        $request->getBody()->write(http_build_query($parsedBody));

        return $this->executeFrontendSubRequest(
            $request,
            (new InternalRequestContext())->withFrontendUserId($frontendUserUid),
        );
    }

    /**
     * @param array<string|int, mixed> $arguments
     * @return string[]
     */
    private function getFormFieldNames(array $arguments, string $prefix = ''): array
    {
        $formFieldNames = [];
        foreach ($arguments as $name => $value) {
            $formFieldName = $prefix === '' ? (string)$name : $prefix . '[' . $name . ']';
            if (is_array($value)) {
                array_push($formFieldNames, ...$this->getFormFieldNames($value, $formFieldName));
            } else {
                $formFieldNames[] = $formFieldName;
            }
        }

        return $formFieldNames;
    }

    /**
     * @return array<string, mixed>
     */
    private function getRecord(string $tableName, int $uid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();
        $record = $queryBuilder
            ->select('*')
            ->from($tableName)
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($record) ? $record : [];
    }

    #[Test]
    public function postCreateWithHiddenFieldKeepsClubHidden(): void
    {
        $response = $this->postCreate([
            '__identity' => (string)self::HIDDEN_CLUB,
            'hidden' => '0',
        ]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, (int)$this->getRecord(self::TABLE_CLUB, self::HIDDEN_CLUB)['hidden']);
    }
}
