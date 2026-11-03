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
 * Builds the CSV export of clubs in memory according to RFC 4180: fields
 * separated by comma, enclosed in double quotes, double quotes escaped by
 * doubling and lines terminated by CRLF. The values are entered by
 * frontend users, so values which a spreadsheet application would
 * interpret as formula are prefixed with a single quote (CSV injection).
 */
readonly class CsvExportService
{
    private const DELIMITER = ',';

    private const ENCLOSURE = '"';

    private const LINE_BREAK = "\r\n";

    private const FORMULA_PREFIX = '\'';

    private const FORMULA_START_CHARACTERS = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    public function createCsv(array $rows): string
    {
        return implode('', array_map($this->createLine(...), $rows));
    }

    public function neutralizeFormula(mixed $value): string
    {
        $value = is_scalar($value) ? (string)$value : '';

        return $value !== '' && in_array($value[0], self::FORMULA_START_CHARACTERS, true)
            ? self::FORMULA_PREFIX . $value
            : $value;
    }

    /**
     * @param array<int, mixed> $row
     */
    private function createLine(array $row): string
    {
        $fields = array_map(
            fn(mixed $value): string => $this->encloseField($this->neutralizeFormula($value)),
            $row,
        );

        return implode(self::DELIMITER, $fields) . self::LINE_BREAK;
    }

    /**
     * Every field is enclosed, so line breaks, commas and leading single
     * quotes of neutralized formulas stay part of the value.
     */
    private function encloseField(string $value): string
    {
        return self::ENCLOSURE
            . str_replace(self::ENCLOSURE, self::ENCLOSURE . self::ENCLOSURE, $value)
            . self::ENCLOSURE;
    }
}
