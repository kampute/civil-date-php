<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date parse.
 */
final class JalaliDateParseTest extends TestCase
{
    /**
     * Tests parses valid inputs.
     *
     * @param array<mixed> $expected Test data.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('validParseInputsProvider')]
    public function testParsesValidInputs(string $input, string $pattern, array $expected, array $options = []): void
    {
        $parsed = JalaliDate::parse($input, $pattern, $options);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for valid parse inputs tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function validParseInputsProvider(): array
    {
        return [
            'Y/n/j' => ['1402/6/31', 'Y/n/j', [1402, 6, 31]],
            'Cardinal year' => ['هزار و چهارصد و دو/6/31', 'V/n/j', [1402, 6, 31]],
            'Persian words only with separators' => ['یک هزار و چهارصد و دو/شهریور/سی و یکم', 'V/F/J', [1402, 6, 31]],
            'English words only with separators' => ['one thousand four hundred two/Shahrivar/thirty-first', 'V/F/J', [1402, 6, 31], ['locale' => 'en']],
            'English words only without separators' => ['one thousand four hundred two Shahrivar thirty-first', 'V F J', [1402, 6, 31], ['locale' => 'en']],
            'Y/n/j with padded month/day' => ['1402/06/31', 'Y/n/j', [1402, 6, 31]],
            'Persian digits' => ['۱۴۰۲/۰۶/۳۱', 'Y/n/j', [1402, 6, 31]],
            'Mixed digits' => ['14۰2/6/۳1', 'Y/n/j', [1402, 6, 31]],
            'Arabic-Indic digits' => ['١٤٠٢/٠٦/٣١', 'Y/n/j', [1402, 6, 31]],
            'Custom d-m-Y' => ['31-06-1402', 'd-m-Y', [1402, 6, 31]],
            'Month name' => ['31 شهریور 1402', 'j F Y', [1402, 6, 31]],
            'English month name' => ['31 Shahrivar 1402', 'j F Y', [1402, 6, 31], ['locale' => 'en']],
            'Day ordinal name' => ['سی و یکم شهریور 1402', 'J F Y', [1402, 6, 31]],
            'Day ordinal from end' => ['آخر شهریور 1402', 'J F Y', [1402, 6, 31]],
            'English day ordinal from end' => ['last day of Shahrivar 1402', 'J "day of" F Y', [1402, 6, 31], ['locale' => 'en']],
            'Abbreviated month name fallback' => ['31 شهریور 1402', 'j M Y', [1402, 6, 31]],
            'Day name' => ['جمعه 1402/6/31', 'l Y/n/j', [1402, 6, 31]],
            'Abbreviated day name fallback' => ['جمعه 1402/6/31', 'D Y/n/j', [1402, 6, 31]],
            'Quarter ordinal name' => ['دوم 1402/6/31', 'q Y/n/j', [1402, 6, 31]],
            'English quarter ordinal name' => ['second 1402/6/31', 'q Y/n/j', [1402, 6, 31], ['locale' => 'en']],
            'Season name' => ['تابستان 1402/6/31', 'Q Y/n/j', [1402, 6, 31]],
            'English season name' => ['Summer 1402/6/31', 'Q Y/n/j', [1402, 6, 31], ['locale' => 'en']],
            'Era name' => ['خورشیدی 1402/6/31', 'C Y/n/j', [1402, 6, 31]],
            'Persian abbreviated era name fallback' => ['خورشیدی 1402/6/31', 'E Y/n/j', [1402, 6, 31]],
            'English era name' => ['Solar Hijri 1402/6/31', 'C Y/n/j', [1402, 6, 31], ['locale' => 'en']],
            'English abbreviated era name' => ['SH 1402/6/31', 'E Y/n/j', [1402, 6, 31], ['locale' => 'en']],
            'Day of year ordinal name' => ['روز صد و هشتاد و ششم سال ۱۴۰۲', 'روز R سال Y', [1402, 6, 31]],
            'Day of year ordinal from end' => ['روز آخر سال ۱۴۰۲', 'روز R سال Y', [1402, 12, 29]],
            'English day of year ordinal from end' => ['second-to-last day of 1402', 'R "day of" Y', [1402, 12, 28], ['locale' => 'en']],
            'Week of year ordinal name' => ['1402 بیست و هفتمین جمعه', 'Y K l', [1402, 6, 31]],
            'Week of year ordinal from end' => ['1402 آخرین جمعه', 'Y K l', [1402, 12, 25]],
            'Week of month ordinal name' => ['1402/6/پنجمین جمعه', 'Y/n/k l', [1402, 6, 31]],
            'Week of month ordinal from end' => ['1402/6/آخرین جمعه', 'Y/n/k l', [1402, 6, 31]],
            'English week of month ordinal from end' => ['last Friday of Shahrivar 1402', 'k l "of" F Y', [1402, 6, 31], ['locale' => 'en']],
            'Quoted literals' => ['Year: 1402, Month: 6, Day: 31', '"Year: "Y", Month: "n", Day: "j', [1402, 6, 31]],
            'Single-quoted literals' => ['Date: 1402-6-31', "'Date: 'Y-n-j", [1402, 6, 31]],
            'Backslash escapes token characters' => ['Y: 1402, m: 6, d: 31', '\\Y: Y, \\m: n, \\d: j', [1402, 6, 31]],
            'Whitespace trimmed and collapsed' => ['  1402   /   6   /   31  ', 'Y / n / j', [1402, 6, 31]],
            'RLM marker stripped' => ["\u{200F}۱۴۰۲/۰۶/۳۱", 'Y/n/j', [1402, 6, 31]],
            'Multiple bidi markers stripped' => ["\u{200F}\u{200E}1402\u{061C}/6/31", 'Y/n/j', [1402, 6, 31]],
            'Pattern whitespace normalized' => ['1402-06-31', '  Y-m-d  ', [1402, 6, 31]],
            'Scoped Gregorian validation' => ['1402/6/31 2023-9-22', 'Y/n/j [gregorian:Y-n-j]', [1402, 6, 31]],
            'Scoped Gregorian month name validation' => ['1402/6/31 سپتامبر 22, 2023', 'Y/n/j [gregorian:F j, Y]', [1402, 6, 31]],
            'Scoped Gregorian era name validation' => ['1402/6/31 میلادی 2023-9-22', 'Y/n/j [gregorian:C Y-n-j]', [1402, 6, 31]],
            'Scoped Gregorian abbreviated era name validation' => ['1402/6/31 میلادی 2023-9-22', 'Y/n/j [gregorian:E Y-n-j]', [1402, 6, 31]],
            'Explicit Jalali scope' => ['1402/6/31', '[jalali:Y/n/j]', [1402, 6, 31]],
            'Case insensitive Jalali scope' => ['1402/6/31', '[JaLaLi:Y/n/j]', [1402, 6, 31]],
            'Scoped Gregorian quarter validation' => ['1402/6/31 سه ماهه سوم', 'Y/n/j [gregorian:سه ماهه q]', [1402, 6, 31]],
            'English locale' => ['Friday 31 Shahrivar 1402 Solar Hijri', 'l j F Y C', [1402, 6, 31], ['locale' => 'en']],
            'English abbreviations' => ['Fri 31 Sha 1402 SH', 'D j M Y E', [1402, 6, 31], ['locale' => 'en']],
            'English scoped Gregorian validation' => ['1402/6/31 Friday 22 September 2023', 'Y/n/j [gregorian:l j F Y]', [1402, 6, 31], ['locale' => 'en']],
            'Persian name normalization' => ['31 شهریور 1402 جلالي', 'j F Y C', [1402, 6, 31]],
            'Non-ambiguous Persian words only without separators' => ['سی‌و‌یکم شهریور یک‌هزار‌و‌چهارصد‌و‌دو', 'J F V', [1402, 6, 31]],
        ];
    }

    /**
     * Tests parses month names.
     */
    #[DataProvider('monthNameParseProvider')]
    public function testParsesMonthNames(string $monthName, int $expectedMonth): void
    {
        $parsed = JalaliDate::parse("15 {$monthName} 1402", 'j F Y');

        self::assertSame([1402, $expectedMonth, 15], $parsed->toArray());
    }

