<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date factories.
 */
final class GregorianDateFactoriesTest extends TestCase
{
    /**
     * Tests today uses gregorian test today.
     */
    public function testTodayUsesGregorianTestToday(): void
    {
        $testToday = new GregorianDate(2025, 3, 21);
        GregorianDate::setTestToday($testToday);

        try {
            self::assertTrue(GregorianDate::today()->equals($testToday));
        } finally {
            GregorianDate::setTestToday(null);
        }
    }

    /**
     * Tests today converts jalali test today.
     */
    public function testTodayConvertsJalaliTestToday(): void
    {
        JalaliDate::setTestToday(new JalaliDate(1403, 1, 1));

        try {
            self::assertSame([2024, 3, 20], GregorianDate::today()->toArray());
            self::assertSame([2024, 3, 20], GregorianDate::getTestToday()?->toArray());
        } finally {
            JalaliDate::setTestToday(null);
        }
    }

    /**
     * Tests yesterday.
     *
     * @param array<mixed> $expectedYesterday Test data.
     */
    #[DataProvider('yesterdayProvider')]
    public function testYesterday(int $todayYear, int $todayMonth, int $todayDay, array $expectedYesterday): void
    {
        GregorianDate::setTestToday(new GregorianDate($todayYear, $todayMonth, $todayDay));

        try {
            self::assertSame($expectedYesterday, GregorianDate::yesterday()->toArray());
        } finally {
            GregorianDate::setTestToday(null);
        }
    }

    /**
     * Provides data for yesterday tests.
     *
     * @return array<array{int,int,int,array<mixed>}> Provider data sets.
     */
    public static function yesterdayProvider(): array
    {
        return [
            'Start of year' => [2025, 1, 1, [2024, 12, 31]],
            'Mid month' => [2025, 6, 15, [2025, 6, 14]],
            'Start of month' => [2025, 3, 1, [2025, 2, 28]],
            'Leap day' => [2024, 3, 1, [2024, 2, 29]],
            'Across year 0' => [1, 1, 1, [-1, 12, 31]],
        ];
    }

    /**
     * Tests tomorrow.
     *
     * @param array<mixed> $expectedTomorrow Test data.
     */
    #[DataProvider('tomorrowProvider')]
    public function testTomorrow(int $todayYear, int $todayMonth, int $todayDay, array $expectedTomorrow): void
    {
        GregorianDate::setTestToday(new GregorianDate($todayYear, $todayMonth, $todayDay));

        try {
            self::assertSame($expectedTomorrow, GregorianDate::tomorrow()->toArray());
        } finally {
            GregorianDate::setTestToday(null);
        }
    }

    /**
     * Provides data for tomorrow tests.
     *
     * @return array<array{int,int,int,array<mixed>}> Provider data sets.
     */
    public static function tomorrowProvider(): array
    {
        return [
            'Start of year' => [2025, 1, 1, [2025, 1, 2]],
            'Mid month' => [2025, 6, 15, [2025, 6, 16]],
            'End of month' => [2025, 1, 31, [2025, 2, 1]],
            'Leap day' => [2024, 2, 28, [2024, 2, 29]],
            'Across year 0' => [-1, 12, 31, [1, 1, 1]],
        ];
    }

    /**
     * Tests from j d n.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromJDNProvider')]
    public function testFromJDN(int $jdn, array $expected): void
    {
        $date = GregorianDate::fromJDN($jdn);

        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for from j d n tests.
     *
     * @return array<array{int,array<mixed>}> Provider data sets.
     */
    public static function fromJDNProvider(): array
    {
        return [
            'Unix epoch' => [2440588, [1970, 1, 1]],
            'Nowruz 1403' => [2460390, [2024, 3, 20]],
            'Nowruz 1404' => [2460756, [2025, 3, 21]],
            'Y2K' => [2451545, [2000, 1, 1]],
            'Ancient date' => [1721426, [1, 1, 1]],
        ];
    }

    /**
     * Tests from j d n round trips.
     */
    #[DataProvider('jdnRoundTripProvider')]
    public function testFromJDNRoundTrips(int $year, int $month, int $day): void
    {
        $original = new GregorianDate($year, $month, $day);
        $fromJDN = GregorianDate::fromJDN($original->jdn());

        self::assertSame($original->toArray(), $fromJDN->toArray());
    }

