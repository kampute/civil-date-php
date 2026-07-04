<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Calendars;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\TabularIslamicCalendar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests tabular islamic calendar.
 */
final class TabularIslamicCalendarTest extends TestCase
{
    /**
     * Tests calendar identity.
     */
    public function testCalendarIdentity(): void
    {
        $calendarSystem = TabularIslamicCalendar::instance();

        self::assertInstanceOf(CalendarSystem::class, $calendarSystem);
        self::assertSame($calendarSystem, TabularIslamicCalendar::instance());
        self::assertSame(Calendar::Islamic, $calendarSystem->id());
        self::assertNull($calendarSystem->todayTimeZone());
    }

    /**
     * Tests validation queries.
     */
    #[DataProvider('validationProvider')]
    public function testValidationQueries(int $year, int $month, int $day, bool $validYear, bool $validMonth, bool $validDay): void
    {
        $calendarSystem = TabularIslamicCalendar::instance();

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
            'first day of year' => [1446, 1, 1, true, true, true],
            '30-day month end' => [1446, 1, 30, true, true, true],
            '29-day month end' => [1446, 2, 29, true, true, true],
            'leap year month 12 day 30' => [2, 12, 30, true, true, true],
            'negative year' => [-1, 12, 29, true, true, true],

            // Invalid dates
            'year zero' => [0, 1, 1, false, false, false],
            'month zero' => [1446, 0, 1, true, false, false],
            'month thirteen' => [1446, 13, 1, true, false, false],
            'day zero' => [1446, 1, 0, true, true, false],
            'day 31' => [1446, 1, 31, true, true, false],
            'day 30 in 29-day month' => [1446, 2, 30, true, true, false],
            'day 30 in common year month 12' => [1, 12, 30, true, true, false],
        ];
    }

    /**
     * Tests accepts valid date assertions.
     */
    public function testAcceptsValidDateAssertions(): void
    {
        $calendarSystem = TabularIslamicCalendar::instance();

        $calendarSystem->assertValidYear(1446);
        $calendarSystem->assertValidMonth(1446, 12);
        $calendarSystem->assertValidDay(2, 12, 30);

        self::addToAssertionCount(3);
    }

    /**
     * Tests days in month.
     */
    #[DataProvider('daysInMonthProvider')]
    public function testDaysInMonth(int $year, int $month, int $expected): void
    {
        self::assertSame($expected, TabularIslamicCalendar::instance()->daysInMonth($year, $month));
    }

    /**
     * Provides data for days in month tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function daysInMonthProvider(): array
    {
        $data = [];
        for ($month = 1; $month <= 11; ++$month) {
            $data["Month {$month}"] = [1446, $month, 29 + ($month % 2)];
        }
        $data['Month 12 common year'] = [1, 12, 29];
        $data['Month 12 leap year'] = [2, 12, 30];
        return $data;
    }

    /**
     * Tests leap years and year lengths.
     */
    #[DataProvider('leapYearProvider')]
    public function testLeapYears(int $year, bool $expected, int $expectedDays): void
    {
        $calendarSystem = TabularIslamicCalendar::instance();

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
            'year 1 common' => [1, false, 354],
            'year 2 leap' => [2, true, 355],
            'year 30 common' => [30, false, 354],
            'negative year common' => [-1, false, 354],
        ];
    }

    /**
     * Tests day of year and month day conversions.
     */
    #[DataProvider('dayOfYearConversionProvider')]
    public function testDayOfYearAndMonthDayConversions(int $year, int $month, int $day, int $dayOfYear): void
    {
        $calendarSystem = TabularIslamicCalendar::instance();

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
            'First day of year' => [1446, 1, 1, 1],
            'Last day of Muharram' => [1446, 1, 30, 30],
            'First day of Safar' => [1446, 2, 1, 31],
            'First day of Ramadan' => [1446, 9, 1, 237],
            'Last day of common year' => [1446, 12, 29, 354],
            'Last day of leap year' => [2, 12, 30, 355],
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
        $calendarSystem = TabularIslamicCalendar::instance();

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
            'Islamic epoch' => [1948440, [1, 1, 1]],
            'Day before epoch' => [1948439, [-1, 12, 29]],
            'Negative year start' => [1948086, [-1, 1, 1]],
            'Muharram 1446' => [2460500, [1446, 1, 1]],
            'Ramadan 1446' => [2460736, [1446, 9, 1]],
            'Muharram 1447' => [2460854, [1447, 1, 1]],
        ];
    }

    /**
     * Tests JDN conversions round trip across the leap cycle and year boundary.
     */
    public function testJDNConversionsRoundTripAcrossLeapCycleAndYearBoundary(): void
    {
        $calendarSystem = TabularIslamicCalendar::instance();

        for ($year = -40; $year <= 40; ++$year) {
            if ($year === 0) {
                continue;
            }

            for ($month = 1; $month <= 12; ++$month) {
                foreach ([1, $calendarSystem->daysInMonth($year, $month)] as $day) {
                    $jdn = $calendarSystem->toJDN($year, $month, $day);
                    self::assertSame([$year, $month, $day], $calendarSystem->toYearMonthDay($jdn));
                }
            }
        }
    }

    /**
     * Tests rejects invalid operation inputs.
     */
    #[DataProvider('invalidOperationProvider')]
    public function testRejectsInvalidOperationInputs(callable $operation): void
    {
        $this->expectException(InvalidArgumentException::class);

        $operation(TabularIslamicCalendar::instance());
    }

    /**
     * Provides data for invalid operation tests.
     *
     * @return array<array{callable}> Provider data sets.
     */
    public static function invalidOperationProvider(): array
    {
        return [
            'isLeapYear year zero' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->isLeapYear(0)],
            'monthsInYear year zero' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->monthsInYear(0)],
            'assertValidYear year zero' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->assertValidYear(0)],
            'daysInMonth year zero' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->daysInMonth(0, 1)],
            'assertValidMonth month 0' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->assertValidMonth(1446, 0)],
            'daysInMonth month 0' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->daysInMonth(1446, 0)],
            'daysInMonth month 13' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->daysInMonth(1446, 13)],
            'assertValidDay invalid day' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->assertValidDay(1446, 2, 30)],
            'toDayOfYear invalid month' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->toDayOfYear(1446, 0, 1)],
            'toDayOfYear invalid day' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->toDayOfYear(1446, 2, 30)],
            'toMonthDay day 0' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->toMonthDay(1446, 0)],
            'toMonthDay day 355 common year' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->toMonthDay(1446, 355)],
            'toJDN invalid day' => [static fn (TabularIslamicCalendar $calendarSystem) => $calendarSystem->toJDN(1446, 2, 30)],
        ];
    }
}
