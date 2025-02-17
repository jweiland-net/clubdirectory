<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\UserFunc;

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Update the Label of address table to show more information about that record
 */
class AltLabelForAddressTableUserFunc
{
    public function setAddressLabel(array &$parameters = []): void
    {
        // Set title as a fallback. It was cleared by TYPO3 just before this UserFunc was called.
        $parameters['title'] = is_array($parameters['row']['title'])
            ? (string)($parameters['row']['title'][0] ?? '')
            : (string)($parameters['row']['title'] ?? '');

        // Column "club" can be empty, if hidden by an Integrator or if shown as inline in club record
        if ((int)($parameters['row']['club'] ?? 0) > 0) {
            $club = BackendUtility::getRecord('tx_clubdirectory_domain_model_club', $parameters['row']['club']);
            if ($parameters['title'] !== '') {
                $parameters['title'] = sprintf(
                    '%s (%d) - %s',
                    $club['title'],
                    $club['uid'],
                    $parameters['title'],
                );
            }
        }
    }
}
