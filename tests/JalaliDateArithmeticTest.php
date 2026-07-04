<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\JalaliDate;
use Kampute\CivilDate\DateOutOfRangeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date arithmetic.
 */
final class JalaliDateArithmeticTest extends TestCase
{
    /**
     * Tests add days.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addDaysProvider')]
    public function testAddDays(JalaliDate $start, int $days, array $expected): void
    {
        self::assertSame($expected, $start->addDays($days)->toArray());
    }

    /**
     * Provides data for add days tests.
     *
     * @return array<array{JalaliDate,int,array<mixed>}> Provider data sets.
     */
    public static function addDaysProvider(): array
    {
        return [
            // Month boundary
            'Last day of month 6 +1' => [new JalaliDate(1402, 6, 31), 1, [1402, 7, 1]],
            'First day of month 7 -1' => [new JalaliDate(1402, 7, 1), -1, [1402, 6, 31]],

            // Year boundary (non-leap to leap)
            'Last day of 1402 +1' => [new JalaliDate(1402, 12, 29), 1, [1403, 1, 1]],
            'First day of 1403 -1' => [new JalaliDate(1403, 1, 1), -1, [1402, 12, 29]],

            // Year boundary (leap to non-leap)
            'Last day of 1403 +1' => [new JalaliDate(1403, 12, 30), 1, [1404, 1, 1]],
            'First day of 1404 -1' => [new JalaliDate(1404, 1, 1), -1, [1403, 12, 30]],

            // Full year forward
            'Full leap year +365' => [new JalaliDate(1403, 1, 1), 365, [1403, 12, 30]],
            'Full non-leap year +365' => [new JalaliDate(1402, 1, 1), 365, [1403, 1, 1]],

            // Large offsets
            'Forward 100 days' => [new JalaliDate(1402, 1, 1), 100, [1402, 4, 8]],
            'Backward 100 days' => [new JalaliDate(1402, 12, 29), -100, [1402, 9, 19]],
            'Forward 1000 days' => [new JalaliDate(1400, 1, 1), 1000, [1402, 9, 25]],

            // Zero offset
            'Add zero days' => [new JalaliDate(1402, 6, 15), 0, [1402, 6, 15]],

            // Zero boundary
            'Last day of year -0001 +1' => [new JalaliDate(-1, 12, 30), 1, [1, 1, 1]],
            'First day of year 0001 -1' => [new JalaliDate(1, 1, 1), -1, [-1, 12, 30]],
        ];
    }

    /**
     * Tests add days zero offset.
     */
    public function testAddDaysZeroOffset(): void
    {
        $date = new JalaliDate(1402, 6, 15);
        self::assertTrue($date->addDays(100)->addDays(-100)->equals($date));
        self::assertTrue($date->addDays(0)->equals($date));
    }

    /**
     * Tests add days rejects outside supported range.
     */
    #[DataProvider('addDaysOutOfRangeProvider')]
    public function testAddDaysRejectsOutsideSupportedRange(JalaliDate $date, int $days): void
    {
        $this->expectException(DateOutOfRangeException::class);
        $date->addDays($days);
    }

