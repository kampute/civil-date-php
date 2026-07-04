<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Calendars;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\GregorianCalendar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests gregorian calendar.
 */
final class GregorianCalendarTest extends TestCase
{
    /**
     * Tests calendar identity.
     */
    public function testCalendarIdentity(): void
    {
        $calendarSystem = GregorianCalendar::instance();

        self::assertInstanceOf(CalendarSystem::class, $calendarSystem);
        self::assertSame($calendarSystem, GregorianCalendar::instance());
        self::assertSame(Calendar::Gregorian, $calendarSystem->id());
        self::assertNull($calendarSystem->todayTimeZone());
    }

    /**
     * Tests validation queries.
     */
    #[DataProvider('validationProvider')]
    public function testValidationQueries(int $year, int $month, int $day, bool $validYear, bool $validMonth, bool $validDay): void
    {
        $calendarSystem = GregorianCalendar::instance();

        self::assertSame($validYear, $calendarSystem->isValidYear($year));
        self::assertSame($validMonth, $calendarSystem->isValidMonth($year, $month));
        self::assertSame($validDay, $calendarSystem->isValidDay($year, $month, $day));
    }

    /**
     * Provides data for validation tests.
     *
     * @return array<array{int,int,int,bool,bool,bool}> Provider data sets.
     */
    public static function validationProvider(): array
    {
        return [
            // Valid dates
            'valid date' => [2025, 3, 21, true, true, true],
            'leap February 29' => [2024, 2, 29, true, true, true],
            'non-leap February 28' => [2025, 2, 28, true, true, true],
            'first day of year' => [2025, 1, 1, true, true, true],
            'last day of year' => [2025, 12, 31, true, true, true],
            'century leap year' => [2000, 2, 29, true, true, true],

            // Invalid dates
            'year zero' => [0, 3, 21, false, false, false],
            'month zero' => [2025, 0, 21, true, false, false],
            'month thirteen' => [2025, 13, 21, true, false, false],
            'non-leap February 29' => [2025, 2, 29, true, true, false],
            'April 31' => [2025, 4, 31, true, true, false],
            'day zero' => [2025, 1, 0, true, true, false],
            'day 32' => [2025, 1, 32, true, true, false],
            'century non-leap year Feb 29' => [1900, 2, 29, true, true, false],
            '2100 Feb 29 invalid' => [2100, 2, 29, true, true, false],
        ];
    }

    /**
     * Tests accepts valid date assertions.
     */
    public function testAcceptsValidDateAssertions(): void
    {
        $calendarSystem = GregorianCalendar::instance();

        $calendarSystem->assertValidYear(2025);
        $calendarSystem->assertValidMonth(2025, 12);
        $calendarSystem->assertValidDay(2024, 2, 29);

        self::addToAssertionCount(3);
    }

    /**
     * Tests days in month.
     */
    #[DataProvider('daysInMonthProvider')]
    public function testDaysInMonth(int $year, int $month, int $expected): void
    {
        self::assertSame($expected, GregorianCalendar::instance()->daysInMonth($year, $month));
    }

    /**
     * Provides data for days in month tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function daysInMonthProvider(): array
    {
        return [
            'January' => [2025, 1, 31],
            'February non-leap' => [2025, 2, 28],
            'February leap' => [2024, 2, 29],
            'February 2000 (century leap)' => [2000, 2, 29],
            'February 1900 (century non-leap)' => [1900, 2, 28],
            'March' => [2025, 3, 31],
            'April' => [2025, 4, 30],
            'May' => [2025, 5, 31],
            'June' => [2025, 6, 30],
            'July' => [2025, 7, 31],
            'August' => [2025, 8, 31],
            'September' => [2025, 9, 30],
            'October' => [2025, 10, 31],
            'November' => [2025, 11, 30],
            'December' => [2025, 12, 31],
        ];
    }

    /**
     * Tests leap years.
     */
    #[DataProvider('leapYearProvider')]
    public function testLeapYears(int $year, bool $expected, int $expectedDays): void
    {
        $calendarSystem = GregorianCalendar::instance();

        self::assertSame($expected, $calendarSystem->isLeapYear($year));
        self::assertSame($expectedDays, $calendarSystem->daysInYear($year));
        self::assertSame(12, $calendarSystem->monthsInYear($year));
    }

