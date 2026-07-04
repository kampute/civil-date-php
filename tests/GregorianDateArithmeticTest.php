<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date arithmetic.
 */
final class GregorianDateArithmeticTest extends TestCase
{
    /**
     * Tests add days.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addDaysProvider')]
    public function testAddDays(int $year, int $month, int $day, int $daysToAdd, array $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        $result = $date->addDays($daysToAdd);

        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for add days tests.
     *
     * @return array<array{int,int,int,int,array<mixed>}> Provider data sets.
     */
    public static function addDaysProvider(): array
    {
        return [
            'Add 1 day' => [2025, 3, 21, 1, [2025, 3, 22]],
            'Add 10 days' => [2025, 3, 21, 10, [2025, 3, 31]],
            'Add 11 days crossing month' => [2025, 3, 21, 11, [2025, 4, 1]],
            'Add 365 days' => [2025, 1, 1, 365, [2026, 1, 1]],
            'Add negative days' => [2025, 3, 21, -20, [2025, 3, 1]],
            'Subtract across year' => [2025, 1, 10, -20, [2024, 12, 21]],
            'Leap year Feb 28 + 1' => [2024, 2, 28, 1, [2024, 2, 29]],
            'Leap year Feb 29 + 1' => [2024, 2, 29, 1, [2024, 3, 1]],
            'Non-leap year Feb 28 + 1' => [2025, 2, 28, 1, [2025, 3, 1]],
            'Add zero days' => [2025, 3, 21, 0, [2025, 3, 21]],
            'Negative year' => [-100, 6, 15, 30, [-100, 7, 15]],
            'Zero boundary (forward)' => [-1, 12, 31, 1, [1, 1, 1]],
            'Zero boundary (backward)' => [1, 1, 1, -1, [-1, 12, 31]],
        ];
    }

    /**
     * Tests add days zero offset.
     */
    public function testAddDaysZeroOffset(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        self::assertTrue($date->addDays(100)->addDays(-100)->equals($date));
        self::assertTrue($date->addDays(0)->equals($date));
    }

    /**
     * Tests add months.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addMonthsProvider')]
    public function testAddMonths(int $year, int $month, int $day, int $monthsToAdd, array $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        $result = $date->addMonths($monthsToAdd);

        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for add months tests.
     *
     * @return array<array{int,int,int,int,array<mixed>}> Provider data sets.
     */
    public static function addMonthsProvider(): array
    {
        return [
            'Add 1 month' => [2025, 3, 21, 1, [2025, 4, 21]],
            'Add 12 months' => [2025, 3, 21, 12, [2026, 3, 21]],
            'Add negative months' => [2025, 3, 21, -2, [2025, 1, 21]],
            'Day clamping Jan 31 + 1 month' => [2025, 1, 31, 1, [2025, 2, 28]],
            'Day clamping leap year' => [2024, 1, 31, 1, [2024, 2, 29]],
            'Day clamping May 31 + 1 month' => [2025, 5, 31, 1, [2025, 6, 30]],
            'Crossing year boundary' => [2025, 11, 15, 3, [2026, 2, 15]],
            'Subtract crossing year' => [2025, 2, 15, -3, [2024, 11, 15]],
            'Add zero months' => [2025, 3, 21, 0, [2025, 3, 21]],
            'Add 24 months' => [2025, 3, 21, 24, [2027, 3, 21]],
        ];
    }

    /**
     * Tests add months zero offset.
     */
    public function testAddMonthsZeroOffset(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        self::assertTrue($date->addMonths(100)->addMonths(-100)->equals($date));
        self::assertTrue($date->addMonths(0)->equals($date));
    }

    /**
     * Tests add years.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addYearsProvider')]
    public function testAddYears(int $year, int $month, int $day, int $yearsToAdd, array $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        $result = $date->addYears($yearsToAdd);

        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for add years tests.
     *
     * @return array<array{int,int,int,int,array<mixed>}> Provider data sets.
     */
    public static function addYearsProvider(): array
    {
        return [
            'Add 1 year' => [2025, 3, 21, 1, [2026, 3, 21]],
            'Add 10 years' => [2025, 3, 21, 10, [2035, 3, 21]],
            'Add negative years' => [2025, 3, 21, -5, [2020, 3, 21]],
            'Leap to non-leap Feb 29' => [2024, 2, 29, 1, [2025, 2, 28]],
            'Non-leap to leap Feb 28' => [2025, 2, 28, -1, [2024, 2, 28]],
            'Century crossing' => [1999, 12, 31, 2, [2001, 12, 31]],
            'Add zero years' => [2025, 3, 21, 0, [2025, 3, 21]],
            'Year arithmetic with negatives' => [1, 1, 1, -2, [-2, 1, 1]],
        ];
    }

