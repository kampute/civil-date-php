<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Tests islamic date parse.
 */
final class IslamicDateParseTest extends TestCase
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
        self::assertSame($expected, IslamicDate::parse($input, $pattern, $options)->toArray());
    }

    /**
     * Provides data for valid parse input tests.
     *
     * @return array<array{string,string,array<mixed>,3?:array<mixed>}> Provider data sets.
     */
    public static function validParseInputsProvider(): array
    {
        return [
            'Y-m-d' => ['1446-01-01', 'Y-m-d', [1446, 1, 1]],
            'Cardinal year' => ['هزار و چهارصد و چهل و شش-1-1', 'V-n-j', [1446, 1, 1]],
            'Non-ambiguous Persian words only without separators' => ['بیست‌و‌نهم ذی‌الحجه یک‌هزار‌و‌چهارصد‌و‌چهل‌و‌شش', 'J F V', [1446, 12, 29]],
            'Persian words only with separators' => ['یک هزار و چهارصد و چهل و شش/رمضان/اول', 'V/F/J', [1446, 9, 1]],
            'English words only with separators' => ['one thousand four hundred forty-six/Ramadan/first', 'V/F/J', [1446, 9, 1], ['locale' => 'en']],
            'Persian digits' => ['۱۴۴۶/۰۱/۰۱', 'Y/m/d', [1446, 1, 1]],
            'Arabic-Indic digits' => ['١٤٤٦/١/١', 'Y/n/j', [1446, 1, 1]],
            'Persian month name' => ['۱ محرم ۱۴۴۶', 'j F Y', [1446, 1, 1]],
            'Persian day ordinal name' => ['اول محرم ۱۴۴۶', 'J F Y', [1446, 1, 1]],
            'Persian day ordinal from end' => ['آخر رمضان ۱۴۴۶', 'J F Y', [1446, 9, 30]],
            'English month name' => ['1 Muharram 1446', 'j F Y', [1446, 1, 1], ['locale' => 'en']],
            'English day ordinal from end' => ['last day of Ramadan 1446', 'J "day of" F Y', [1446, 9, 30], ['locale' => 'en']],
            'Persian day and era names' => ['دوشنبه ۱ محرم ۱۴۴۶ قمری', 'l j F Y C', [1446, 1, 1]],
            'English day and era names' => ['Monday 1 Muharram 1446 Hijri', 'l j F Y C', [1446, 1, 1], ['locale' => 'en']],
            'Persian abbreviated day and month names' => ['شنبه ۱ رمضان ۱۴۴۶', 'D j M Y', [1446, 9, 1]],
            'English abbreviations' => ['Sat 1 Ram 1446 AH', 'D j M Y E', [1446, 9, 1], ['locale' => 'en']],
            'Persian quarter ordinal name' => ['سوم ۱۴۴۶-۹-۱', 'q Y-n-j', [1446, 9, 1]],
            'English quarter ordinal name' => ['third 1446-9-1', 'q Y-n-j', [1446, 9, 1], ['locale' => 'en']],
            'Persian season name' => ['زمستان ۱۴۴۶-۹-۱', 'Q Y-n-j', [1446, 9, 1]],
            'English season name' => ['Winter 1446-9-1', 'Q Y-n-j', [1446, 9, 1], ['locale' => 'en']],
            'Explicit Islamic scope' => ['1446/1/1', '[islamic:Y/n/j]', [1446, 1, 1]],
            'Scoped Gregorian validation' => ['1446/1/1 2024-7-8', 'Y/n/j [gregorian:Y-n-j]', [1446, 1, 1]],
            'Scoped Jalali validation' => ['1446/1/1 1403-4-18', 'Y/n/j [jalali:Y-n-j]', [1446, 1, 1]],
            'Persian day of year ordinal name' => ['۱۴۴۶/دویست و سی و هفتم', 'Y/R', [1446, 9, 1]],
            'English day of year ordinal name' => ['1446/two hundred thirty-seventh', 'Y/R', [1446, 9, 1], ['locale' => 'en']],
            'Persian day of year ordinal from end' => ['۱۴۴۶/آخر', 'Y/R', [1446, 12, 29]],
            'English day of year ordinal from end' => ['second-to-last day of 1446', 'R "day of" Y', [1446, 12, 28], ['locale' => 'en']],
            'Persian week of year ordinal name' => ['۱۴۴۶ سی و چهارمین شنبه', 'Y K l', [1446, 9, 1]],
            'English week of year ordinal name' => ['thirty-fourth Saturday of 1446', 'K l "of" Y', [1446, 9, 1], ['locale' => 'en']],
            'Persian week of month ordinal name' => ['۱۴۴۶/۹/اولین شنبه', 'Y/n/k l', [1446, 9, 1]],
            'English week of month ordinal name' => ['first Saturday of Ramadan 1446', 'k l "of" F Y', [1446, 9, 1], ['locale' => 'en']],
            'Persian week of month ordinal from end' => ['۱۴۴۶/۹/آخرین شنبه', 'Y/n/k l', [1446, 9, 29]],
            'English week of month ordinal from end' => ['last Saturday of Ramadan 1446', 'k l "of" F Y', [1446, 9, 29], ['locale' => 'en']],
            'Persian year end day of year' => ['۱۴۴۶/سیصد و پنجاه و چهارم', 'Y/R', [1446, 12, 29]],
            'English year end day of year' => ['1446/three hundred fifty-fourth', 'Y/R', [1446, 12, 29], ['locale' => 'en']],
            'English year end words only' => ['one thousand four hundred forty-six/Dhu al-Hijjah/twenty-ninth', 'V/F/J', [1446, 12, 29], ['locale' => 'en']],
            'Negative year' => ['-1/12/29', 'Y/n/j', [-1, 12, 29]],
            'Whitespace normalized' => ['  1446  /  1  /  1 ', 'Y / n / j', [1446, 1, 1]],
        ];
    }

    /**
     * Tests parses all month names.
     *
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('monthNameProvider')]
    public function testParsesMonthNames(string $name, int $month, array $options = []): void
    {
        self::assertSame([1446, $month, 1], IslamicDate::parse("1 {$name} 1446", 'j F Y', $options)->toArray());
    }

    /**
     * Provides month names.
     *
     * @return array<array{string,int,2?:array<mixed>}> Provider data sets.
     */
    public static function monthNameProvider(): array
    {
        return [
            'Muharram' => ['محرم', 1],
            'Safar' => ['صفر', 2],
            'Rabi al-Awwal' => ['ربیع‌الاول', 3],
            'Rabi al-Awwal with space' => ['ربیع الاول', 3],
            'English Rabi al-Awwal' => ['Rabi al-Awwal', 3, ['locale' => 'en']],
            'Rabi al-Thani' => ['ربیع‌الثانی', 4],
            'English Rabi al-Thani' => ['Rabi al-Thani', 4, ['locale' => 'en']],
            'Jumada al-Awwal' => ['جمادی‌الاول', 5],
            'Jumada al-Thani' => ['جمادی‌الثانی', 6],
            'Rajab' => ['رجب', 7],
            'Shaban' => ['شعبان', 8],
            'Ramadan' => ['رمضان', 9],
            'Shawwal' => ['شوال', 10],
            'Dhu al-Qadah' => ['ذی‌القعده', 11],
            'Dhu al-Qadah with space' => ['ذی القعده', 11],
            'English Dhu al-Qadah' => ['Dhu al-Qadah', 11, ['locale' => 'en']],
            'Dhu al-Hijjah' => ['ذی‌الحجه', 12],
            'English Dhu al-Hijjah' => ['Dhu al-Hijjah', 12, ['locale' => 'en']],
        ];
    }

    /**
     * Tests rejects invalid inputs.
     *
     * @param class-string<Throwable> $exception Exception class.
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('invalidParseInputProvider')]
    public function testRejectsInvalidInputs(string $input, string $pattern, string $exception = DateParseException::class, array $options = []): void
    {
        $this->expectException($exception);
        IslamicDate::parse($input, $pattern, $options);
    }

    /**
     * Provides invalid parse inputs.
     *
     * @return array<array{string,string,2?:class-string<\Throwable>,3?:array<mixed>}> Provider data sets.
     */
    public static function invalidParseInputProvider(): array
    {
        return [
            'Year zero' => ['0/1/1', 'Y/n/j'],
            'Invalid month' => ['1446/13/1', 'Y/n/j'],
            'Invalid even-month day' => ['1446/2/30', 'Y/n/j'],
            'Mismatched day name' => ['Friday 1 Muharram 1446', 'l j F Y'],
            'English month without English locale' => ['1 Muharram 1446', 'j F Y'],
            'Persian month with English locale' => ['۱ محرم ۱۴۴۶', 'j F Y', DateParseException::class, ['locale' => 'en']],
            'Only era name' => ['Hijri', 'C', DateParseException::class, ['locale' => 'en']],
            'Only abbreviated era name' => ['AH', 'E', DateParseException::class, ['locale' => 'en']],
            'Only season name' => ['Winter', 'Q', DateParseException::class, ['locale' => 'en']],
            'Only quarter name' => ['third', 'q', DateParseException::class, ['locale' => 'en']],
            'Only day name' => ['Saturday', 'l', DateParseException::class, ['locale' => 'en']],
            'Conflicting repeated year' => ['1446/1447/9/1', 'Y/Y/n/j'],
            'Ambiguous Persian words only without separators' => ['بیست و نهم ذی‌الحجه یک هزار و چهارصد و چهل و شش', 'J F V'],
            'Ambiguous English words only without separators' => ['Dhu al-Hijjah twenty-ninth one thousand four hundred forty-six', 'F J V', DateParseException::class, ['locale' => 'en']],
            'Digit day of year ordinal token' => ['1446/237', 'Y/R'],
            'Wrong day of year' => ['1446/دویست و سی و ششم 1446/9/1', 'Y/R Y/n/j'],
            'Wrong English day of year' => ['1446/two hundred thirty-sixth 1446/9/1', 'Y/R Y/n/j', DateParseException::class, ['locale' => 'en']],
            'Wrong week of year' => ['1446 سی و سومین شنبه 1446/9/1', 'Y K l Y/n/j'],
            'Wrong English week of year' => ['thirty-third Saturday of 1446 1446/9/1', 'K l "of "Y Y/n/j', DateParseException::class, ['locale' => 'en']],
            'Wrong week of month' => ['1446/9/دومین شنبه 1446/9/1', 'Y/n/k l Y/n/j'],
            'Wrong English week of month' => ['second Saturday of Ramadan 1446 1446/9/1', 'k l "of "F Y Y/n/j', DateParseException::class, ['locale' => 'en']],
            'Wrong quarter' => ['چهارم 1446-9-1', 'q Y-n-j'],
            'Wrong English quarter' => ['fourth 1446-9-1', 'q Y-n-j', DateParseException::class, ['locale' => 'en']],
            'Wrong season' => ['تابستان 1446-9-1', 'Q Y-n-j'],
            'Wrong English season' => ['Summer 1446-9-1', 'Q Y-n-j', DateParseException::class, ['locale' => 'en']],
            'Wrong era name' => ['خورشیدی 1446/1/1', 'C Y/n/j'],
            'Wrong English era name' => ['Solar Hijri 1446/1/1', 'C Y/n/j', DateParseException::class, ['locale' => 'en']],
            'Wrong English abbreviated era name' => ['SH 1446/1/1', 'E Y/n/j', DateParseException::class, ['locale' => 'en']],
            'Mismatched Gregorian scope' => ['1446/1/1 2024-7-7', 'Y/n/j [gregorian:Y-n-j]'],
            'Unsupported scope' => ['1446/1/1', '[unknown:Y/n/j]', InvalidArgumentException::class],
        ];
    }
}
