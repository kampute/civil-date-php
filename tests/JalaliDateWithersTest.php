<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date withers.
 */
final class JalaliDateWithersTest extends TestCase
{
    /**
     * Tests with year.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withYearProvider')]
    public function testWithYear(int $year, int $month, int $day, int $newYear, array $expected): void
    {
        $date = new JalaliDate($year, $month, $day);
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
            'Change year' => [1402, 6, 15, 1403, [1403, 6, 15]],
            'Leap to non-leap (clamp day)' => [1403, 12, 30, 1404, [1404, 12, 29]],
            'Non-leap to leap' => [1404, 12, 29, 1403, [1403, 12, 29]],
            'Negative year' => [1402, 6, 15, -100, [-100, 6, 15]],
        ];
    }

    /**
     * Tests with month.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withMonthProvider')]
    public function testWithMonth(int $year, int $month, int $day, int $newMonth, array $expected): void
    {
        $date = new JalaliDate($year, $month, $day);
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
            'Change month' => [1402, 3, 21, 4, [1402, 4, 21]],
            'Day clamping 31 -> 30' => [1402, 6, 31, 7, [1402, 7, 30]],
            'Leap not clamping to Esfand' => [1403, 7, 30, 12, [1403, 12, 30]],
            'Non-Leap clamping to Esfand' => [1402, 7, 30, 12, [1402, 12, 29]],
        ];
    }

    /**
     * Tests with day.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('withDayProvider')]
    public function testWithDay(int $year, int $month, int $day, int $newDay, array $expected): void
    {
        $date = new JalaliDate($year, $month, $day);
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
            'Change day' => [1402, 3, 21, 15, [1402, 3, 15]],
            'First day' => [1402, 3, 21, 1, [1402, 3, 1]],
            'Last day' => [1402, 3, 21, 31, [1402, 3, 31]],
        ];
    }

    /**
     * Tests withers immutability.
     */
    public function testWithersImmutability(): void
    {
        $date = new JalaliDate(1402, 6, 15);

        $withYear = $date->withYear(1403);
        $withMonth = $date->withMonth(7);
        $withDay = $date->withDay(1);

        self::assertNotSame($date, $withYear);
        self::assertNotSame($date, $withMonth);
        self::assertNotSame($date, $withDay);
        self::assertSame([1402, 6, 15], $date->toArray());
    }
}
