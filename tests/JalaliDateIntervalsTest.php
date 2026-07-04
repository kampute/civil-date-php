<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date intervals.
 */
final class JalaliDateIntervalsTest extends TestCase
{
    /**
     * Tests difference in days.
     */
    #[DataProvider('differenceInDaysProvider')]
    public function testDifferenceInDays(JalaliDate $start, JalaliDate $end, int $expected): void
    {
        self::assertSame($expected, $start->differenceInDays($end));
    }

    /**
     * Provides data for difference in days tests.
     *
     * @return array<array{JalaliDate,JalaliDate,int}> Provider data sets.
     */
    public static function differenceInDaysProvider(): array
    {
        return [
            // Same month
            '1403/5/1 to 1403/5/11' => [new JalaliDate(1403, 5, 1), new JalaliDate(1403, 5, 11), 10],
            '1403/5/11 to 1403/5/1' => [new JalaliDate(1403, 5, 11), new JalaliDate(1403, 5, 1), -10],
            'Same date' => [new JalaliDate(1403, 5, 15), new JalaliDate(1403, 5, 15), 0],

            // Month boundary
            '1403/5/25 to 1403/6/5' => [new JalaliDate(1403, 5, 25), new JalaliDate(1403, 6, 5), 11],

            // Year boundary
            '1402/12/25 to 1403/1/5' => [new JalaliDate(1402, 12, 25), new JalaliDate(1403, 1, 5), 9],

            // Full year (leap)
            '1403/1/1 to 1403/12/30' => [new JalaliDate(1403, 1, 1), new JalaliDate(1403, 12, 30), 365],

            // Full year (non-leap)
            '1402/1/1 to 1402/12/29' => [new JalaliDate(1402, 1, 1), new JalaliDate(1402, 12, 29), 364],

            // Large differences
            '1400/1/1 to 1403/1/1' => [new JalaliDate(1400, 1, 1), new JalaliDate(1403, 1, 1), 1095],
        ];
    }

    /**
     * Tests difference in days across year zero boundary.
     */
    #[DataProvider('differenceInDaysAcrossYearZeroProvider')]
    public function testDifferenceInDaysAcrossYearZeroBoundary(JalaliDate $start, JalaliDate $end, int $expected): void
    {
        self::assertSame($expected, $start->differenceInDays($end));
    }

    /**
     * Provides data for difference in days across year zero tests.
     *
     * @return array<array{JalaliDate,JalaliDate,int}> Provider data sets.
     */
    public static function differenceInDaysAcrossYearZeroProvider(): array
    {
        $calendar = JalaliDate::calendarSystem();
        $lastDay = $calendar->daysInMonth(-1, 12);
        return [
            '-1/12/last to 1/1/1' => [new JalaliDate(-1, 12, $lastDay), new JalaliDate(1, 1, 1), 1],
            '1/1/1 to -1/12/last' => [new JalaliDate(1, 1, 1), new JalaliDate(-1, 12, $lastDay), -1],
        ];
    }

    /**
     * Tests difference in months.
     */
    #[DataProvider('differenceInMonthsProvider')]
    public function testDifferenceInMonths(JalaliDate $start, JalaliDate $end, int $expected): void
    {
        self::assertSame($expected, $start->differenceInMonths($end));
    }

    /**
     * Provides data for difference in months tests.
     *
     * @return array<array{JalaliDate,JalaliDate,int}> Provider data sets.
     */
    public static function differenceInMonthsProvider(): array
    {
        return [
            // Simple month differences
            '1403/1/15 to 1403/5/15' => [new JalaliDate(1403, 1, 15), new JalaliDate(1403, 5, 15), 4],
            '1403/5/15 to 1403/1/15' => [new JalaliDate(1403, 5, 15), new JalaliDate(1403, 1, 15), -4],

            // Same month (different days)
            '1403/5/15 to 1403/5/20' => [new JalaliDate(1403, 5, 15), new JalaliDate(1403, 5, 20), 0],

            // Not full month (day before)
            '1403/1/20 to 1403/2/15' => [new JalaliDate(1403, 1, 20), new JalaliDate(1403, 2, 15), 0],
            '1403/1/15 to 1403/2/14' => [new JalaliDate(1403, 1, 15), new JalaliDate(1403, 2, 14), 0],

            // Full month (exact day)
            '1403/1/15 to 1403/2/15' => [new JalaliDate(1403, 1, 15), new JalaliDate(1403, 2, 15), 1],

            // Year boundary
            '1402/10/15 to 1403/2/15' => [new JalaliDate(1402, 10, 15), new JalaliDate(1403, 2, 15), 4],

            // Year-0 boundary
            '-1/6/15 to 1/6/15' => [new JalaliDate(-1, 6, 15), new JalaliDate(1, 6, 15), 12],

            // Full year
            '1402/1/15 to 1403/1/15' => [new JalaliDate(1402, 1, 15), new JalaliDate(1403, 1, 15), 12],

            // Large differences
            '1400/1/1 to 1410/1/1' => [new JalaliDate(1400, 1, 1), new JalaliDate(1410, 1, 1), 120],
        ];
    }

