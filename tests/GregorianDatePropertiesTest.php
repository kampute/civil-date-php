<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date properties.
 */
final class GregorianDatePropertiesTest extends TestCase
{
    /**
     * Tests j d n matches known anchors.
     */
    #[DataProvider('jdnKnownAnchorsProvider')]
    public function testJDNMatchesKnownAnchors(GregorianDate $date, int $expectedJDN): void
    {
        self::assertSame($expectedJDN, $date->jdn);
    }

    /**
     * Provides data for jdn known anchors tests.
     *
     * @return array<array{GregorianDate,int}> Provider data sets.
     */
    public static function jdnKnownAnchorsProvider(): array
    {
        return [
            'Unix epoch' => [new GregorianDate(1970, 1, 1), 2440588],
            'Nowruz 1403' => [new GregorianDate(2024, 3, 20), 2460390],
            'Nowruz 1404' => [new GregorianDate(2025, 3, 21), 2460756],
            'Y2K' => [new GregorianDate(2000, 1, 1), 2451545],
        ];
    }

    /**
     * Tests j d n is consecutive for adjacent dates.
     */
    #[DataProvider('consecutiveDatesProvider')]
    public function testJDNIsConsecutiveForAdjacentDates(GregorianDate $date1, GregorianDate $date2): void
    {
        self::assertSame(1, $date2->jdn - $date1->jdn);
    }

    /**
     * Provides data for consecutive dates tests.
     *
     * @return array<array{GregorianDate,GregorianDate}> Provider data sets.
     */
    public static function consecutiveDatesProvider(): array
    {
        return [
            'Month boundary 2025/3/31 -> 2025/4/1' => [new GregorianDate(2025, 3, 31), new GregorianDate(2025, 4, 1)],
            'Year boundary 2024/12/31 -> 2025/1/1' => [new GregorianDate(2024, 12, 31), new GregorianDate(2025, 1, 1)],
            'Year-0 boundary -1/12/31 -> 1/1/1' => [new GregorianDate(-1, 12, 31), new GregorianDate(1, 1, 1)],
        ];
    }

    /**
     * Tests date components.
     */
    #[DataProvider('dateComponentsProvider')]
    public function testDateComponents(GregorianDate $date, int $expectedYear, int $expectedMonth, int $expectedDay): void
    {
        self::assertSame($expectedYear, $date->year);
        self::assertSame($expectedMonth, $date->month);
        self::assertSame($expectedDay, $date->day);
    }

    /**
     * Provides data for date components tests.
     *
     * @return array<array{GregorianDate,int,int,int}> Provider data sets.
     */
    public static function dateComponentsProvider(): array
    {
        return [
            'Positive date' => [new GregorianDate(2025, 3, 21), 2025, 3, 21],
            'Negative date' => [new GregorianDate(-1, 12, 31), -1, 12, 31],
        ];
    }

    /**
     * Tests quarter.
     */
    #[DataProvider('quarterProvider')]
    public function testQuarter(int $month, int $expected): void
    {
        $date = new GregorianDate(2025, $month, 15);
        self::assertSame($expected, $date->quarter());
    }

    /**
     * Provides data for quarter tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function quarterProvider(): array
    {
        return [
            'Q1 January' => [1, 1],
            'Q1 February' => [2, 1],
            'Q1 March' => [3, 1],
            'Q2 April' => [4, 2],
            'Q2 May' => [5, 2],
            'Q2 June' => [6, 2],
            'Q3 July' => [7, 3],
            'Q3 August' => [8, 3],
            'Q3 September' => [9, 3],
            'Q4 October' => [10, 4],
            'Q4 November' => [11, 4],
            'Q4 December' => [12, 4],
        ];
    }

    /**
     * Tests season.
     */
    #[DataProvider('seasonProvider')]
    public function testSeason(int $year, int $month, int $day, Season $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        self::assertSame($expected, $date->season());
    }

    /**
     * Provides data for season tests.
     *
     * @return array<array{int,int,int,Season}> Provider data sets.
     */
    public static function seasonProvider(): array
    {
        return [
            'Winter before Nowruz' => [2025, 3, 20, Season::Winter],
            'Spring on Nowruz' => [2025, 3, 21, Season::Spring],
            'Spring after Nowruz' => [2025, 4, 15, Season::Spring],
            'Summer' => [2025, 7, 15, Season::Summer],
            'Autumn' => [2025, 10, 15, Season::Autumn],
            'Winter' => [2025, 1, 15, Season::Winter],
        ];
    }

    /**
     * Tests day of week.
     */
    #[DataProvider('dayOfWeekProvider')]
    public function testDayOfWeek(int $year, int $month, int $day, DayOfWeek $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        self::assertSame($expected, $date->dayOfWeek);
    }

    /**
     * Provides data for day of week tests.
     *
     * @return array<array{int,int,int,DayOfWeek}> Provider data sets.
     */
    public static function dayOfWeekProvider(): array
    {
        return [
            'Friday' => [2025, 3, 21, DayOfWeek::Friday],
            'Saturday' => [2025, 3, 22, DayOfWeek::Saturday],
            'Sunday' => [2025, 3, 23, DayOfWeek::Sunday],
            'Monday' => [2025, 3, 24, DayOfWeek::Monday],
            'Tuesday' => [2025, 3, 25, DayOfWeek::Tuesday],
            'Wednesday' => [2025, 3, 26, DayOfWeek::Wednesday],
            'Thursday' => [2025, 3, 27, DayOfWeek::Thursday],
        ];
    }

