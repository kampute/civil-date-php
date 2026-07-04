<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date constructor.
 */
final class GregorianDateConstructorTest extends TestCase
{
    /**
     * Tests creates valid representative dates.
     */
    #[DataProvider('validDateProvider')]
    public function testCreatesValidRepresentativeDates(int $year, int $month, int $day): void
    {
        $date = new GregorianDate($year, $month, $day);

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
        return [
            '2025/3/21' => [2025, 3, 21],
            'Leap year Feb 29' => [2024, 2, 29],
            'Non-leap year Feb 28' => [2025, 2, 28],
            'First day of year' => [2025, 1, 1],
            'Last day of year' => [2025, 12, 31],
            'Month with 30 days' => [2025, 4, 30],
            'Month with 31 days' => [2025, 7, 31],
            'Negative year' => [-100, 6, 15],
            'Year -1' => [-1, 12, 31],
            'Year 1' => [1, 1, 1],
            'Century leap year' => [2000, 2, 29],
            'Century non-leap year' => [1900, 2, 28],
        ];
    }

    /**
     * Tests rejects year zero.
     */
    public function testRejectsYearZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GregorianDate(0, 1, 1);
    }

    /**
     * Tests rejects invalid months.
     */
    #[DataProvider('invalidMonthsProvider')]
    public function testRejectsInvalidMonths(int $month): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GregorianDate(2025, $month, 1);
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
        new GregorianDate($year, $month, $day);
    }

    /**
     * Provides data for invalid days tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function invalidDaysProvider(): array
    {
        return [
            'Day 0' => [2025, 1, 0],
            'Day -1' => [2025, 1, -1],
            'Day 32 in January' => [2025, 1, 32],
            'Day 32 in August' => [2025, 8, 32],
            'Invalid February 29 non-leap' => [2025, 2, 29],
            'Invalid February 30 leap' => [2024, 2, 30],
            'Invalid February 30 non-leap' => [2025, 2, 30],
            'Invalid April 31' => [2025, 4, 31],
            'Invalid June 31' => [2025, 6, 31],
            'Invalid September 31' => [2025, 9, 31],
            'Invalid November 31' => [2025, 11, 31],
            'Century non-leap Feb 29' => [1900, 2, 29],
            '2100 is not leap' => [2100, 2, 29],
        ];
    }
}