    /**
     * Tests add years zero offset.
     */
    public function testAddYearsZeroOffset(): void
    {
        $date = new GregorianDate(2025, 3, 21);
        self::assertTrue($date->addYears(100)->addYears(-100)->equals($date));
        self::assertTrue($date->addYears(0)->equals($date));
    }


    /**
     * Tests next day of week.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('nextDayOfWeekProvider')]
    public function testNextDayOfWeek(DayOfWeek|int $dayOfWeek, int $occurrence, array $expected): void
    {
        $date = new GregorianDate(2025, 3, 21);

        self::assertSame($expected, $date->nextDayOfWeek($dayOfWeek, $occurrence)->toArray());
    }

    /**
     * Provides data for next day of week tests.
     *
     * @return array<array{DayOfWeek|int,int,array<mixed>}> Provider data sets.
     */
    public static function nextDayOfWeekProvider(): array
    {
        return [
            'Saturday' => [DayOfWeek::Saturday, 1, [2025, 3, 22]],
            'Same day is exclusive' => [DayOfWeek::Friday, 1, [2025, 3, 28]],
            'Second Monday' => [DayOfWeek::Monday, 2, [2025, 3, 31]],
            'Sunday from integer day of week' => [DayOfWeek::Sunday->value, 1, [2025, 3, 23]],
        ];
    }

