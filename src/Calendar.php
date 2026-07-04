<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\GregorianCalendar;
use Kampute\CivilDate\Calendars\IslamicCalendar;
use Kampute\CivilDate\Calendars\JalaliCalendar;

/**
 * Identifies a supported calendar.
 *
 * Use this enum when an API needs to select a calendar without naming a date
 * class directly.
 *
 * @see CalendarDate::calendar()
 */
enum Calendar: int
{
    /**
     * Jalali calendar.
     */
    case Jalali = 1;

    /**
     * Gregorian calendar.
     */
    case Gregorian = 2;

    /**
     * Islamic civil calendar.
     */
    case Islamic = 3;

    /**
     * Returns the date class for this calendar.
     *
     * @return class-string<CalendarDate> Calendar-date class for the calendar.
     *
     * @see CalendarDate::calendar()
     */
    public function dateClass(): string
    {
        return match ($this) {
            self::Jalali => JalaliDate::class,
            self::Gregorian => GregorianDate::class,
            self::Islamic => IslamicDate::class,
        };
    }

    /**
     * Returns the calendar system for this calendar.
     *
     * @return CalendarSystem Calendar system for the calendar.
     *
     * @see CalendarDate::calendarSystem()
     * @see CalendarSystem::id()
     */
    public function system(): CalendarSystem
    {
        return match ($this) {
            self::Jalali => JalaliCalendar::instance(),
            self::Gregorian => GregorianCalendar::instance(),
            self::Islamic => IslamicCalendar::instance(),
        };
    }
}
