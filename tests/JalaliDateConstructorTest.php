<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\DateOutOfRangeException;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date constructor.
 */
final class JalaliDateConstructorTest extends TestCase
{
    /**
     * Tests creates valid representative dates.
     */
    #[DataProvider('validDateProvider')]
    public function testCreatesValidRepresentativeDates(int $year, int $month, int $day): void
    {
        $date = new JalaliDate($year, $month, $day);

        self::assertSame($year, $date->year());
        self::assertSame($month, $date->month());
        self::assertSame($day, $date->day());
    }

    /**
     * Provides data for valid date tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function validDateProvider(): array
    {
        $calendar = JalaliDate::calendarSystem();
        return [
            '1402/1/1' => [1402, 1, 1],
            '1402/6/31' => [1402, 6, 31],
            '1402/7/30' => [1402, 7, 30],
            '1403/12/30 (leap)' => [1403, 12, 30],
            '-100/1/1' => [-100, 1, 1],
            '-1/12/last day' => [-1, 12, $calendar->daysInMonth(-1, 12)],
            'MIN_YEAR boundary' => [JalaliDate::MIN_YEAR, 1, 1],
            'MAX_YEAR boundary' => [JalaliDate::MAX_YEAR, 12, $calendar->daysInMonth(JalaliDate::MAX_YEAR, 12)],
        ];
    }

    /**
     * Tests rejects year zero.
     */
    public function testRejectsYearZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JalaliDate(0, 1, 1);
    }

    /**
     * Tests rejects years outside supported range.
     */
    #[DataProvider('outOfRangeYearsProvider')]
    public function testRejectsYearsOutsideSupportedRange(int $year): void
    {
        $this->expectException(DateOutOfRangeException::class);
        new JalaliDate($year, 1, 1);
    }

    /**
     * Provides data for out of range years tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function outOfRangeYearsProvider(): array
    {
        return [
            'Below MIN_YEAR' => [JalaliDate::MIN_YEAR - 1],
            'Above MAX_YEAR' => [JalaliDate::MAX_YEAR + 1],
        ];
    }

    /**
     * Tests rejects invalid months.
     */
    #[DataProvider('invalidMonthsProvider')]
    public function testRejectsInvalidMonths(int $month): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JalaliDate(1403, $month, 1);
    }

    /**
     * Provides data for invalid months tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function invalidMonthsProvider(): array
    {
        return [
            'Month 0' => [0],
            'Month 13' => [13],
            'Month -1' => [-1],
        ];
    }

    /**
     * Tests rejects invalid days by month length.
     */
    #[DataProvider('invalidDaysProvider')]
    public function testRejectsInvalidDaysByMonthLength(int $year, int $month, int $day): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JalaliDate($year, $month, $day);
    }

    /**
     * Provides data for invalid days tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function invalidDaysProvider(): array
    {
        return [
            'Day 0' => [1403, 1, 0],
            'Day 32 in month 1' => [1403, 1, 32],
            'Day 31 in month 7' => [1403, 7, 31],
            'Day 30 in month 12 (non-leap)' => [1402, 12, 30],
            'Day 31 in month 12 (leap)' => [1403, 12, 31],
        ];
    }
}
