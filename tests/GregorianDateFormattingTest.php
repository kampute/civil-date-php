<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date formatting.
 */
final class GregorianDateFormattingTest extends TestCase
{
    /**
     * Tests format.
     *
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('formatProvider')]
    public function testFormat(string $pattern, string $expected, array $options = []): void
    {
        self::assertSame($expected, (new GregorianDate(2025, 3, 21))->format($pattern, $options));
    }

    /**
     * Provides data for format tests.
     *
     * @return array<array{string,string,2?:array<mixed>}> Provider data sets.
     */
    public static function formatProvider(): array
    {
        return [
            'default Gregorian scope' => ['Y/m/d', '۲۰۲۵/۰۳/۲۱'],
            'scoped Jalali date' => ['Y/m/d = [jalali:Y/m/d]', '۲۰۲۵/۰۳/۲۱ = ۱۴۰۴/۰۱/۰۱'],
            'only scoped Jalali tokens' => ['[jalali:j F Y]', '۱ فروردین ۱۴۰۴'],
            'era names' => ['C = [jalali:C]', 'میلادی = خورشیدی'],
            'abbreviated era names' => ['E = [jalali:E]', 'م = ه.ش'],
            'explicit Gregorian scope' => ['[gregorian:Y-m-d]', '۲۰۲۵-۰۳-۲۱'],
            'case insensitive Gregorian scope' => ['[GrEgOrIaN:Y-m-d]', '۲۰۲۵-۰۳-۲۱'],
            'quoted literals' => ['"Year "Y", month "m', 'Year ۲۰۲۵, month ۰۳'],
            'escaped token characters' => ['\\Y: Y, \\m: m, \\d: d', 'Y: ۲۰۲۵, m: ۰۳, d: ۲۱'],
            'protected text direction' => ['"Date "Y/m/d', "\u{2067}Date ۲۰۲۵/۰۳/۲۱\u{2069}", ['protectTextDirection' => true]],
            'deprecated unprotected option ignored' => ['Y/m/d', '۲۰۲۵/۰۳/۲۱', ['protectTextDirection' => false]],
            'English locale' => ['l j F Y C', 'Friday 21 March 2025 Common Era', ['locale' => 'en']],
            'English abbreviations' => ['D j M Y E', 'Fri 21 Mar 2025 CE', ['locale' => 'en']],
            'Persian abbreviation fallback' => ['D j M Y', 'جمعه ۲۱ مارس ۲۰۲۵'],
        ];
    }

    /**
     * Tests format rejects unsupported calendar scope.
     */
    public function testFormatRejectsUnsupportedCalendarScope(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GregorianDate(2025, 3, 21))->format('[unknown:Y/m/d]');
    }

    /**
     * Tests format rejects invalid locale option.
     */
    public function testFormatRejectsInvalidLocaleOption(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GregorianDate(2025, 3, 21))->format('Y/m/d', ['locale' => 'invalid']);
    }

    /**
     * Tests formatting single-field patterns.
     */
    #[DataProvider('singleFieldPatternProvider')]
    public function testFormatsSingleFieldPattern(GregorianDate $date, string $pattern, string $expected): void
    {
        self::assertSame($expected, $date->format($pattern));
    }

    /**
     * Provides data for single-field pattern tests.
     *
     * @return array<array{GregorianDate,string,string}> Provider data sets.
     */
    public static function singleFieldPatternProvider(): array
    {
        $date = new GregorianDate(2025, 3, 21);

        return [
            'full year' => [$date, 'Y', '۲۰۲۵'],
            'cardinal year' => [$date, 'V', 'دو هزار و بیست و پنج'],
            'two-digit year' => [$date, 'y', '۲۵'],
            'month' => [$date, 'n', '۳'],
            'padded month' => [$date, 'm', '۰۳'],
            'month name' => [$date, 'F', 'مارس'],
            'abbreviated month name' => [$date, 'M', 'مارس'],
            'day' => [$date, 'j', '۲۱'],
            'padded day' => [new GregorianDate(2025, 3, 1), 'd', '۰۱'],
            'day ordinal name' => [$date, 'J', 'بیست و یکم'],
            'day name' => [$date, 'l', 'جمعه'],
            'abbreviated day name' => [$date, 'D', 'جمعه'],
            'quarter' => [$date, 'q', 'اول'],
            'season' => [$date, 'Q', 'بهار'],
            'era' => [$date, 'C', 'میلادی'],
            'abbreviated era' => [$date, 'E', 'م'],
            'day of year' => [$date, 'R', 'هشتادم'],
            'day of week in month' => [$date, 'k', 'سوم'],
            'day of week in year' => [$date, 'K', 'دوازدهم'],
            'negative full year' => [new GregorianDate(-123, 6, 15), 'Y', '−۰۱۲۳'],
            'negative cardinal year' => [new GregorianDate(-123, 6, 15), 'V', 'منفی صد و بیست و سه'],
            'unknown token' => [$date, 'x', 'x'],
        ];
    }

    /**
     * Tests string cast formats gregorian date.
     */
    #[DataProvider('stringCastProvider')]
    public function testStringCastFormatsGregorianDate(GregorianDate $date, string $expected): void
    {
        self::assertSame($expected, (string) $date);
    }

    /**
     * Provides data for string cast tests.
     *
     * @return array<array{GregorianDate,string}> Provider data sets.
     */
    public static function stringCastProvider(): array
    {
        return [
            'positive year' => [new GregorianDate(2025, 3, 21), '2025/03/21'],
            'negative year' => [new GregorianDate(-1, 1, 1), '-0001/01/01'],
            'leap day' => [new GregorianDate(2024, 2, 29), '2024/02/29'],
        ];
    }

    /**
     * Tests php serialization uses julian day number.
     */
    public function testPhpSerializationUsesJulianDayNumber(): void
    {
        $date = new GregorianDate(2024, 3, 20);

        self::assertSame(['jdn' => 2460390], $date->__serialize());
    }

    /**
     * Tests php serialization round trip restores gregorian date from julian day number.
     */
    public function testPhpSerializationRoundTripRestoresGregorianDateFromJulianDayNumber(): void
    {
        $date = new GregorianDate(2024, 3, 20);
        $restored = unserialize(serialize($date));

        self::assertInstanceOf(GregorianDate::class, $restored);
        self::assertSame($date->jdn(), $restored->jdn());
        self::assertSame($date->toArray(), $restored->toArray());
    }

    /**
     * Tests php unserialization rejects invalid serialized state.
     */
    public function testPhpUnserializationRejectsInvalidSerializedState(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GregorianDate(2024, 3, 20))->__unserialize(['jdn' => '2460390']);
    }
}
