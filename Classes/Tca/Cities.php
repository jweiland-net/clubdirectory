<?php

namespace JWeiland\Clubdirectory\Tca;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class Cities
 */
class Cities
{
    /**
     * @var DatabaseConnection
     */
    protected $database;

    /**
     * initializes the DB connection.
     */
    protected function init()
    {
        $this->database = $GLOBALS['TYPO3_DB'];
    }

    /**
     * add cities to select box.
     *
     * @param array $parentArray
     */
    public function addCityItems(array $parentArray)
    {
        $this->init();

        $rows = $this->database->exec_SELECTgetRows(
            'city',
            'tx_clubdirectory_domain_model_address',
            '1=1 ' .
            BackendUtility::BEenableFields('tx_clubdirectory_domain_model_address') .
            BackendUtility::deleteClause('tx_clubdirectory_domain_model_address'),
            'city',
            'city'
        );

        foreach ($rows as $row) {
            $item = [];
            $item[0] = $row['city'];
            $item[1] = $row['city'];
            $item[2] = null;
            $parentArray['items'][] = $item;
        }
    }
}
