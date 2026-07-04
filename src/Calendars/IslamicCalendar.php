<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Calendars;

use InvalidArgumentException;
use LogicException;

/**
 * Defines validation, conversion, and calendar-length behavior for the Islamic civil calendar.
 *
 * The calendar uses tabular Islamic month lengths by default. Authoritative month lengths can
 * be configured before calendrical use, and then become part of the calendar's effective month
 * and year lengths.
 */
class IslamicCalendar extends TabularIslamicCalendar
{
    /**
     * Whether calendrical use has locked authoritative month lengths.
     *
     * @var bool
     */
    private bool $locked = false;

    /**
     * Authoritative month-length adjustments indexed by Islamic year and month.
     *
     * @var array<int,array<int,int>>
     */
    private array $monthAdjustments = [];

    /**
     * Authoritative year-length adjustments indexed by Islamic year.
     *
     * @var array<int,int>
     */
    private array $yearAdjustments = [];

    /**
     * Cache of the first day of Islamic years in Julian Day Numbers.
     *
     * @var array<int,int>
     */
    private array $firstDayOfYearCache = [];

    /**
     * Configures authoritative lengths for selected Islamic months.
     *
     * Supplied month lengths override the tabular length for those months.
     * Months without an override continue to use the tabular calendar. Each
     * supplied month must have 29 or 30 days, and every affected year must have
     * 354 or 355 days.
     *
     * Once the calendar has been used for any calendrical operation, authoritative
     * month lengths can no longer be changed.
     *
     * Example:
     *
     * ```php
     * IslamicCalendar::instance()->setAuthoritativeMonthLengths([
     *     1446 => [
     *         9 => 29,  // Ramadan 1446 has 29 days instead of 30
     *         10 => 30, // Shawwal 1446 has 30 days instead of 29
     *     ],
     * ]);
     * ```
     *
     * @param array<int,array<int,int>> $monthLengths Authoritative month lengths indexed by Islamic year and month.
     *                                  Each supplied month must contain either 29 or 30 days, and each affected
     *                                  year must contain either 354 or 355 days after applying the overrides.
     *
     * @return void
     *
     * @throws InvalidArgumentException If a definition contains an invalid year, month, length, or effective year length.
     * @throws LogicException If authoritative month lengths are no longer changeable due to prior calendrical use.
     */
    public function setAuthoritativeMonthLengths(array $monthLengths): void
    {
        if ($this->locked) {
            throw new LogicException('Authoritative Islamic month lengths cannot be changed after the calendar has been used.');
        }

        $yearAdjustments = [];
        $monthAdjustments = [];

        foreach ($monthLengths as $year => $months) {
            if ($year === 0) {
                throw new InvalidArgumentException('Authoritative Islamic years must be nonzero integers.');
            }

            $yearAdjustment = 0;
            foreach ($months as $month => $length) {
                if ($month < 1 || $month > 12) {
                    throw new InvalidArgumentException("Authoritative Islamic month {$month} of year {$year} is out of valid range: 1..12.");
                }
                if ($length !== 29 && $length !== 30) {
                    throw new InvalidArgumentException("Islamic month {$month} of year {$year} must contain either 29 or 30 days.");
                }

                $monthAdjustment = $length - parent::daysInMonth($year, $month);
                if ($monthAdjustment === 0) {
                    continue;
                }

                $monthAdjustments[$year][$month] = $monthAdjustment;
                $yearAdjustment += $monthAdjustment;
            }

            if ($yearAdjustment === 0) {
                continue;
            }

            $yearLength = parent::daysInYear($year) + $yearAdjustment;
            if ($yearLength !== 354 && $yearLength !== 355) {
                throw new InvalidArgumentException("Islamic year {$year} must contain either 354 or 355 days after applying authoritative month lengths; {$yearLength} given.");
            }

            $yearAdjustments[$year] = $yearAdjustment;
        }

        ksort($yearAdjustments);

        $this->yearAdjustments = $yearAdjustments;
        $this->monthAdjustments = $monthAdjustments;
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
        return $this->daysInYear($year) === 355;
    }

    /**
     * Returns the effective number of days in an Islamic month.
     *
     * @param int $year Islamic year.
     * @param int $month Islamic month number.
     *
     * @return int Effective month length.
     *
     * @throws InvalidArgumentException If the year or month is invalid.
     *
     * @override
     */
    public function daysInMonth(int $year, int $month): int
    {
        return parent::daysInMonth($year, $month) + $this->monthAdjustment($year, $month);
    }

    /**
     * Returns the effective number of days in an Islamic year.
     *
     * @param int $year Islamic year.
     *
     * @return int Effective year length.
     *
     * @throws InvalidArgumentException If the year is zero.
     *
     * @override
     */
    public function daysInYear(int $year): int
    {
        return parent::daysInYear($year) + $this->yearAdjustment($year);
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
        return $this->firstDayOfYearCache[$year] ??= parent::firstDayOfYearJDN($year) + $this->adjustmentBeforeYear($year);
    }

    /**
     * Returns the cumulative day difference before an Islamic year.
     *
     * @param int $year Target Islamic year.
     *
     * @return int Cumulative day difference before the year due to authoritative month lengths.
     */
    private function adjustmentBeforeYear(int $year): int
    {
        $this->lockAuthoritativeConfiguration();

        $adjustmentsBefore = 0;
        foreach ($this->yearAdjustments as $Year => $yearAdjustment) {
            if ($Year >= $year) {
                break;
            }

            $adjustmentsBefore += $yearAdjustment;
        }
        return $adjustmentsBefore;
    }

    /**
     * Returns the day difference for a configured Islamic month.
     *
     * @param int $year Islamic year.
     * @param int $month Islamic month number.
     *
     * @return int Day difference for the month, or 0 when no override is defined.
     */
    private function monthAdjustment(int $year, int $month): int
    {
        $this->lockAuthoritativeConfiguration();
        return $this->monthAdjustments[$year][$month] ?? 0;
    }

    /**
     * Returns the day difference for a configured Islamic year.
     *
     * @param int $year Islamic year.
     *
     * @return int Day difference for the year, or 0 when no override is defined.
     */
    private function yearAdjustment(int $year): int
    {
        $this->lockAuthoritativeConfiguration();
        return $this->yearAdjustments[$year] ?? 0;
    }

    /**
     * Marks the authoritative month-length configuration as no longer changeable.
     *
     * @return void
     */
    private function lockAuthoritativeConfiguration(): void
    {
        $this->locked = true;
    }
}
