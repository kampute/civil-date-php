<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\JalaliDate;
use Kampute\CivilDate\Localization\LocaleRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date formatting.
 */
final class JalaliDateFormattingTest extends TestCase
{
    /**
     * Tests format.
     *
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('formatProvider')]
    public function testFormat(string $pattern, string $expected, array $options = []): void
    {
        self::assertSame($expected, (new JalaliDate(1403, 1, 1))->format($pattern, $options));
    }

    /**
     * Provides data for format tests.
     *
     * @return array<array{string,string,2?:array<mixed>}> Provider data sets.
     */
    public static function formatProvider(): array
    {
        return [
            'default Jalali scope' => ['Y/m/d', '۱۴۰۳/۰۱/۰۱'],
            'scoped Gregorian date' => ['Y/m/d = [gregorian:Y-m-d]', '۱۴۰۳/۰۱/۰۱ = ۲۰۲۴-۰۳-۲۰'],
            'only scoped Gregorian tokens' => ['[gregorian:j F Y]', '۲۰ مارس ۲۰۲۴'],
            'era names' => ['C = [gregorian:C]', 'خورشیدی = میلادی'],
            'abbreviated era names' => ['E = [gregorian:E]', 'ه.ش = م'],
            'explicit Jalali scope' => ['[jalali:Y/m/d]', '۱۴۰۳/۰۱/۰۱'],
            'case insensitive Jalali scope' => ['[JaLaLi:Y/m/d]', '۱۴۰۳/۰۱/۰۱'],
            'quoted literals' => ['"سال "Y" ماه "m', 'سال ۱۴۰۳ ماه ۰۱'],
            'single quoted literals' => ["'Date: 'Y/m/d", 'Date: ۱۴۰۳/۰۱/۰۱'],
            'escaped token characters' => ['\\Y: Y, \\m: m, \\d: d', 'Y: ۱۴۰۳, m: ۰۱, d: ۰۱'],
            'escaped scope delimiters' => ['\\[Y\\]', '[۱۴۰۳]'],
            'protected text direction' => ['"Date "Y/m/d', "\u{2067}Date ۱۴۰۳/۰۱/۰۱\u{2069}", ['protectTextDirection' => true]],
            'protected English text direction' => ['"Date "Y/m/d', "\u{2066}Date 1403/01/01\u{2069}", ['locale' => 'en', 'protectTextDirection' => true]],
            'deprecated unprotected option ignored' => ['Y/m/d', '۱۴۰۳/۰۱/۰۱', ['protectTextDirection' => false]],
            'language-only Persian locale' => ['Y/m/d', '۱۴۰۳/۰۱/۰۱', ['locale' => 'fa']],
            'English locale' => ['l j F Y C', 'Wednesday 1 Farvardin 1403 Solar Hijri', ['locale' => 'en']],
            'English abbreviations' => ['D j M Y E', 'Wed 1 Far 1403 SH', ['locale' => 'en']],
            'Persian abbreviation fallback' => ['D j M Y', 'چهارشنبه ۱ فروردین ۱۴۰۳'],
        ];
    }

    /**
     * Tests format rejects unsupported calendar scope.
     */
    public function testFormatRejectsUnsupportedCalendarScope(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->format('[unknown:Y/m/d]');
    }

    /**
     * Tests format rejects invalid locale option.
     */
    public function testFormatRejectsInvalidLocaleOption(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->format('Y/m/d', ['locale' => 'invalid']);
    }

    /**
     * Tests format rejects unknown locale.
     */
    public function testFormatRejectsUnknownLocale(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->format('Y/m/d', ['locale' => 'unknown']);
    }

    /**
     * Tests format uses configured default locale.
     */
    public function testFormatUsesConfiguredDefaultLocale(): void
    {
        LocaleRegistry::setDefault('en');

        try {
            self::assertSame('1403/01/01', (new JalaliDate(1403, 1, 1))->format('Y/m/d'));
        } finally {
            LocaleRegistry::setDefault('fa');
        }
    }

    /**
     * Tests formatting single-field patterns.
     */
    #[DataProvider('singleFieldPatternProvider')]
    public function testFormatsSingleFieldPattern(string $pattern, string $expected): void
    {
        self::assertSame($expected, (new JalaliDate(1402, 6, 31))->format($pattern));
    }

    /**
     * Provides data for single-field pattern tests.
     *
     * @return array<array{string,string}> Provider data sets.
     */
    public static function singleFieldPatternProvider(): array
    {
        return [
            'full year' => ['Y', '۱۴۰۲'],
            'cardinal year' => ['V', 'یک هزار و چهارصد و دو'],
            'two-digit year' => ['y', '۰۲'],
            'month' => ['n', '۶'],
            'padded month' => ['m', '۰۶'],
            'month name' => ['F', 'شهریور'],
            'abbreviated month name' => ['M', 'شهریور'],
            'day' => ['j', '۳۱'],
            'padded day' => ['d', '۳۱'],
            'day ordinal name' => ['J', 'سی و یکم'],
            'day name' => ['l', 'جمعه'],
            'abbreviated day name' => ['D', 'جمعه'],
            'quarter' => ['q', 'دوم'],
            'season' => ['Q', 'تابستان'],
            'era' => ['C', 'خورشیدی'],
            'abbreviated era' => ['E', 'ه.ش'],
            'day of year' => ['R', 'صد و هشتاد و ششم'],
            'day of week in month' => ['k', 'پنجم'],
            'day of week in year' => ['K', 'بیست و هفتم'],
            'unknown token' => ['x', 'x'],
        ];
    }

    /**
     * Tests string cast formats jalali date.
     */
    #[DataProvider('stringCastProvider')]
    public function testStringCastFormatsJalaliDate(JalaliDate $date, string $expected): void
    {
        self::assertSame($expected, (string) $date);
    }

    /**
     * Provides data for string cast tests.
     *
     * @return array<array{JalaliDate,string}> Provider data sets.
     */
    public static function stringCastProvider(): array
    {
        return [
            'positive year' => [new JalaliDate(1402, 6, 5), '1402/06/05'],
            'negative year' => [new JalaliDate(-1, 1, 1), '-0001/01/01'],
            'leap day' => [new JalaliDate(1403, 12, 30), '1403/12/30'],
        ];
    }

    /**
     * Tests php serialization uses julian day number.
     */
    public function testPhpSerializationUsesJulianDayNumber(): void
    {
        $date = new JalaliDate(1403, 1, 1);

        self::assertSame(['jdn' => 2460390], $date->__serialize());
    }

    /**
     * Tests php serialization round trip restores jalali date from julian day number.
     */
    public function testPhpSerializationRoundTripRestoresJalaliDateFromJulianDayNumber(): void
    {
        $date = new JalaliDate(1403, 1, 1);
        $restored = unserialize(serialize($date));

        self::assertInstanceOf(JalaliDate::class, $restored);
        self::assertSame($date->jdn(), $restored->jdn());
        self::assertSame($date->toArray(), $restored->toArray());
    }

    /**
     * Tests php unserialization rejects invalid serialized state.
     */
    public function testPhpUnserializationRejectsInvalidSerializedState(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->__unserialize(['jdn' => '2460390']);
    }
}
