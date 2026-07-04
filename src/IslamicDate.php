<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use Kampute\CivilDate\Calendars\IslamicCalendar;

/**
 * Represents an immutable date in the Islamic civil calendar.
 *
 * @property-read int $year Islamic year number.
 * @property-read int $month Islamic month number.
 * @property-read int $day Islamic day of month.
 * @property-read int $jdn Julian Day Number of this date.
 * @property-read int $dayOfWeekInYear Occurrence of this date's day of week within the year.
 * @property-read int $dayOfWeekInMonth Occurrence of this date's day of week within the month.
 * @property-read int $dayOfYear Day of the Islamic year, with Muharram 1 as day 1.
 * @property-read DayOfWeek $dayOfWeek Day of the week of this date.
 * @property-read bool $isLeapYear True when this date falls in a 355-day Islamic year.
 * @property-read int $monthsInYear Number of months in the year.
 * @property-read int $daysInMonth Number of days in this date's effective Islamic month.
 * @property-read int $daysInYear Number of days in this date's effective Islamic year.
 * @property-read int $quarter Quarter of the Islamic year.
 * @property-read Season $season Iran/northern-hemisphere season for this date.
 */
class IslamicDate extends CalendarDate
{
    /**
     * Returns the Islamic calendar system.
     *
     * @return IslamicCalendar Islamic calendar system.
     *
     * @override
     */
    public static function calendarSystem(): IslamicCalendar
    {
        return IslamicCalendar::instance();
    }
}
