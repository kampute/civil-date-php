<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use Kampute\CivilDate\JalaliDate;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date properties.
 */
final class JalaliDatePropertiesTest extends TestCase
{
    /**
     * Tests j d n matches known anchors.
     */
    #[DataProvider('jdnKnownAnchorsProvider')]
    public function testJDNMatchesKnownAnchors(JalaliDate $jalali, int $expectedJDN): void
    {
        self::assertSame($expectedJDN, $jalali->jdn);
    }

    /**
     * Provides data for jdn known anchors tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function jdnKnownAnchorsProvider(): array
    {
        return [
            '1403/1/1' => [new JalaliDate(1403, 1, 1), (new GregorianDate(2024, 3, 20))->jdn()],
            '1404/1/1' => [new JalaliDate(1404, 1, 1), (new GregorianDate(2025, 3, 21))->jdn()],
        ];
    }

    /**
     * Tests j d n is consecutive for adjacent dates.
     */
    #[DataProvider('consecutiveDatesProvider')]
    public function testJDNIsConsecutiveForAdjacentDates(JalaliDate $date1, JalaliDate $date2): void
    {
        self::assertSame(1, $date2->jdn - $date1->jdn);
    }

    /**
     * Provides data for consecutive dates tests.
     *
     * @return array<array{JalaliDate,JalaliDate}> Provider data sets.
     */
    public static function consecutiveDatesProvider(): array
    {
        $calendar = JalaliDate::calendarSystem();
        return [
            'Month boundary 1402/6/31 -> 1402/7/1' => [new JalaliDate(1402, 6, 31), new JalaliDate(1402, 7, 1)],
            'Year boundary 1402/12/29 -> 1403/1/1' => [new JalaliDate(1402, 12, 29), new JalaliDate(1403, 1, 1)],
            'Year-0 boundary -1/12/last -> 1/1/1' => [new JalaliDate(-1, 12, $calendar->daysInMonth(-1, 12)), new JalaliDate(1, 1, 1)],
        ];
    }

    /**
     * Tests date components.
     */
    #[DataProvider('dateComponentsProvider')]
    public function testDateComponents(JalaliDate $date, int $expectedYear, int $expectedMonth, int $expectedDay): void
    {
        self::assertSame($expectedYear, $date->year);
        self::assertSame($expectedMonth, $date->month);
        self::assertSame($expectedDay, $date->day);
    }

    /**
     * Provides data for date components tests.
     *
     * @return array<array{JalaliDate,int,int,int}> Provider data sets.
     */
    public static function dateComponentsProvider(): array
    {
        return [
            'Positive date' => [new JalaliDate(1403, 1, 1), 1403, 1, 1],
            'Negative date' => [new JalaliDate(-1, 12, 30), -1, 12, 30],
        ];
    }

    /**
     * Tests quarter.
     */
    #[DataProvider('quarterProvider')]
    public function testQuarter(int $month, int $expected): void
    {
        $date = new JalaliDate(1403, $month, 1);
        self::assertSame($expected, $date->quarter);
    }

    /**
     * Provides data for quarter tests.
     *
     * @return array<array{int,int}> Provider data sets.
     */
    public static function quarterProvider(): array
    {
        return [
            'Spring month 1' => [1, 1],
            'Spring month 2' => [2, 1],
            'Spring month 3' => [3, 1],
            'Summer month 4' => [4, 2],
            'Summer month 5' => [5, 2],
            'Summer month 6' => [6, 2],
            'Autumn month 7' => [7, 3],
            'Autumn month 8' => [8, 3],
            'Autumn month 9' => [9, 3],
            'Winter month 10' => [10, 4],
            'Winter month 11' => [11, 4],
            'Winter month 12' => [12, 4],
        ];
    }

    /**
     * Tests season.
     */
    #[DataProvider('seasonProvider')]
    public function testSeason(int $month, Season $expected): void
    {
        $date = new JalaliDate(1403, $month, 1);
        self::assertSame($expected, $date->season);
    }

    /**
     * Provides data for season tests.
     *
     * @return array<array{int,Season}> Provider data sets.
     */
    public static function seasonProvider(): array
    {
        return [
            'Spring' => [1, Season::Spring],
            'Summer' => [4, Season::Summer],
            'Autumn' => [7, Season::Autumn],
            'Winter' => [10, Season::Winter],
        ];
    }

    /**
     * Tests day of year cases.
     */
    #[DataProvider('dayOfYearProvider')]
    public function testDayOfYearCases(JalaliDate $date, int $expected): void
    {
        self::assertSame($expected, $date->dayOfYear);
    }

    /**
     * Provides data for day of year tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function dayOfYearProvider(): array
    {
        return [
            '1402/1/1' => [new JalaliDate(1402, 1, 1), 1],
            '1402/1/31' => [new JalaliDate(1402, 1, 31), 31],
            '1402/2/1' => [new JalaliDate(1402, 2, 1), 32],
            '1402/6/31' => [new JalaliDate(1402, 6, 31), 186],
            '1402/7/1' => [new JalaliDate(1402, 7, 1), 187],
            '1402/12/29' => [new JalaliDate(1402, 12, 29), 365],
            '1403/12/30' => [new JalaliDate(1403, 12, 30), 366],

            // Different years
            '1403/1/1 (leap)' => [new JalaliDate(1403, 1, 1), 1],
            '1403/6/31 (leap)' => [new JalaliDate(1403, 6, 31), 186],

            // Edge months
            '1402/7/30' => [new JalaliDate(1402, 7, 30), 216],
            '1402/11/30' => [new JalaliDate(1402, 11, 30), 336],
        ];
    }

    /**
     * Tests day of week.
     */
    #[DataProvider('dayOfWeekProvider')]
    public function testDayOfWeek(JalaliDate $date, DayOfWeek $expected): void
    {
        self::assertSame($expected, $date->dayOfWeek);
    }

