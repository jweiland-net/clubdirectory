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
use TYPO3\CMS\Extbase\Property\Exception as PropertyException;
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

    private const TABLE_ADDRESS = 'tx_clubdirectory_domain_model_address';

    private const TABLE_POI_COLLECTION = 'tx_maps2_domain_model_poicollection';

    /**
     * Address with POI collection of the hidden club (Fixtures/MapCreate.csv)
     */
    private const ADDRESS_WITH_POI = 4;

    private const POI_COLLECTION = 13;

    /**
     * Address without POI collection of the hidden club (Fixtures/ClubAccess.csv)
     */
    private const ADDRESS_WITHOUT_POI = 2;

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

    /**
     * @param string[] $fieldNames
     * @return array<string, mixed>
     */
    private function getFields(string $tableName, int $uid, array $fieldNames): array
    {
        return array_intersect_key($this->getRecord($tableName, $uid), array_flip($fieldNames));
    }

    private function countRecords(string $tableName): int
    {
        return $this->getConnectionPool()->getConnectionForTable($tableName)->count('*', $tableName, []);
    }

    /**
     * Fields of the map form: Map/New.html with partial Maps2/EditPoiCollection
     *
     * @return array<string, mixed>
     */
    private function getMapFormArgument(string $latitude, string $longitude): array
    {
        return [
            '__identity' => (string)self::HIDDEN_CLUB,
            'addresses' => [
                1 => [
                    '__identity' => (string)self::ADDRESS_WITH_POI,
                    'txMaps2Uid' => [
                        '__identity' => (string)self::POI_COLLECTION,
                        'title' => 'Hidden club',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function postCreateSavesCoordinatesOfPoiCollection(): void
    {
        $this->postCreate($this->getMapFormArgument('47.9876543210', '7.8123456789'));

        $poiCollection = $this->getRecord(self::TABLE_POI_COLLECTION, self::POI_COLLECTION);
        self::assertEqualsWithDelta(47.987654321, (float)$poiCollection['latitude'], 0.0000000001);
        self::assertEqualsWithDelta(7.8123456789, (float)$poiCollection['longitude'], 0.0000000001);
        self::assertSame('Hidden club', $poiCollection['title']);
    }

    /**
     * The trusted properties token of the edit form allows all of these
     * fields for the argument "club".
     */
    #[Test]
    public function postCreateWithFieldsOfEditFormKeepsClubAndAddress(): void
    {
        $clubFields = ['title', 'email', 'description', 'website', 'hidden'];
        $addressFields = ['title', 'street', 'zip', 'city'];
        $expectedClub = $this->getFields(self::TABLE_CLUB, self::HIDDEN_CLUB, $clubFields);
        $expectedAddress = $this->getFields(self::TABLE_ADDRESS, self::ADDRESS_WITH_POI, $addressFields);

        $club = $this->getMapFormArgument('47.9876543210', '7.8123456789');
        $club['title'] = 'Manipulated club';
        $club['email'] = 'attacker@example.com';
        $club['description'] = 'Manipulated description';
        $club['website'] = 'https://attacker.example.com';
        $club['addresses'][1]['street'] = 'Manipulated street';
        $club['addresses'][1]['zip'] = '12345';
        $club['addresses'][1]['txMaps2Uid']['title'] = 'Manipulated POI';
        $this->postCreate($club);

        self::assertSame($expectedClub, $this->getFields(self::TABLE_CLUB, self::HIDDEN_CLUB, $clubFields));
        self::assertSame($expectedAddress, $this->getFields(self::TABLE_ADDRESS, self::ADDRESS_WITH_POI, $addressFields));
        $poiCollection = $this->getRecord(self::TABLE_POI_COLLECTION, self::POI_COLLECTION);
        self::assertSame('Hidden club', $poiCollection['title']);
        self::assertEqualsWithDelta(47.987654321, (float)$poiCollection['latitude'], 0.0000000001);
    }

    #[Test]
    public function postCreateWithNewAddressRejectsCreationOfRecords(): void
    {
        $club = $this->getMapFormArgument('47.9876543210', '7.8123456789');
        $club['addresses'][2] = [
            'title' => 'clubHome',
            'zip' => '79098',
            'city' => 'Freiburg',
            'txMaps2Uid' => ['latitude' => '48.0', 'longitude' => '7.9'],
        ];

        try {
            $this->postCreate($club);
            self::fail('Property mapping of new records was expected to be rejected.');
        } catch (PropertyException $propertyException) {
            self::assertStringContainsString('Creation of objects not allowed', $propertyException->getMessage());
        }

        self::assertSame(4, $this->countRecords(self::TABLE_ADDRESS));
        self::assertSame(1, $this->countRecords(self::TABLE_POI_COLLECTION));
    }

    /**
     * The ownership of the submitted identities is checked before the
     * property mapping: POI collection 11 belongs to the address of club 1.
     */
    #[Test]
    public function postCreateWithForeignPoiCollectionDeniesAccess(): void
    {
        $club = $this->getMapFormArgument('47.9876543210', '7.8123456789');
        $club['addresses'][1]['txMaps2Uid']['__identity'] = '11';

        $response = $this->postCreate($club);

        self::assertSame(403, $response->getStatusCode());
        self::assertEqualsWithDelta(
            47.999,
            (float)$this->getRecord(self::TABLE_POI_COLLECTION, self::POI_COLLECTION)['latitude'],
            0.0000000001,
        );
    }

    /**
     * The map form contains the addresses with POI collection only
     */
    #[Test]
    public function postCreateKeepsAddressesWithoutPoiCollection(): void
    {
        $this->postCreate($this->getMapFormArgument('47.9876543210', '7.8123456789'));

        self::assertSame(
            ['deleted' => 0, 'club' => 2],
            array_map('intval', $this->getFields(self::TABLE_ADDRESS, self::ADDRESS_WITHOUT_POI, ['club', 'deleted'])),
        );
        self::assertSame(
            ['deleted' => 0, 'club' => 2],
            array_map('intval', $this->getFields(self::TABLE_ADDRESS, self::ADDRESS_WITH_POI, ['club', 'deleted'])),
        );
    }
}
