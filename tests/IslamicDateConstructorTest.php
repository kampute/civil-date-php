<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use InvalidArgumentException;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date constructor.
 */
final class IslamicDateConstructorTest extends TestCase
{
    /**
     * Tests creates valid representative dates.
     */
    #[DataProvider('validDateProvider')]
    public function testCreatesValidRepresentativeDates(int $year, int $month, int $day): void
    {
        $date = new IslamicDate($year, $month, $day);

        self::assertSame([$year, $month, $day], $date->toArray());
    }

    /**
     * Provides data for valid date tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function validDateProvider(): array
    {
        return [
            'Islamic epoch' => [1, 1, 1],
            'Regular 30-day month' => [1446, 1, 30],
            'Regular 29-day month' => [1446, 2, 29],
            'Common year end' => [1, 12, 29],
            'Leap year end' => [2, 12, 30],
            'Negative year' => [-1, 12, 29],
        ];
    }

    /**
     * Tests rejects year zero.
     */
    public function testRejectsYearZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IslamicDate(0, 1, 1);
    }

    /**
     * Tests rejects invalid months.
     */
    #[DataProvider('invalidMonthsProvider')]
    public function testRejectsInvalidMonths(int $month): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IslamicDate(1446, $month, 1);
    }

    /**
     * Provides data for invalid months tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function invalidMonthsProvider(): array
    {
        return [
            'Month zero' => [0],
            'Month thirteen' => [13],
            'Negative month' => [-1],
        ];
    }

    /**
     * Tests rejects invalid days by month length.
     */
    #[DataProvider('invalidDaysProvider')]
    public function testRejectsInvalidDaysByMonthLength(int $year, int $month, int $day): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IslamicDate($year, $month, $day);
    }

    /**
     * Provides data for invalid day tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function invalidDaysProvider(): array
    {
        return [
            'Day zero' => [1446, 1, 0],
            'Day 31' => [1446, 1, 31],
            'Day 30 in even month' => [1446, 2, 30],
            'Day 30 in common Dhu al-Hijjah' => [1, 12, 30],
        ];
    }
}
