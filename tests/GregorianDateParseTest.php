<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Tests gregorian date parse.
 */
final class GregorianDateParseTest extends TestCase
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
        self::assertSame($expected, GregorianDate::parse($input, $pattern, $options)->toArray());
    }

    /**
     * Provides data for valid parse inputs tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function validParseInputsProvider(): array
    {
        return [
            'Y-m-d' => ['2025-03-21', 'Y-m-d', [2025, 3, 21]],
            'cardinal year' => ['دو هزار و بیست و پنج-3-21', 'V-n-j', [2025, 3, 21]],
            'Y/n/j' => ['2025/3/21', 'Y/n/j', [2025, 3, 21]],
            'Persian digits' => ['۲۰۲۵-۰۳-۲۱', 'Y-m-d', [2025, 3, 21]],
            'mixed digits' => ['20۲5-0۳-2۱', 'Y-m-d', [2025, 3, 21]],
            'Persian words only with separators' => ['دو هزار و بیست و پنج/مارس/بیست و یکم', 'V/F/J', [2025, 3, 21]],
            'English words only with separators' => ['two thousand twenty-five/March/twenty-first', 'V/F/J', [2025, 3, 21], ['locale' => 'en']],
            'English words only without separators' => ['two thousand twenty-five March twenty-first', 'V F J', [2025, 3, 21], ['locale' => 'en']],
            'month name' => ['21 مارس 2025', 'j F Y', [2025, 3, 21]],
            'English month name' => ['21 March 2025', 'j F Y', [2025, 3, 21], ['locale' => 'en']],
            'day ordinal name' => ['بیست و یکم مارس 2025', 'J F Y', [2025, 3, 21]],
            'day ordinal from end' => ['آخر مارس 2025', 'J F Y', [2025, 3, 31]],
            'English day ordinal from end' => ['last day of March 2025', 'J "day of" F Y', [2025, 3, 31], ['locale' => 'en']],
            'abbreviated month name fallback' => ['21 مارس 2025', 'j M Y', [2025, 3, 21]],
            'day name' => ['جمعه 2025-3-21', 'l Y-n-j', [2025, 3, 21]],
            'abbreviated day name fallback' => ['جمعه 2025-3-21', 'D Y-n-j', [2025, 3, 21]],
            'English day and era names' => ['Friday 21 March 2025 Common Era', 'l j F Y C', [2025, 3, 21], ['locale' => 'en']],
            'English abbreviations' => ['Fri 21 Mar 2025 CE', 'D j M Y E', [2025, 3, 21], ['locale' => 'en']],
            'quarter ordinal name' => ['اول 2025-3-21', 'q Y-n-j', [2025, 3, 21]],
            'English quarter ordinal name' => ['first 2025-3-21', 'q Y-n-j', [2025, 3, 21], ['locale' => 'en']],
            'season name' => ['بهار 2025-3-21', 'Q Y-n-j', [2025, 3, 21]],
            'English season name' => ['Spring 2025-3-21', 'Q Y-n-j', [2025, 3, 21], ['locale' => 'en']],
            'era name' => ['میلادی 2025-3-21', 'C Y-n-j', [2025, 3, 21]],
            'English era name' => ['Common Era 2025-3-21', 'C Y-n-j', [2025, 3, 21], ['locale' => 'en']],
            'English abbreviated era name' => ['CE 2025-3-21', 'E Y-n-j', [2025, 3, 21], ['locale' => 'en']],
            'day of year ordinal name' => ['روز هشتادم سال 2025', 'روز R سال Y', [2025, 3, 21]],
            'English day of year ordinal name' => ['2025/eightieth', 'Y/R', [2025, 3, 21], ['locale' => 'en']],
            'day of year ordinal from end' => ['2025/دوم از آخر', 'Y/R', [2025, 12, 30]],
            'English day of year ordinal from end' => ['second-to-last day of 2025', 'R "day of" Y', [2025, 12, 30], ['locale' => 'en']],
            'week of year ordinal name' => ['2025 دوازدهمین جمعه', 'Y K l', [2025, 3, 21]],
            'English week of year ordinal name' => ['twelfth Friday of 2025', 'K l "of" Y', [2025, 3, 21], ['locale' => 'en']],
            'week of year ordinal from end' => ['2025 آخرین جمعه', 'Y K l', [2025, 12, 26]],
            'English week of year ordinal from end' => ['last Friday of 2025', 'K l "of" Y', [2025, 12, 26], ['locale' => 'en']],
            'week of month ordinal name' => ['2025/3/سومین جمعه', 'Y/n/k l', [2025, 3, 21]],
            'English week of month ordinal name' => ['third Friday of March 2025', 'k l "of" F Y', [2025, 3, 21], ['locale' => 'en']],
            'week of month ordinal from end' => ['2025/3/آخرین جمعه', 'Y/n/k l', [2025, 3, 28]],
            'English week of month ordinal from end' => ['last Friday of March 2025', 'k l "of" F Y', [2025, 3, 28], ['locale' => 'en']],
            'explicit Gregorian scope' => ['2025-03-21', '[gregorian:Y-m-d]', [2025, 3, 21]],
            'case insensitive Gregorian scope' => ['2025-03-21', '[GrEgOrIaN:Y-m-d]', [2025, 3, 21]],
            'scoped Jalali validation' => ['2025-03-21 1404/01/01', 'Y-m-d [jalali:Y/m/d]', [2025, 3, 21]],
            'scoped Jalali era name validation' => ['2025-03-21 خورشیدی 1404/01/01', 'Y-m-d [jalali:C Y/m/d]', [2025, 3, 21]],
            'scoped Jalali abbreviated era name validation' => ['2025-03-21 خورشیدی 1404/01/01', 'Y-m-d [jalali:E Y/m/d]', [2025, 3, 21]],
            'scoped Jalali month name validation' => ['2025-03-21 فروردین 1, 1404', 'Y-m-d [jalali:F j, Y]', [2025, 3, 21]],
            'quoted literals' => ['Year: 2025, Month: 3, Day: 21', '"Year: "Y", Month: "n", Day: "j', [2025, 3, 21]],
            'backslash escapes token characters' => ['Y: 2025, m: 3, d: 21', '\\Y: Y, \\m: n, \\d: j', [2025, 3, 21]],
            'whitespace trimmed and collapsed' => ['  2025   -   03   -   21  ', 'Y - m - d', [2025, 3, 21]],
            'RLM marker stripped' => ["\u{200F}۲۰۲۵-۰۳-۲۱", 'Y-m-d', [2025, 3, 21]],
            'preserve exact controls' => ["\u{200F}2025-3-21", "\u{200F}Y-n-j", [2025, 3, 21], ['preserveBidiControls' => true]],
            'negative year' => ['-100-6-15', 'Y-n-j', [-100, 6, 15]],
            'negative cardinal year' => ['منفی صد-6-15', 'V-n-j', [-100, 6, 15]],
            'leap day' => ['2024-2-29', 'Y-n-j', [2024, 2, 29]],
            'leap day from Persian day of year' => ['2024/شصتم', 'Y/R', [2024, 2, 29]],
            'leap day from English day of year' => ['2024/sixtieth', 'Y/R', [2024, 2, 29], ['locale' => 'en']],
            'day 366 in leap year' => ['2024/سیصد و شصت و ششم', 'Y/R', [2024, 12, 31]],
            'English day 366 in leap year' => ['2024/three hundred sixty-sixth', 'Y/R', [2024, 12, 31], ['locale' => 'en']],
            'non-ambiguous Persian words only without separators' => ['بیست‌و‌یکم مارس دو‌هزار‌و‌بیست‌و‌پنج', 'J F V', [2025, 3, 21]],
        ];
    }

    /**
     * Tests expands two digit years around reference date.
     */
    #[DataProvider('twoDigitYearExpansionProvider')]
    public function testExpandsTwoDigitYearsAroundReferenceDate(string $input, int $expectedYear): void
    {
        JalaliDate::setTestToday(new JalaliDate(1403, 1, 1)); // Gregorian 2024-03-20
        try {
            self::assertSame([$expectedYear, 3, 21], GregorianDate::parse($input, 'y-n-j')->toArray());
        } finally {
            JalaliDate::setTestToday(null);
        }
    }

    /**
     * Provides data for two digit year expansion tests.
     *
     * @return array<array{string,int}> Provider data sets.
     */
    public static function twoDigitYearExpansionProvider(): array
    {
        return [
            '24 -> 2024' => ['24-3-21', 2024],
            '25 -> 2025' => ['25-3-21', 2025],
            '99 -> 1999' => ['99-3-21', 1999],
            '00 -> 2000' => ['00-3-21', 2000],
        ];
    }

    /**
     * Tests skip validation allows mismatched scoped tokens.
     */
    public function testSkipValidationAllowsMismatchedScopedTokens(): void
    {
        $parsed = GregorianDate::parse(
            'Friday اول بهار 2025-3-21 1404/01/02',
            '"Friday "q" "Q" "Y-n-j [jalali:Y/m/d]',
            ['skipValidation' => true]
        );

        self::assertSame([2025, 3, 21], $parsed->toArray());
    }

    /**
     * Tests rejects invalid inputs.
     *
     * @param class-string<Throwable> $exceptionClass Expected exception class.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('invalidParseInputsProvider')]
    public function testRejectsInvalidInputs(string $input, string $pattern, string $exceptionClass = DateParseException::class, array $options = []): void
    {
        $this->expectException($exceptionClass);

        GregorianDate::parse($input, $pattern, $options);
    }

    /**
     * Provides data for invalid parse inputs tests.
     *
     * @return array<array{string,string,2?:class-string<Throwable>,3?:array<mixed>}> Provider data sets.
     */
    public static function invalidParseInputsProvider(): array
    {
        return [
            'empty string' => ['', 'Y-m-d'],
            'incomplete date' => ['2025-03', 'Y-m-d'],
            'extra text' => ['2025-03-21 extra', 'Y-m-d'],
            'wrong separator' => ['2025/03/21', 'Y-m-d'],
            'unknown month name' => ['21 Unknown 2025', 'j F Y'],
            'English month without English locale' => ['21 March 2025', 'j F Y'],
            'Persian month with English locale' => ['21 مارس 2025', 'j F Y', DateParseException::class, ['locale' => 'en']],
            'full English month with abbreviated token' => ['21 March 2025', 'j M Y', DateParseException::class, ['locale' => 'en']],
            'full English day with abbreviated token' => ['Friday 2025-3-21', 'D Y-n-j', DateParseException::class, ['locale' => 'en']],
            'only era name' => ['Common Era', 'C', DateParseException::class, ['locale' => 'en']],
            'only abbreviated era name' => ['CE', 'E', DateParseException::class, ['locale' => 'en']],
            'only season name' => ['Spring', 'Q', DateParseException::class, ['locale' => 'en']],
            'only quarter name' => ['first', 'q', DateParseException::class, ['locale' => 'en']],
            'only day name' => ['Friday', 'l', DateParseException::class, ['locale' => 'en']],
            'conflicting repeated year' => ['2025/2024/3/21', 'Y/Y/n/j'],
            'ambiguous Persian words only without separators' => ['بیست و یکم مارس دو هزار و بیست و پنج', 'J F V'],
            'invalid month' => ['2025-13-21', 'Y-m-d'],
            'invalid day' => ['2025-04-31', 'Y-m-d'],
            'invalid leap day' => ['2025-02-29', 'Y-m-d'],
            'year zero' => ['0-1-1', 'Y-n-j'],
            'invalid day name' => ['جمعه 2025-3-20', 'l Y-n-j'],
            'wrong quarter' => ['دوم 2025-3-21', 'q Y-n-j'],
            'wrong English quarter' => ['second 2025-3-21', 'q Y-n-j', DateParseException::class, ['locale' => 'en']],
            'wrong season' => ['تابستان 2025-3-21', 'Q Y-n-j'],
            'wrong English season' => ['Summer 2025-3-21', 'Q Y-n-j', DateParseException::class, ['locale' => 'en']],
            'wrong era name' => ['خورشیدی 2025-3-21', 'C Y-n-j'],
            'wrong English era name' => ['Solar Hijri 2025-3-21', 'C Y-n-j', DateParseException::class, ['locale' => 'en']],
            'wrong English abbreviated era name' => ['SH 2025-3-21', 'E Y-n-j', DateParseException::class, ['locale' => 'en']],
            'invalid era name' => ['InvalidEra 2025-3-21', 'C Y-n-j'],
            'wrong day of year' => ['2025/هفتاد و نهم 2025-3-21', 'Y/R Y-n-j'],
            'wrong English day of year' => ['2025/seventy-ninth 2025-3-21', 'Y/R Y-n-j', DateParseException::class, ['locale' => 'en']],
            'wrong week of year' => ['2025 یازدهمین جمعه 2025-3-21', 'Y K l Y-n-j'],
            'wrong English week of year' => ['eleventh Friday of 2025 2025-3-21', 'K l "of "Y Y-n-j', DateParseException::class, ['locale' => 'en']],
            'wrong week of month' => ['2025/3/دومین جمعه 2025-3-21', 'Y/n/k l Y-n-j'],
            'wrong English week of month' => ['second Friday of March 2025 2025-3-21', 'k l "of "F Y Y-n-j', DateParseException::class, ['locale' => 'en']],
            'mismatched Jalali year' => ['2025-03-21 1405/01/01', 'Y-m-d [jalali:Y/m/d]'],
            'mismatched Jalali day' => ['2025-03-21 1404/01/02', 'Y-m-d [jalali:Y/m/d]'],
            'mismatched Jalali era name' => ['2025-03-21 میلادی 1404/01/01', 'Y-m-d [jalali:C Y/m/d]'],
            'unsupported calendar scope' => ['2025-03-21 1446/09/21', 'Y-m-d [unknown:Y/m/d]', InvalidArgumentException::class],
            'only scoped non-primary calendar' => ['1404/01/01', '[jalali:Y/m/d]'],
            'preserve whitespace requires exact input' => [' 2025-3-21 ', 'Y-n-j', DateParseException::class, ['preserveWhitespace' => true]],
            'two-digit year out of range' => ['100-3-21', 'y-n-j'],
        ];
    }
}
