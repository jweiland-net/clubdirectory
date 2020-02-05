<?php
declare(strict_types = 1);
namespace JWeiland\Clubdirectory\UserFunc;

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

/**
 * Update the Label of address table to show more information about that record
 */
class AltLabelForAddressTableUserFunc
{
    public function setAddressLabel(array &$parameters = [])
    {
        $club = BackendUtility::getRecord('tx_clubdirectory_domain_model_club', $parameters['row']['club']);
        $parameters['title'] = sprintf(
            '%s (%d) - %s',
            $club['title'],
            $club['uid'],
            $parameters['row']['title']
        );
    }
}