    /**
     * Tests day of year.
     */
    #[DataProvider('dayOfYearProvider')]
    public function testDayOfYear(int $year, int $month, int $day, int $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        self::assertSame($expected, $date->dayOfYear());
    }

    /**
     * Provides data for day of year tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function dayOfYearProvider(): array
    {
        return [
            'First day of year' => [2025, 1, 1, 1],
            'End of January' => [2025, 1, 31, 31],
            'Start of February' => [2025, 2, 1, 32],
            'Feb 28 non-leap' => [2025, 2, 28, 59],
            'March 1 non-leap' => [2025, 3, 1, 60],
            'Feb 28 leap year' => [2024, 2, 28, 59],
            'Feb 29 leap year' => [2024, 2, 29, 60],
            'March 1 leap year' => [2024, 3, 1, 61],
            'Nowruz 2025' => [2025, 3, 21, 80],
            'Last day non-leap' => [2025, 12, 31, 365],
            'Last day leap' => [2024, 12, 31, 366],
        ];
    }

    /**
     * Tests day of week in year.
     */
    #[DataProvider('dayOfWeekInYearProvider')]
    public function testDayOfWeekInYear(int $year, int $month, int $day, int $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        self::assertSame($expected, $date->dayOfWeekInYear());
    }

    /**
     * Provides data for day of week in year tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function dayOfWeekInYearProvider(): array
    {
        return [
            'Last Saturday in 2024' => [2024, 12, 28, 52],
            'First Wednesday in 2025' => [2025, 1, 1, 1],
            'First Friday in 2025' => [2025, 1, 3, 1],
            'First Saturday in 2025' => [2025, 1, 4, 1],
            'Mid-year Saturday' => [2025, 6, 21, 25],
            'Last Friday in 2025' => [2025, 12, 26, 52],
        ];
    }

    /**
     * Tests day of week in month.
     */
    #[DataProvider('dayOfWeekInMonthProvider')]
    public function testDayOfWeekInMonth(int $year, int $month, int $day, int $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        self::assertSame($expected, $date->dayOfWeekInMonth());
    }

    /**
     * Provides data for day of week in month tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function dayOfWeekInMonthProvider(): array
    {
        return [
            'March 1, 2025 (Saturday)' => [2025, 3, 1, 1],
            'March 7, 2025 (Friday)' => [2025, 3, 7, 1],
            'March 8, 2025 (Saturday)' => [2025, 3, 8, 2],
            'March 21, 2025 (Friday)' => [2025, 3, 21, 3],
            'March 22, 2025 (Saturday)' => [2025, 3, 22, 4],
            'March 31, 2025 (Monday)' => [2025, 3, 31, 5],
        ];
    }

    /**
     * Tests is leap year.
     */
    #[DataProvider('isLeapYearProvider')]
    public function testIsLeapYear(int $year, bool $expected): void
    {
        $date = new GregorianDate($year, 1, 1);
        self::assertSame($expected, $date->isLeapYear);
    }

    /**
     * Provides data for is leap year tests.
     *
     * @return array<array{int,bool}> Provider data sets.
     */
    public static function isLeapYearProvider(): array
    {
        return [
            '2024 is leap' => [2024, true],
            '2025 is not leap' => [2025, false],
            '2000 is leap divisible by 400' => [2000, true],
            '1900 is not leap divisible by 100 but not 400' => [1900, false],
            '2100 is not leap' => [2100, false],
            '2400 is leap' => [2400, true],
            '1996 is leap' => [1996, true],
            '1997 is not leap' => [1997, false],
        ];
    }

    /**
     * Tests months in year.
     */
    #[DataProvider('monthsInYearProvider')]
    public function testMonthsInYear(int $year): void
    {
        $date = new GregorianDate($year, 1, 1);
        self::assertSame(12, $date->monthsInYear);
    }

    /**
     * Provides data for months in year tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function monthsInYearProvider(): array
    {
        return [
            'Standard year' => [2025],
            'Leap year' => [2024],
            'Negative year' => [-100],
        ];
    }

    /**
     * Tests days in month.
     */
    #[DataProvider('daysInMonthProvider')]
    public function testDaysInMonth(int $year, int $month, int $expected): void
    {
        $date = new GregorianDate($year, $month, 1);
        self::assertSame($expected, $date->daysInMonth);
    }

    /**
     * Provides data for days in month tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function daysInMonthProvider(): array
    {
        return [
            'January' => [2025, 1, 31],
            'February non-leap' => [2025, 2, 28],
            'February leap' => [2024, 2, 29],
            'March' => [2025, 3, 31],
            'April' => [2025, 4, 30],
            'May' => [2025, 5, 31],
            'June' => [2025, 6, 30],
            'July' => [2025, 7, 31],
            'August' => [2025, 8, 31],
            'September' => [2025, 9, 30],
            'October' => [2025, 10, 31],
            'November' => [2025, 11, 30],
            'December' => [2025, 12, 31],
        ];
    }

    /**
     * Tests days in year.
     */
    #[DataProvider('daysInYearProvider')]
    public function testDaysInYear(int $year, int $expected): void
    {
        $date = new GregorianDate($year, 1, 1);
        self::assertSame($expected, $date->daysInYear);
    }

    /**
     * Provides data for days in year tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function daysInYearProvider(): array
    {
        return [
            '2024 leap year' => [2024, 366],
            '2025 non-leap year' => [2025, 365],
            '2000 leap year' => [2000, 366],
            '1900 non-leap year' => [1900, 365],
        ];
    }
}
