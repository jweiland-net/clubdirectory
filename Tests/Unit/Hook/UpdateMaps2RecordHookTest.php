<?php
namespace JWeiland\Clubdirectory\Tests\Unit\Helper;

/*
 * This file is part of the clubdirectory project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Clubdirectory\Hook\UpdateMaps2RecordHook;
use JWeiland\Clubdirectory\Tests\Unit\AbstractUnitTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case
 */
class UpdateMaps2RecordHookTest extends AbstractUnitTestCase
{
    /**
     * @var UpdateMaps2RecordHook
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new UpdateMaps2RecordHook();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function postUpdateWillSetTitleOfPoiCollectionToTitleOfClubRecord()
    {
        $this->buildAssertionForDatabaseWithReturnValue(
            'tx_clubdirectory_domain_model_club',
            [
                'uid' => 392,
                'title' => 'Fightclub'
            ],
            [
                [
                    'expr' => 'eq',
                    'field' => 'uid',
                    'value' => '392'
                ]
            ]
        );

        /** @var Connection|ObjectProphecy $connectionProphecy */
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy
            ->update(
                'tx_maps2_domain_model_poicollection',
                [
                    'title' => 'Fightclub'
                ],
                [
                    'uid' => 64893
                ]
            )
            ->shouldBeCalled();
        /** @var ConnectionPool|ObjectProphecy $connectionPoolProphecy */
        $connectionPoolProphecy = $this->prophesize(ConnectionPool::class);
        $connectionPoolProphecy
            ->getConnectionForTable('tx_maps2_domain_model_poicollection')
            ->shouldBeCalled()
            ->willReturn($connectionProphecy->reveal());
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPoolProphecy->reveal());

        $addressRecord = [
            'uid' => '295',
            'title' => 'postAddress',
            'club' => 392
        ];

        $this->subject->postUpdatePoiCollection(
            'tx_maps2_domain_model_poicollection',
            64893,
            $addressRecord,
            []
        );
    }
}