    /**
     * Provides data for month name parse tests.
     *
     * @return array<array{string,int}> Provider data sets.
     */
    public static function monthNameParseProvider(): array
    {
        return [
            'Farvardin' => ['فروردین', 1],
            'Ordibehesht' => ['اردیبهشت', 2],
            'Khordad' => ['خرداد', 3],
            'Tir' => ['تیر', 4],
            'Mordad' => ['مرداد', 5],
            'Shahrivar' => ['شهریور', 6],
            'Mehr' => ['مهر', 7],
            'Aban' => ['آبان', 8],
            'Azar' => ['آذر', 9],
            'Dey' => ['دی', 10],
            'Bahman' => ['بهمن', 11],
            'Esfand' => ['اسفند', 12],
        ];
    }

    /**
     * Tests parses and validates day names.
     */
    #[DataProvider('dayNameParseProvider')]
    public function testParsesAndValidatesDayNames(string $dayName, int $year, int $month, int $day): void
    {
        $parsed = JalaliDate::parse("{$dayName} {$year}/{$month}/{$day}", 'l Y/n/j');

        self::assertSame([$year, $month, $day], $parsed->toArray());
    }

    /**
     * Provides data for day name parse tests.
     *
     * @return array<array{string,int,int,int}> Provider data sets.
     */
    public static function dayNameParseProvider(): array
    {
        return [
            'Saturday' => ['شنبه', 1402, 6, 25],
            'Sunday' => ['یکشنبه', 1402, 6, 26],
            'Monday' => ['دوشنبه', 1402, 6, 27],
            'Tuesday' => ['سه‌شنبه', 1402, 6, 28],
            'Wednesday' => ['چهارشنبه', 1402, 6, 29],
            'Thursday' => ['پنجشنبه', 1402, 6, 30],
            'Friday' => ['جمعه', 1402, 6, 31],
        ];
    }