    /**
     * Provides data for day of week tests.
     *
     * @return array<array{JalaliDate,DayOfWeek}> Provider data sets.
     */
    public static function dayOfWeekProvider(): array
    {
        return [
            '1403/1/1 (Wed)' => [new JalaliDate(1403, 1, 1), DayOfWeek::Wednesday],
            '1403/1/2 (Thu)' => [new JalaliDate(1403, 1, 2), DayOfWeek::Thursday],
            '1403/1/3 (Fri)' => [new JalaliDate(1403, 1, 3), DayOfWeek::Friday],
            '1403/1/4 (Sat)' => [new JalaliDate(1403, 1, 4), DayOfWeek::Saturday],
            '1403/1/5 (Sun)' => [new JalaliDate(1403, 1, 5), DayOfWeek::Sunday],
            '1403/1/6 (Mon)' => [new JalaliDate(1403, 1, 6), DayOfWeek::Monday],
            '1403/1/7 (Tue)' => [new JalaliDate(1403, 1, 7), DayOfWeek::Tuesday],
        ];
    }

    /**
     * Tests day of week in year.
     */
    #[DataProvider('dayOfWeekInYearProvider')]
    public function testDayOfWeekInYear(JalaliDate $date, int $expected): void
    {
        self::assertSame($expected, $date->dayOfWeekInYear);
    }

    /**
     * Provides data for day of week in year tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function dayOfWeekInYearProvider(): array
    {
        return [
            '1403/1/1 (Wed)' => [new JalaliDate(1403, 1, 1), 1],
            '1403/1/3 (Fri)' => [new JalaliDate(1403, 1, 3), 1],
            '1403/1/4 (Sat)' => [new JalaliDate(1403, 1, 4), 1],
            '1403/1/31 (last day)' => [new JalaliDate(1403, 1, 31), 5],
        ];
    }

    /**
     * Tests day of week in month.
     */
    #[DataProvider('dayOfWeekInMonthProvider')]
    public function testDayOfWeekInMonth(JalaliDate $date, int $expected): void
    {
        self::assertSame($expected, $date->dayOfWeekInMonth);
    }

    /**
     * Provides data for day of week in month tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function dayOfWeekInMonthProvider(): array
    {
        return [
            '1403/1/1 (Wed)' => [new JalaliDate(1403, 1, 1), 1],
            '1403/1/3 (Fri)' => [new JalaliDate(1403, 1, 3), 1],
            '1403/1/4 (Sat)' => [new JalaliDate(1403, 1, 4), 1],
            '1403/1/31 (last day)' => [new JalaliDate(1403, 1, 31), 5],
        ];
    }

    /**
     * Tests is leap year.
     */
    #[DataProvider('isLeapYearProvider')]
    public function testIsLeapYear(int $year, int $month, int $day, bool $expected): void
    {
        $date = new JalaliDate($year, $month, $day);
        self::assertSame($expected, $date->isLeapYear);
    }

    /**
     * Provides data for is leap year tests.
     *
     * @return array<array{int,int,int,bool}> Provider data sets.
     */
    public static function isLeapYearProvider(): array
    {
        return [
            'Non-leap year' => [1402, 1, 1, false],
            'Leap year' => [1403, 1, 1, true],
        ];
    }

    /**
     * Tests months in year.
     */
    #[DataProvider('monthsInYearProvider')]
    public function testMonthsInYear(int $year): void
    {
        self::assertSame(12, (new JalaliDate($year, 1, 1))->monthsInYear);
    }

    /**
     * Provides data for months in year tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function monthsInYearProvider(): array
    {
        return [
            'Non-leap year' => [1402],
            'Leap year' => [1403],
            'Negative year' => [-100],
        ];
    }

    /**
     * Tests days in month.
     */
    #[DataProvider('daysInMonthProvider')]
    public function testDaysInMonth(int $year, int $month, int $day, int $expected): void
    {
        $date = new JalaliDate($year, $month, $day);
        self::assertSame($expected, $date->daysInMonth);
    }

    /**
     * Provides data for days in month tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function daysInMonthProvider(): array
    {
        return [
            'Non-leap month 12' => [1402, 12, 1, 29],
            'Leap month 12' => [1403, 12, 1, 30],
        ];
    }

    /**
     * Tests days in year.
     */
    #[DataProvider('daysInYearProvider')]
    public function testDaysInYear(int $year, int $month, int $day, int $expected): void
    {
        $date = new JalaliDate($year, $month, $day);
        self::assertSame($expected, $date->daysInYear);
    }

    /**
     * Provides data for days in year tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function daysInYearProvider(): array
    {
        return [
            'Non-leap year' => [1402, 1, 1, 365],
            'Leap year' => [1403, 1, 1, 366],
        ];
    }
}
