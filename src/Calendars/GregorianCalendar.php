<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Calendars;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Support\EuclideanDivision;
use Kampute\CivilDate\Support\YearNumbering;

/**
 * Defines validation, conversion, and calendar-length behavior for the proleptic Gregorian calendar.
 */
class GregorianCalendar extends CalendarSystem
{
    /**
     * Number of days in each common Gregorian month, indexed by month number.
     *
     * @var array<int,int>
     */
    private const DAYS_IN_MONTH = [
        1 => 31,
        2 => 28, // Non-leap year value; leap years are handled separately.
        3 => 31,
        4 => 30,
        5 => 31,
        6 => 30,
        7 => 31,
        8 => 31,
        9 => 30,
        10 => 31,
        11 => 30,
        12 => 31,
    ];

    /**
     * Returns the Gregorian calendar identifier.
     *
     * @return Calendar Gregorian calendar identifier.
     *
     * @override
     */
    public function id(): Calendar
    {
        return Calendar::Gregorian;
    }

    /**
     * Determines whether a Gregorian year is leap.
     *
     * @param int $year Gregorian year.
     *
     * @return bool True when the year is leap, false otherwise.
     *
     * @throws InvalidArgumentException If the year is invalid.
     *
     * @override
     */
    public function isLeapYear(int $year): bool
    {
        $this->assertValidYear($year);

        $year = YearNumbering::toAstronomicalYear($year);
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }

    /**
     * Returns the number of days in a Gregorian month.
     *
     * @param int $year Gregorian year.
     * @param int $month Gregorian month number.
     *
     * @return int Number of days in the month.
     *
     * @throws InvalidArgumentException If the year or month is invalid.
     *
     * @override
     */
    public function daysInMonth(int $year, int $month): int
    {
        $this->assertValidMonth($year, $month);

        $days = self::DAYS_IN_MONTH[$month];
        if ($month === 2 && $this->isLeapYear($year)) {
            ++$days;
        }
        return $days;
    }

    /**
     * Returns the number of days in a Gregorian year.
     *
     * @param int $year Gregorian year.
     *
     * @return int Number of days in the year.
     *
     * @throws InvalidArgumentException If the year is invalid.
     *
     * @override
     */
    public function daysInYear(int $year): int
    {
        return $this->isLeapYear($year) ? 366 : 365;
    }

    /**
     * Finds the Gregorian year containing a Julian Day Number.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return int Gregorian year containing the Julian Day Number.
     *
     * @override
     */
    protected function findYear(int $jdn): int
    {
        $a = $jdn + 32044;
        $b = EuclideanDivision::quotient((4 * $a) + 3, 146097);
        $c = $a - EuclideanDivision::quotient(146097 * $b, 4);
        $d = EuclideanDivision::quotient((4 * $c) + 3, 1461);
        $e = $c - EuclideanDivision::quotient(1461 * $d, 4);
        $m = EuclideanDivision::quotient((5 * $e) + 2, 153);
        $astronomicalYear = (100 * $b) + $d - 4800 + EuclideanDivision::quotient($m, 10);

        return YearNumbering::toCalendarYear($astronomicalYear);
    }

    /**
     * Returns the Julian Day Number of the first day of a Gregorian year.
     *
     * @param int $year Gregorian year.
     *
     * @return int Julian Day Number of the year's first day.
     *
     * @override
     */
    protected function firstDayOfYearJDN(int $year): int
    {
        $yearsBefore = YearNumbering::toAstronomicalYear($year) - 1;

        return 1721426
            + (365 * $yearsBefore)
            + EuclideanDivision::quotient($yearsBefore, 4)
            - EuclideanDivision::quotient($yearsBefore, 100)
            + EuclideanDivision::quotient($yearsBefore, 400);
    }
}
