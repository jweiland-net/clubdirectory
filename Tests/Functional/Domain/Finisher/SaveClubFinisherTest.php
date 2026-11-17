<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\Domain\Finisher;

use JWeiland\Clubdirectory\Converter\RteContentConverter;
use JWeiland\Clubdirectory\Domain\Finisher\SaveClubFinisher;
use JWeiland\Clubdirectory\Helper\PathSegmentHelper;
use JWeiland\Clubdirectory\Service\ClubAccessService;
use JWeiland\Clubdirectory\Service\ClubCacheService;
use JWeiland\Clubdirectory\Service\ClubFormRecordService;
use JWeiland\Clubdirectory\Service\GeocodingService;
use JWeiland\Clubdirectory\Service\PoiCollectionService;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubAccessTestTrait;
use JWeiland\Clubdirectory\Tests\Functional\Traits\ClubFormTestTrait;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Domain\Runtime\FormState;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for SaveClubFinisher
 */
class SaveClubFinisherTest extends FunctionalTestCase
{
    use ClubAccessTestTrait;
    use ClubFormTestTrait;

    protected array $coreExtensionsToLoad = [
        'typo3/cms-form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubAccess.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/ClubForm.csv');
    }

    /**
     * @param array<string, mixed> $formValues
     */
    private function executeFinisher(
        array $formValues,
        int $frontendUserUid = self::OWNER,
        int $clubUid = 0,
        bool $isBackendAdmin = false,
    ): FinisherContext {
        $context = $this->createContext($frontendUserUid, $isBackendAdmin);
        $clubAccessService = new ClubAccessService($this->get(ConnectionPool::class), $context, new HashService());
        $clubFormRecordService = new ClubFormRecordService($this->get(ConnectionPool::class));
        $subject = new SaveClubFinisher(
            $this->get(ConnectionPool::class),
            $clubAccessService,
            $clubFormRecordService,
            $this->get(ClubCacheService::class),
            new PathSegmentHelper(),
            new PoiCollectionService(
                $this->get(ConnectionPool::class),
                $clubFormRecordService,
                $this->get(MapService::class),
                $this->createExtConf(),
            ),
            new GeocodingService(self::createStub(GeoCodeService::class), new NullLogger()),
            new RteContentConverter(),
            $context,
        );
        $subject->setFinisherIdentifier('SaveClubFinisher');
        $subject->setOptions($this->getSaveClubFinisherOptions());

        $formState = new FormState();
        foreach ($formValues as $identifier => $value) {
            $formState->setFormValue($identifier, $value);
        }

        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime->method('getFormState')->willReturn($formState);
        $formRuntime->method('getFormDefinition')->willReturn($this->createFormDefinition());
        $formRuntime->method('getRenderingOptions')->willReturn(['translation' => ['translationFiles' => []]]);

        $finisherContext = new FinisherContext($formRuntime, $this->createFormRequest($clubUid));
        $subject->execute($finisherContext);

        return $finisherContext;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchRecord(string $table, int $uid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $record = $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->executeQuery()
            ->fetchAssociative();

        return is_array($record) ? $record : [];
    }

    /**
     * @return list<int>
     */
    private function getCategoryUids(int $clubUid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_category_record_mm');

        return array_map('intval', $queryBuilder
            ->select('uid_local')
            ->from('sys_category_record_mm')
            ->where($queryBuilder->expr()->eq('uid_foreign', $clubUid))
            ->orderBy('sorting_foreign')
            ->executeQuery()
            ->fetchFirstColumn());
    }

    #[Test]
    public function executeCreatesClubWithRelations(): void
    {
        $finisherContext = $this->executeFinisher([
            'title' => 'Chess club',
            'description' => "First line\nSecond line",
            'categories' => ['7', '9'],
            'address-1-title' => 'clubAddress',
            'address-1-street' => 'Main street',
            'address-1-zip' => '79100',
            'address-1-city' => 'Freiburg',
            'address-1-position' => ['latitude' => '47.991234', 'longitude' => '7.841234'],
        ]);

        $clubUid = (int)$finisherContext->getFinisherVariableProvider()->get(
            'SaveClub',
            'insertedUids.tx_clubdirectory_domain_model_club',
        );
        $club = $this->fetchRecord('tx_clubdirectory_domain_model_club', $clubUid);

        self::assertSame('Chess club', $club['title']);
        self::assertSame('chess-club', $club['path_segment']);
        self::assertSame('<p>First line<br />Second line</p>', $club['description']);
        self::assertSame(1, (int)$club['pid']);
        // Category 9 is not an option of the form
        self::assertSame([7], $this->getCategoryUids($clubUid));
        self::assertSame(1, (int)$club['fe_users']);
        self::assertNotSame('', $finisherContext->getFinisherVariableProvider()->get('SaveClub', 'moderationToken'));
    }

    #[Test]
    public function executeCreatesAddressWithPoiCollection(): void
    {
        $finisherContext = $this->executeFinisher([
            'title' => 'Chess club',
            'address-1-title' => 'clubAddress',
            'address-1-street' => 'Main street',
            'address-1-zip' => '79100',
            'address-1-city' => 'Freiburg',
            'address-1-position' => ['latitude' => '47.991234', 'longitude' => '7.841234'],
        ]);

        $clubUid = (int)$finisherContext->getFinisherVariableProvider()->get('SaveClub', 'insertedUids.1');
        $address = (new ClubFormRecordService($this->get(ConnectionPool::class)))->getAddressRecord($clubUid, 1);
        $poiCollection = $this->fetchRecord('tx_maps2_domain_model_poicollection', (int)$address['tx_maps2_uid']);

        self::assertSame('Main street', $address['street']);
        self::assertSame($clubUid, (int)$address['club']);
        self::assertEqualsWithDelta(47.991234, (float)$poiCollection['latitude'], 0.000001);
        self::assertEqualsWithDelta(7.841234, (float)$poiCollection['longitude'], 0.000001);
        self::assertSame('Chess club', $poiCollection['title']);
    }

    #[Test]
    public function executeUpdatesClubAndPositionOfExistingPoiCollection(): void
    {
        $this->executeFinisher([
            'title' => 'Updated swimming club',
            'categories' => ['7'],
            'address-1-title' => 'postAddress',
            'address-1-street' => 'Main street',
            'address-1-zip' => '79100',
            'address-1-city' => 'Freiburg',
            'address-1-position' => ['latitude' => '48.000001', 'longitude' => '7.900001'],
        ], self::OWNER, self::VISIBLE_CLUB);

        $club = $this->fetchRecord('tx_clubdirectory_domain_model_club', self::VISIBLE_CLUB);
        $address = $this->fetchRecord('tx_clubdirectory_domain_model_address', 1);
        $poiCollection = $this->fetchRecord('tx_maps2_domain_model_poicollection', 11);

        self::assertSame('Updated swimming club', $club['title']);
        // The path segment of an edited club is kept
        self::assertSame('swimming-club', $club['path_segment']);
        self::assertSame([7], $this->getCategoryUids(self::VISIBLE_CLUB));
        self::assertSame('postAddress', $address['title']);
        self::assertSame(11, (int)$address['tx_maps2_uid']);
        self::assertEqualsWithDelta(48.000001, (float)$poiCollection['latitude'], 0.000001);
        self::assertEqualsWithDelta(7.900001, (float)$poiCollection['longitude'], 0.000001);
    }

    #[Test]
    public function executeWithInvalidPositionKeepsPoiCollection(): void
    {
        $this->executeFinisher([
            'title' => 'Swimming club',
            'address-1-title' => 'postAddress',
            'address-1-zip' => '79100',
            'address-1-city' => 'Freiburg',
            'address-1-position' => ['latitude' => '123.4', 'longitude' => '7.9'],
        ], self::OWNER, self::VISIBLE_CLUB);

        $poiCollection = $this->fetchRecord('tx_maps2_domain_model_poicollection', 11);

        self::assertEqualsWithDelta(47.9, (float)$poiCollection['latitude'], 0.000001);
    }

    #[Test]
    public function executeWithoutAddressTypeDeletesAddressAndPoiCollection(): void
    {
        $this->executeFinisher([
            'title' => 'Swimming club',
            'address-1-title' => '',
        ], self::OWNER, self::VISIBLE_CLUB);

        self::assertSame(1, (int)$this->fetchRecord('tx_clubdirectory_domain_model_address', 1)['deleted']);
        self::assertSame(1, (int)$this->fetchRecord('tx_maps2_domain_model_poicollection', 11)['deleted']);
    }

    #[Test]
    public function executeOfEditedClubKeepsFrontendUsers(): void
    {
        $this->executeFinisher(['title' => 'Hidden club'], 0, self::HIDDEN_CLUB, true);

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_clubdirectory_club_user_mm');
        $numberOfUsers = $queryBuilder
            ->count('uid_local')
            ->from('tx_clubdirectory_club_user_mm')
            ->where($queryBuilder->expr()->eq('uid_local', self::HIDDEN_CLUB))
            ->executeQuery()
            ->fetchOne();

        self::assertSame(1, (int)$numberOfUsers);
    }

    #[Test]
    public function executeWithForeignUserThrowsException(): void
    {
        $this->expectException(FinisherException::class);
        $this->expectExceptionCode(1763337603);

        $this->executeFinisher(['title' => 'Manipulated club'], self::FOREIGN_USER, self::VISIBLE_CLUB);
    }

    #[Test]
    public function executeWithForeignUserDoesNotChangeClub(): void
    {
        try {
            $this->executeFinisher(['title' => 'Manipulated club'], self::FOREIGN_USER, self::VISIBLE_CLUB);
        } catch (FinisherException) {
        }

        self::assertSame('Swimming club', $this->fetchRecord('tx_clubdirectory_domain_model_club', self::VISIBLE_CLUB)['title']);
    }

    #[Test]
    public function executeWithoutLoginDoesNotCreateClub(): void
    {
        $this->expectException(FinisherException::class);

        $this->executeFinisher(['title' => 'Anonymous club'], 0);
    }
}
