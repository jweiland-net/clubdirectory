<?php

namespace JWeiland\Clubdirectory\Tca;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Cities
{
    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
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
     * add cities to selectbox.
     *
     * @param array                              $parentArray
     * @param \TYPO3\CMS\Backend\Form\FormEngine $fObj
     */
    public function addCityItems(array $parentArray, \TYPO3\CMS\Backend\Form\FormEngine $fObj)
    {
        $this->init();

        $rows = $this->database->exec_SELECTgetRows(
            'city',
            'tx_clubdirectory_domain_model_address',
            '1=1 '.
            BackendUtility::BEenableFields('tx_clubdirectory_domain_model_address').
            BackendUtility::deleteClause('tx_clubdirectory_domain_model_address'),
            'city', 'city', ''
        );

        foreach ($rows as $row) {
            $item = array();
            $item[0] = $row['city'];
            $item[1] = $row['city'];
            $item[2] = null;
            $parentArray['items'][] = $item;
        }
    }
}