    /**
     * Tests parses and validates quarter ordinal names.
     */
    #[DataProvider('quarterNameParseProvider')]
    public function testParsesAndValidatesQuarterOrdinalNames(string $quarterName, int $month): void
    {
        $parsed = JalaliDate::parse("{$quarterName} 1402/{$month}/15", 'q Y/n/j');

        self::assertSame([1402, $month, 15], $parsed->toArray());
    }

    /**
     * Provides data for quarter name parse tests.
     *
     * @return array<array{string,int}> Provider data sets.
     */
    public static function quarterNameParseProvider(): array
    {
        return [
            'First' => ['یکم', 1],
            'Second' => ['دوم', 4],
            'Third' => ['سوم', 7],
            'Fourth' => ['چهارم', 10],
        ];
    }

    /**
     * Tests parses and validates season names.
     */
    #[DataProvider('seasonNameParseProvider')]
    public function testParsesAndValidatesSeasonNames(string $seasonName, int $month): void
    {
        $parsed = JalaliDate::parse("{$seasonName} 1402/{$month}/15", 'Q Y/n/j');

        self::assertSame([1402, $month, 15], $parsed->toArray());
    }

    /**
     * Provides data for season name parse tests.
     *
     * @return array<array{string,int}> Provider data sets.
     */
    public static function seasonNameParseProvider(): array
    {
        return [
            'Spring (Bahar)' => ['بهار', 1],
            'Summer (Tabestan)' => ['تابستان', 4],
            'Autumn (Paeez)' => ['پاییز', 7],
            'Winter (Zemestan)' => ['زمستان', 10],
        ];
    }

    /**
     * Tests expands two digit jalali years around reference date.
     */
    #[DataProvider('twoDigitYearExpansionProvider')]
    public function testExpandsTwoDigitJalaliYearsAroundReferenceDate(int $refYear, string $input, int $expectedYear): void
    {
        JalaliDate::setTestToday(new JalaliDate($refYear, 1, 1));
        try {
            self::assertSame([$expectedYear, 1, 1], JalaliDate::parse($input, 'y/n/j')->toArray());
        } finally {
            JalaliDate::setTestToday(null);
        }
    }

