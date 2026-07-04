<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\Calendars\IslamicCalendar;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date intervals.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class IslamicDateIntervalsTest extends TestCase
{
    /**
     * New Crescent Society UK 1447 AH authoritative month lengths.
     *
     * @var array<int,array<int,int>>
     */
    private const NCS_1447_MONTH_LENGTHS = [
        1447 => [
            1 => 30,
            2 => 30,
            3 => 30,
            4 => 30,
            5 => 30,
            6 => 29,
            7 => 29,
            8 => 29,
            9 => 30,
            10 => 29,
            11 => 29,
            12 => 29,
        ],
    ];

    /**
     * Configures authoritative Islamic month lengths.
     */
    protected function setUp(): void
    {
        IslamicCalendar::instance()->setAuthoritativeMonthLengths(self::NCS_1447_MONTH_LENGTHS);
    }

    /**
     * Tests difference in days.
     *
     * @param array{int,int,int} $startComponents Start date components.
     * @param array{int,int,int} $endComponents End date components.
     */
    #[DataProvider('differenceInDaysProvider')]
    public function testDifferenceInDays(array $startComponents, array $endComponents, int $expected): void
    {
        $start = new IslamicDate(...$startComponents);
        $end = new IslamicDate(...$endComponents);

        self::assertSame($expected, $start->differenceInDays($end));
    }

    /**
     * Provides data for difference in days tests.
     *
     * @return array<array{array{int,int,int},array{int,int,int},int}> Provider data sets.
     */
    public static function differenceInDaysProvider(): array
    {
        return [
            'Same day' => [[1446, 1, 15], [1446, 1, 15], 0],
            'Forward month boundary' => [[1446, 1, 15], [1446, 2, 15], 30],
            'Backward month boundary' => [[1446, 2, 15], [1446, 1, 15], -30],
        ];
    }

    /**
     * Tests difference in months.
     *
     * @param array{int,int,int} $startComponents Start date components.
     * @param array{int,int,int} $endComponents End date components.
     */
    #[DataProvider('differenceInMonthsProvider')]
    public function testDifferenceInMonths(array $startComponents, array $endComponents, int $expected): void
    {
        $start = new IslamicDate(...$startComponents);
        $end = new IslamicDate(...$endComponents);

        self::assertSame($expected, $start->differenceInMonths($end));
    }

    /**
     * Provides data for difference in months tests.
     *
     * @return array<array{array{int,int,int},array{int,int,int},int}> Provider data sets.
     */
    public static function differenceInMonthsProvider(): array
    {
        return [
            'Full month' => [[1446, 1, 15], [1446, 2, 15], 1],
            'Incomplete month' => [[1446, 1, 15], [1446, 2, 14], 0],
        ];
    }

    /**
     * Tests difference in years.
     *
     * @param array{int,int,int} $startComponents Start date components.
     * @param array{int,int,int} $endComponents End date components.
     */
    #[DataProvider('differenceInYearsProvider')]
    public function testDifferenceInYears(array $startComponents, array $endComponents, int $expected): void
    {
        $start = new IslamicDate(...$startComponents);
        $end = new IslamicDate(...$endComponents);

        self::assertSame($expected, $start->differenceInYears($end));
    }

    /**
     * Provides data for difference in years tests.
     *
     * @return array<array{array{int,int,int},array{int,int,int},int}> Provider data sets.
     */
    public static function differenceInYearsProvider(): array
    {
        return [
            'Full year' => [[1446, 1, 15], [1447, 1, 15], 1],
            'Incomplete year' => [[1446, 1, 15], [1447, 1, 14], 0],
        ];
    }

    /**
     * Tests days of week in year.
     */
    public function testDaysOfWeekInYear(): void
    {
        $date = new IslamicDate(1446, 1, 1);
        self::assertSame(50, $date->daysOfWeekInYear(DayOfWeek::Friday));
    }

    /**
     * Tests days of week in month.
     */
    public function testDaysOfWeekInMonth(): void
    {
        $date = new IslamicDate(1446, 1, 1);
        self::assertSame(4, $date->daysOfWeekInMonth(DayOfWeek::Friday));
    }

    /**
     * Tests weeks in year.
     */
    #[DataProvider('weeksInYearProvider')]
    public function testWeeksInYear(int $year, DayOfWeek $firstDayOfWeek, int $expected): void
    {
        $date = new IslamicDate($year, 1, 1);

        self::assertSame($expected, $date->weeksInYear($firstDayOfWeek));
    }

    /**
     * Provides data for weeks in year tests.
     *
     * @return array<array{int,DayOfWeek,int}> Provider data sets.
     */
    public static function weeksInYearProvider(): array
    {
        return [
            '354-day Friday-start year with Saturday week start' => [1447, DayOfWeek::Saturday, 52],
            '354-day Friday-start year with Wednesday week start' => [1447, DayOfWeek::Wednesday, 51],
        ];
    }

    /**
     * Tests weeks in month.
     */
    #[DataProvider('weeksInMonthProvider')]
    public function testWeeksInMonth(int $year, int $month, DayOfWeek $firstDayOfWeek, int $expected): void
    {
        $date = new IslamicDate($year, $month, 1);

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
            '30-day Friday-start month with Saturday week start' => [1447, 1, DayOfWeek::Saturday, 6],
            '30-day Friday-start month with Sunday week start' => [1447, 1, DayOfWeek::Sunday, 5],
        ];
    }

    /**
     * Tests day of week counts reject invalid input.
     */
    public function testDayOfWeekCountsRejectInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $date = new IslamicDate(1446, 1, 1);
        $date->daysOfWeekInYear(7);
    }
}
