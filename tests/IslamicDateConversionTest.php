<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\IslamicDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic date conversion.
 */
final class IslamicDateConversionTest extends TestCase
{
    /**
     * Tests to calendar.
     *
     * @param array<mixed> $expected Test data.
     */
    #[DataProvider('toCalendarProvider')]
    public function testToCalendar(IslamicDate $islamic, Calendar $calendar, array $expected): void
    {
        $converted = $islamic->toCalendar($calendar);

        self::assertSame($calendar, $converted->calendar());
        self::assertSame($expected, $converted->toArray());
        self::assertSame($islamic->jdn(), $converted->jdn());
    }

    /**
     * Provides data for to calendar tests.
     *
     * @return array<array{IslamicDate,Calendar,array<mixed>}> Provider data sets.
     */
    public static function toCalendarProvider(): array
    {
        return [
            'Islamic epoch to Gregorian' => [new IslamicDate(1, 1, 1), Calendar::Gregorian, [622, 7, 19]],
            'Muharram 1446 to Gregorian' => [new IslamicDate(1446, 1, 1), Calendar::Gregorian, [2024, 7, 8]],
            'Muharram 1446 to Jalali' => [new IslamicDate(1446, 1, 1), Calendar::Jalali, [1403, 4, 18]],
        ];
    }

    /**
     * Tests to calendar returns same instance for same calendar.
     */
    public function testToCalendarReturnsSameInstanceForSameCalendar(): void
    {
        $date = new IslamicDate(1446, 9, 1);
        self::assertSame($date, $date->toCalendar(Calendar::Islamic));
    }

    /**
     * Tests to iso8601 date string.
     */
    #[DataProvider('toIso8601DateStringProvider')]
    public function testToIso8601DateString(IslamicDate $date, string $expected): void
    {
        self::assertSame($expected, $date->toIso8601DateString());
    }

    /**
     * Provides data for to iso8601 date string tests.
     *
     * @return array<array{IslamicDate,string}> Provider data sets.
     */
    public static function toIso8601DateStringProvider(): array
    {
        return [
            'Islamic epoch' => [new IslamicDate(1, 1, 1), '0622-07-19'],
            'Muharram 1446' => [new IslamicDate(1446, 1, 1), '2024-07-08'],
        ];
    }

    /**
     * Tests to array.
     */
    public function testToArray(): void
    {
        self::assertSame([-1, 12, 29], (new IslamicDate(-1, 12, 29))->toArray());
    }
}
