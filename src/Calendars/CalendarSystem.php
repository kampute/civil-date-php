<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Calendars;

use DateTimeZone;
use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateOutOfRangeException;

/**
 * Defines validation, conversion, and calendar-length behavior for a calendar system.
 *
 * @see Calendar::system()
 * @see \Kampute\CivilDate\CalendarDate::calendarSystem()
 */
abstract class CalendarSystem
{
    /**
     * Shared calendar system instances indexed by class name.
     *
     * @var array<class-string,self>
     */
    private static array $instances = [];

    /**
     * Disallows direct construction.
     */
    final private function __construct()
    {
    }

    /**
     * Returns the shared instance for the concrete calendar system.
     *
     * @return static Calendar system instance.
     *
     * @see Calendar::system()
     */
    final public static function instance(): static
    {
        /** @var static */
        return self::$instances[static::class] ??= new static();
    }

    /**
     * Returns the calendar identifier represented by this system.
     *
     * @return Calendar Calendar identifier.
     *
     * @see Calendar::system()
     */
    abstract public function id(): Calendar;

    /**
     * Validates a calendar year.
     *
     * @param int $year Calendar year.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the year is invalid.
     */
    public function assertValidYear(int $year): void
    {
        if (!$this->isValidYear($year)) {
            throw new InvalidArgumentException($this->id()->name . " year {$year} is not valid.");
        }
    }

    /**
     * Validates a calendar month.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the year or month is invalid.
     */
    final public function assertValidMonth(int $year, int $month): void
    {
        $this->assertValidYear($year);

        if (!$this->isValidMonth($year, $month)) {
            throw new InvalidArgumentException("Month {$month} is out of valid range: 1..{$this->monthsInYear($year)}.");
        }
    }

    /**
     * Validates a calendar day.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $day Calendar day of month.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the date components are invalid.
     */
    final public function assertValidDay(int $year, int $month, int $day): void
    {
        $this->assertValidMonth($year, $month);

        if (!$this->isValidDay($year, $month, $day)) {
            throw new InvalidArgumentException("Day {$day} is out of valid range: 1..{$this->daysInMonth($year, $month)}.");
        }
    }

    /**
     * Determines whether a year number is valid for this calendar.
     *
     * @param int $year Calendar year.
     *
     * @return bool True when the year is valid, false otherwise.
     */
    public function isValidYear(int $year): bool
    {
        return $year !== 0;
    }

    /**
     * Determines whether a month number is valid for a calendar year.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     *
     * @return bool True when the month is valid, false otherwise.
     */
    public function isValidMonth(int $year, int $month): bool
    {
        return $this->isValidYear($year)
            && $month >= 1
            && $month <= $this->monthsInYear($year);
    }

    /**
     * Determines whether a day number is valid for a calendar month.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $day Calendar day of month.
     *
     * @return bool True when the day is valid, false otherwise.
     */
    public function isValidDay(int $year, int $month, int $day): bool
    {
        return $this->isValidMonth($year, $month)
            && $day >= 1
            && $day <= $this->daysInMonth($year, $month);
    }

    /**
     * Returns whether a year is leap according to this calendar.
     *
     * @param int $year Calendar year.
     *
     * @return bool True when the year is leap.
     *
     * @throws InvalidArgumentException If the year is invalid.
     */
    abstract public function isLeapYear(int $year): bool;

    /**
     * Returns the number of months in a year.
     *
     * @param int $year Calendar year.
     *
     * @return int Number of months in the year.
     *
     * @throws InvalidArgumentException If the year is invalid.
     */
    public function monthsInYear(int $year): int
    {
        $this->assertValidYear($year);

        return 12;
    }

    /**
     * Returns the number of days in a month.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     *
     * @return int Number of days in the month.
     *
     * @throws InvalidArgumentException If the year or month is invalid.
     */
    abstract public function daysInMonth(int $year, int $month): int;

