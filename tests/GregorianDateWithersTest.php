<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date withers.
 */
final class GregorianDateWithersTest extends TestCase
{
    /**
     * Tests with year.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withYearProvider')]
    public function testWithYear(int $year, int $month, int $day, int $newYear, array $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        $result = $date->withYear($newYear);

        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for with year tests.
     *
     * @return array<array{int,int,int,int,array<mixed>}> Provider data sets.
     */
    public static function withYearProvider(): array
    {
        return [
            'Change year' => [2025, 3, 21, 2026, [2026, 3, 21]],
            'Leap to non-leap Feb 29' => [2024, 2, 29, 2025, [2025, 2, 28]],
            'Non-leap to leap Feb 28' => [2025, 2, 28, 2024, [2024, 2, 28]],
            'Negative year' => [2025, 6, 15, -100, [-100, 6, 15]],
        ];
    }

    /**
     * Tests with month.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withMonthProvider')]
    public function testWithMonth(int $year, int $month, int $day, int $newMonth, array $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        $result = $date->withMonth($newMonth);

        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for with month tests.
     *
     * @return array<array{int,int,int,int,array<mixed>}> Provider data sets.
     */
    public static function withMonthProvider(): array
    {
        return [
            'Change month' => [2025, 3, 21, 4, [2025, 4, 21]],
            'Day clamping Jan 31 to Feb' => [2025, 1, 31, 2, [2025, 2, 28]],
            'Day clamping to April' => [2025, 5, 31, 4, [2025, 4, 30]],
            'Leap year clamping' => [2024, 1, 31, 2, [2024, 2, 29]],
        ];
    }

    /**
     * Tests with day.
     *
        *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withDayProvider')]
    public function testWithDay(int $year, int $month, int $day, int $newDay, array $expected): void
    {
        $date = new GregorianDate($year, $month, $day);
        $result = $date->withDay($newDay);

        self::assertSame($expected, $result->toArray());
    }

    /**
     * Provides data for with day tests.
     *
     * @return array<array{int,int,int,int,array<mixed>}> Provider data sets.
     */
    public static function withDayProvider(): array
    {
        return [
            'Change day' => [2025, 3, 21, 15, [2025, 3, 15]],
            'First day' => [2025, 3, 21, 1, [2025, 3, 1]],
            'Last day' => [2025, 3, 21, 31, [2025, 3, 31]],
        ];
    }

    /**
     * Tests withers immutability.
     */
    public function testWithersImmutability(): void
    {
        $date = new GregorianDate(2025, 3, 21);

        $withYear = $date->withYear(2026);
        $withMonth = $date->withMonth(4);
        $withDay = $date->withDay(15);

        self::assertNotSame($date, $withYear);
        self::assertNotSame($date, $withMonth);
        self::assertNotSame($date, $withDay);
        self::assertSame([2025, 3, 21], $date->toArray());
    }
}