    /**
     * Provides data for add days out of range tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function addDaysOutOfRangeProvider(): array
    {
        return [
            'MIN_YEAR -1 day' => [new JalaliDate(JalaliDate::MIN_YEAR, 1, 1), -1],
            'MAX_YEAR +1 day' => [new JalaliDate(JalaliDate::MAX_YEAR, 12, 29), 1],
        ];
    }

    /**
     * Tests add months.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addMonthsProvider')]
    public function testAddMonths(JalaliDate $start, int $months, array $expected): void
    {
        self::assertSame($expected, $start->addMonths($months)->toArray());
    }

    /**
     * Provides data for add months tests.
     *
     * @return array<array{JalaliDate,int,array<mixed>}> Provider data sets.
     */
    public static function addMonthsProvider(): array
    {
        return [
            // Simple month addition
            'Month 5 +1' => [new JalaliDate(1402, 5, 15), 1, [1402, 6, 15]],
            'Month 12 +1 (year boundary)' => [new JalaliDate(1402, 12, 1), 1, [1403, 1, 1]],

            // Day overflow (31 -> 30)
            'Day 31 +1 month (overflow)' => [new JalaliDate(1402, 6, 31), 1, [1402, 7, 30]],

            // Negative months
            'Month 3 -2' => [new JalaliDate(1402, 3, 15), -2, [1402, 1, 15]],

            // Full year increments
            '+12 months' => [new JalaliDate(1402, 6, 15), 12, [1403, 6, 15]],
            '+24 months' => [new JalaliDate(1402, 6, 15), 24, [1404, 6, 15]],
            '-13 months' => [new JalaliDate(1402, 2, 15), -13, [1401, 1, 15]],

            // Leap year day overflow
            'Leap last day +12 (to non-leap)' => [new JalaliDate(1403, 12, 30), 12, [1404, 12, 29]],

            // Year-0 boundary
            'Year 1 month 1 -1' => [new JalaliDate(1, 1, 15), -1, [-1, 12, 15]],
            'Year -1 month 6 +7' => [new JalaliDate(-1, 6, 15), 7, [1, 1, 15]],

            // Large offsets
            '+36 months' => [new JalaliDate(1400, 6, 15), 36, [1403, 6, 15]],
            '-36 months' => [new JalaliDate(1403, 6, 15), -36, [1400, 6, 15]],

            // Zero offset
            'Add zero months' => [new JalaliDate(1402, 6, 15), 0, [1402, 6, 15]],
        ];
    }

    /**
     * Tests add months zero offset.
     */
    public function testAddMonthsZeroOffset(): void
    {
        $date = new JalaliDate(1402, 6, 15);
        self::assertNotSame($date, $date->addMonths(100)->addMonths(-100)->endOfMonth());
        self::assertTrue($date->addMonths(0)->equals($date));
    }

    /**
     * Tests add months rejects outside supported range.
     */
    #[DataProvider('addMonthsOutOfRangeProvider')]
    public function testAddMonthsRejectsOutsideSupportedRange(JalaliDate $date, int $months): void
    {
        $this->expectException(DateOutOfRangeException::class);
        $date->addMonths($months);
    }

    /**
     * Provides data for add months out of range tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function addMonthsOutOfRangeProvider(): array
    {
        return [
            'MIN_YEAR -1 month' => [new JalaliDate(JalaliDate::MIN_YEAR, 1, 1), -1],
            'MAX_YEAR +1 month' => [new JalaliDate(JalaliDate::MAX_YEAR, 12, 1), 1],
        ];
    }

    /**
     * Tests add years.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('addYearsProvider')]
    public function testAddYears(JalaliDate $start, int $years, array $expected): void
    {
        self::assertSame($expected, $start->addYears($years)->toArray());
    }

    /**
     * Provides data for add years tests.
     *
     * @return array<array{JalaliDate,int,array<mixed>}> Provider data sets.
     */
    public static function addYearsProvider(): array
    {
        return [
            // Simple year addition
            'Year 1402 +1' => [new JalaliDate(1402, 6, 15), 1, [1403, 6, 15]],

            // Leap year day overflow
            'Leap last day +1 (to non-leap)' => [new JalaliDate(1403, 12, 30), 1, [1404, 12, 29]],

            // Negative years
            'Year 1402 -2' => [new JalaliDate(1402, 6, 15), -2, [1400, 6, 15]],

            // Large offsets
            '+10 years' => [new JalaliDate(1400, 6, 15), 10, [1410, 6, 15]],
            '+100 years' => [new JalaliDate(1300, 6, 15), 100, [1400, 6, 15]],
            '-100 years' => [new JalaliDate(1400, 6, 15), -100, [1300, 6, 15]],

            // Year-0 boundary
            'Year -1 +1' => [new JalaliDate(-1, 6, 15), 1, [1, 6, 15]],
            'Year 1 -1' => [new JalaliDate(1, 6, 15), -1, [-1, 6, 15]],
            'Year -5 +10 (crosses zero)' => [new JalaliDate(-5, 6, 15), 10, [6, 6, 15]],
            'Year 5 -10 (crosses zero)' => [new JalaliDate(5, 6, 15), -10, [-6, 6, 15]],

            // Zero offset
            'Add zero years' => [new JalaliDate(1402, 6, 15), 0, [1402, 6, 15]],
        ];
    }

    /**
     * Tests add years zero offset.
     */
    public function testAddYearsZeroOffset(): void
    {
        $date = new JalaliDate(1402, 6, 15);
        self::assertNotSame($date, $date->addYears(100)->addYears(-100)->endOfYear());
        self::assertTrue($date->addYears(0)->equals($date));
    }

