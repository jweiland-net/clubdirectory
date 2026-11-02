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
        $row = is_array($parameters['row'] ?? null) ? $parameters['row'] : [];

        // Set title as a fallback. It was cleared by TYPO3 just before this UserFunc was called.
        $parameters['title'] = $this->getScalarValue($row, 'title');

        // Column "club" can be empty, if hidden by an Integrator or if shown as inline in club record.
        // For new inline records the parent UID may still be a "NEW..." placeholder.
        $clubUid = (int)$this->getScalarValue($row, 'club');
        if ($clubUid <= 0 || $parameters['title'] === '') {
            return;
        }

        $club = BackendUtility::getRecord('tx_clubdirectory_domain_model_club', $clubUid);
        if ($club === null) {
            return;
        }

        $parameters['title'] = sprintf(
            '%s (%d) - %s',
            (string)($club['title'] ?? ''),
            (int)$club['uid'],
            $parameters['title'],
        );
    }

    /**
     * FormEngine (TcaSelectItems) provides select values as array, while
     * BackendUtility::getRecordTitle() provides the raw DB value.
     */
    private function getScalarValue(array $row, string $column): string
    {
        $value = $row[$column] ?? '';
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? (string)$value : '';
    }
}
