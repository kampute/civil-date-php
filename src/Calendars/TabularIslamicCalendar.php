<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Calendars;

use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\Support\EuclideanDivision;
use Kampute\CivilDate\Support\YearNumbering;

/**
 * Defines validation, conversion, and calendar-length behavior for the tabular Islamic civil calendar.
 */
class TabularIslamicCalendar extends CalendarSystem
{
    /**
     * Julian Day Number of 1 Muharram 1 AH in the tabular Islamic calendar.
     *
     * @var int
     */
    protected const EPOCH_JDN = 1948440;

    /**
     * Returns the Islamic calendar identifier.
     *
     * @return Calendar Islamic calendar identifier.
     *
     * @override
     */
    final public function id(): Calendar
    {
        return Calendar::Islamic;
    }

    /**
     * Determines whether an Islamic year contains 355 days.
     *
     * @param int $year Islamic year.
     *
     * @return bool True when the year contains 355 days, false when it contains 354 days.
     *
     * @throws InvalidArgumentException If the year is zero.
     *
     * @override
     */
    public function isLeapYear(int $year): bool
    {
        $this->assertValidYear($year);

        return self::isLeapYearInCycle($year);
    }

    /**
     * Returns the number of days in a tabular Islamic month.
     *
     * @param int $year Islamic year.
     * @param int $month Islamic month number.
     *
     * @return int Tabular month length.
     *
     * @throws InvalidArgumentException If the year or month is invalid.
     *
     * @override
     */
    public function daysInMonth(int $year, int $month): int
    {
        $this->assertValidMonth($year, $month);

        if ($month !== 12) {
            return 29 + ($month % 2);
        }

        return self::isLeapYearInCycle($year) ? 30 : 29;
    }

    /**
     * Returns the number of days in a tabular Islamic year.
     *
     * @param int $year Islamic year.
     *
     * @return int Tabular year length.
     *
     * @throws InvalidArgumentException If the year is zero.
     *
     * @override
     */
    public function daysInYear(int $year): int
    {
        $this->assertValidYear($year);

        return self::isLeapYearInCycle($year) ? 355 : 354;
    }

    /**
     * Finds the Islamic year containing a Julian Day Number.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return int Islamic year containing the Julian Day Number.
     *
     * @override
     */
    protected function findYear(int $jdn): int
    {
        $astronomicalYear = EuclideanDivision::quotient((30 * ($jdn - self::EPOCH_JDN)) + 10646, 10631);

        while ($this->firstDayOfYearJDN(YearNumbering::toCalendarYear($astronomicalYear)) > $jdn) {
            --$astronomicalYear;
        }

        while ($this->firstDayOfYearJDN(YearNumbering::toCalendarYear($astronomicalYear + 1)) <= $jdn) {
            ++$astronomicalYear;
        }

        return YearNumbering::toCalendarYear($astronomicalYear);
    }

    /**
     * Returns the Julian Day Number of the first day of an Islamic year.
     *
     * @param int $year Islamic year.
     *
     * @return int Julian Day Number of the year's first day.
     *
     * @override
     */
    protected function firstDayOfYearJDN(int $year): int
    {
        $astronomicalYear = YearNumbering::toAstronomicalYear($year);
        return self::EPOCH_JDN
            + (354 * ($astronomicalYear - 1))
            + EuclideanDivision::quotient((11 * $astronomicalYear) + 3, 30);
    }

    /**
     * Determines whether an Islamic year is leap in the tabular 30-year cycle.
     *
     * @param int $year Islamic year.
     *
     * @return bool True when the year is leap.
     */
    private static function isLeapYearInCycle(int $year): bool
    {
        $astronomicalYear = YearNumbering::toAstronomicalYear($year);
        return EuclideanDivision::remainder((11 * $astronomicalYear) + 14, 30) < 11;
    }
}
