<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\GregorianDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian date conversion.
 */
final class GregorianDateConversionTest extends TestCase
{
    /**
     * Tests to calendar.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('toCalendarProvider')]
    public function testToCalendar(GregorianDate $gregorian, Calendar $calendar, array $expected): void
    {
        $converted = $gregorian->toCalendar($calendar);

        self::assertSame($calendar, $converted->calendar());
        self::assertSame($expected, $converted->toArray());
        self::assertSame($gregorian->jdn(), $converted->jdn());
    }

    /**
     * Provides data for to calendar tests.
     *
     * @return array<array{GregorianDate,Calendar,array<mixed>}> Provider data sets.
     */
    public static function toCalendarProvider(): array
    {
        return [
            'Nowruz 1402' => [new GregorianDate(2023, 3, 21), Calendar::Jalali, [1402, 1, 1]],
            'Gregorian leap day' => [new GregorianDate(2024, 2, 29), Calendar::Jalali, [1402, 12, 10]],
            'Jalali leap day' => [new GregorianDate(2025, 3, 20), Calendar::Jalali, [1403, 12, 30]],
            'Negative year' => [new GregorianDate(-100, 6, 15), Calendar::Jalali, [-721, 3, 24]],
        ];
    }

    /**
     * Tests to calendar returns same instance for same calendar.
     */
    public function testToCalendarReturnsSameInstanceForSameCalendar(): void
    {
        $date = new GregorianDate(2025, 3, 21);

        self::assertSame($date, $date->toCalendar(Calendar::Gregorian));
    }

    /**
     * Tests to iso8601 date string.
     */
    #[DataProvider('toIso8601DateStringProvider')]
    public function testToIso8601DateString(GregorianDate $date, string $expected): void
    {
        self::assertSame($expected, $date->toIso8601DateString());
    }

    /**
     * Provides data for to iso8601 date string tests.
     *
     * @return array<array{GregorianDate,string}> Provider data sets.
     */
    public static function toIso8601DateStringProvider(): array
    {
        return [
            'Standard date' => [new GregorianDate(2025, 3, 21), '2025-03-21'],
            'Leap day' => [new GregorianDate(2024, 2, 29), '2024-02-29'],
            'Negative year' => [new GregorianDate(-100, 6, 15), '-0100-06-15'],
        ];
    }

    /**
     * Tests to array.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('toArrayProvider')]
    public function testToArray(GregorianDate $date, array $expected): void
    {
        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for to array tests.
     *
     * @return array<array{GregorianDate,array<mixed>}> Provider data sets.
     */
    public static function toArrayProvider(): array
    {
        return [
            'Standard date' => [new GregorianDate(2025, 3, 21), [2025, 3, 21]],
            'Leap day' => [new GregorianDate(2024, 2, 29), [2024, 2, 29]],
            'Negative year' => [new GregorianDate(-100, 6, 15), [-100, 6, 15]],
            'Year -1' => [new GregorianDate(-1, 12, 31), [-1, 12, 31]],
            'Year 1' => [new GregorianDate(1, 1, 1), [1, 1, 1]],
        ];
    }
}
