<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Service;

/**
 * Builds the CSV export of clubs in memory. The values are entered by
 * frontend users, so values which a spreadsheet application would
 * interpret as formula are prefixed with a single quote (CSV injection).
 */
readonly class CsvExportService
{
    private const DELIMITER = ';';

    private const ENCLOSURE = '\'';

    private const FORMULA_PREFIX = '\'';

    private const FORMULA_START_CHARACTERS = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    public function createCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            return '';
        }

        foreach ($rows as $row) {
            fputcsv($stream, array_map($this->neutralizeFormula(...), $row), self::DELIMITER, self::ENCLOSURE, '');
        }

        rewind($stream);
        $csv = (string)stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    public function neutralizeFormula(mixed $value): string
    {
        $value = is_scalar($value) ? (string)$value : '';

        return $value !== '' && in_array($value[0], self::FORMULA_START_CHARACTERS, true)
            ? self::FORMULA_PREFIX . $value
            : $value;
    }
}
