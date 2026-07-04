<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Calendars;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\JalaliCalendar;
use Kampute\CivilDate\DateOutOfRangeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Tests jalali calendar.
 */
final class JalaliCalendarTest extends TestCase
{
    // Source: https://en.wikipedia.org/wiki/Solar_Hijri_calendar#Comparison_with_Gregorian_calendar
    private const KNOWN_LEAP_YEARS = [
        1354, 1358, 1362, 1366, 1370, 1375, 1379, 1383, 1387, 1391, 1395, 1399, 1403, 1408, 1412, 1416
    ];

    /**
     * Tests calendar identity.
     */
    public function testCalendarIdentity(): void
    {
        $calendarSystem = JalaliCalendar::instance();

        self::assertInstanceOf(CalendarSystem::class, $calendarSystem);
        self::assertSame($calendarSystem, JalaliCalendar::instance());
        self::assertSame(Calendar::Jalali, $calendarSystem->id());
        self::assertEquals(new DateTimeZone('+03:30'), $calendarSystem->todayTimeZone());
    }

    /**
     * Tests validation queries.
     */
    #[DataProvider('validationProvider')]
    public function testValidationQueries(int $year, int $month, int $day, bool $validYear, bool $validMonth, bool $validDay): void
    {
        $calendarSystem = JalaliCalendar::instance();

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
            'valid date 1403/1/1' => [1403, 1, 1, true, true, true],
            'valid date 1403/6/31' => [1403, 6, 31, true, true, true],
            'valid date 1403/7/30' => [1403, 7, 30, true, true, true],
            'leap Esfand 30' => [1403, 12, 30, true, true, true],
            'non-leap Esfand 29' => [1402, 12, 29, true, true, true],
            'MIN_YEAR' => [JalaliCalendar::MIN_YEAR, 1, 1, true, true, true],
            'MAX_YEAR' => [JalaliCalendar::MAX_YEAR, 12, 29, true, true, true],

            // Invalid dates
            'year zero' => [0, 1, 1, false, false, false],
            'below supported range' => [JalaliCalendar::MIN_YEAR - 1, 1, 1, false, false, false],
            'above supported range' => [JalaliCalendar::MAX_YEAR + 1, 1, 1, false, false, false],
            'month zero' => [1403, 0, 1, true, false, false],
            'month thirteen' => [1403, 13, 1, true, false, false],
            'non-leap Esfand 30' => [1402, 12, 30, true, true, false],
            'month 7 day 31' => [1403, 7, 31, true, true, false],
            'day zero' => [1403, 1, 0, true, true, false],
            'day 32' => [1403, 1, 32, true, true, false],
        ];
    }

    /**
     * Tests accepts valid date assertions.
     */
    public function testAcceptsValidDateAssertions(): void
    {
        $calendarSystem = JalaliCalendar::instance();

        $calendarSystem->assertValidYear(1403);
        $calendarSystem->assertValidMonth(1403, 12);
        $calendarSystem->assertValidDay(1403, 12, 30);

        self::addToAssertionCount(3);
    }

    /**
     * Tests days in month.
     */
    #[DataProvider('daysInMonthProvider')]
    public function testDaysInMonth(int $year, int $month, int $expected): void
    {
        self::assertSame($expected, JalaliCalendar::instance()->daysInMonth($year, $month));
    }

    /**
     * Provides data for days in month tests.
     *
     * @return array<array{int,int,int}> Provider data sets.
     */
    public static function daysInMonthProvider(): array
    {
        $data = [];
        for ($month = 1; $month <= 6; ++$month) {
            $data["Month {$month}"] = [1403, $month, 31];
        }
        for ($month = 7; $month <= 11; ++$month) {
            $data["Month {$month}"] = [1403, $month, 30];
        }
        $data['Esfand non-leap'] = [1402, 12, 29];
        $data['Esfand leap'] = [1403, 12, 30];
        return $data;
    }

    /**
     * Tests leap years.
     */
    #[DataProvider('leapYearProvider')]
    public function testLeapYears(int $year): void
    {
        $calendarSystem = JalaliCalendar::instance();

        self::assertTrue($calendarSystem->isLeapYear($year));
        self::assertSame(366, $calendarSystem->daysInYear($year));
        self::assertSame(12, $calendarSystem->monthsInYear($year));
    }

    /**
     * Provides data for leap year tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function leapYearProvider(): array
    {
        $data = [];
        foreach (self::KNOWN_LEAP_YEARS as $year) {
            $data["Year {$year}"] = [$year];
        }
        return $data;
    }

    /**
     * Tests non leap years.
     */
    #[DataProvider('nonLeapYearProvider')]
    public function testNonLeapYears(int $year): void
    {
        $calendarSystem = JalaliCalendar::instance();

        self::assertFalse($calendarSystem->isLeapYear($year));
        self::assertSame(365, $calendarSystem->daysInYear($year));
    }

    /**
     * Provides data for non leap year tests.
     *
     * @return array<array{int}> Provider data sets.
     */
    public static function nonLeapYearProvider(): array
    {
        $data = [];
        foreach (range(1351, 1419) as $year) {
            if (!in_array($year, self::KNOWN_LEAP_YEARS, true)) {
                $data["Year {$year}"] = [$year];
            }
        }
        return $data;
    }

    /**
     * Tests day of year and month day conversions.
     */
    #[DataProvider('dayOfYearConversionProvider')]
    public function testDayOfYearAndMonthDayConversions(int $year, int $month, int $day, int $dayOfYear): void
    {
        $calendarSystem = JalaliCalendar::instance();

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
            'First day of year' => [1403, 1, 1, 1],
            'Last day of Farvardin' => [1403, 1, 31, 31],
            'First day of Ordibehesht' => [1403, 2, 1, 32],
            'Last day of Shahrivar' => [1403, 6, 31, 186],
            'First day of Mehr' => [1403, 7, 1, 187],
            'Last day of non-leap year' => [1402, 12, 29, 365],
            'Last day of leap year' => [1403, 12, 30, 366],
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
        $calendarSystem = JalaliCalendar::instance();

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
            'Unix epoch' => [2440588, [1348, 10, 11]],
            'Y2K' => [2451545, [1378, 10, 11]],
            'Last day of 1402' => [2460389, [1402, 12, 29]],
            'Nowruz 1403' => [2460390, [1403, 1, 1]],
            'Leap day 1403' => [2460755, [1403, 12, 30]],
            'Nowruz 1404' => [2460756, [1404, 1, 1]],
        ];
    }

    /**
     * Tests vernal equinox.
     */
    #[DataProvider('vernalEquinoxProvider')]
    public function testVernalEquinox(int $jalaliYear, string $expectedTime): void
    {
        $timezone = new DateTimeZone('+03:30');
        $actual = JalaliCalendar::instance()->vernalEquinox($jalaliYear);
        $expected = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $expectedTime, $timezone);

        self::assertInstanceOf(DateTimeImmutable::class, $expected);
        self::assertEquals($timezone, $actual->getTimezone());
        self::assertEqualsWithDelta($expected->getTimestamp(), $actual->getTimestamp(), 30);
    }

    /**
     * Provides data for vernal equinox tests.
     *
     * @return array<array{int,string}> Provider data sets.
     */
    public static function vernalEquinoxProvider(): array
    {
        // Historical vernal equinox times in Iran Standard Time (UTC+3:30).
        return [
            '1395 (2016)' => [1395, '2016-03-20 08:00:12'],
            '1396 (2017)' => [1396, '2017-03-20 13:58:40'],
            '1397 (2018)' => [1397, '2018-03-20 19:45:28'],
            '1398 (2019)' => [1398, '2019-03-21 01:28:27'],
            '1399 (2020)' => [1399, '2020-03-20 07:19:37'],
            '1400 (2021)' => [1400, '2021-03-20 13:07:28'],
            '1401 (2022)' => [1401, '2022-03-20 19:03:26'],
            '1402 (2023)' => [1402, '2023-03-21 00:54:28'],
            '1403 (2024)' => [1403, '2024-03-20 06:36:26'],
            '1404 (2025)' => [1404, '2025-03-20 12:31:30'],
            '1405 (2026)' => [1405, '2026-03-20 18:15:59'],
        ];
    }

    /**
     * Tests rejects invalid year operation inputs.
     *
        *
     * @param class-string<Throwable> $exceptionClass Expected exception class.
     */
    #[DataProvider('invalidYearOperationProvider')]
    public function testRejectsInvalidYearOperationInputs(callable $operation, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);

        $operation(JalaliCalendar::instance());
    }

    /**
     * Provides data for invalid year operation tests.
     *
     * @return array<array{callable,class-string<Throwable>}> Provider data sets.
     */
    public static function invalidYearOperationProvider(): array
    {
        return [
            'isLeapYear year zero' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->isLeapYear(0), InvalidArgumentException::class],
            'monthsInYear year zero' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->monthsInYear(0), InvalidArgumentException::class],
            'assertValidYear year zero' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->assertValidYear(0), InvalidArgumentException::class],
            'daysInMonth year zero' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->daysInMonth(0, 1), InvalidArgumentException::class],
            'vernalEquinox year zero' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->vernalEquinox(0), InvalidArgumentException::class],
            'assertValidYear below supported range' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->assertValidYear(JalaliCalendar::MIN_YEAR - 1), DateOutOfRangeException::class],
            'below supported range' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->monthsInYear(JalaliCalendar::MIN_YEAR - 1), DateOutOfRangeException::class],
            'above supported range' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->monthsInYear(JalaliCalendar::MAX_YEAR + 1), DateOutOfRangeException::class],
        ];
    }

    /**
     * Tests rejects invalid month operation inputs.
     */
    #[DataProvider('invalidMonthOperationProvider')]
    public function testRejectsInvalidMonthOperationInputs(callable $operation): void
    {
        $this->expectException(InvalidArgumentException::class);

        $operation(JalaliCalendar::instance());
    }

    /**
     * Provides data for invalid month operation tests.
     *
     * @return array<array{callable}> Provider data sets.
     */
    public static function invalidMonthOperationProvider(): array
    {
        return [
            'assertValidMonth month 0' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->assertValidMonth(1403, 0)],
            'daysInMonth month 0' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->daysInMonth(1403, 0)],
            'daysInMonth month 13' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->daysInMonth(1403, 13)],
            'assertValidDay invalid day' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->assertValidDay(1402, 12, 30)],
            'toDayOfYear invalid month' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->toDayOfYear(1403, 0, 1)],
            'toDayOfYear invalid day' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->toDayOfYear(1402, 12, 30)],
            'toMonthDay day 0' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->toMonthDay(1403, 0)],
            'toMonthDay day 366 non-leap' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->toMonthDay(1402, 366)],
            'toJDN invalid day' => [static fn (JalaliCalendar $calendarSystem) => $calendarSystem->toJDN(1402, 12, 30)],
        ];
    }

    /**
     * Tests rejects j d n outside supported range.
     */
    public function testRejectsJDNOutsideSupportedRange(): void
    {
        $this->expectException(DateOutOfRangeException::class);

        JalaliCalendar::instance()->toYearMonthDay(1);
    }
}
