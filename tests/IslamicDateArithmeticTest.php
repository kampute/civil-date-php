<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date arithmetic.
 */
final class IslamicDateArithmeticTest extends TestCase
{
    /**
     * Tests add days.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addDaysProvider')]
    public function testAddDays(IslamicDate $date, int $days, array $expected): void
    {
        self::assertSame($expected, $date->addDays($days)->toArray());
    }

    /**
     * Provides data for add days tests.
     *
     * @return array<array{IslamicDate,int,array<mixed>}> Provider data sets.
     */
    public static function addDaysProvider(): array
    {
        return [
            'Within month' => [new IslamicDate(1446, 1, 1), 29, [1446, 1, 30]],
            'Across month' => [new IslamicDate(1446, 1, 30), 1, [1446, 2, 1]],
            'Across common year' => [new IslamicDate(1446, 12, 29), 1, [1447, 1, 1]],
            'Across leap year' => [new IslamicDate(2, 12, 30), 1, [3, 1, 1]],
            'Across year zero' => [new IslamicDate(-1, 12, 29), 1, [1, 1, 1]],
            'Negative offset' => [new IslamicDate(1, 1, 1), -1, [-1, 12, 29]],
        ];
    }

    /**
     * Tests add days zero offset.
     */
    public function testAddDaysZeroOffset(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertEquals($date, $date->addDays(0));
        self::assertEquals($date, $date->addDays(100)->addDays(-100));
    }

    /**
     * Tests add months.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addMonthsProvider')]
    public function testAddMonths(IslamicDate $date, int $months, array $expected): void
    {
        self::assertSame($expected, $date->addMonths($months)->toArray());
    }

    /**
     * Provides data for add month tests.
     *
     * @return array<array{IslamicDate,int,array<mixed>}> Provider data sets.
     */
    public static function addMonthsProvider(): array
    {
        return [
            'Next month clamps day' => [new IslamicDate(1446, 1, 30), 1, [1446, 2, 29]],
            'Across year' => [new IslamicDate(1446, 12, 29), 1, [1447, 1, 29]],
            'Across year zero' => [new IslamicDate(-1, 12, 29), 1, [1, 1, 29]],
            'Previous month' => [new IslamicDate(1446, 2, 29), -1, [1446, 1, 29]],
        ];
    }

    /**
     * Tests add months zero offset.
     */
    public function testAddMonthsZeroOffset(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertEquals($date, $date->addMonths(0));
    }

    /**
     * Tests add years.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addYearsProvider')]
    public function testAddYears(IslamicDate $date, int $years, array $expected): void
    {
        self::assertSame($expected, $date->addYears($years)->toArray());
    }

    /**
     * Provides data for add year tests.
     *
     * @return array<array{IslamicDate,int,array<mixed>}> Provider data sets.
     */
    public static function addYearsProvider(): array
    {
        return [
            'One year' => [new IslamicDate(1446, 1, 1), 1, [1447, 1, 1]],
            'Leap day clamps' => [new IslamicDate(2, 12, 30), 1, [3, 12, 29]],
            'Across year zero' => [new IslamicDate(-1, 1, 1), 1, [1, 1, 1]],
            'Negative years' => [new IslamicDate(2, 1, 1), -2, [-1, 1, 1]],
        ];
    }

    /**
     * Tests add years zero offset.
     */
    public function testAddYearsZeroOffset(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertEquals($date, $date->addYears(0));
    }

    /**
     * Tests next day of week.
     */
    public function testNextDayOfWeek(): void
    {
        $date = new IslamicDate(1446, 9, 1);

        self::assertSame(7, $date->differenceInDays($date->nextDayOfWeek($date->dayOfWeek)));
        self::assertSame(14, $date->differenceInDays($date->nextDayOfWeek($date->dayOfWeek, 2)));
    }

    /**
     * Tests next day of week rejects invalid input.
     */
    public function testNextDayOfWeekRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new IslamicDate(1446, 1, 1))->nextDayOfWeek(7);
    }

    /**
     * Tests previous day of week.
     */
    public function testPreviousDayOfWeek(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertSame(-7, $date->differenceInDays($date->previousDayOfWeek($date->dayOfWeek)));
    }

    /**
     * Tests previous day of week rejects invalid input.
     */
    public function testPreviousDayOfWeekRejectsInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new IslamicDate(1446, 1, 1))->previousDayOfWeek(7);
    }

    /**
     * Tests start of year.
     */
    public function testStartOfYear(): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame([1446, 1, 1], $date->startOfYear()->toArray());
    }

    /**
     * Tests end of year.
     */
    public function testEndOfYear(): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame([1446, 12, 29], $date->endOfYear()->toArray());
    }

    /**
     * Tests start of month.
     */
    public function testStartOfMonth(): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame([1446, 5, 1], $date->startOfMonth()->toArray());
    }

    /**
     * Tests end of month.
     */
    public function testEndOfMonth(): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame([1446, 5, 30], $date->endOfMonth()->toArray());
    }

    /**
     * Tests start of quarter.
     */
    public function testStartOfQuarter(): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame([1446, 4, 1], $date->startOfQuarter()->toArray());
    }

    /**
     * Tests end of quarter.
     */
    public function testEndOfQuarter(): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame([1446, 6, 29], $date->endOfQuarter()->toArray());
    }

    /**
     * Tests start of week.
     */
    #[DataProvider('startOfWeekProvider')]
    public function testStartOfWeek(DayOfWeek $firstDayOfWeek): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame($firstDayOfWeek, $date->startOfWeek($firstDayOfWeek)->dayOfWeek);
    }

    /**
     * Provides data for start of week tests.
     *
     * @return array<array{DayOfWeek}> Provider data sets.
     */
    public static function startOfWeekProvider(): array
    {
        return [
            'Saturday week start' => [DayOfWeek::Saturday],
            'Sunday week start' => [DayOfWeek::Sunday],
            'Monday week start' => [DayOfWeek::Monday],
        ];
    }

    /**
     * Tests end of week.
     */
    #[DataProvider('endOfWeekProvider')]
    public function testEndOfWeek(DayOfWeek $firstDayOfWeek, DayOfWeek $expected): void
    {
        $date = new IslamicDate(1446, 5, 15);
        self::assertSame($expected, $date->endOfWeek($firstDayOfWeek)->dayOfWeek);
        self::assertSame(
            6,
            $date->startOfWeek($firstDayOfWeek)->differenceInDays($date->endOfWeek($firstDayOfWeek))
        );
    }

    /**
     * Provides data for end of week tests.
     *
     * @return array<array{DayOfWeek,DayOfWeek}> Provider data sets.
     */
    public static function endOfWeekProvider(): array
    {
        return [
            'Saturday week start' => [DayOfWeek::Saturday, DayOfWeek::Friday],
            'Sunday week start' => [DayOfWeek::Sunday, DayOfWeek::Saturday],
            'Monday week start' => [DayOfWeek::Monday, DayOfWeek::Sunday],
        ];
    }
}