    /**
     * Provides data for jdn round trip tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function jdnRoundTripProvider(): array
    {
        return [
            'Standard date' => [2025, 3, 21],
            'Leap year' => [2024, 2, 29],
            'First day of year' => [2025, 1, 1],
            'Last day of year' => [2025, 12, 31],
            'Century leap year' => [2000, 2, 29],
            'Century non-leap year' => [1900, 2, 28],
        ];
    }

    /**
     * Tests from gregorian date.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromGregorianDateProvider')]
    public function testFromGregorianDate(int $year, int $month, int $day, array $expected): void
    {
        $date = GregorianDate::fromGregorianDate($year, $month, $day);

        self::assertSame($expected, $date->toArray());
        self::assertSame((new GregorianDate($year, $month, $day))->jdn(), $date->jdn());
    }

    /**
     * Provides data for from gregorian date tests.
     *
     * @return array<array{int,int,int,array<mixed>}> Provider data sets.
     */
    public static function fromGregorianDateProvider(): array
    {
        return [
            'Standard date' => [2025, 3, 21, [2025, 3, 21]],
            'Leap day' => [2024, 2, 29, [2024, 2, 29]],
            'Negative year' => [-100, 6, 15, [-100, 6, 15]],
        ];
    }

    /**
     * Tests from gregorian date rejects invalid date.
     */
    #[DataProvider('invalidGregorianDateProvider')]
    public function testFromGregorianDateRejectsInvalidDate(int $year, int $month, int $day): void
    {
        $this->expectException(InvalidArgumentException::class);

        GregorianDate::fromGregorianDate($year, $month, $day);
    }

    /**
     * Provides data for invalid gregorian date tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function invalidGregorianDateProvider(): array
    {
        return [
            'Year 0' => [0, 1, 1],
            'Month 0' => [2025, 0, 1],
            'Month 13' => [2025, 13, 1],
            'Invalid leap day' => [2025, 2, 29],
            'Invalid April 31' => [2025, 4, 31],
        ];
    }

    /**
     * Tests from iso8601 date string.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromIso8601DateStringProvider')]
    public function testFromIso8601DateString(string $isoDate, array $expected): void
    {
        self::assertSame($expected, GregorianDate::fromIso8601DateString($isoDate)->toArray());
    }

    /**
     * Provides data for from iso8601 date string tests.
     *
     * @return array<array{string,array<mixed>}> Provider data sets.
     */
    public static function fromIso8601DateStringProvider(): array
    {
        return [
            'Standard date' => ['2025-03-21', [2025, 3, 21]],
            'Leap day' => ['2024-02-29', [2024, 2, 29]],
            'Negative year' => ['-0100-06-15', [-100, 6, 15]],
        ];
    }

    /**
     * Tests from iso8601 date string rejects invalid format.
     */
    #[DataProvider('invalidIso8601DateStringFormatProvider')]
    public function testFromIso8601DateStringRejectsInvalidFormat(string $input): void
    {
        $this->expectException(DateParseException::class);

        GregorianDate::fromIso8601DateString($input);
    }

    /**
     * Provides data for invalid iso8601 date string format tests.
     *
     * @return array<array{string}> Provider data sets.
     */
    public static function invalidIso8601DateStringFormatProvider(): array
    {
        return [
            'Empty string' => [''],
            'Wrong separator' => ['2025/03/21'],
            'Two-digit year' => ['25-03-21'],
            'Single-digit month' => ['2025-3-21'],
            'Single-digit day' => ['2025-03-2'],
            'With time component' => ['2025-03-21T00:00:00Z'],
        ];
    }

    /**
     * Tests from iso8601 date string rejects invalid data.
     */
    #[DataProvider('invalidIso8601DateStringDataProvider')]
    public function testFromIso8601DateStringRejectsInvalidData(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        GregorianDate::fromIso8601DateString($input);
    }

    /**
     * Provides data for invalid iso8601 date string data tests.
     *
     * @return array<array{string}> Provider data sets.
     */
    public static function invalidIso8601DateStringDataProvider(): array
    {
        return [
            'Year 0' => ['0000-01-01'],
            'Invalid leap day' => ['2025-02-29'],
            'Invalid February 30' => ['2024-02-30'],
            'Invalid April 31' => ['2025-04-31'],
        ];
    }