    /**
     * Provides data for two digit year expansion tests.
     *
     * @return array<array{int,string,int}> Provider data sets.
     */
    public static function twoDigitYearExpansionProvider(): array
    {
        return [
            'Ref 1405: 00 -> 1400' => [1405, '00/1/1', 1400],
            'Ref 1405: 02 -> 1402' => [1405, '02/1/1', 1402],
            'Ref 1405: 10 -> 1410' => [1405, '10/1/1', 1410],
            'Ref 1405: 92 -> 1392' => [1405, '92/1/1', 1392],
            'Ref 1405: 99 -> 1399' => [1405, '99/1/1', 1399],
            'Ref 1492: 05 -> 1505' => [1492, '05/1/1', 1505],
            'Ref 1492: 88 -> 1488' => [1492, '88/1/1', 1488],
            'Ref 1492: 92 -> 1492' => [1492, '92/1/1', 1492],
            'Ref 1492: 99 -> 1499' => [1492, '99/1/1', 1499],
        ];
    }

    /**
     * Tests expands two digit gregorian years around reference date.
     */
    public function testExpandsTwoDigitGregorianYearsAroundReferenceDate(): void
    {
        JalaliDate::setTestToday(new JalaliDate(1403, 1, 1)); // Gregorian 2024-03-20
        try {
            $parsed = JalaliDate::parse('1403/1/1 24-3-20', 'Y/n/j [gregorian:y-n-j]');
            self::assertSame([1403, 1, 1], $parsed->toArray());
        } finally {
            JalaliDate::setTestToday(null);
        }
    }

    /**
     * Tests skip validation allows mismatched validation tokens.
     */
    public function testSkipValidationAllowsMismatchedValidationTokens(): void
    {
        $parsed = JalaliDate::parse(
            'شنبه اولین زمستان 1402/6/31 2024-1-1',
            'l q Q Y/n/j [gregorian:Y-n-j]',
            ['skipValidation' => true]
        );

        self::assertSame([1402, 6, 31], $parsed->toArray());
    }

    /**
     * Tests preserve whitespace requires exact whitespace.
     */
    public function testPreserveWhitespaceRequiresExactWhitespace(): void
    {
        $this->expectException(DateParseException::class);

        JalaliDate::parse(' 1402/6/31 ', 'Y/n/j', ['preserveWhitespace' => true]);
    }

    /**
     * Tests preserve whitespace allows exact controls.
     */
    public function testPreserveWhitespaceAllowsExactControls(): void
    {
        self::assertSame(
            [1402, 6, 31],
            JalaliDate::parse("\u{200F}1402/6/31", "\u{200F}Y/n/j", ['preserveBidiControls' => true])->toArray()
        );
    }

    /**
     * Tests rejects unsupported calendar scope.
     */
    public function testRejectsUnsupportedCalendarScope(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JalaliDate::parse('1402/6/31 1445/3/7', 'Y/n/j [unknown:Y/n/j]');
    }

