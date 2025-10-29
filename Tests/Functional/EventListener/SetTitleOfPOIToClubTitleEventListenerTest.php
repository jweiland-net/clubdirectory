<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\EventListener;

use Doctrine\DBAL\Driver\Exception;
use JWeiland\Clubdirectory\EventListener\SetTitleOfPOIToClubTitleEventListener;
use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class SetTitleOfPOIToClubTitleEventListenerTest extends FunctionalTestCase
{
    protected SetTitleOfPOIToClubTitleEventListener $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_clubdirectory_domain_model_club.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_maps2_domain_model_poicollection.csv');

        $this->subject = new SetTitleOfPOIToClubTitleEventListener();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function callInvokePostProcessPoiCollectionRecordEvent(): void
    {
        $event = new PostProcessPoiCollectionRecordEvent(
            'tx_maps2_domain_model_poicollection',
            123,
            'tx_clubdirectory_domain_model_address',
            [
                'title' => 'Postal Address',
                'club' => 1,
            ],
            [],
        );
        call_user_func($this->subject, $event);

        $queryBuilder = $this
            ->getConnectionPool()
            ->getConnectionForTable('tx_maps2_domain_model_poicollection');

        try {
            $poiRecord = $queryBuilder
                ->select(
                    ['*'],
                    'tx_maps2_domain_model_poicollection',
                    ['uid' => 123],
                )
                ->fetchAssociative();
        } catch (Exception|\Doctrine\DBAL\Exception $e) {
            $poiRecord['title'] = false;
        }

        self::assertSame(
            'Swimmingclub',
            $poiRecord['title'],
        );
    }
}
