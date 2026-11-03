<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/clubdirectory.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Clubdirectory\Tests\Unit\Service;

use JWeiland\Clubdirectory\Service\CsvExportService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for CsvExportService
 */
class CsvExportServiceTest extends UnitTestCase
{
    protected CsvExportService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CsvExportService();
    }

    protected function tearDown(): void
    {
        unset($this->subject);

        parent::tearDown();
    }

    public static function formulaDataProvider(): array
    {
        return [
            'equals sign' => ['=HYPERLINK("https://example.com")', '\'=HYPERLINK("https://example.com")'],
            'plus sign' => ['+49 761 12345', '\'+49 761 12345'],
            'minus sign' => ['-2+3', '\'-2+3'],
            'at sign' => ['@SUM(A1:A2)', '\'@SUM(A1:A2)'],
            'tab' => ["\t=1+1", "'\t=1+1"],
            'carriage return' => ["\r=1+1", "'\r=1+1"],
        ];
    }

    #[Test]
    #[DataProvider('formulaDataProvider')]
    public function neutralizeFormulaPrefixesFormulaWithSingleQuote(string $value, string $expectedValue): void
    {
        self::assertSame($expectedValue, $this->subject->neutralizeFormula($value));
    }

    public static function regularValueDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'title' => ['Swimming club', 'Swimming club'],
            'email' => ['info@example.com', 'info@example.com'],
            'formula char not at start' => ['A=B', 'A=B'],
            'integer' => [79100, '79100'],
            'null' => [null, ''],
            'array' => [['foo'], ''],
        ];
    }

    #[Test]
    #[DataProvider('regularValueDataProvider')]
    public function neutralizeFormulaKeepsRegularValues(mixed $value, string $expectedValue): void
    {
        self::assertSame($expectedValue, $this->subject->neutralizeFormula($value));
    }

    #[Test]
    public function createCsvReturnsCommaSeparatedEnclosedFieldsWithCrlf(): void
    {
        $csv = $this->subject->createCsv([
            ['Title', 'Email', 'Tel'],
            ['Swimming club', 'info@example.com', '0761 12345'],
        ]);

        self::assertSame(
            '"Title","Email","Tel"' . "\r\n"
            . '"Swimming club","info@example.com","0761 12345"' . "\r\n",
            $csv,
        );
    }

    public static function specialValueDataProvider(): array
    {
        return [
            'comma' => ['Club, Inc.', '"Club, Inc."'],
            'double quotes' => ['Club "Blue"', '"Club ""Blue"""'],
            'single quote' => ['Club \'Blue\'', '"Club \'Blue\'"'],
            'backslash before quote' => ['C:\\"x', '"C:\\""x"'],
            'line feed' => ["Line 1\nLine 2", "\"Line 1\nLine 2\""],
            'CRLF' => ["Line 1\r\nLine 2", "\"Line 1\r\nLine 2\""],
            'umlauts' => ['Müllheimer Ruderverein Öhringen', '"Müllheimer Ruderverein Öhringen"'],
            'empty string' => ['', '""'],
            'null' => [null, '""'],
            'integer' => [79100, '"79100"'],
        ];
    }

    #[Test]
    #[DataProvider('specialValueDataProvider')]
    public function createCsvEnclosesAndEscapesSpecialValues(mixed $value, string $expectedField): void
    {
        self::assertSame($expectedField . "\r\n", $this->subject->createCsv([[$value]]));
    }

    public static function formulaFieldDataProvider(): array
    {
        return [
            'equals sign with quotes' => ['=cmd|\' /C calc\'!A0', '"\'=cmd|\' /C calc\'!A0"'],
            'hyperlink with double quotes' => ['=HYPERLINK("https://example.com")', '"\'=HYPERLINK(""https://example.com"")"'],
            'plus sign' => ['+49 761 12345', '"\'+49 761 12345"'],
            'minus sign' => ['-2+3', '"\'-2+3"'],
            'at sign' => ['@SUM(A1:A2)', '"\'@SUM(A1:A2)"'],
            'tab' => ["\t=1+1", "\"'\t=1+1\""],
            'carriage return' => ["\r=1+1", "\"'\r=1+1\""],
        ];
    }

    #[Test]
    #[DataProvider('formulaFieldDataProvider')]
    public function createCsvEnclosesNeutralizedFormulas(string $value, string $expectedField): void
    {
        self::assertSame(
            '"Title",' . $expectedField . "\r\n",
            $this->subject->createCsv([['Title', $value]]),
        );
    }

    #[Test]
    public function createCsvCanBeParsedWithStrGetCsv(): void
    {
        $row = ['Club "Blue", Müllheim', "Line 1\r\nLine 2", '=1+1', 'info@example.com'];

        $csv = $this->subject->createCsv([$row]);

        self::assertSame(
            ['Club "Blue", Müllheim', "Line 1\r\nLine 2", '\'=1+1', 'info@example.com'],
            str_getcsv(substr($csv, 0, -2), ',', '"', ''),
        );
    }

    #[Test]
    public function createCsvWithoutRowsReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->createCsv([]));
    }
}
