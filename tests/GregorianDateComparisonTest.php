<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date comparison.
 */
final class GregorianDateComparisonTest extends TestCase
{
    /**
     * Tests compare to.
     */
    #[DataProvider('compareToProvider')]
    public function testCompareTo(GregorianDate $date1, GregorianDate $date2, int $expected): void
    {
        self::assertSame($expected, $date1->compareTo($date2));
    }

    /**
     * Provides data for compare to tests.
     *
     * @return array<array{GregorianDate,GregorianDate,int}> Provider data sets.
     */
    public static function compareToProvider(): array
    {
        return [
            'Same date' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), 0],
            'Earlier date' => [new GregorianDate(2025, 3, 20), new GregorianDate(2025, 3, 21), -1],
            'Later date' => [new GregorianDate(2025, 3, 22), new GregorianDate(2025, 3, 21), 1],
            'Different years' => [new GregorianDate(2024, 12, 31), new GregorianDate(2025, 1, 1), -1],
            'Different months' => [new GregorianDate(2025, 2, 28), new GregorianDate(2025, 3, 1), -1],
        ];
    }

    /**
     * Tests equals.
     */
    #[DataProvider('equalsProvider')]
    public function testEquals(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->equals($date2));
    }

    /**
     * Provides data for equals tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function equalsProvider(): array
    {
        return [
            'Same date' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), true],
            'Different day' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 22), false],
            'Different month' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 4, 21), false],
            'Different year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2024, 3, 21), false],
        ];
    }

    /**
     * Tests is before.
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isBefore($date2));
    }

    /**
     * Provides data for is before tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isBeforeProvider(): array
    {
        return [
            'Before' => [new GregorianDate(2025, 3, 20), new GregorianDate(2025, 3, 21), true],
            'Same date' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), false],
            'After' => [new GregorianDate(2025, 3, 22), new GregorianDate(2025, 3, 21), false],
        ];
    }

    /**
     * Tests is after.
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isAfter($date2));
    }

    /**
     * Provides data for is after tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isAfterProvider(): array
    {
        return [
            'After' => [new GregorianDate(2025, 3, 22), new GregorianDate(2025, 3, 21), true],
            'Same date' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), false],
            'Before' => [new GregorianDate(2025, 3, 20), new GregorianDate(2025, 3, 21), false],
        ];
    }

    /**
     * Tests is between.
     */
    #[DataProvider('isBetweenProvider')]
    public function testIsBetween(GregorianDate $date, GregorianDate $start, GregorianDate $end, bool $expected): void
    {
        self::assertSame($expected, $date->isBetween($start, $end));
    }

    /**
     * Provides data for is between tests.
     *
     * @return array<array{GregorianDate,GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isBetweenProvider(): array
    {
        return [
            'Between' => [new GregorianDate(2025, 3, 15), new GregorianDate(2025, 3, 10), new GregorianDate(2025, 3, 20), true],
            'At start' => [new GregorianDate(2025, 3, 10), new GregorianDate(2025, 3, 10), new GregorianDate(2025, 3, 20), true],
            'At end' => [new GregorianDate(2025, 3, 20), new GregorianDate(2025, 3, 10), new GregorianDate(2025, 3, 20), true],
            'Before range' => [new GregorianDate(2025, 3, 9), new GregorianDate(2025, 3, 10), new GregorianDate(2025, 3, 20), false],
            'After range' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 10), new GregorianDate(2025, 3, 20), false],
        ];
    }

    /**
     * Tests is same day.
     */
    #[DataProvider('isSameDayProvider')]
    public function testIsSameDay(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameDay($date2));
    }

    /**
     * Provides data for is same day tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isSameDayProvider(): array
    {
        return [
            'Same day' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 21), true],
            'Different day' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 22), false],
        ];
    }

    /**
     * Tests is same week.
     */
    #[DataProvider('isSameWeekProvider')]
    public function testIsSameWeek(GregorianDate $date1, GregorianDate $date2, DayOfWeek $firstDayOfWeek, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameWeek($date2, $firstDayOfWeek));
    }

    /**
     * Provides data for is same week tests.
     *
     * @return array<array{GregorianDate,GregorianDate,DayOfWeek,bool}> Provider data sets.
     */
    public static function isSameWeekProvider(): array
    {
        return [
            'Same Saturday-start week' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 15), DayOfWeek::Saturday, true],
            'Next Saturday-start week' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 22), DayOfWeek::Saturday, false],
            'Same Monday-start week' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 17), DayOfWeek::Monday, true],
        ];
    }

    /**
     * Tests is same month.
     */
    #[DataProvider('isSameMonthProvider')]
    public function testIsSameMonth(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameMonth($date2));
    }

    /**
     * Provides data for is same month tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isSameMonthProvider(): array
    {
        return [
            'Same month' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 3, 15), true],
            'Different month same year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 4, 21), false],
            'Different year same month' => [new GregorianDate(2025, 3, 21), new GregorianDate(2024, 3, 21), false],
        ];
    }

    /**
     * Tests is same quarter.
     */
    #[DataProvider('isSameQuarterProvider')]
    public function testIsSameQuarter(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameQuarter($date2));
    }

    /**
     * Provides data for is same quarter tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isSameQuarterProvider(): array
    {
        return [
            'Q1 first and last day' => [new GregorianDate(2025, 1, 1), new GregorianDate(2025, 3, 31), true],
            'Q1 to Q2 boundary' => [new GregorianDate(2025, 3, 31), new GregorianDate(2025, 4, 1), false],
            'Different years same quarter' => [new GregorianDate(2025, 2, 1), new GregorianDate(2024, 2, 1), false],
        ];
    }

    /**
     * Tests is same season.
     */
    #[DataProvider('isSameSeasonProvider')]
    public function testIsSameSeason(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameSeason($date2));
    }

    /**
     * Provides data for is same season tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isSameSeasonProvider(): array
    {
        return [
            'Same astronomical spring' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 6, 21), true],
            'Winter to spring boundary' => [new GregorianDate(2025, 3, 20), new GregorianDate(2025, 3, 21), false],
            'Different years same season name' => [new GregorianDate(2025, 4, 1), new GregorianDate(2024, 4, 1), false],
        ];
    }

    /**
     * Tests is same year.
     */
    #[DataProvider('isSameYearProvider')]
    public function testIsSameYear(GregorianDate $date1, GregorianDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameYear($date2));
    }

    /**
     * Provides data for is same year tests.
     *
     * @return array<array{GregorianDate,GregorianDate,bool}> Provider data sets.
     */
    public static function isSameYearProvider(): array
    {
        return [
            'Same year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2025, 12, 31), true],
            'Different year' => [new GregorianDate(2025, 3, 21), new GregorianDate(2024, 3, 21), false],
        ];
    }
}