    /**
     * Returns the number of days in a year.
     *
     * @param int $year Calendar year.
     *
     * @return int Number of days in the year.
     *
     * @throws InvalidArgumentException If the year is invalid.
     */
    abstract public function daysInYear(int $year): int;

    /**
     * Converts calendar date components to a day of year value.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $day Calendar day of month.
     *
     * @return int Day of year, where 1 is the first day of the year.
     *
     * @throws InvalidArgumentException If the date components are invalid.
     */
    public function toDayOfYear(int $year, int $month, int $day): int
    {
        $this->assertValidDay($year, $month, $day);

        $dayOfYear = $day;
        for ($m = 1; $m < $month; ++$m) {
            $dayOfYear += $this->daysInMonth($year, $m);
        }

        return $dayOfYear;
    }

    /**
     * Converts a day of year value to calendar month and day components.
     *
     * @param int $year Calendar year.
     * @param int $dayOfYear Day of year, where 1 is the first day of the year.
     *
     * @return array{0:int,1:int} Calendar [month, day] components.
     *
     * @throws InvalidArgumentException If the year or day of year is invalid.
     */
    public function toMonthDay(int $year, int $dayOfYear): array
    {
        $this->assertValidYear($year);

        if ($dayOfYear <= 0) {
            throw new InvalidArgumentException('Day of year cannot be zero or negative.');
        }

        $monthsInYear = $this->monthsInYear($year);
        $day = $dayOfYear;

        for ($month = 1; $month <= $monthsInYear; ++$month) {
            $daysInMonth = $this->daysInMonth($year, $month);
            if ($day <= $daysInMonth) {
                return [$month, $day];
            }
            $day -= $daysInMonth;
        }

        throw new InvalidArgumentException("Day of year value {$dayOfYear} is out of range for year {$year}, which has {$this->daysInYear($year)} days.");
    }

    /**
     * Converts a Julian Day Number to calendar year, month, and day components.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return array{0:int,1:int,2:int} Calendar [year, month, day] components.
     *
     * @throws DateOutOfRangeException If the Julian Day Number cannot be converted to a valid calendar date.
     *
     * @see CalendarSystem::toJDN()
     * @see \Kampute\CivilDate\CalendarDate::fromJDN()
     */
    public function toYearMonthDay(int $jdn): array
    {
        $year = $this->findYear($jdn);
        if ($year === 0) {
            throw new DateOutOfRangeException("Cannot determine calendar year for Julian Day Number {$jdn}.");
        }

        $dayOfYear = $jdn - $this->firstDayOfYearJDN($year) + 1;
        [$month, $day] = $this->toMonthDay($year, $dayOfYear);

        return [$year, $month, $day];
    }

    /**
     * Converts calendar date components to a Julian Day Number.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $day Calendar day of month.
     *
     * @return int Julian Day Number.
     *
     * @throws InvalidArgumentException If the date components are invalid.
     *
     * @see CalendarSystem::toYearMonthDay()
     * @see \Kampute\CivilDate\CalendarDate::jdn()
     */
    public function toJDN(int $year, int $month, int $day): int
    {
        $this->assertValidDay($year, $month, $day);

        return $this->firstDayOfYearJDN($year) + $this->toDayOfYear($year, $month, $day) - 1;
    }

    /**
     * Returns the timezone used to resolve today's date.
     *
     * @return DateTimeZone|null Timezone for today, or null for the default timezone.
     */
    public function todayTimeZone(): ?DateTimeZone
    {
        return null;
    }

    /**
     * Finds the calendar year corresponding to a Julian Day Number.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return int Calendar year corresponding to the Julian Day Number, or zero if the year cannot be determined.
     */
    abstract protected function findYear(int $jdn): int;

    /**
     * Returns the Julian Day Number of the first day of a calendar year.
     *
     * @param int $year Calendar year.
     *
     * @return int Julian Day Number of the first day of the year.
     */
    abstract protected function firstDayOfYearJDN(int $year): int;
}
