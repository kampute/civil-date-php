<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date comparison.
 */
final class JalaliDateComparisonTest extends TestCase
{
    /**
     * Tests compare to.
     */
    #[DataProvider('compareToProvider')]
    public function testCompareTo(JalaliDate $date1, JalaliDate $date2, int $expected): void
    {
        self::assertSame($expected, $date1->compareTo($date2));
    }

    /**
     * Provides data for compare to tests.
     *
     * @return array<array{JalaliDate,JalaliDate,int}> Provider data sets.
     */
    public static function compareToProvider(): array
    {
        $date = new JalaliDate(1403, 5, 15);

        return [
            'Same date' => [$date, new JalaliDate(1403, 5, 15), 0],
            'After earlier date' => [$date, new JalaliDate(1403, 5, 14), 1],
            'Before later date' => [$date, new JalaliDate(1403, 5, 16), -1],
        ];
    }

    /**
     * Tests equals.
     */
    #[DataProvider('equalsProvider')]
    public function testEquals(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->equals($date2));
    }

    /**
     * Provides data for equals tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function equalsProvider(): array
    {
        $date = new JalaliDate(1403, 5, 15);

        return [
            'Same date' => [$date, new JalaliDate(1403, 5, 15), true],
            'Different date' => [$date, new JalaliDate(1403, 5, 14), false],
        ];
    }

    /**
     * Tests is before.
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isBefore($date2));
    }

    /**
     * Provides data for is before tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isBeforeProvider(): array
    {
        $date = new JalaliDate(1403, 5, 15);

        return [
            'Before later date' => [new JalaliDate(1403, 5, 14), $date, true],
            'Not before same date' => [$date, new JalaliDate(1403, 5, 15), false],
        ];
    }

    /**
     * Tests is after.
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isAfter($date2));
    }

    /**
     * Provides data for is after tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isAfterProvider(): array
    {
        $date = new JalaliDate(1403, 5, 15);

        return [
            'After earlier date' => [new JalaliDate(1403, 5, 16), $date, true],
            'Not after same date' => [$date, new JalaliDate(1403, 5, 15), false],
        ];
    }

    /**
     * Tests is between.
     */
    #[DataProvider('isBetweenProvider')]
    public function testIsBetween(JalaliDate $date, JalaliDate $boundary1, JalaliDate $boundary2, bool $expected): void
    {
        self::assertSame($expected, $date->isBetween($boundary1, $boundary2));
    }

    /**
     * Provides data for is between tests.
     *
     * @return array<array{JalaliDate,JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isBetweenProvider(): array
    {
        $start = new JalaliDate(1403, 5, 10);
        $end = new JalaliDate(1403, 5, 20);
        $middle = new JalaliDate(1403, 5, 15);

        return [
            'Date in middle of range' => [$middle, $start, $end, true],
            'Start boundary' => [$start, $start, $end, true],
            'End boundary' => [$end, $start, $end, true],
            'Before start' => [$start->addDays(-1), $start, $end, false],
            'After end' => [$end->addDays(1), $start, $end, false],
        ];
    }

    /**
     * Tests is same day.
     */
    #[DataProvider('isSameDayProvider')]
    public function testIsSameDay(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameDay($date2));
    }

    /**
     * Provides data for is same day tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isSameDayProvider(): array
    {
        return [
            'Same day' => [new JalaliDate(1403, 5, 15), new JalaliDate(1403, 5, 15), true],
            'Different day' => [new JalaliDate(1403, 5, 15), new JalaliDate(1403, 5, 16), false],
        ];
    }

    /**
     * Tests is same week.
     */
    #[DataProvider('isSameWeekProvider')]
    public function testIsSameWeek(JalaliDate $date1, JalaliDate $date2, DayOfWeek $firstDayOfWeek, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameWeek($date2, $firstDayOfWeek));
    }

    /**
     * Provides data for is same week tests.
     *
     * @return array<array{JalaliDate,JalaliDate,DayOfWeek,bool}> Provider data sets.
     */
    public static function isSameWeekProvider(): array
    {
        return [
            'Same Saturday-start week' => [new JalaliDate(1403, 1, 1), new JalaliDate(1403, 1, 3), DayOfWeek::Saturday, true],
            'Next Saturday-start week' => [new JalaliDate(1403, 1, 1), new JalaliDate(1403, 1, 8), DayOfWeek::Saturday, false],
            'Same Monday-start week' => [new JalaliDate(1403, 1, 1), new JalaliDate(1402, 12, 28), DayOfWeek::Monday, true],
            'Previous Monday-start week' => [new JalaliDate(1403, 1, 1), new JalaliDate(1402, 12, 27), DayOfWeek::Monday, false],
        ];
    }

    /**
     * Tests is same month.
     */
    #[DataProvider('isSameMonthProvider')]
    public function testIsSameMonth(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameMonth($date2));
    }

    /**
     * Provides data for is same month tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isSameMonthProvider(): array
    {
        return [
            'Same year and month, different days' => [new JalaliDate(1403, 5, 1), new JalaliDate(1403, 5, 31), true],
            'Same year, different months' => [new JalaliDate(1403, 5, 1), new JalaliDate(1403, 6, 1), false],
            'Different years, same month/day' => [new JalaliDate(1403, 5, 1), new JalaliDate(1404, 5, 1), false],
        ];
    }

    /**
     * Tests is same quarter.
     */
    #[DataProvider('isSameQuarterProvider')]
    public function testIsSameQuarter(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameQuarter($date2));
    }

    /**
     * Provides data for is same quarter tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isSameQuarterProvider(): array
    {
        return [
            'Q1 first and last day' => [new JalaliDate(1403, 1, 1), new JalaliDate(1403, 3, 31), true],
            'Q2 first and last day' => [new JalaliDate(1403, 4, 1), new JalaliDate(1403, 6, 31), true],
            'Q3 first and last day' => [new JalaliDate(1403, 7, 1), new JalaliDate(1403, 9, 30), true],
            'Q4 first and last day' => [new JalaliDate(1403, 10, 1), new JalaliDate(1403, 12, 30), true],
            'Q1 to Q2 boundary' => [new JalaliDate(1403, 3, 31), new JalaliDate(1403, 4, 1), false],
            'Different years same quarter' => [new JalaliDate(1403, 5, 15), new JalaliDate(1404, 5, 15), false],
        ];
    }

    /**
     * Tests is same season.
     */
    #[DataProvider('isSameSeasonProvider')]
    public function testIsSameSeason(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameSeason($date2));
    }

    /**
     * Provides data for is same season tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isSameSeasonProvider(): array
    {
        return [
            'Same spring' => [new JalaliDate(1403, 1, 1), new JalaliDate(1403, 3, 31), true],
            'Spring to summer boundary' => [new JalaliDate(1403, 3, 31), new JalaliDate(1403, 4, 1), false],
            'Different years same season' => [new JalaliDate(1403, 5, 15), new JalaliDate(1404, 5, 15), false],
        ];
    }

    /**
     * Tests is same year.
     */
    #[DataProvider('isSameYearProvider')]
    public function testIsSameYear(JalaliDate $date1, JalaliDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameYear($date2));
    }

    /**
     * Provides data for is same year tests.
     *
     * @return array<array{JalaliDate,JalaliDate,bool}> Provider data sets.
     */
    public static function isSameYearProvider(): array
    {
        return [
            'Same year, different months' => [new JalaliDate(1403, 5, 15), new JalaliDate(1403, 12, 29), true],
            'Different years, same month/day' => [new JalaliDate(1403, 5, 15), new JalaliDate(1404, 5, 15), false],
        ];
    }
}
