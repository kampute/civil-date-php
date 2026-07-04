<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date comparison.
 */
final class IslamicDateComparisonTest extends TestCase
{
    /**
     * Tests compare to.
     */
    #[DataProvider('compareToProvider')]
    public function testCompareTo(IslamicDate $date1, IslamicDate $date2, int $expected): void
    {
        self::assertSame($expected, $date1->compareTo($date2));
    }

    /**
     * Provides data for compare to tests.
     *
     * @return array<array{IslamicDate,IslamicDate,int}> Provider data sets.
     */
    public static function compareToProvider(): array
    {
        return [
            'Earlier date' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 1), -1],
            'Same date' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 1, 30), 0],
            'Later date' => [new IslamicDate(1446, 2, 1), new IslamicDate(1446, 1, 30), 1],
        ];
    }

    /**
     * Tests equals.
     */
    #[DataProvider('equalsProvider')]
    public function testEquals(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->equals($date2));
    }

    /**
     * Provides data for equals tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function equalsProvider(): array
    {
        return [
            'Same date' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 1, 30), true],
            'Different date' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 1), false],
        ];
    }

    /**
     * Tests is before.
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isBefore($date2));
    }

    /**
     * Provides data for is before tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isBeforeProvider(): array
    {
        return [
            'Before' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 1), true],
            'Same date' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 1, 30), false],
            'After' => [new IslamicDate(1446, 2, 1), new IslamicDate(1446, 1, 30), false],
        ];
    }

    /**
     * Tests is after.
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isAfter($date2));
    }

    /**
     * Provides data for is after tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isAfterProvider(): array
    {
        return [
            'After' => [new IslamicDate(1446, 2, 1), new IslamicDate(1446, 1, 30), true],
            'Same date' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 1, 30), false],
            'Before' => [new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 1), false],
        ];
    }

    /**
     * Tests is between.
     */
    #[DataProvider('isBetweenProvider')]
    public function testIsBetween(IslamicDate $date, IslamicDate $start, IslamicDate $end, bool $expected): void
    {
        self::assertSame($expected, $date->isBetween($start, $end));
    }

    /**
     * Provides data for is between tests.
     *
     * @return array<array{IslamicDate,IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isBetweenProvider(): array
    {
        return [
            'Between' => [new IslamicDate(1446, 2, 1), new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 2), true],
            'Reversed boundaries' => [new IslamicDate(1446, 2, 1), new IslamicDate(1446, 2, 2), new IslamicDate(1446, 1, 30), false],
            'After range' => [new IslamicDate(1446, 2, 3), new IslamicDate(1446, 1, 30), new IslamicDate(1446, 2, 2), false],
        ];
    }

    /**
     * Tests is same day.
     */
    #[DataProvider('isSameDayProvider')]
    public function testIsSameDay(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameDay($date2));
    }

    /**
     * Provides data for is same day tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isSameDayProvider(): array
    {
        return [
            'Same day' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 5, 15), true],
            'Different day' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 5, 16), false],
        ];
    }

    /**
     * Tests is same week.
     */
    #[DataProvider('sameWeekProvider')]
    public function testIsSameWeek(DayOfWeek $firstDayOfWeek, bool $expected): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame($expected, $date->isSameWeek($date->addDays(1), $firstDayOfWeek));
    }

    /**
     * Provides data for same week tests.
     *
     * @return array<array{DayOfWeek,bool}> Provider data sets.
     */
    public static function sameWeekProvider(): array
    {
        return [
            'Saturday week start' => [DayOfWeek::Saturday, true],
            'Sunday week start' => [DayOfWeek::Sunday, true],
            'Monday week start' => [DayOfWeek::Monday, false],
        ];
    }

    /**
     * Tests is same month.
     */
    #[DataProvider('isSameMonthProvider')]
    public function testIsSameMonth(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameMonth($date2));
    }

    /**
     * Provides data for is same month tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isSameMonthProvider(): array
    {
        return [
            'Same month' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 5, 30), true],
            'Different month same year' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 6, 1), false],
            'Different year same month' => [new IslamicDate(1446, 5, 15), new IslamicDate(1447, 5, 15), false],
        ];
    }

    /**
     * Tests is same quarter.
     */
    #[DataProvider('isSameQuarterProvider')]
    public function testIsSameQuarter(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameQuarter($date2));
    }

    /**
     * Provides data for is same quarter tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isSameQuarterProvider(): array
    {
        return [
            'Same quarter' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 6, 1), true],
            'Different quarter' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 7, 1), false],
            'Different year same quarter' => [new IslamicDate(1446, 5, 15), new IslamicDate(1447, 5, 15), false],
        ];
    }

    /**
     * Tests is same season.
     */
    #[DataProvider('isSameSeasonProvider')]
    public function testIsSameSeason(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameSeason($date2));
    }

    /**
     * Provides data for is same season tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isSameSeasonProvider(): array
    {
        return [
            'Same season' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 5, 16), true],
            'Different season' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 8, 15), false],
            'Different year same season' => [new IslamicDate(1446, 5, 15), new IslamicDate(1447, 5, 15), false],
        ];
    }

    /**
     * Tests is same year.
     */
    #[DataProvider('isSameYearProvider')]
    public function testIsSameYear(IslamicDate $date1, IslamicDate $date2, bool $expected): void
    {
        self::assertSame($expected, $date1->isSameYear($date2));
    }

    /**
     * Provides data for is same year tests.
     *
     * @return array<array{IslamicDate,IslamicDate,bool}> Provider data sets.
     */
    public static function isSameYearProvider(): array
    {
        return [
            'Same year' => [new IslamicDate(1446, 5, 15), new IslamicDate(1446, 12, 29), true],
            'Different year' => [new IslamicDate(1446, 5, 15), new IslamicDate(1447, 5, 15), false],
        ];
    }
}
