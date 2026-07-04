<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Tests\Calendars;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\IslamicCalendar;
use Kampute\CivilDate\Calendars\TabularIslamicCalendar;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Tests islamic calendar.
 */
final class IslamicCalendarTest extends TestCase
{
    /**
     * Tests calendar identity.
     */
    public function testCalendarIdentity(): void
    {
        $calendarSystem = IslamicCalendar::instance();

        self::assertInstanceOf(CalendarSystem::class, $calendarSystem);
        self::assertInstanceOf(TabularIslamicCalendar::class, $calendarSystem);
        self::assertSame($calendarSystem, IslamicCalendar::instance());
        self::assertSame(Calendar::Islamic, $calendarSystem->id());
        self::assertNull($calendarSystem->todayTimeZone());
    }

    /**
     * Tests defaults match tabular islamic behavior before authoritative configuration.
     */
    public function testDefaultsMatchTabularIslamicBehavior(): void
    {
        $calendarSystem = IslamicCalendar::instance();
        $tabularCalendarSystem = TabularIslamicCalendar::instance();

        self::assertSame($tabularCalendarSystem->daysInMonth(1446, 2), $calendarSystem->daysInMonth(1446, 2));
        self::assertSame($tabularCalendarSystem->daysInYear(1446), $calendarSystem->daysInYear(1446));
        self::assertSame($tabularCalendarSystem->isLeapYear(2), $calendarSystem->isLeapYear(2));
        self::assertSame($tabularCalendarSystem->toJDN(1446, 3, 1), $calendarSystem->toJDN(1446, 3, 1));
        self::assertSame($tabularCalendarSystem->toYearMonthDay(2460736), $calendarSystem->toYearMonthDay(2460736));
    }

    /**
     * Tests authoritative month lengths shift following months cumulatively.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAuthoritativeMonthLengthsShiftFollowingMonthsCumulatively(): void
    {
        $calendarSystem = IslamicCalendar::instance();
        $calendarSystem->setAuthoritativeMonthLengths([
            1446 => [2 => 30],
        ]);

        self::assertSame(30, $calendarSystem->daysInMonth(1446, 2));
        self::assertSame(355, $calendarSystem->daysInYear(1446));
        self::assertTrue($calendarSystem->isLeapYear(1446));
        self::assertSame(2460560, $calendarSystem->toJDN(1446, 3, 1));
        self::assertSame([1446, 2, 30], $calendarSystem->toYearMonthDay(2460559));
        self::assertSame(2460855, $calendarSystem->toJDN(1447, 1, 1));
    }

    /**
     * Tests compensating authoritative month lengths restore tabular boundary.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCompensatingAuthoritativeMonthLengthsRestoreTabularBoundary(): void
    {
        $calendarSystem = IslamicCalendar::instance();
        $calendarSystem->setAuthoritativeMonthLengths([
            1446 => [2 => 30, 3 => 29],
        ]);

        self::assertSame(2460560, $calendarSystem->toJDN(1446, 3, 1));
        self::assertSame(2460589, $calendarSystem->toJDN(1446, 4, 1));
        self::assertSame(2460854, $calendarSystem->toJDN(1447, 1, 1));
    }

    /**
     * Tests explicit tabular lengths are harmless.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testExplicitTabularLengthsAreHarmless(): void
    {
        $calendarSystem = IslamicCalendar::instance();
        $calendarSystem->setAuthoritativeMonthLengths([
            1446 => [1 => 30, 2 => 29],
        ]);

        self::assertSame(2460559, $calendarSystem->toJDN(1446, 3, 1));
    }

    /**
     * Tests rejects invalid authoritative month-length definitions.
     *
     * @param array<int,array<int,int>> $definitions Authoritative month-length definitions.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[DataProvider('invalidAuthoritativeMonthLengthProvider')]
    public function testRejectsInvalidAuthoritativeMonthLengths(array $definitions): void
    {
        $this->expectException(InvalidArgumentException::class);

        IslamicCalendar::instance()->setAuthoritativeMonthLengths($definitions);
    }

    /**
     * Provides data for invalid authoritative month-length tests.
     *
     * @return array<array{array<int,array<int,int>>}> Provider data sets.
     */
    public static function invalidAuthoritativeMonthLengthProvider(): array
    {
        return [
            'year zero' => [[0 => [1 => 29]]],
            'month zero' => [[1446 => [0 => 29]]],
            'invalid month length' => [[1446 => [1 => 28]]],
            'invalid year length' => [[1446 => [1 => 29]]],
        ];
    }

    /**
     * Tests authoritative configuration locks on first calendrical use.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAuthoritativeConfigurationLocksOnFirstCalendricalUse(): void
    {
        $calendarSystem = IslamicCalendar::instance();
        $calendarSystem->setAuthoritativeMonthLengths([
            1446 => [2 => 30],
        ]);

        self::assertSame(355, $calendarSystem->daysInYear(1446));

        $this->expectException(LogicException::class);
        $calendarSystem->setAuthoritativeMonthLengths([]);
    }
}