    /**
     * Tests from date time.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromDateTimeProvider')]
    public function testFromDateTime(string $dateTimeString, array $expected): void
    {
        $dateTime = new DateTimeImmutable($dateTimeString);
        $gregorianDate = GregorianDate::fromDateTime($dateTime);

        self::assertSame($expected, $gregorianDate->toArray());
    }

    /**
     * Provides data for from date time tests.
     *
     * @return array<array{string,array<mixed>}> Provider data sets.
     */
    public static function fromDateTimeProvider(): array
    {
        return [
            'Gregorian midnight in Tehran' => ['2024-03-20T00:00:00+03:30', [2024, 3, 20]],
            'Same instant before Gregorian midnight in UTC' => ['2024-03-19T20:30:00Z', [2024, 3, 19]],
            'Leap day' => ['2024-02-29T12:00:00Z', [2024, 2, 29]],
        ];
    }

    /**
     * Tests from day of month.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromDayOfMonthProvider')]
    public function testFromDayOfMonth(int $year, int $month, int $dayOfMonth, array $expected): void
    {
        $date = GregorianDate::fromDayOfMonth($year, $month, $dayOfMonth);

        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for from day of month tests.
     *
     * @return array<array{int,int,int,array<mixed>}> Provider data sets.
     */
    public static function fromDayOfMonthProvider(): array
    {
        return [
            'First day of month' => [2025, 1, 1, [2025, 1, 1]],
            'Last day of month' => [2025, 1, 31, [2025, 1, 31]],
            'Leap February 29' => [2024, 2, 29, [2024, 2, 29]],
            'Day -1 (last day of month)' => [2025, 2, -1, [2025, 2, 28]],
            'Day -31 (first day of month)' => [2025, 3, -31, [2025, 3, 1]],
        ];
    }

    /**
     * Tests from day of month invalid.
     */
    #[DataProvider('fromDayOfMonthInvalidProvider')]
    public function testFromDayOfMonthInvalid(int $year, int $month, int $dayOfMonth): void
    {
        $this->expectException(InvalidArgumentException::class);

        GregorianDate::fromDayOfMonth($year, $month, $dayOfMonth);
    }

    /**
     * Provides data for from day of month invalid tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function fromDayOfMonthInvalidProvider(): array
    {
        return [
            'Day 0' => [2025, 1, 0],
            'Day 32' => [2025, 1, 32],
            'Day -32' => [2025, 1, -32],
            'February 29 non-leap' => [2025, 2, 29],
            'Day -29 February non-leap' => [2025, 2, -29],
        ];
    }

    /**
     * Tests from day of year.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromDayOfYearProvider')]
    public function testFromDayOfYear(int $year, int $dayOfYear, array $expected): void
    {
        $date = GregorianDate::fromDayOfYear($year, $dayOfYear);

        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for from day of year tests.
     *
     * @return array<array{int,int,array<mixed>}> Provider data sets.
     */
    public static function fromDayOfYearProvider(): array
    {
        return [
            'Day 1' => [2025, 1, [2025, 1, 1]],
            'Day 31 (Jan 31)' => [2025, 31, [2025, 1, 31]],
            'Day 32 (Feb 1)' => [2025, 32, [2025, 2, 1]],
            'Day 59 (Feb 28 non-leap)' => [2025, 59, [2025, 2, 28]],
            'Day 60 (Mar 1 non-leap)' => [2025, 60, [2025, 3, 1]],
            'Leap year day 59 (Feb 28)' => [2024, 59, [2024, 2, 28]],
            'Leap year day 60 (Feb 29)' => [2024, 60, [2024, 2, 29]],
            'Leap year day 61 (Mar 1)' => [2024, 61, [2024, 3, 1]],
            'Day 365 (Dec 31 non-leap)' => [2025, 365, [2025, 12, 31]],
            'Day 366 (Dec 31 leap)' => [2024, 366, [2024, 12, 31]],
            'Mid-year' => [2025, 180, [2025, 6, 29]],
            'Day -1 (Dec 31 non-leap)' => [2025, -1, [2025, 12, 31]],
            'Day -31 (Dec 1 non-leap)' => [2025, -31, [2025, 12, 1]],
            'Day -32 (Nov 30 non-leap)' => [2025, -32, [2025, 11, 30]],
            'Day -365 (Jan 1 non-leap)' => [2025, -365, [2025, 1, 1]],
            'Day -366 (Jan 1 leap)' => [2024, -366, [2024, 1, 1]],
        ];
    }

