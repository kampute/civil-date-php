<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date intervals.
 */
final class GregorianDateIntervalsTest extends TestCase
{
    /**
     * Tests difference in days.
     */
    #[DataProvider('differenceInDaysProvider')]
    public function testDifferenceInDays(GregorianDate $date1, GregorianDate $date2, int $expected): void
    {
        self::assertSame($expected, $date1->differenceInDays($date2));
    }

    /**
     * Provides data for difference in days tests.
     *
     * @return array<array{GregorianDate,GregorianDate,int}> Provider data sets.
     */
    public static function differenceInDaysProvider(): array
    {
        return [
            'Same day' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), 0],
            'One day forward' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 22), 1],
            'One day backward' => [new GregorianDate(2025, 3, 22), new GregorianDate(2025, 3, 21), -1],
            'Month crossing' => [new GregorianDate(2025, 3, 31), new GregorianDate(2025, 4, 1), 1],
            'Year crossing' => [new GregorianDate(2024, 12, 31), new GregorianDate(2025, 1, 1), 1],
            'Leap year February to March' => [new GregorianDate(2024, 2, 28), new GregorianDate(2024, 3, 1), 2],
            'Non-leap February to March' => [new GregorianDate(2025, 2, 28), new GregorianDate(2025, 3, 1), 1],
        ];
    }

    /**
     * Tests difference in months.
     */
    #[DataProvider('differenceInMonthsProvider')]
    public function testDifferenceInMonths(GregorianDate $date1, GregorianDate $date2, int $expected): void
    {
        self::assertSame($expected, $date1->differenceInMonths($date2));
    }

    /**
     * Provides data for difference in months tests.
     *
     * @return array<array{GregorianDate,GregorianDate,int}> Provider data sets.
     */
    public static function differenceInMonthsProvider(): array
    {
        return [
            'Same month' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), 0],
            'One month forward' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 4, 21), 1],
            'One month backward' => [new GregorianDate(2025, 4, 21), new GregorianDate(2025, 3, 21), -1],
            'Year crossing' => [new GregorianDate(2024, 12, 15), new GregorianDate(2025, 1, 15), 1],
            'Earlier day is not full month' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 4, 20), 0],
            'Later day is full month' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 4, 22), 1],
            'Multiple years' => [new GregorianDate(2023, 3, 21), new GregorianDate(2025, 3, 21), 24],
        ];
    }

    /**
     * Tests difference in years.
     */
    #[DataProvider('differenceInYearsProvider')]
    public function testDifferenceInYears(GregorianDate $date1, GregorianDate $date2, int $expected): void
    {
        self::assertSame($expected, $date1->differenceInYears($date2));
    }

    /**
     * Provides data for difference in years tests.
     *
     * @return array<array{GregorianDate,GregorianDate,int}> Provider data sets.
     */
    public static function differenceInYearsProvider(): array
    {
        return [
            'Same year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), 0],
            'One year forward' => [new GregorianDate(2025, 3, 21), new GregorianDate(2026, 3, 21), 1],
            'One year backward' => [new GregorianDate(2026, 3, 21), new GregorianDate(2025, 3, 21), -1],
            'Earlier month is not full year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2026, 2, 21), 0],
            'Earlier day is not full year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2026, 3, 20), 0],
            'Later month is full year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2026, 4, 21), 1],
            'Leap to non-leap birthday' => [new GregorianDate(2024, 2, 29), new GregorianDate(2025, 2, 28), 0],
            'Crossing year zero' => [new GregorianDate(-1, 6, 15), new GregorianDate(1, 6, 15), 1],
        ];
    }

    /**
     * Tests days of week in year.
     */
    #[DataProvider('daysOfWeekInYearProvider')]
    public function testDaysOfWeekInYear(int $year, DayOfWeek $dayOfWeek, int $expected): void
    {
        self::assertSame($expected, (new GregorianDate($year, 1, 1))->daysOfWeekInYear($dayOfWeek));
    }

    /**
     * Provides data for days of week in year tests.
     *
     * @return array<array{int,DayOfWeek,int}> Provider data sets.
     */
    public static function daysOfWeekInYearProvider(): array
    {
        return [
            'Saturday in 2025' => [2025, DayOfWeek::Saturday, 52],
            'Wednesday in 2025' => [2025, DayOfWeek::Wednesday, 53],
            'Monday in leap year 2024' => [2024, DayOfWeek::Monday, 53],
        ];
    }

    /**
     * Tests days of week in year rejects invalid day of week.
     */
    public function testDaysOfWeekInYearRejectsInvalidDayOfWeek(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GregorianDate(2025, 1, 1))->daysOfWeekInYear(7);
    }

    /**
     * Tests days of week in month.
     */
    #[DataProvider('daysOfWeekInMonthProvider')]
    public function testDaysOfWeekInMonth(int $year, int $month, DayOfWeek $dayOfWeek, int $expected): void
    {
        self::assertSame($expected, (new GregorianDate($year, $month, 1))->daysOfWeekInMonth($dayOfWeek));
    }

    /**
     * Provides data for days of week in month tests.
     *
     * @return array<array{int,int,DayOfWeek,int}> Provider data sets.
     */
    public static function daysOfWeekInMonthProvider(): array
    {
        return [
            'Saturday in March 2025' => [2025, 3, DayOfWeek::Saturday, 5],
            'Friday in March 2025' => [2025, 3, DayOfWeek::Friday, 4],
            'Thursday in February 2024' => [2024, 2, DayOfWeek::Thursday, 5],
        ];
    }

    /**
     * Tests weeks in year.
     */
    #[DataProvider('weeksInYearProvider')]
    public function testWeeksInYear(int $year, DayOfWeek $firstDayOfWeek, int $expected): void
    {
        self::assertSame($expected, (new GregorianDate($year, 1, 1))->weeksInYear($firstDayOfWeek));
    }

    /**
     * Provides data for weeks in year tests.
     *
     * @return array<array{int,DayOfWeek,int}> Provider data sets.
     */
    public static function weeksInYearProvider(): array
    {
        return [
            '2025 Saturday week start' => [2025, DayOfWeek::Saturday, 53],
            '2025 Sunday week start' => [2025, DayOfWeek::Sunday, 53],
            '2025 Monday week start' => [2025, DayOfWeek::Monday, 53],
        ];
    }

    /**
     * Tests weeks in month.
     */
    #[DataProvider('weeksInMonthProvider')]
    public function testWeeksInMonth(int $year, int $month, DayOfWeek $firstDayOfWeek, int $expected): void
    {
        $date = new GregorianDate($year, $month, 1);
        self::assertSame($expected, $date->weeksInMonth($firstDayOfWeek));
    }

    /**
     * Provides data for weeks in month tests.
     *
     * @return array<array{int,int,DayOfWeek,int}> Provider data sets.
     */
    public static function weeksInMonthProvider(): array
    {
        return [
            'March 2025 Saturday week start' => [2025, 3, DayOfWeek::Saturday, 5],
            'March 2025 Sunday week start' => [2025, 3, DayOfWeek::Sunday, 6],
            'March 2025 Monday week start' => [2025, 3, DayOfWeek::Monday, 6],
            'April 2025 Saturday week start' => [2025, 4, DayOfWeek::Saturday, 5],
            'February 2025 Saturday week start' => [2025, 2, DayOfWeek::Saturday, 4],
            'February 2024 leap Saturday week start' => [2024, 2, DayOfWeek::Saturday, 5],
        ];
    }
}
