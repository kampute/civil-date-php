<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\Locales\English;
use Kampute\CivilDate\Locales\Persian;
use Kampute\CivilDate\Support\DatePattern\Tokens\TwoDigitYear;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests two-digit year token rules.
 */
final class TwoDigitYearTest extends TestCase
{
    /**
     * Tests capture regex matches only digits.
     */
    #[DataProvider('captureRegexProvider')]
    public function testCaptureRegex(string $input, int $expected): void
    {
        $rule = new TwoDigitYear();
        $regex = '~^' . $rule->captureRegex() . '$~u';

        self::assertSame($expected, preg_match($regex, $input));
    }

    /**
     * Provides data for capture regex tests.
     *
     * @return array<string,array{string,int}> Provider data sets.
     */
    public static function captureRegexProvider(): array
    {
        return [
            'Latin digits' => ['25', 1],
            'Persian digits' => ['۲۵', 1],
            'Arabic-Indic digits' => ['٢٥', 1],
            'English words' => ['twenty-five', 0],
            'Signed digits' => ['+25', 0],
        ];
    }

    /**
     * Tests formatting two-digit years across all calendar systems.
     */
    #[DataProvider('formatProvider')]
    public function testFormat(GregorianDate $date, string $expected): void
    {
        $rule = new TwoDigitYear();

        self::assertSame($expected, $rule->format($date, new English()));
    }

    /**
     * Provides data for two-digit year formatting tests.
     *
     * @return array<string, array{GregorianDate, string}> Provider data sets.
     */
    public static function formatProvider(): array
    {
        return [
            'Year 2025' => [new GregorianDate(2025, 3, 21), '25'],
            'Year 2000' => [new GregorianDate(2000, 1, 1), '00'],
            'Year 2005' => [new GregorianDate(2005, 6, 15), '05'],
            'Year 1999' => [new GregorianDate(1999, 12, 31), '99'],
            'Year 1900' => [new GregorianDate(1900, 1, 1), '00'],
            'Year 5' => [new GregorianDate(5, 3, 1), '05'],
            'Year 1' => [new GregorianDate(1, 1, 1), '01'],
            'Year 100' => [new GregorianDate(100, 6, 30), '00'],
            'Year 199' => [new GregorianDate(199, 4, 10), '99'],
            'year -1' => [new GregorianDate(-1, 1, 1), '99'],
            'year -10' => [new GregorianDate(-10, 1, 1), '90'],
            'year -100' => [new GregorianDate(-100, 1, 1), '00'],
            'year -150' => [new GregorianDate(-150, 1, 1), '50'],
            'year -1999' => [new GregorianDate(-1999, 6, 15), '99'],
            'year -2000' => [new GregorianDate(-2000, 1, 1), '00'],
            'year -2001' => [new GregorianDate(-2001, 1, 1), '01'],
        ];
    }

    /**
     * Tests parsing expands two-digit years around the reference year.
     */
    #[DataProvider('parseProvider')]
    public function testParse(string $input, int $referenceYear, int $expected): void
    {
        GregorianDate::setTestToday(new GregorianDate($referenceYear, 1, 1));
        $rule = new TwoDigitYear();

        try {
            self::assertSame($expected, $rule->parse($input, Calendar::Gregorian, new English()));
        } finally {
            GregorianDate::setTestToday(null);
        }
    }

    /**
     * Provides data for two-digit year parsing tests.
     *
     * @return array<string, array{string, int, int}> Provider data sets.
     */
    public static function parseProvider(): array
    {
        return [
            'Parse 25 ref 2026' => ['25', 2026, 2025],
            'Parse 00 ref 2026' => ['00', 2026, 2000],
            'Parse 99 ref 2026' => ['99', 2026, 1999],
            'Parse 50 ref 2026' => ['50', 2026, 2050],
            'Parse 75 ref 2026' => ['75', 2026, 2075],
            'Parse 76 ref 2026' => ['76', 2026, 1976],
            'Parse 00 ref 1990' => ['00', 1990, 2000],
            'Parse 05 ref 1990' => ['05', 1990, 2005],
            'Parse 50 ref 1990' => ['50', 1990, 1950],
            'Parse 95 ref 1990' => ['95', 1990, 1995],
            'Parse 00 ref 1' => ['00', 1, 100],
            'Parse 05 ref 1' => ['05', 1, 5],
            'Parse 00 ref -1' => ['00', -1, -100],
            'Parse 05 ref -1' => ['05', -1, -5],
            'Parse 50 ref -1410' => ['50', -1410, -1450],
        ];
    }

    /**
     * Tests parsing localized digits.
     */
    public function testParseLocalizedDigits(): void
    {
        GregorianDate::setTestToday(new GregorianDate(2026, 1, 1));
        $rule = new TwoDigitYear();

        try {
            self::assertSame(2025, $rule->parse('۲۵', Calendar::Gregorian, new Persian()));
        } finally {
            GregorianDate::setTestToday(null);
        }
    }

    /**
     * Tests parsing rejects values outside 00..99.
     */
    public function testParseRejectsOutOfScopeValue(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('out of scope');

        $rule = new TwoDigitYear();
        $rule->parse('100', Calendar::Gregorian, new English());
    }

    /**
     * Tests parsing rejects number words.
     */
    public function testParseRejectsNumberWords(): void
    {
        $this->expectException(DateParseException::class);
        $this->expectExceptionMessage('Invalid year value');

        $rule = new TwoDigitYear();
        $rule->parse('twenty-five', Calendar::Gregorian, new English());
    }

}
