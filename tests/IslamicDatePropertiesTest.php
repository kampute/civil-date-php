<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\IslamicDate;
use Kampute\CivilDate\Season;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date properties.
 */
final class IslamicDatePropertiesTest extends TestCase
{
    /**
     * Tests j d n matches known anchors.
     */
    #[DataProvider('jdnKnownAnchorsProvider')]
    public function testJDNMatchesKnownAnchors(IslamicDate $date, int $expectedJDN): void
    {
        self::assertSame($expectedJDN, $date->jdn);
    }

    /**
     * Provides data for jdn known anchors tests.
     *
     * @return array<array{IslamicDate,int}> Provider data sets.
     */
    public static function jdnKnownAnchorsProvider(): array
    {
        return [
            'Islamic epoch' => [new IslamicDate(1, 1, 1), 1948440],
            'Day before epoch' => [new IslamicDate(-1, 12, 29), 1948439],
            'Muharram 1446' => [new IslamicDate(1446, 1, 1), 2460500],
            'Ramadan 1446' => [new IslamicDate(1446, 9, 1), 2460736],
        ];
    }

    /**
     * Tests j d n is consecutive across boundaries.
     */
    #[DataProvider('consecutiveDatesProvider')]
    public function testJDNIsConsecutiveForAdjacentDates(IslamicDate $first, IslamicDate $second): void
    {
        self::assertSame(1, $second->jdn - $first->jdn);
    }

    /**
     * Provides data for consecutive date tests.
     *
     * @return array<array{IslamicDate,IslamicDate}> Provider data sets.
     */
    public static function consecutiveDatesProvider(): array
    {
        return [
            'Month boundary' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 1)],
            'Year boundary' => [new IslamicDate(1446, 12, 29), new IslamicDate(1447, 1, 1)],
            'Year-zero boundary' => [new IslamicDate(-1, 12, 29), new IslamicDate(1, 1, 1)],
        ];
    }

    /**
     * Tests date components.
     */
    public function testDateComponents(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertSame(1446, $date->year);
        self::assertSame(9, $date->month);
        self::assertSame(1, $date->day);
    }

    /**
     * Tests quarter.
     */
    public function testQuarter(): void
    {
        self::assertSame(3, (new IslamicDate(1446, 9, 1))->quarter);
    }

    /**
     * Tests season.
     */
    public function testSeason(): void
    {
        self::assertInstanceOf(Season::class, (new IslamicDate(1446, 9, 1))->season);
    }

    /**
     * Tests day of year.
     */
    public function testDayOfYear(): void
    {
        self::assertSame(237, (new IslamicDate(1446, 9, 1))->dayOfYear);
    }

    /**
     * Tests day of week.
     */
    public function testDayOfWeek(): void
    {
        self::assertInstanceOf(DayOfWeek::class, (new IslamicDate(1446, 9, 1))->dayOfWeek);
    }

    /**
     * Tests day of week in year.
     */
    public function testDayOfWeekInYear(): void
    {
        self::assertSame(34, (new IslamicDate(1446, 9, 1))->dayOfWeekInYear);
    }

    /**
     * Tests day of week in month.
     */
    public function testDayOfWeekInMonth(): void
    {
        self::assertSame(1, (new IslamicDate(1446, 9, 1))->dayOfWeekInMonth);
    }

    /**
     * Tests months in year.
     */
    public function testMonthsInYear(): void
    {
        self::assertSame(12, (new IslamicDate(1446, 9, 1))->monthsInYear);
    }

    /**
     * Tests days in month.
     */
    #[DataProvider('calendarLengthProvider')]
    public function testDaysInMonth(int $year, int $month, int $daysInMonth): void
    {
        self::assertSame($daysInMonth, (new IslamicDate($year, $month, 1))->daysInMonth);
    }

    /**
     * Tests days in year.
     */
    #[DataProvider('calendarLengthProvider')]
    public function testDaysInYear(int $year, int $month, int $daysInMonth, int $daysInYear): void
    {
        self::assertSame($daysInYear, (new IslamicDate($year, $month, 1))->daysInYear);
    }

    /**
     * Tests leap years.
     */
    #[DataProvider('calendarLengthProvider')]
    public function testIsLeapYear(int $year, int $month, int $daysInMonth, int $daysInYear, bool $isLeap): void
    {
        self::assertSame($isLeap, (new IslamicDate($year, $month, 1))->isLeapYear);
    }

    /**
     * Provides data for calendar length tests.
     *
     * @return array<array{int,int,int,int,bool}> Provider data sets.
     */
    public static function calendarLengthProvider(): array
    {
        return [
            'Odd month' => [1, 1, 30, 354, false],
            'Even month' => [1, 2, 29, 354, false],
            'Common Dhu al-Hijjah' => [1, 12, 29, 354, false],
            'Leap Dhu al-Hijjah' => [2, 12, 30, 355, true],
        ];
    }
}
