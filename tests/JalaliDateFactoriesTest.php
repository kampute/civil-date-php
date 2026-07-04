<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\JalaliDate;
use Kampute\CivilDate\DateOutOfRangeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date factories.
 */
final class JalaliDateFactoriesTest extends TestCase
{
    /**
     * Tests today uses jalali test today.
     */
    public function testTodayUsesJalaliTestToday(): void
    {
        $testToday = new JalaliDate(1405, 1, 1);
        JalaliDate::setTestToday($testToday);

        try {
            self::assertTrue(JalaliDate::today()->equals($testToday));
        } finally {
            JalaliDate::setTestToday(null);
        }
    }

    /**
     * Tests today converts gregorian test today.
     */
    public function testTodayConvertsGregorianTestToday(): void
    {
        GregorianDate::setTestToday(new GregorianDate(2024, 3, 20));

        try {
            self::assertSame([1403, 1, 1], JalaliDate::today()->toArray());
            self::assertSame([1403, 1, 1], JalaliDate::getTestToday()?->toArray());
        } finally {
            GregorianDate::setTestToday(null);
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
        JalaliDate::setTestToday(new JalaliDate($todayYear, $todayMonth, $todayDay));

        try {
            self::assertSame($expectedYesterday, JalaliDate::yesterday()->toArray());
        } finally {
            JalaliDate::setTestToday(null);
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
            'Start of year' => [1405, 1, 1, [1404, 12, 29]],
            'Mid month' => [1403, 6, 15, [1403, 6, 14]],
            'Start of month' => [1403, 7, 1, [1403, 6, 31]],
            'Across year 0' => [1, 1, 1, [-1, 12, 30]],
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
        JalaliDate::setTestToday(new JalaliDate($todayYear, $todayMonth, $todayDay));

        try {
            self::assertSame($expectedTomorrow, JalaliDate::tomorrow()->toArray());
        } finally {
            JalaliDate::setTestToday(null);
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
            'Start of year' => [1405, 1, 1, [1405, 1, 2]],
            'Mid month' => [1403, 6, 15, [1403, 6, 16]],
            'End of month' => [1403, 6, 31, [1403, 7, 1]],
            'End of year (leap)' => [1403, 12, 30, [1404, 1, 1]],
            'Across year 0' => [-1, 12, 30, [1, 1, 1]],
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
        self::assertSame($expected, JalaliDate::fromJDN($jdn)->toArray());
    }

    /**
     * Provides data for from j d n tests.
     *
     * @return array<array{int,array<mixed>}> Provider data sets.
     */
    public static function fromJDNProvider(): array
    {
        return [
            'Unix epoch' => [2440588, [1348, 10, 11]],
            'Y2K' => [2451545, [1378, 10, 11]],
            'Last day of 1402' => [2460389, [1402, 12, 29]],
            'Nowruz 1403' => [2460390, [1403, 1, 1]],
            'Leap day 1403' => [2460755, [1403, 12, 30]],
            'Nowruz 1404' => [2460756, [1404, 1, 1]],
        ];
    }

    /**
     * Tests from j d n round trips.
     */
    #[DataProvider('jdnRoundTripProvider')]
    public function testFromJDNRoundTrips(JalaliDate $sample): void
    {
        $fromJDN = JalaliDate::fromJDN($sample->jdn);
        self::assertEquals($sample->toArray(), $fromJDN->toArray());
    }

    /**
     * Provides data for jdn round trip tests.
     *
     * @return array<array{JalaliDate}> Provider data sets.
     */
    public static function jdnRoundTripProvider(): array
    {
        $calendar = JalaliDate::calendarSystem();
        return [
            '1402/1/1' => [new JalaliDate(1402, 1, 1)],
            '1403/12/30 (leap)' => [new JalaliDate(1403, 12, 30)],
            '1/1/1' => [new JalaliDate(1, 1, 1)],
            '-1/12/last day' => [new JalaliDate(-1, 12, $calendar->daysInMonth(-1, 12))],
            '-100/6/15' => [new JalaliDate(-100, 6, 15)],
        ];
    }

    /**
     * Tests from j d n rejects outside supported range.
     */
    #[DataProvider('jdnOutOfRangeProvider')]
    public function testFromJDNRejectsOutsideSupportedRange(int $jdn): void
    {
        $this->expectException(DateOutOfRangeException::class);
        JalaliDate::fromJDN($jdn);
    }

    /**
     * Provides data for jdn out of range tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function jdnOutOfRangeProvider(): array
    {
        $calendar = JalaliDate::calendarSystem();
        $min = (new JalaliDate(JalaliDate::MIN_YEAR, 1, 1))->jdn;
        $max = (new JalaliDate(JalaliDate::MAX_YEAR, 12, $calendar->daysInMonth(JalaliDate::MAX_YEAR, 12)))->jdn;

        return [
            'Below minimum' => [$min - 1],
            'Above maximum' => [$max + 1],
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
        $gregorianDate = new GregorianDate($year, $month, $day);
        $jalaliDate = JalaliDate::fromGregorianDate($year, $month, $day);

        self::assertSame($expected, $jalaliDate->toArray());
        self::assertSame($gregorianDate->jdn(), $jalaliDate->jdn());
    }

    /**
     * Provides data for from gregorian date tests.
     *
     * @return array<array{int,int,int,array<mixed>}> Provider data sets.
     */
    public static function fromGregorianDateProvider(): array
    {
        return [
            'Nowruz 1403' => [2024, 3, 20, [1403, 1, 1]],
            'Gregorian leap day' => [2024, 2, 29, [1402, 12, 10]],
            'Jalali leap day' => [2025, 3, 20, [1403, 12, 30]],
            'Negative Gregorian year' => [-100, 6, 15, [-721, 3, 24]],
        ];
    }

    /**
     * Tests from gregorian date rejects invalid date.
     */
    #[DataProvider('invalidGregorianDateProvider')]
    public function testFromGregorianDateRejectsInvalidDate(int $year, int $month, int $day): void
    {
        $this->expectException(InvalidArgumentException::class);

        JalaliDate::fromGregorianDate($year, $month, $day);
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
     * Tests from iso8601 date string known date.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromIso8601DateStringProvider')]
    public function testFromIso8601DateStringKnownDate(string $isoDate, array $expected): void
    {
        self::assertSame($expected, JalaliDate::fromIso8601DateString($isoDate)->toArray());
    }

    /**
     * Provides data for from iso8601 date string tests.
     *
     * @return array<array{string,array<mixed>}> Provider data sets.
     */
    public static function fromIso8601DateStringProvider(): array
    {
        return [
            '2024-03-20' => ['2024-03-20', [1403, 1, 1]],
            '2025-03-20' => ['2025-03-20', [1403, 12, 30]],
        ];
    }

    /**
     * Tests from iso8601 date string rejects invalid format.
     */
    #[DataProvider('invalidIsoDateStringFormatProvider')]
    public function testFromIso8601DateStringRejectsInvalidFormat(string $input): void
    {
        $this->expectException(DateParseException::class);
        JalaliDate::fromIso8601DateString($input);
    }

    /**
     * Provides data for invalid iso date string format tests.
     *
     * @return array<array{string}> Provider data sets.
     */
    public static function invalidIsoDateStringFormatProvider(): array
    {
        return [
            'Empty string' => [''],
            'Wrong separator' => ['2024/03/20'],
            'Two-digit year' => ['24-03-20'],
            'Single-digit month' => ['2024-3-20'],
            'Single-digit day' => ['2024-03-2'],
            'With time component' => ['2024-03-20T00:00:00Z'],
        ];
    }

    /**
     * Tests from iso8601 date string rejects invalid data.
     */
    #[DataProvider('invalidIsoDateStringDataProvider')]
    public function testFromIso8601DateStringRejectsInvalidData(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        JalaliDate::fromIso8601DateString($input);
    }

    /**
     * Provides data for invalid iso date string data tests.
     *
     * @return array<array{string}> Provider data sets.
     */
    public static function invalidIsoDateStringDataProvider(): array
    {
        return [
            'Invalid date' => ['2024-02-30'],
            'Year 0' => ['0000-01-01'],
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
        $jalaliDate = JalaliDate::fromDateTime($dateTime);

        self::assertSame($expected, $jalaliDate->toArray());
    }

    /**
     * Provides data for from date time tests.
     *
     * @return array<array{string,array<mixed>}> Provider data sets.
     */
    public static function fromDateTimeProvider(): array
    {
        return [
            'Before Nowruz in Tehran' => ['2024-03-19T23:59:59+03:30', [1402, 12, 29]],
            'After Nowruz in Tehran' => ['2024-03-20T00:00:00+03:30', [1403, 1, 1]],
            'Same instant before Gregorian midnight in UTC' => ['2024-03-19T20:30:00Z', [1402, 12, 29]],
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
        self::assertSame($expected, JalaliDate::fromDayOfMonth($year, $month, $dayOfMonth)->toArray());
    }

    /**
     * Provides data for from day of month tests.
     *
     * @return array<array{int,int,int,array<mixed>}> Provider data sets.
     */
    public static function fromDayOfMonthProvider(): array
    {
        return [
            'First day of month' => [1403, 1, 1, [1403, 1, 1]],
            'Last day of Farvardin' => [1403, 1, 31, [1403, 1, 31]],
            'Last day of Mehr' => [1403, 7, 30, [1403, 7, 30]],
            'Leap Esfand 30' => [1403, 12, 30, [1403, 12, 30]],
            'Day -1 (last day of non-leap Esfand)' => [1402, 12, -1, [1402, 12, 29]],
            'Day -31 (first day of Farvardin)' => [1403, 1, -31, [1403, 1, 1]],
            'Negative year' => [-100, 6, 15, [-100, 6, 15]],
        ];
    }

    /**
     * Tests from day of month rejects invalid input.
     */
    #[DataProvider('invalidDayOfMonthProvider')]
    public function testFromDayOfMonthRejectsInvalidInput(int $year, int $month, int $dayOfMonth): void
    {
        $this->expectException(InvalidArgumentException::class);
        JalaliDate::fromDayOfMonth($year, $month, $dayOfMonth);
    }

    /**
     * Provides data for invalid day of month tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function invalidDayOfMonthProvider(): array
    {
        return [
            'Day 0' => [1403, 1, 0],
            'Day 32 of Farvardin' => [1403, 1, 32],
            'Day -32 of Farvardin' => [1403, 1, -32],
            'Day 31 of Mehr' => [1403, 7, 31],
            'Day -31 of Mehr' => [1403, 7, -31],
            'Non-leap Esfand 30' => [1402, 12, 30],
        ];
    }

    /**
     * Tests from day of year.
     */
    #[DataProvider('fromDayOfYearProvider')]
    public function testFromDayOfYear(int $year, int $dayOfYear, int $expectedMonth, int $expectedDay): void
    {
        self::assertSame([$year, $expectedMonth, $expectedDay], JalaliDate::fromDayOfYear($year, $dayOfYear)->toArray());
    }

    /**
     * Provides data for from day of year tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function fromDayOfYearProvider(): array
    {
        return [
            'Day 1 of 1402' => [1402, 1, 1, 1],
            'Day 31 of 1402' => [1402, 31, 1, 31],
            'Day 32 of 1402' => [1402, 32, 2, 1],
            'Day 186 of 1402' => [1402, 186, 6, 31],
            'Day 187 of 1402' => [1402, 187, 7, 1],
            'Day 365 of 1402' => [1402, 365, 12, 29],
            'Day 366 of 1403' => [1403, 366, 12, 30],
            'Day -1 of 1402' => [1402, -1, 12, 29],
            'Day -31 of 1402' => [1402, -31, 11, 29],
            'Day -32 of 1402' => [1402, -32, 11, 28],
            'Day -186 of 1402' => [1402, -186, 6, 25],
            'Day -187 of 1402' => [1402, -187, 6, 24],
            'Day -365 of 1402' => [1402, -365, 1, 1],
            'Day -366 of 1403' => [1403, -366, 1, 1],
            'First day of month 8' => [1402, 217, 8, 1],
            'Negative year day 1' => [-100, 1, 1, 1],
            'Negative year day 365' => [-100, 365, 12, 29],
        ];
    }

    /**
     * Tests from day of year rejects invalid input.
     */
    #[DataProvider('invalidDayOfYearProvider')]
    public function testFromDayOfYearRejectsInvalidInput(int $year, int $dayOfYear): void
    {
        $this->expectException(InvalidArgumentException::class);
        JalaliDate::fromDayOfYear($year, $dayOfYear);
    }

    /**
     * Provides data for invalid day of year tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function invalidDayOfYearProvider(): array
    {
        return [
            'Day 0 of 1402' => [1402, 0],
            'Day 366 of 1402 (non-leap)' => [1402, 366],
            'Day 367 of 1403 (leap)' => [1403, 367],
            'Day -366 of 1402 (non-leap)' => [1402, -366],
            'Day -367 of 1403 (leap)' => [1403, -367],
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
        self::assertSame($expected, JalaliDate::fromNthDayOfWeekInYear($year, $occurrence, $dayOfWeek)->toArray());
    }

    /**
     * Provides data for from nth day of week in year tests.
     *
     * @return array<array{int,int,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function fromNthDayOfWeekInYearProvider(): array
    {
        return [
            'First Saturday' => [1403, 1, DayOfWeek::Saturday, [1403, 1, 4]],
            'First Wednesday' => [1403, 1, DayOfWeek::Wednesday, [1403, 1, 1]],
            'First Friday' => [1403, 1, DayOfWeek::Friday, [1403, 1, 3]],
            'Twenty-sixth Wednesday' => [1402, 26, DayOfWeek::Wednesday, [1402, 6, 22]],
            'Fifty-second Saturday' => [1403, 52, DayOfWeek::Saturday, [1403, 12, 25]],
        ];
    }

    /**
     * Tests from nth day of week in year round trips.
     */
    #[DataProvider('fromNthDayOfWeekInYearRoundTripProvider')]
    public function testFromNthDayOfWeekInYearRoundTrips(JalaliDate $sample): void
    {
        $computed = JalaliDate::fromNthDayOfWeekInYear($sample->year, $sample->dayOfWeekInYear, $sample->dayOfWeek);
        self::assertSame($sample->toArray(), $computed->toArray());
    }

    /**
     * Provides data for from nth day of week in year round trip tests.
     *
     * @return array<array{JalaliDate}> Provider data sets.
     */
    public static function fromNthDayOfWeekInYearRoundTripProvider(): array
    {
        return [
            '1403/1/1' => [new JalaliDate(1403, 1, 1)],
            '1403/1/4' => [new JalaliDate(1403, 1, 4)],
            '1403/6/15' => [new JalaliDate(1403, 6, 15)],
            '1403/12/30' => [new JalaliDate(1403, 12, 30)],
        ];
    }

    /**
     * Tests from nth day of week in year rejects invalid input.
     */
    #[DataProvider('invalidWeekOfYearProvider')]
    public function testFromNthDayOfWeekInYearRejectsInvalidInput(int $year, int $occurrence, int $dayOfWeek): void
    {
        $this->expectException(InvalidArgumentException::class);
        JalaliDate::fromNthDayOfWeekInYear($year, $occurrence, $dayOfWeek);
    }

    /**
     * Provides data for invalid week of year tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function invalidWeekOfYearProvider(): array
    {
        return [
            'Occurrence 0' => [1403, 0, 6],
            'Occurrence 54' => [1403, 54, 6],
            'Day of week -1' => [1403, 1, -1],
            'Day of week 7' => [1403, 1, 7],
            'Year 0' => [0, 1, 6],
        ];
    }

    /**
     * Tests from nth day of week in month.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('fromNthDayOfWeekInMonthProvider')]
    public function testFromNthDayOfWeekInMonth(int $year, int $month, int $occurrence, DayOfWeek|int $dayOfWeek, array $expected): void
    {
        self::assertSame($expected, JalaliDate::fromNthDayOfWeekInMonth($year, $month, $occurrence, $dayOfWeek)->toArray());
    }

    /**
     * Provides data for from nth day of week in month tests.
     *
     * @return array<array{int,int,int,DayOfWeek|int,array<mixed>}> Provider data sets.
     */
    public static function fromNthDayOfWeekInMonthProvider(): array
    {
        return [
            'First Wednesday' => [1402, 6, 1, DayOfWeek::Wednesday, [1402, 6, 1]],
            'First Thursday inside month' => [1402, 6, 1, DayOfWeek::Thursday, [1402, 6, 2]],
            'Last Friday from end' => [1402, 6, -1, DayOfWeek::Friday, [1402, 6, 31]],
            'Second Saturday in 1403/1' => [1403, 1, 2, DayOfWeek::Saturday, [1403, 1, 11]],
            'Third Monday in 1405/3' => [1405, 3, 3, DayOfWeek::Monday, [1405, 3, 18]],
            'Fifth Friday in month with 5 Fridays' => [1402, 6, 5, DayOfWeek::Friday, [1402, 6, 31]],
            'Second Friday from end' => [1402, 6, -2, DayOfWeek::Friday, [1402, 6, 24]],
            'First Saturday of 1403/1' => [1403, 1, 1, DayOfWeek::Saturday->value, [1403, 1, 4]],
            'Last Friday of 1403/1' => [1403, 1, -1, DayOfWeek::Friday->value, [1403, 1, 31]],
            'Second Wednesday of 1403/7' => [1403, 7, 2, DayOfWeek::Wednesday->value, [1403, 7, 11]],
            'First Saturday of 1405/1' => [1405, 1, 1, DayOfWeek::Saturday->value, [1405, 1, 1]],
            'Last Saturday of 1405/1' => [1405, 1, -1, DayOfWeek::Saturday->value, [1405, 1, 29]],
            'Second Saturday of 1405/7' => [1405, 7, 2, DayOfWeek::Saturday->value, [1405, 7, 11]],
            'Third Thursday of 1402/6' => [1402, 6, 3, DayOfWeek::Thursday->value, [1402, 6, 16]],
            'Fourth Monday of 1403/12' => [1403, 12, 4, DayOfWeek::Monday->value, [1403, 12, 27]],
            'Fifth Friday of 1402/6' => [1402, 6, 5, DayOfWeek::Friday->value, [1402, 6, 31]],
            'First Sunday of 1405/3' => [1405, 3, 1, DayOfWeek::Sunday->value, [1405, 3, 3]],
            'Last Tuesday of 1402/12' => [1402, 12, -1, DayOfWeek::Tuesday->value, [1402, 12, 29]],
            'Month with only 4 occurrences of day of week' => [1402, 12, 4, 6, [1402, 12, 26]],
            'Negative year' => [-100, 6, 2, 3, [-100, 6, 11]],
        ];
    }

    /**
     * Tests from nth day of week in month round trips.
     */
    #[DataProvider('fromNthDayOfWeekInMonthRoundTripProvider')]
    public function testFromNthDayOfWeekInMonthRoundTrips(JalaliDate $sample): void
    {
        $computed = JalaliDate::fromNthDayOfWeekInMonth($sample->year, $sample->month, $sample->dayOfWeekInMonth, $sample->dayOfWeek);
        self::assertSame($sample->toArray(), $computed->toArray());
    }

    /**
     * Provides data for from nth day of week in month round trip tests.
     *
     * @return array<array{JalaliDate}> Provider data sets.
     */
    public static function fromNthDayOfWeekInMonthRoundTripProvider(): array
    {
        return [
            '1403/1/1' => [new JalaliDate(1403, 1, 1)],
            '1403/1/8' => [new JalaliDate(1403, 1, 8)],
            '1402/6/15' => [new JalaliDate(1402, 6, 15)],
            '1402/6/31' => [new JalaliDate(1402, 6, 31)],
            '1405/3/9' => [new JalaliDate(1405, 3, 9)],
        ];
    }

    /**
     * Tests factories work at boundary years.
     *
     * @param array<mixed> $args Test data.
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('boundaryYearFactoriesProvider')]
    public function testFactoriesWorkAtBoundaryYears(string $method, array $args, array $expected): void
    {
        $result = JalaliDate::$method(...$args);
        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for boundary year factories tests.
     *
     * @return array<array{string,array<mixed>,array<mixed>}> Provider data sets.
     */
    public static function boundaryYearFactoriesProvider(): array
    {
        $calendar = JalaliDate::calendarSystem();
        return [
            'MIN_YEAR fromDayOfYear' => ['fromDayOfYear', [JalaliDate::MIN_YEAR, 1], [JalaliDate::MIN_YEAR, 1, 1]],
            'MAX_YEAR fromDayOfYear last day' => ['fromDayOfYear', [JalaliDate::MAX_YEAR, $calendar->daysInYear(JalaliDate::MAX_YEAR)], [JalaliDate::MAX_YEAR, 12, $calendar->daysInMonth(JalaliDate::MAX_YEAR, 12)]],
        ];
    }

    /**
     * Tests from nth day of week in month rejects invalid input.
     */
    #[DataProvider('invalidNthDayOfWeekInMonthProvider')]
    public function testFromNthDayOfWeekInMonthRejectsInvalidInput(int $year, int $month, int $occurrence, int $dayOfWeek): void
    {
        $this->expectException(InvalidArgumentException::class);
        JalaliDate::fromNthDayOfWeekInMonth($year, $month, $occurrence, $dayOfWeek);
    }

    /**
     * Provides data for invalid nth day of week in month tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function invalidNthDayOfWeekInMonthProvider(): array
    {
        return [
            'Occurrence 0' => [1403, 1, 0, 6],
            'Occurrence 6' => [1403, 1, 6, 6],
            'Occurrence -6' => [1403, 1, -6, 6],
            'Month 0' => [1403, 0, 1, 6],
            'Day of week 7' => [1403, 1, 1, 7],
        ];
    }

    /**
     * Tests set test today to null.
     */
    public function testSetTestTodayToNull(): void
    {
        JalaliDate::setTestToday(new JalaliDate(1405, 1, 1));
        JalaliDate::setTestToday(null);

        self::assertNull(JalaliDate::getTestToday());
    }
}
