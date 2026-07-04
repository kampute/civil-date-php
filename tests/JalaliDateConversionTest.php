<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\JalaliDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests jalali date conversion.
 */
final class JalaliDateConversionTest extends TestCase
{
    /**
     * Tests to calendar.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('toCalendarProvider')]
    public function testToCalendar(JalaliDate $jalali, Calendar $calendar, array $expected): void
    {
        $converted = $jalali->toCalendar($calendar);

        self::assertSame($calendar, $converted->calendar());
        self::assertSame($expected, $converted->toArray());
        self::assertSame($jalali->jdn(), $converted->jdn());
    }

    /**
     * Provides data for to calendar tests.
     *
     * @return array<array{JalaliDate,Calendar,array<mixed>}> Provider data sets.
     */
    public static function toCalendarProvider(): array
    {
        return [
            'Nowruz 1402' => [new JalaliDate(1402, 1, 1), Calendar::Gregorian, [2023, 3, 21]],
            'Gregorian leap day' => [new JalaliDate(1402, 12, 10), Calendar::Gregorian, [2024, 2, 29]],
            'Jalali leap day' => [new JalaliDate(1403, 12, 30), Calendar::Gregorian, [2025, 3, 20]],
            'Negative year' => [new JalaliDate(-100, 6, 15), Calendar::Gregorian, [522, 9, 6]],
        ];
    }

    /**
     * Tests to calendar returns same instance for same calendar.
     */
    public function testToCalendarReturnsSameInstanceForSameCalendar(): void
    {
        $date = new JalaliDate(1404, 1, 1);

        self::assertSame($date, $date->toCalendar(Calendar::Jalali));
    }

    /**
     * Tests to iso8601 date string.
     */
    #[DataProvider('toIso8601DateStringProvider')]
    public function testToIso8601DateString(JalaliDate $date, string $expected): void
    {
        self::assertSame($expected, $date->toIso8601DateString());
    }

    /**
     * Provides data for to iso8601 date string tests.
     *
     * @return array<array{JalaliDate,string}> Provider data sets.
     */
    public static function toIso8601DateStringProvider(): array
    {
        return [
            'Nowruz 1402' => [new JalaliDate(1402, 1, 1), '2023-03-21'],
            'Nowruz 1403' => [new JalaliDate(1403, 1, 1), '2024-03-20'],
            'Jalali leap day' => [new JalaliDate(1403, 12, 30), '2025-03-20'],
            'Negative year' => [new JalaliDate(-100, 6, 15), '0522-09-06'],
        ];
    }

    /**
     * Tests to array.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('toArrayProvider')]
    public function testToArray(JalaliDate $date, array $expected): void
    {
        self::assertSame($expected, $date->toArray());
    }

    /**
     * Provides data for to array tests.
     *
     * @return array<array{JalaliDate,array<mixed>}> Provider data sets.
     */
    public static function toArrayProvider(): array
    {
        return [
            'Standard date' => [new JalaliDate(1402, 6, 5), [1402, 6, 5]],
            'Leap day' => [new JalaliDate(1403, 12, 30), [1403, 12, 30]],
            'Negative year' => [new JalaliDate(-1, 12, JalaliDate::calendarSystem()->daysInMonth(-1, 12)), [-1, 12, 30]],
            'Minimum boundary' => [new JalaliDate(JalaliDate::MIN_YEAR, 1, 1), [JalaliDate::MIN_YEAR, 1, 1]],
            'Maximum boundary' => [new JalaliDate(JalaliDate::MAX_YEAR, 12, JalaliDate::calendarSystem()->daysInMonth(JalaliDate::MAX_YEAR, 12)), [JalaliDate::MAX_YEAR, 12, 29]],
        ];
    }
}