    /**
     * Tests parses day of year.
     *
     * @param array<mixed> $expected Test data.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('dayOfYearParseProvider')]
    public function testParsesDayOfYear(string $input, string $pattern, array $expected, array $options = []): void
    {
        $parsed = JalaliDate::parse($input, $pattern, $options);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for day of year parse tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function dayOfYearParseProvider(): array
    {
        return [
            'First day of year' => ['1402/یکم', 'Y/R', [1402, 1, 1]],
            'Day 31 (end of month 1)' => ['1402/سی و یکم', 'Y/R', [1402, 1, 31]],
            'Day 32 (start of month 2)' => ['1402/سی و دوم', 'Y/R', [1402, 2, 1]],
            'Day 62 (end of month 2)' => ['1402/شصت و دوم', 'Y/R', [1402, 2, 31]],
            'Day 186 (end of month 6)' => ['1402/صد و هشتاد و ششم', 'Y/R', [1402, 6, 31]],
            'Day 187 (start of month 7)' => ['1402/صد و هشتاد و هفتم', 'Y/R', [1402, 7, 1]],
            'Day 216 (end of month 7)' => ['1402/دویست و شانزدهم', 'Y/R', [1402, 7, 30]],
            'Day 365 (non-leap year end)' => ['1402/سیصد و شصت و پنجم', 'Y/R', [1402, 12, 29]],
            'Day 366 (leap year end)' => ['1403/سیصد و شصت و ششم', 'Y/R', [1403, 12, 30]],
            'English day 186' => ['1402/one hundred eighty-sixth', 'Y/R', [1402, 6, 31], ['locale' => 'en']],
            'English day 365 (non-leap year end)' => ['1402/three hundred sixty-fifth', 'Y/R', [1402, 12, 29], ['locale' => 'en']],
            'English day 366 (leap year end)' => ['1403/three hundred sixty-sixth', 'Y/R', [1403, 12, 30], ['locale' => 'en']],
            'Last day of non-leap year' => ['1402/آخر', 'Y/R', [1402, 12, 29]],
            'Second day from end of leap year' => ['1403/دوم از آخر', 'Y/R', [1403, 12, 29]],
            'Negative year' => ['-100/پنجاهم', 'Y/R', [-100, 2, 19]],
        ];
    }

    /**
     * Tests parses day of week in year.
     *
     * @param array<mixed> $expected Test data.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('dayOfWeekInYearParseProvider')]
    public function testParsesDayOfWeekInYear(string $input, string $pattern, array $expected, array $options = []): void
    {
        $parsed = JalaliDate::parse($input, $pattern, $options);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for day of week in year parse tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function dayOfWeekInYearParseProvider(): array
    {
        return [
            'Week 1 Wednesday (Nowruz)' => ['1403 اولین چهارشنبه', 'Y K l', [1403, 1, 1]],
            'Week 27 Friday' => ['1402 بیست و هفتمین جمعه', 'Y K l', [1402, 6, 31]],
            'English week 27 Friday' => ['twenty-seventh Friday of 1402', 'K l "of" Y', [1402, 6, 31], ['locale' => 'en']],
            '52nd Saturday' => ['1403 پنجاه و دومین شنبه', 'Y K l', [1403, 12, 25]],
            'English week 53 Thursday' => ['fifty-third Thursday of 1403', 'K l "of" Y', [1403, 12, 30], ['locale' => 'en']],
            'Last Thursday in leap year' => ['1403 آخرین پنجشنبه', 'Y K l', [1403, 12, 30]],
            'English last Thursday in leap year' => ['last Thursday of 1403', 'K l "of" Y', [1403, 12, 30], ['locale' => 'en']],
            'With different separators' => ['1403/بیست و هفتمین/شنبه', 'Y/K/l', [1403, 6, 31]],
            'Week 10 Monday' => ['1405 دهمین دوشنبه', 'Y K l', [1405, 3, 4]],
        ];
    }

    /**
     * Tests parses day of week in month.
     *
     * @param array<mixed> $expected Test data.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('dayOfWeekInMonthParseProvider')]
    public function testParsesDayOfWeekInMonth(string $input, string $pattern, array $expected, array $options = []): void
    {
        $parsed = JalaliDate::parse($input, $pattern, $options);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for day of week in month parse tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function dayOfWeekInMonthParseProvider(): array
    {
        return [
            'First week of month' => ['1402/6/اولین چهارشنبه', 'Y/n/k l', [1402, 6, 1]],
            'Fifth week of month' => ['1402/6/پنجمین جمعه', 'Y/n/k l', [1402, 6, 31]],
            'English fifth week of month' => ['fifth Friday of Shahrivar 1402', 'k l "of" F Y', [1402, 6, 31], ['locale' => 'en']],
            'Second Saturday in 1403/1' => ['1403/1/دومین شنبه', 'Y/n/k l', [1403, 1, 11]],
            'Third Monday in 1405/3' => ['1405/3/سومین دوشنبه', 'Y/n/k l', [1405, 3, 18]],
            'English fifth week at leap year end' => ['fifth Thursday of Esfand 1403', 'k l "of" F Y', [1403, 12, 30], ['locale' => 'en']],
            'Last Thursday in month' => ['1403/12/آخرین پنجشنبه', 'Y/n/k l', [1403, 12, 30]],
            'English last Thursday in month' => ['last Thursday of Esfand 1403', 'k l "of" F Y', [1403, 12, 30], ['locale' => 'en']],
            'With space separator' => ['1402/6 پنجمین جمعه', 'Y/n k l', [1402, 6, 31]],
        ];
    }

    /**
     * Tests parses negative years.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('negativeYearParseProvider')]
    public function testParsesNegativeYears(string $input, string $pattern, array $expected): void
    {
        $parsed = JalaliDate::parse($input, $pattern);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for negative year parse tests.
     *
     * @return array<array{string,string,array<mixed>}> Provider data sets.
     */
    public static function negativeYearParseProvider(): array
    {
        return [
            'Negative year with full format' => ['-100/6/15', 'Y/n/j', [-100, 6, 15]],
            'Negative year with padding' => ['-0100/06/15', 'Y/m/d', [-100, 6, 15]],
            'Year -1' => ['-1/1/1', 'Y/n/j', [-1, 1, 1]],
            'Year -1 end' => ['-1/12/29', 'Y/n/j', [-1, 12, 29]],
            'Persian digits negative' => ['-۱۰۰/۶/۱۵', 'Y/n/j', [-100, 6, 15]],
        ];
    }

