<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use Kampute\CivilDate\Calendars\JalaliCalendar;

/**
 * Represents an immutable date in the Jalali calendar system.
 *
 * @property-read int $year Jalali year number.
 * @property-read int $month Jalali month number.
 * @property-read int $day Jalali day of month.
 * @property-read int $jdn Julian Day Number of this date.
 * @property-read int $dayOfWeekInYear Occurrence of this date's day of week within the year.
 * @property-read int $dayOfWeekInMonth Occurrence of this date's day of week within the month.
 * @property-read int $dayOfYear Day of the year of this date, with Nowruz as day 1.
 * @property-read DayOfWeek $dayOfWeek Day of the week of this date.
 * @property-read bool $isLeapYear True if this date falls in a leap year, false otherwise.
 * @property-read int $monthsInYear Number of months in the year.
 * @property-read int $daysInMonth Number of days in the month of this date.
 * @property-read int $daysInYear Number of days in the year of this date.
 * @property-read int $quarter Jalali quarter of this date.
 * @property-read Season $season Iran/northern-hemisphere season for this date.
 */
class JalaliDate extends CalendarDate
{
    /**
     * Minimum supported Jalali year.
     *
     * @var int
     */
    public const MIN_YEAR = JalaliCalendar::MIN_YEAR;

    /**
     * Maximum supported Jalali year.
     *
     * @var int
     */
    public const MAX_YEAR = JalaliCalendar::MAX_YEAR;

    /**
     * Returns the Jalali calendar system.
     *
     * @return JalaliCalendar Jalali calendar system.
     *
     * @override
     */
    public static function calendarSystem(): JalaliCalendar
    {
        return JalaliCalendar::instance();
    }

    /**
     * Returns this Jalali date's Iran/northern-hemisphere season.
     *
     * @return Season Season enum value.
     *
     * @override
     */
    final public function season(): Season
    {
        return Season::from($this->quarter());
    }
}
