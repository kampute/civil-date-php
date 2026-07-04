<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date formatting.
 */
final class IslamicDateFormattingTest extends TestCase
{
    /**
     * Tests format.
     *
     * @param array<mixed> $options Test data.
     */
    #[DataProvider('formatProvider')]
    public function testFormat(string $pattern, string $expected, array $options = []): void
    {
        self::assertSame($expected, (new IslamicDate(1446, 1, 1))->format($pattern, $options));
    }

    /**
     * Provides data for format tests.
     *
     * @return array<array{string,string,2?:array<mixed>}> Provider data sets.
     */
    public static function formatProvider(): array
    {
        return [
            'Default Islamic scope' => ['Y/m/d', '۱۴۴۶/۰۱/۰۱'],
            'Explicit Islamic scope' => ['[islamic:Y-m-d]', '۱۴۴۶-۰۱-۰۱'],
            'Case insensitive Islamic scope' => ['[IsLaMiC:Y-m-d]', '۱۴۴۶-۰۱-۰۱'],
            'Scoped Gregorian date' => ['Y/m/d = [gregorian:Y/m/d]', '۱۴۴۶/۰۱/۰۱ = ۲۰۲۴/۰۷/۰۸'],
            'Scoped Jalali date' => ['[jalali:Y/m/d]', '۱۴۰۳/۰۴/۱۸'],
            'Persian names' => ['l j F Y C', 'دوشنبه ۱ محرم ۱۴۴۶ قمری'],
            'English names' => ['l j F Y C', 'Monday 1 Muharram 1446 Hijri', ['locale' => 'en']],
            'English abbreviations' => ['D j M Y E', 'Mon 1 Muh 1446 AH', ['locale' => 'en']],
            'Quoted literals' => ['"Year "Y", month "m', 'Year ۱۴۴۶, month ۰۱'],
        ];
    }

    /**
     * Tests format rejects invalid options.
     */
    #[DataProvider('invalidFormatProvider')]
    public function testFormatRejectsInvalidOptions(callable $format): void
    {
        $this->expectException(InvalidArgumentException::class);
        $format();
    }

    /**
     * Provides invalid format calls.
     *
     * @return array<array{callable():string}> Provider data sets.
     */
    public static function invalidFormatProvider(): array
    {
        return [
            'Unsupported scope' => [static fn () => (new IslamicDate(1446, 1, 1))->format('[unknown:Y/m/d]')],
            'Invalid locale' => [static fn () => (new IslamicDate(1446, 1, 1))->format('Y/m/d', ['locale' => 'invalid'])],
        ];
    }

    /**
     * Tests formatting single-field patterns.
     */
    #[DataProvider('singleFieldPatternProvider')]
    public function testFormatsSingleFieldPattern(string $pattern, string $expected): void
    {
        self::assertSame($expected, (new IslamicDate(1446, 1, 1))->format($pattern));
    }

    /**
     * Provides data for single-field pattern tests.
     *
     * @return array<array{string,string}> Provider data sets.
     */
    public static function singleFieldPatternProvider(): array
    {
        return [
            'Full year' => ['Y', '۱۴۴۶'],
            'Cardinal year' => ['V', 'یک هزار و چهارصد و چهل و شش'],
            'Two-digit year' => ['y', '۴۶'],
            'Month' => ['n', '۱'],
            'Padded month' => ['m', '۰۱'],
            'Month name' => ['F', 'محرم'],
            'Day' => ['j', '۱'],
            'Padded day' => ['d', '۰۱'],
            'Day ordinal name' => ['J', 'اول'],
            'Day name' => ['l', 'دوشنبه'],
            'Quarter' => ['q', 'اول'],
            'Season' => ['Q', 'تابستان'],
            'Era' => ['C', 'قمری'],
            'Abbreviated era' => ['E', 'ه.ق'],
            'Day of year' => ['R', 'اول'],
            'Unknown token' => ['x', 'x'],
        ];
    }

    /**
     * Tests string cast formats islamic date.
     */
    public function testStringCastFormatsIslamicDate(): void
    {
        self::assertSame('1446/01/01', (string) new IslamicDate(1446, 1, 1));
        self::assertSame('-0001/12/29', (string) new IslamicDate(-1, 12, 29));
    }

    /**
     * Tests php serialization uses julian day number.
     */
    public function testPhpSerializationUsesJulianDayNumber(): void
    {
        $date = new IslamicDate(1446, 1, 1);
        self::assertSame(['jdn' => 2460500], $date->__serialize());
    }

    /**
     * Tests php serialization round trip restores islamic date from julian day number.
     */
    public function testPhpSerializationRoundTripRestoresIslamicDateFromJulianDayNumber(): void
    {
        $date = new IslamicDate(1446, 1, 1);
        $restored = unserialize(serialize($date));
        self::assertInstanceOf(IslamicDate::class, $restored);
        self::assertSame($date->toArray(), $restored->toArray());
    }

    /**
     * Tests php unserialization rejects invalid serialized state.
     */
    public function testPhpUnserializationRejectsInvalidSerializedState(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new IslamicDate(1446, 1, 1))->__unserialize(['jdn' => '2460500']);
    }
}
