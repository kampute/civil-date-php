<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use Kampute\CivilDate\Calendars\GregorianCalendar;

/**
 * Represents an immutable date in the proleptic Gregorian calendar system.
 *
 * @property-read int $year Gregorian year number.
 * @property-read int $month Gregorian month number.
 * @property-read int $day Gregorian day of month.
 * @property-read int $jdn Julian Day Number of this date.
 * @property-read int $dayOfWeekInYear Occurrence of this date's day of week within the year.
 * @property-read int $dayOfWeekInMonth Occurrence of this date's day of week within the month.
 * @property-read int $dayOfYear Day of the year of this date, with January 1 as day 1.
 * @property-read DayOfWeek $dayOfWeek Day of the week of this date.
 * @property-read bool $isLeapYear True if this date falls in a leap year, false otherwise.
 * @property-read int $monthsInYear Number of months in the year of this date.
 * @property-read int $daysInMonth Number of days in the month of this date.
 * @property-read int $daysInYear Number of days in the year of this date.
 * @property-read int $quarter Quarter of the year for this date.
 * @property-read Season $season Iran/northern-hemisphere season for this date.
 */
class GregorianDate extends CalendarDate
{
    /**
     * Returns the Gregorian calendar system.
     *
     * @return GregorianCalendar Gregorian calendar system.
     *
     * @override
     */
    public static function calendarSystem(): GregorianCalendar
    {
        return GregorianCalendar::instance();
    }
}