    /**
     * Tests difference in years.
     */
    #[DataProvider('differenceInYearsProvider')]
    public function testDifferenceInYears(JalaliDate $start, JalaliDate $end, int $expected): void
    {
        self::assertSame($expected, $start->differenceInYears($end));
    }

    /**
     * Provides data for difference in years tests.
     *
     * @return array<array{JalaliDate,JalaliDate,int}> Provider data sets.
     */
    public static function differenceInYearsProvider(): array
    {
        return [
            // Simple year differences
            '1400/5/15 to 1403/5/15' => [new JalaliDate(1400, 5, 15), new JalaliDate(1403, 5, 15), 3],
            '1403/5/15 to 1400/5/15' => [new JalaliDate(1403, 5, 15), new JalaliDate(1400, 5, 15), -3],

            // Same year (different months)
            '1403/1/15 to 1403/12/15' => [new JalaliDate(1403, 1, 15), new JalaliDate(1403, 12, 15), 0],

            // Not full year (month before)
            '1400/6/15 to 1403/5/15' => [new JalaliDate(1400, 6, 15), new JalaliDate(1403, 5, 15), 2],

            // Full year (exact month/day)
            '1400/5/15 to 1403/6/15' => [new JalaliDate(1400, 5, 15), new JalaliDate(1403, 6, 15), 3],

            // Year-0 boundary
            '-1/5/15 to 1/5/15' => [new JalaliDate(-1, 5, 15), new JalaliDate(1, 5, 15), 1],

            // Large differences
            '1300/1/1 to 1400/1/1' => [new JalaliDate(1300, 1, 1), new JalaliDate(1400, 1, 1), 100],
            '1400/1/1 to 1300/1/1' => [new JalaliDate(1400, 1, 1), new JalaliDate(1300, 1, 1), -100],
        ];
    }

    /**
     * Tests days of week in year.
     */
    #[DataProvider('daysOfWeekInYearProvider')]
    public function testDaysOfWeekInYear(int $year, DayOfWeek $dayOfWeek, int $expected): void
    {
        $date = new JalaliDate($year, 1, 1);

        self::assertSame($expected, $date->daysOfWeekInYear($dayOfWeek));
    }

    /**
     * Provides data for days of week in year tests.
     *
     * @return array<array{int,DayOfWeek,int}> Provider data sets.
     */
    public static function daysOfWeekInYearProvider(): array
    {
        return [
            'Saturday in 1402' => [1402, DayOfWeek::Saturday, 52],
            'Friday in 1402' => [1402, DayOfWeek::Friday, 52],
            'Wednesday in 1403 leap year' => [1403, DayOfWeek::Wednesday, 53],
        ];
    }

    /**
     * Tests days of week in year rejects invalid day of week.
     */
    public function testDaysOfWeekInYearRejectsInvalidDayOfWeek(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->daysOfWeekInYear(7);
    }

    /**
     * Tests days of week in month.
     */
    #[DataProvider('daysOfWeekInMonthProvider')]
    public function testDaysOfWeekInMonth(DayOfWeek $dayOfWeek, int $expected): void
    {
        $date = new JalaliDate(1402, 6, 15);

        self::assertSame($expected, $date->daysOfWeekInMonth($dayOfWeek));
    }

    /**
     * Provides data for days of week in month tests.
     *
     * @return array<array{DayOfWeek,int}> Provider data sets.
     */
    public static function daysOfWeekInMonthProvider(): array
    {
        return [
            'Friday in 31-day month' => [DayOfWeek::Friday, 5],
            'Saturday in 31-day month' => [DayOfWeek::Saturday, 4],
            'Thursday in 31-day month' => [DayOfWeek::Thursday, 5],
        ];
    }

    /**
     * Tests weeks in year.
     */
    #[DataProvider('weeksInYearProvider')]
    public function testWeeksInYear(DayOfWeek $firstDayOfWeek, int $expected): void
    {
        $date = new JalaliDate(1402, 6, 15);

        self::assertSame($expected, $date->weeksInYear($firstDayOfWeek));
    }

    /**
     * Provides data for weeks in year tests.
     *
     * @return array<array{DayOfWeek,int}> Provider data sets.
     */
    public static function weeksInYearProvider(): array
    {
        return [
            'Saturday week start' => [DayOfWeek::Saturday, 53],
            'Sunday week start' => [DayOfWeek::Sunday, 53],
            'Monday week start' => [DayOfWeek::Monday, 53],
        ];
    }

    /**
     * Tests weeks in month.
     */
    #[DataProvider('weeksInMonthProvider')]
    public function testWeeksInMonth(DayOfWeek $firstDayOfWeek, int $expected): void
    {
        $date = new JalaliDate(1402, 6, 15);

        self::assertSame($expected, $date->weeksInMonth($firstDayOfWeek));
    }

    /**
     * Provides data for weeks in month tests.
     *
     * @return array<array{DayOfWeek,int}> Provider data sets.
     */
    public static function weeksInMonthProvider(): array
    {
        return [
            'Saturday week start' => [DayOfWeek::Saturday, 5],
            'Sunday week start' => [DayOfWeek::Sunday, 5],
            'Monday week start' => [DayOfWeek::Monday, 5],
        ];
    }
}