    /**
     * Tests parses leap year dates.
     *
     * @param array<mixed> $expected Test data.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('leapYearParseProvider')]
    public function testParsesLeapYearDates(string $input, string $pattern, array $expected, array $options = []): void
    {
        $parsed = JalaliDate::parse($input, $pattern, $options);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for leap year parse tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function leapYearParseProvider(): array
    {
        return [
            'Leap year last day' => ['1403/12/30', 'Y/n/j', [1403, 12, 30]],
            'Leap year day 366' => ['1403/سیصد و شصت و ششم', 'Y/R', [1403, 12, 30]],
            'English leap year last day words' => ['one thousand four hundred three/Esfand/thirtieth', 'V/F/J', [1403, 12, 30], ['locale' => 'en']],
            'Non-leap year last day' => ['1402/12/29', 'Y/n/j', [1402, 12, 29]],
            'Non-leap year day 365' => ['1402/سیصد و شصت و پنجم', 'Y/R', [1402, 12, 29]],
            'English non-leap year last day words' => ['one thousand four hundred two/Esfand/twenty-ninth', 'V/F/J', [1402, 12, 29], ['locale' => 'en']],
        ];
    }

    /**
     * Tests parses edge cases.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('edgeCaseParseProvider')]
    public function testParsesEdgeCases(string $input, string $pattern, array $expected): void
    {
        $parsed = JalaliDate::parse($input, $pattern);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for edge case parse tests.
     *
     * @return array<array{string,string,array<mixed>}> Provider data sets.
     */
    public static function edgeCaseParseProvider(): array
    {
        return [
            'Year boundary MIN_YEAR' => [JalaliDate::MIN_YEAR . '/1/1', 'Y/n/j', [JalaliDate::MIN_YEAR, 1, 1]],
            'Year boundary MAX_YEAR' => [JalaliDate::MAX_YEAR . '/1/1', 'Y/n/j', [JalaliDate::MAX_YEAR, 1, 1]],
            'First day first month' => ['1402/1/1', 'Y/n/j', [1402, 1, 1]],
            'Last day last month leap' => ['1403/12/30', 'Y/n/j', [1403, 12, 30]],
            'Last day last month non-leap' => ['1402/12/29', 'Y/n/j', [1402, 12, 29]],
            'Month 7 transition' => ['1402/7/1', 'Y/n/j', [1402, 7, 1]],
            'Month 7 last day' => ['1402/7/30', 'Y/n/j', [1402, 7, 30]],
        ];
    }

    /**
     * Tests parses complex patterns.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('complexPatternParseProvider')]
    public function testParsesComplexPatterns(string $input, string $pattern, array $expected): void
    {
        $parsed = JalaliDate::parse($input, $pattern);

        self::assertSame($expected, $parsed->toArray());
    }

    /**
     * Provides data for complex pattern parse tests.
     *
     * @return array<array{string,string,array<mixed>}> Provider data sets.
     */
    public static function complexPatternParseProvider(): array
    {
        return [
            'Day-of-year with literals' => ['سال 1402 روز صد و هشتاد و ششم', '"سال "Y" روز "R', [1402, 6, 31]],
        ];
    }

    /**
     * Tests rejects invalid day of year.
     */
    #[DataProvider('invalidDayOfYearParseProvider')]
    public function testRejectsInvalidDayOfYear(string $input, string $pattern): void
    {
        $this->expectException(DateParseException::class);
        JalaliDate::parse($input, $pattern);
    }

