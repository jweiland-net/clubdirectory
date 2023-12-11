<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Functional\EventListener;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use JWeiland\Clubdirectory\EventListener\SetTitleOfPOIToClubTitleEventListener;
use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case
 */
class SetTitleOfPOIToClubTitleEventListenerTest extends FunctionalTestCase
{
    protected Typo3Version $typo3Version;

    protected SetTitleOfPOIToClubTitleEventListener $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
        'jweiland/glossary2',
        'jweiland/clubdirectory',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/tx_clubdirectory_domain_model_club.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_maps2_domain_model_poicollection.xml');

        $this->typo3Version = GeneralUtility::makeInstance(Typo3Version::class);

        $this->subject = new SetTitleOfPOIToClubTitleEventListener($this->typo3Version);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->typo3Version
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function callInvokeWithWrongTypo3Version(): void
    {
        if (version_compare($this->typo3Version->getBranch(), '11.0', '<')) {
            self::markTestSkipped('This test will only work in TYPO3 11 and higher');
        }

        $event = new PostProcessPoiCollectionRecordEvent(
            'tx_maps2_domain_model_poicollection',
            123,
            'tx_clubdirectory_domain_model_address',
            [
                'title' => 'Postal Address',
                'club' => 1,
            ],
            []
        );
        call_user_func($this->subject, $event);

        $poiRecord = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_maps2_domain_model_poicollection', 'uid = 123');
        self::assertSame(
            'Swimmingclub',
            $poiRecord['title']
        );
    }
}