    /**
     * Tests next day of week rejects invalid input.
     */
    #[DataProvider('invalidNextDayOfWeekProvider')]
    public function testNextDayOfWeekRejectsInvalidInput(DayOfWeek|int $dayOfWeek, int $occurrence): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GregorianDate(2025, 3, 21))->nextDayOfWeek($dayOfWeek, $occurrence);
    }

    /**
     * Provides data for invalid next day of week tests.
     *
     * @return array<array{DayOfWeek|int,int}> Provider data sets.
     */
    public static function invalidNextDayOfWeekProvider(): array
    {
        return [
            'Occurrence zero' => [DayOfWeek::Friday, 0],
            'Negative occurrence' => [DayOfWeek::Friday, -1],
            'Invalid integer day of week' => [7, 1],
        ];
    }

    /**
     * Tests previous day of week.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('previousDayOfWeekProvider')]
    public function testPreviousDayOfWeek(DayOfWeek|int $dayOfWeek, int $occurrence, array $expected): void
    {
        $date = new GregorianDate(2025, 3, 21);

        self::assertSame($expected, $date->previousDayOfWeek($dayOfWeek, $occurrence)->toArray());
    }

    /**
     * Provides data for previous day of week tests.
     *
     * @return array<array{DayOfWeek|int,int,array<mixed>}> Provider data sets.
     */
    public static function previousDayOfWeekProvider(): array
    {
        return [
            'Thursday' => [DayOfWeek::Thursday, 1, [2025, 3, 20]],
            'Same day is exclusive' => [DayOfWeek::Friday, 1, [2025, 3, 14]],
            'Second Saturday' => [DayOfWeek::Saturday, 2, [2025, 3, 8]],
            'Sunday from integer day of week' => [DayOfWeek::Sunday->value, 1, [2025, 3, 16]],
        ];
    }

    /**
     * Tests previous day of week rejects invalid input.
     */
    #[DataProvider('invalidPreviousDayOfWeekProvider')]
    public function testPreviousDayOfWeekRejectsInvalidInput(DayOfWeek|int $dayOfWeek, int $occurrence): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new GregorianDate(2025, 3, 21))->previousDayOfWeek($dayOfWeek, $occurrence);
    }

    /**
     * Provides data for invalid previous day of week tests.
     *
     * @return array<array{DayOfWeek|int,int}> Provider data sets.
     */
    public static function invalidPreviousDayOfWeekProvider(): array
    {
        return [
            'Occurrence zero' => [DayOfWeek::Friday, 0],
            'Negative occurrence' => [DayOfWeek::Friday, -1],
            'Invalid integer day of week' => [-1, 1],
        ];
    }

    /**
     * Tests start of year.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfYearProvider')]
    public function testStartOfYear(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->startOfYear()->toArray());
    }

    /**
     * Provides data for start of year tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function startOfYearProvider(): array
    {
        return [
            'Middle of year' => [new GregorianDate(2025, 6, 15), [2025, 1, 1]],
            'Already start of year' => [new GregorianDate(2025, 1, 1), [2025, 1, 1]],
        ];
    }

    /**
     * Tests end of year.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfYearProvider')]
    public function testEndOfYear(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->endOfYear()->toArray());
    }

    /**
     * Provides data for end of year tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function endOfYearProvider(): array
    {
        return [
            'Middle of year' => [new GregorianDate(2025, 6, 15), [2025, 12, 31]],
            'Leap year' => [new GregorianDate(2024, 2, 29), [2024, 12, 31]],
        ];
    }

    /**
     * Tests start of month.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfMonthProvider')]
    public function testStartOfMonth(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->startOfMonth()->toArray());
    }

    /**
     * Provides data for start of month tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function startOfMonthProvider(): array
    {
        return [
            'Middle of month' => [new GregorianDate(2025, 6, 15), [2025, 6, 1]],
            'Leap February' => [new GregorianDate(2024, 2, 29), [2024, 2, 1]],
        ];
    }

    /**
     * Tests end of month.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfMonthProvider')]
    public function testEndOfMonth(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->endOfMonth()->toArray());
    }

    /**
     * Provides data for end of month tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function endOfMonthProvider(): array
    {
        return [
            'Thirty-day month' => [new GregorianDate(2025, 6, 15), [2025, 6, 30]],
            'Thirty-one-day month' => [new GregorianDate(2025, 7, 15), [2025, 7, 31]],
            'Leap February' => [new GregorianDate(2024, 2, 15), [2024, 2, 29]],
            'Non-leap February' => [new GregorianDate(2025, 2, 15), [2025, 2, 28]],
        ];
    }

    /**
     * Tests start of week.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfWeekProvider')]
    public function testStartOfWeek(GregorianDate $date, DayOfWeek $firstDayOfWeek, array $expected): void
    {
        self::assertSame($expected, $date->startOfWeek($firstDayOfWeek)->toArray());
    }

    /**
     * Provides data for start of week tests.
     *
     * @return array<array{GregorianDate,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function startOfWeekProvider(): array
    {
        return [
            'Default Saturday start' => [new GregorianDate(2025, 3, 21), DayOfWeek::Saturday, [2025, 3, 15]],
            'Monday start' => [new GregorianDate(2025, 3, 21), DayOfWeek::Monday, [2025, 3, 17]],
        ];
    }

    /**
     * Tests end of week.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfWeekProvider')]
    public function testEndOfWeek(GregorianDate $date, DayOfWeek $firstDayOfWeek, array $expected): void
    {
        self::assertSame($expected, $date->endOfWeek($firstDayOfWeek)->toArray());
    }

    /**
     * Provides data for end of week tests.
     *
     * @return array<array{GregorianDate,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function endOfWeekProvider(): array
    {
        return [
            'Default Saturday start' => [new GregorianDate(2025, 3, 21), DayOfWeek::Saturday, [2025, 3, 21]],
            'Monday start' => [new GregorianDate(2025, 3, 21), DayOfWeek::Monday, [2025, 3, 23]],
        ];
    }

    /**
     * Tests start of quarter.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfQuarterProvider')]
    public function testStartOfQuarter(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->startOfQuarter()->toArray());
    }

    /**
     * Provides data for start of quarter tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function startOfQuarterProvider(): array
    {
        return [
            'Q2 middle' => [new GregorianDate(2025, 5, 15), [2025, 4, 1]],
            'Q1 start' => [new GregorianDate(2025, 1, 1), [2025, 1, 1]],
            'Q4 middle' => [new GregorianDate(2025, 11, 15), [2025, 10, 1]],
        ];
    }

    /**
     * Tests end of quarter.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfQuarterProvider')]
    public function testEndOfQuarter(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->endOfQuarter()->toArray());
    }

    /**
     * Provides data for end of quarter tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function endOfQuarterProvider(): array
    {
        return [
            'Q2 middle' => [new GregorianDate(2025, 5, 15), [2025, 6, 30]],
            'Q1 leap year' => [new GregorianDate(2024, 2, 15), [2024, 3, 31]],
            'Q4 middle' => [new GregorianDate(2025, 11, 15), [2025, 12, 31]],
        ];
    }
}