    /**
     * Provides data for invalid day of year parse tests.
     *
     * @return array<array{string,string}> Provider data sets.
     */
    public static function invalidDayOfYearParseProvider(): array
    {
        return [
            'Day 0' => ['1402/صفر', 'Y/R'],
            'Day 366 in non-leap' => ['1402/سیصد و شصت و ششم', 'Y/R'],
            'Day 367 in leap' => ['1403/سیصد و شصت و هفتم', 'Y/R'],
            'Day 400' => ['1402/چهارصدم', 'Y/R'],
            'Persian digits' => ['۱۴۰۲/۱۸۶', 'Y/R'],
            'Latin digits' => ['1402/001', 'Y/R'],
        ];
    }

    /**
     * Tests rejects invalid week of year.
     */
    #[DataProvider('invalidWeekOfYearParseProvider')]
    public function testRejectsInvalidWeekOfYear(string $input, string $pattern): void
    {
        $this->expectException(DateParseException::class);
        JalaliDate::parse($input, $pattern);
    }

    /**
     * Provides data for invalid week of year parse tests.
     *
     * @return array<array{string,string}> Provider data sets.
     */
    public static function invalidWeekOfYearParseProvider(): array
    {
        return [
            'Week 0' => ['1402 صفر شنبه', 'Y K l'],
            'Week 54' => ['1402 پنجاه و چهارمین شنبه', 'Y K l'],
            'Week beyond year boundary' => ['1403 پنجاه و چهارمین شنبه', 'Y K l'],
            'Persian digits' => ['۱۴۰۳ ۲۷ جمعه', 'Y K l'],
        ];
    }

    /**
     * Tests rejects invalid week of month.
     */
    #[DataProvider('invalidWeekOfMonthParseProvider')]
    public function testRejectsInvalidWeekOfMonth(string $input, string $pattern): void
    {
        $this->expectException(DateParseException::class);
        JalaliDate::parse($input, $pattern);
    }

    /**
     * Provides data for invalid week of month parse tests.
     *
     * @return array<array{string,string}> Provider data sets.
     */
    public static function invalidWeekOfMonthParseProvider(): array
    {
        return [
            'Week 0' => ['1402/6/صفر شنبه', 'Y/n/k l'],
            'Week 6 beyond month' => ['1402/6/ششمین شنبه', 'Y/n/k l'],
            'Week outside month boundary' => ['1402/1/پنجمین جمعه 1402/6/31', 'Y/n/k l Y/n/j'],
            'Persian digits' => ['۱۴۰۲/۶/۵ جمعه', 'Y/n/k l'],
        ];
    }