    /**
     * Tests add years rejects outside supported range.
     */
    #[DataProvider('addYearsOutOfRangeProvider')]
    public function testAddYearsRejectsOutsideSupportedRange(JalaliDate $date, int $years): void
    {
        $this->expectException(DateOutOfRangeException::class);
        $date->addYears($years);
    }

    /**
     * Provides data for add years out of range tests.
     *
     * @return array<array{JalaliDate,int}> Provider data sets.
     */
    public static function addYearsOutOfRangeProvider(): array
    {
        return [
            'MIN_YEAR -1 year' => [new JalaliDate(JalaliDate::MIN_YEAR, 1, 1), -1],
            'MAX_YEAR +1 year' => [new JalaliDate(JalaliDate::MAX_YEAR, 1, 1), 1],
        ];
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
        $date = new JalaliDate(1403, 1, 1);

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
            'Thursday' => [DayOfWeek::Thursday, 1, [1403, 1, 2]],
            'Same day is exclusive' => [DayOfWeek::Wednesday, 1, [1403, 1, 8]],
            'Second Saturday' => [DayOfWeek::Saturday, 2, [1403, 1, 11]],
            'Sunday from integer day of week' => [DayOfWeek::Sunday->value, 1, [1403, 1, 5]],
        ];
    }

    /**
     * Tests next day of week rejects invalid input.
     */
    #[DataProvider('invalidNextDayOfWeekProvider')]
    public function testNextDayOfWeekRejectsInvalidInput(DayOfWeek|int $dayOfWeek, int $occurrence): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->nextDayOfWeek($dayOfWeek, $occurrence);
    }

    /**
     * Provides data for invalid next day of week tests.
     *
     * @return array<array{DayOfWeek|int,int}> Provider data sets.
     */
    public static function invalidNextDayOfWeekProvider(): array
    {
        return [
            'Occurrence zero' => [DayOfWeek::Wednesday, 0],
            'Negative occurrence' => [DayOfWeek::Wednesday, -1],
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
        $date = new JalaliDate(1403, 1, 1);

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
            'Tuesday' => [DayOfWeek::Tuesday, 1, [1402, 12, 29]],
            'Same day is exclusive' => [DayOfWeek::Wednesday, 1, [1402, 12, 23]],
            'Second Saturday' => [DayOfWeek::Saturday, 2, [1402, 12, 19]],
            'Sunday from integer day of week' => [DayOfWeek::Sunday->value, 1, [1402, 12, 27]],
        ];
    }

    /**
     * Tests previous day of week rejects invalid input.
     */
    #[DataProvider('invalidPreviousDayOfWeekProvider')]
    public function testPreviousDayOfWeekRejectsInvalidInput(DayOfWeek|int $dayOfWeek, int $occurrence): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JalaliDate(1403, 1, 1))->previousDayOfWeek($dayOfWeek, $occurrence);
    }

    /**
     * Provides data for invalid previous day of week tests.
     *
     * @return array<array{DayOfWeek|int,int}> Provider data sets.
     */
    public static function invalidPreviousDayOfWeekProvider(): array
    {
        return [
            'Occurrence zero' => [DayOfWeek::Wednesday, 0],
            'Negative occurrence' => [DayOfWeek::Wednesday, -1],
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
    public function testStartOfYear(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->startOfYear()->toArray());
    }

    /**
     * Provides data for start of year tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function startOfYearProvider(): array
    {
        return [
            'Non-leap year' => [new JalaliDate(1402, 6, 15), [1402, 1, 1]],
            'Leap year' => [new JalaliDate(1403, 6, 15), [1403, 1, 1]],
        ];
    }

    /**
     * Tests end of year.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfYearProvider')]
    public function testEndOfYear(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->endOfYear()->toArray());
    }

    /**
     * Provides data for end of year tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function endOfYearProvider(): array
    {
        return [
            'Non-leap year' => [new JalaliDate(1402, 6, 15), [1402, 12, 29]],
            'Leap year' => [new JalaliDate(1403, 6, 15), [1403, 12, 30]],
        ];
    }

    /**
     * Tests start of month.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfMonthProvider')]
    public function testStartOfMonth(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->startOfMonth()->toArray());
    }

    /**
     * Provides data for start of month tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function startOfMonthProvider(): array
    {
        return [
            'Month 6' => [new JalaliDate(1402, 6, 15), [1402, 6, 1]],
            'Month 12 leap' => [new JalaliDate(1403, 12, 30), [1403, 12, 1]],
        ];
    }

    /**
     * Tests end of month.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfMonthProvider')]
    public function testEndOfMonth(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->endOfMonth()->toArray());
    }

    /**
     * Provides data for end of month tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function endOfMonthProvider(): array
    {
        return [
            'Month 6' => [new JalaliDate(1402, 6, 15), [1402, 6, 31]],
            'Month 7' => [new JalaliDate(1402, 7, 15), [1402, 7, 30]],
            'Month 12 non-leap' => [new JalaliDate(1402, 12, 15), [1402, 12, 29]],
            'Month 12 leap' => [new JalaliDate(1403, 12, 15), [1403, 12, 30]],
        ];
    }

    /**
     * Tests start of week.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfWeekProvider')]
    public function testStartOfWeek(JalaliDate $date, DayOfWeek $firstDayOfWeek, array $expected): void
    {
        self::assertSame($expected, $date->startOfWeek($firstDayOfWeek)->toArray());
    }

    /**
     * Provides data for start of week tests.
     *
     * @return array<array{JalaliDate,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function startOfWeekProvider(): array
    {
        return [
            'Saturday week start' => [new JalaliDate(1403, 1, 1), DayOfWeek::Saturday, [1402, 12, 26]],
            'Sunday week start' => [new JalaliDate(1403, 1, 1), DayOfWeek::Sunday, [1402, 12, 27]],
            'Monday week start' => [new JalaliDate(1403, 1, 1), DayOfWeek::Monday, [1402, 12, 28]],
        ];
    }

    /**
     * Tests end of week.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfWeekProvider')]
    public function testEndOfWeek(JalaliDate $date, DayOfWeek $firstDayOfWeek, array $expected): void
    {
        self::assertSame($expected, $date->endOfWeek($firstDayOfWeek)->toArray());
    }

    /**
     * Provides data for end of week tests.
     *
     * @return array<array{JalaliDate,DayOfWeek,array<mixed>}> Provider data sets.
     */
    public static function endOfWeekProvider(): array
    {
        return [
            'Saturday week start' => [new JalaliDate(1403, 1, 1), DayOfWeek::Saturday, [1403, 1, 3]],
            'Sunday week start' => [new JalaliDate(1403, 1, 1), DayOfWeek::Sunday, [1403, 1, 4]],
            'Monday week start' => [new JalaliDate(1403, 1, 1), DayOfWeek::Monday, [1403, 1, 5]],
        ];
    }

    /**
     * Tests start of quarter.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('startOfQuarterProvider')]
    public function testStartOfQuarter(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->startOfQuarter()->toArray());
    }

    /**
     * Provides data for start of quarter tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function startOfQuarterProvider(): array
    {
        return [
            'Q1 middle' => [new JalaliDate(1403, 2, 15), [1403, 1, 1]],
            'Q2 middle' => [new JalaliDate(1403, 5, 20), [1403, 4, 1]],
            'Q3 middle' => [new JalaliDate(1403, 8, 10), [1403, 7, 1]],
            'Q4 non-leap' => [new JalaliDate(1402, 11, 15), [1402, 10, 1]],
            'Q4 leap' => [new JalaliDate(1403, 11, 15), [1403, 10, 1]],
        ];
    }

    /**
     * Tests end of quarter.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('endOfQuarterProvider')]
    public function testEndOfQuarter(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->endOfQuarter()->toArray());
    }

    /**
     * Provides data for end of quarter tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function endOfQuarterProvider(): array
    {
        return [
            'Q1 middle' => [new JalaliDate(1403, 2, 15), [1403, 3, 31]],
            'Q2 middle' => [new JalaliDate(1403, 5, 20), [1403, 6, 31]],
            'Q3 middle' => [new JalaliDate(1403, 8, 10), [1403, 9, 30]],
            'Q4 non-leap' => [new JalaliDate(1402, 11, 15), [1402, 12, 29]],
            'Q4 leap' => [new JalaliDate(1403, 11, 15), [1403, 12, 30]],
        ];
    }
}