    /**
     * Tests from day of year invalid.
     */
    #[DataProvider('fromDayOfYearInvalidProvider')]
    public function testFromDayOfYearInvalid(int $year, int $dayOfYear): void
    {
        $this->expectException(InvalidArgumentException::class);

        GregorianDate::fromDayOfYear($year, $dayOfYear);
    }

    /**
     * Provides data for from day of year invalid tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function fromDayOfYearInvalidProvider(): array
    {
        return [
            'Day 0' => [2025, 0],
            'Day 366 non-leap' => [2025, 366],
            'Day 367 leap' => [2024, 367],
            'Day -366 non-leap' => [2025, -366],
            'Day -367 leap' => [2024, -367],
        ];
    }

    /**
     * Tests from nth day of week in year.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromNthDayOfWeekInYearProvider')]
    public function testFromNthDayOfWeekInYear(int $year, int $occurrence, DayOfWeek $dayOfWeek, array $expected): void
    {
        $date = GregorianDate::fromNthDayOfWeekInYear($year, $occurrence, $dayOfWeek);

        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for from nth day of week in year tests.
     *
     * @return array<array{int,int,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function fromNthDayOfWeekInYearProvider(): array
    {
        return [
            'First Saturday' => [2025, 1, DayOfWeek::Saturday, [2025, 1, 4]],
            'First Wednesday' => [2025, 1, DayOfWeek::Wednesday, [2025, 1, 1]],
            'First Friday' => [2025, 1, DayOfWeek::Friday, [2025, 1, 3]],
            'Second Saturday' => [2025, 2, DayOfWeek::Saturday, [2025, 1, 11]],
            'Mid-year occurrence' => [2025, 26, DayOfWeek::Saturday, [2025, 6, 28]],
            'Last Friday' => [2025, 52, DayOfWeek::Friday, [2025, 12, 26]],
        ];
    }

    /**
     * Tests from nth day of week in month.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromNthDayOfWeekInMonthProvider')]
    public function testFromNthDayOfWeekInMonth(int $year, int $month, int $occurrence, DayOfWeek $dayOfWeek, array $expected): void
    {
        $date = GregorianDate::fromNthDayOfWeekInMonth($year, $month, $occurrence, $dayOfWeek);

        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for from nth day of week in month tests.
     *
     * @return array<array{int,int,int,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function fromNthDayOfWeekInMonthProvider(): array
    {
        return [
            'March 2025, occurrence 1, Saturday (first day)' => [2025, 3, 1, DayOfWeek::Saturday, [2025, 3, 1]],
            'March 2025, occurrence 1, Friday' => [2025, 3, 1, DayOfWeek::Friday, [2025, 3, 7]],
            'March 2025, occurrence 2, Saturday' => [2025, 3, 2, DayOfWeek::Saturday, [2025, 3, 8]],
            'March 2025, occurrence 3, Friday' => [2025, 3, 3, DayOfWeek::Friday, [2025, 3, 21]],
            'March 2025, last Monday' => [2025, 3, -1, DayOfWeek::Monday, [2025, 3, 31]],
            'March 2025, last Saturday' => [2025, 3, -1, DayOfWeek::Saturday, [2025, 3, 29]],
            'February 2024, last Thursday (leap year)' => [2024, 2, -1, DayOfWeek::Thursday, [2024, 2, 29]],
            'January 2025, first Wednesday' => [2025, 1, 1, DayOfWeek::Wednesday, [2025, 1, 1]],
        ];
    }

    /**
     * Tests from nth day of week in month rejects zero.
     */
    public function testFromNthDayOfWeekInMonthRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GregorianDate::fromNthDayOfWeekInMonth(2025, 3, 0, DayOfWeek::Saturday);
    }

    /**
     * Tests set test today to null.
     */
    public function testSetTestTodayToNull(): void
    {
        GregorianDate::setTestToday(new GregorianDate(2025, 3, 21));
        GregorianDate::setTestToday(null);

        self::assertNull(GregorianDate::getTestToday());
    }
}