    /**
     * Tests rejects invalid inputs.
     *
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('parseErrorsProvider')]
    public function testRejectsInvalidInputs(string $input, string $pattern = 'Y/n/j', array $options = []): void
    {
        $this->expectException(DateParseException::class);

        JalaliDate::parse($input, $pattern, $options);
    }

    /**
     * Provides data for parse errors tests.
     *
     * @return array<array{string,1?:string,2?:array<mixed>}> Provider data sets.
     */
    public static function parseErrorsProvider(): array
    {
        return [
            'Empty string' => [''],
            'Incomplete date' => ['1402/06'],
            'Extra text' => ['1402/06/31 extra'],
            'Wrong separator' => ['1402-06-31'],
            'Unknown month name' => ['31 Unknown 1402', 'j F Y'],
            'English month without English locale' => ['31 Shahrivar 1402', 'j F Y'],
            'Persian month with English locale' => ['31 شهریور 1402', 'j F Y', ['locale' => 'en']],
            'Invalid month 13' => ['1402/13/01'],
            'Invalid day for month' => ['1402/07/31'],
            'Non-leap year day' => ['1402/12/30'],
            'Pattern mismatch' => ['Year: 1402, Month: 6, Day: 31', '"Wrong: "Y", Month: "n", Day: "j'],
            'Incomplete pattern' => ['1402/06/31', 'Y/m'],
            'Wrong token type' => ['1402/06/31', 'Y/m/l'],
            'Invalid day name' => ['InvalidDay 1402/6/31', 'l Y/n/j'],
            'Invalid quarter ordinal name' => ['InvalidQuarter 1402/6/31', 'q Y/n/j'],
            'Invalid season name' => ['InvalidSeason 1402/6/31', 'Q Y/n/j'],
            'Invalid era name' => ['InvalidEra 1402/6/31', 'C Y/n/j'],
            'Only era name' => ['Solar Hijri', 'C', ['locale' => 'en']],
            'Only abbreviated era name' => ['SH', 'E', ['locale' => 'en']],
            'Only season name' => ['Summer', 'Q', ['locale' => 'en']],
            'Only quarter name' => ['second', 'q', ['locale' => 'en']],
            'Only day name' => ['Friday', 'l', ['locale' => 'en']],
            'Conflicting repeated year' => ['1402/1403/6/31', 'Y/Y/n/j'],
            'Ambiguous Persian words only without separators' => ['سی و یکم شهریور یک هزار و چهارصد و دو', 'J F V'],
            'Wrong day name' => ['شنبه 1402/6/31', 'l Y/n/j'],
            'Wrong quarter ordinal name' => ['یکم 1402/6/31', 'q Y/n/j'],
            'Wrong English quarter ordinal name' => ['first 1402/6/31', 'q Y/n/j', ['locale' => 'en']],
            'Wrong season name' => ['بهار 1402/6/31', 'Q Y/n/j'],
            'Wrong English season name' => ['Spring 1402/6/31', 'Q Y/n/j', ['locale' => 'en']],
            'Wrong era name' => ['میلادی 1402/6/31', 'C Y/n/j'],
            'Wrong English era name' => ['Common Era 1402/6/31', 'C Y/n/j', ['locale' => 'en']],
            'Wrong English abbreviated era name' => ['CE 1402/6/31', 'E Y/n/j', ['locale' => 'en']],
            'Wrong Gregorian year' => ['1402/6/31 2024-9-22', 'Y/n/j [gregorian:Y-n-j]'],
            'Wrong Gregorian month' => ['1402/6/31 2023-10-22', 'Y/n/j [gregorian:Y-n-j]'],
            'Wrong Gregorian day' => ['1402/6/31 2023-9-23', 'Y/n/j [gregorian:Y-n-j]'],
            'Invalid Gregorian month name' => ['1402/6/31 Unknown 22, 2023', 'Y/n/j [gregorian:F j, Y]'],
            'Wrong scoped Gregorian era name' => ['1402/6/31 خورشیدی 2023-9-22', 'Y/n/j [gregorian:C Y-n-j]'],
            'Invalid year with letters' => ['14a2/6/31'],
            'Invalid month with letters' => ['1402/0a/31'],
            'Invalid day with letters' => ['1402/6/3a'],
            'Year with spaces' => ['14 02/6/31'],
            'Two-digit year out of range' => ['100/1/1', 'y/n/j'],
            'Wrong day of year' => ['1402/صد و هشتاد و پنجم 1402/6/31', 'Y/R Y/n/j'],
            'Wrong English day of year' => ['1402/one hundred eighty-fifth 1402/6/31', 'Y/R Y/n/j', ['locale' => 'en']],
            'Wrong week of year' => ['1402 بیست و ششمین جمعه 1402/6/31', 'Y K l Y/n/j'],
            'Wrong English week of year' => ['twenty-sixth Friday of 1402 1402/6/31', 'K l "of" Y Y/n/j', ['locale' => 'en']],
            'Wrong week of month' => ['1402/6/چهارمین جمعه 1402/6/31', 'Y/n/k l Y/n/j'],
            'Wrong English week of month' => ['fourth Friday of Shahrivar 1402 1402/6/31', 'k l "of" F Y Y/n/j', ['locale' => 'en']],
            'Only scoped Gregorian fields' => ['2023-9-22', '[gregorian:Y-n-j]'],
        ];
    }

    /**
     * Tests rejects unknown locale.
     */
    public function testRejectsUnknownLocale(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JalaliDate::parse('1402/6/31', 'Y/n/j', ['locale' => 'unknown']);
    }

    /**
     * Tests rejects invalid locale option.
     */
    public function testRejectsInvalidLocaleOption(): void
    {
        $this->expectException(InvalidArgumentException::class);

        JalaliDate::parse('1402/6/31', 'Y/n/j', ['locale' => 'invalid']);
    }
}