    /**
     * Provides data for leap year tests.
     *
     * @return array<array{int,bool,int}> Provider data sets.
     */
    public static function leapYearProvider(): array
    {
        return [
            '2024 (leap)' => [2024, true, 366],
            '2025 (non-leap)' => [2025, false, 365],
            '2000 (century leap, divisible by 400)' => [2000, true, 366],
            '1900 (century non-leap)' => [1900, false, 365],
            '2100 (century non-leap)' => [2100, false, 365],
            '2400 (century leap)' => [2400, true, 366],
            '1996 (leap)' => [1996, true, 366],
            '1997 (non-leap)' => [1997, false, 365],
            '1 (non-leap)' => [1, false, 365],
            '4 (leap)' => [4, true, 366],
            '5 (non-leap)' => [5, false, 365],
            '400 (century leap)' => [400, true, 366],
            '401 (non-leap)' => [401, false, 365],
            '-1 (astronomical year 0, leap)' => [-1, true, 366],
            '-4 (astronomical year -3, non-leap)' => [-4, false, 365],
            '-5 (astronomical year -4, leap)' => [-5, true, 366],
            '-400 (astronomical year -399, non-leap)' => [-400, false, 365],
            '-401 (astronomical year -400, leap)' => [-401, true, 366],
        ];
    }

    /**
     * Tests day of year and month day conversions.
     */
    #[DataProvider('dayOfYearConversionProvider')]
    public function testDayOfYearAndMonthDayConversions(int $year, int $month, int $day, int $dayOfYear): void
    {
        $calendarSystem = GregorianCalendar::instance();

        self::assertSame($dayOfYear, $calendarSystem->toDayOfYear($year, $month, $day));
        self::assertSame([$month, $day], $calendarSystem->toMonthDay($year, $dayOfYear));
    }

    /**
     * Provides data for day of year conversion tests.
     *
     * @return array<array{int,int,int,int}> Provider data sets.
     */
    public static function dayOfYearConversionProvider(): array
    {
        return [
            'First day of year' => [2025, 1, 1, 1],
            'Last day of January' => [2025, 1, 31, 31],
            'First day of February' => [2025, 2, 1, 32],
            'February 29 in leap year' => [2024, 2, 29, 60],
            'March 1 in leap year' => [2024, 3, 1, 61],
            'Last day of non-leap year' => [2025, 12, 31, 365],
            'Last day of leap year' => [2024, 12, 31, 366],
        ];
    }

    /**
     * Tests j d n and year month day conversions.
     *
        *
     * @param array<mixed> $expectedComponents Test data.
     */
    #[DataProvider('jdnConversionProvider')]
    public function testJDNAndYearMonthDayConversions(int $jdn, array $expectedComponents): void
    {
        $calendarSystem = GregorianCalendar::instance();

        self::assertSame($expectedComponents, $calendarSystem->toYearMonthDay($jdn));
        self::assertSame($jdn, $calendarSystem->toJDN(...$expectedComponents));
    }

    /**
     * Provides data for jdn conversion tests.
     *
     * @return array<array{int,array<mixed>}> Provider data sets.
     */
    public static function jdnConversionProvider(): array
    {
        return [
            'Ancient date' => [1721426, [1, 1, 1]],
            'Unix epoch' => [2440588, [1970, 1, 1]],
            'Y2K' => [2451545, [2000, 1, 1]],
            'Nowruz 1403' => [2460390, [2024, 3, 20]],
            'Nowruz 1404' => [2460756, [2025, 3, 21]],
        ];
    }

    /**
     * Tests rejects invalid operation inputs.
     */
    #[DataProvider('invalidOperationProvider')]
    public function testRejectsInvalidOperationInputs(callable $operation): void
    {
        $this->expectException(InvalidArgumentException::class);

        $operation(GregorianCalendar::instance());
    }

    /**
     * Provides data for invalid operation tests.
     *
     * @return array<array{callable}> Provider data sets.
     */
    public static function invalidOperationProvider(): array
    {
        return [
            'isLeapYear year zero' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->isLeapYear(0)],
            'monthsInYear year zero' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->monthsInYear(0)],
            'assertValidYear year zero' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->assertValidYear(0)],
            'daysInMonth year zero' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->daysInMonth(0, 1)],
            'assertValidMonth month 0' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->assertValidMonth(2025, 0)],
            'daysInMonth month 0' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->daysInMonth(2025, 0)],
            'daysInMonth month 13' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->daysInMonth(2025, 13)],
            'assertValidDay invalid day' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->assertValidDay(2025, 2, 29)],
            'toDayOfYear invalid month' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->toDayOfYear(2025, 0, 1)],
            'toDayOfYear invalid day' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->toDayOfYear(2025, 2, 29)],
            'toMonthDay day 0' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->toMonthDay(2025, 0)],
            'toMonthDay day 366 non-leap' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->toMonthDay(2025, 366)],
            'toJDN invalid day' => [static fn (GregorianCalendar $calendarSystem) => $calendarSystem->toJDN(2025, 2, 29)],
        ];
    }
}
