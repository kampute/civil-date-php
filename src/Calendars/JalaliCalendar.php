<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Calendars;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateOutOfRangeException;
use Kampute\CivilDate\Support\VernalEquinoxCalculator;
use Kampute\CivilDate\Support\YearNumbering;

/**
 * Defines validation, conversion, and calendar-length behavior for the Jalali calendar.
 */
class JalaliCalendar extends CalendarSystem
{
    /**
     * Minimum supported Jalali year.
     *
     * @var int
     */
    public const MIN_YEAR = VernalEquinoxCalculator::MIN_SUPPORTED_YEAR - self::JALALI_TO_GREGORIAN_OFFSET;

    /**
     * Maximum supported Jalali year with a known following Nowruz.
     *
     * @var int
     */
    public const MAX_YEAR = VernalEquinoxCalculator::MAX_SUPPORTED_YEAR - self::JALALI_TO_GREGORIAN_OFFSET - 1;

    /**
     * Julian Day of the Unix epoch at midnight UTC.
     *
     * @var float
     */
    private const UNIX_EPOCH_JD = 2440587.5;

    /**
     * Number of seconds in one civil day.
     *
     * @var int
     */
    private const SECONDS_PER_DAY = 86400;

    /**
     * Fixed Iran Standard Time offset in seconds.
     *
     * @var int
     */
    private const IRAN_OFFSET_SECONDS = 12600;

    /**
     * Mean length of a Jalali year in days.
     *
     * @var float
     */
    private const MEAN_JALALI_YEAR_DAYS = 365.24219858156;

    /**
     * Offset between positive Jalali years and their corresponding Gregorian years.
     *
     * @var int
     */
    private const JALALI_TO_GREGORIAN_OFFSET = 621;

    /**
     * Cache of the first day of Jalali years in Julian Day Numbers.
     *
     * @var array<int,int>
     */
    private array $firstDayOfYearCache = [];

    /**
     * Returns the Jalali calendar identifier.
     *
     * @return Calendar Jalali calendar identifier.
     *
     * @override
     */
    public function id(): Calendar
    {
        return Calendar::Jalali;
    }

    /**
     * Validates a Jalali year.
     *
     * @param int $year Jalali year.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the year is zero.
     * @throws DateOutOfRangeException If the year is outside the supported range.
     *
     * @override
     */
    public function assertValidYear(int $year): void
    {
        if ($year === 0) {
            throw new InvalidArgumentException('Jalali year zero is not defined.');
        }

        if (!$this->isValidYear($year)) {
            throw new DateOutOfRangeException("Jalali year {$year} is out of supported range (" . self::MIN_YEAR . '..-1 and 1..' . self::MAX_YEAR . ').');
        }
    }

    /**
     * Determines whether a Jalali year is valid and within the supported astronomical range.
     *
     * @param int $year Jalali year.
     *
     * @return bool True when the year is valid and supported, false otherwise.
     *
     * @override
     */
    public function isValidYear(int $year): bool
    {
        return $year !== 0 && $year >= self::MIN_YEAR && $year <= self::MAX_YEAR;
    }

    /**
     * Determines whether a Jalali year is leap.
     *
     * @param int $year Jalali year.
     *
     * @return bool True when the year is leap, false otherwise.
     *
     * @throws InvalidArgumentException If the year is zero.
     * @throws DateOutOfRangeException If the year is outside the supported range.
     *
     * @override
     */
    public function isLeapYear(int $year): bool
    {
        return $this->daysInYear($year) === 366;
    }

    /**
     * Returns the number of days in a Jalali month.
     *
     * @param int $year Jalali year.
     * @param int $month Jalali month number.
     *
     * @return int Number of days in the month.
     *
     * @throws InvalidArgumentException If the year is zero or the month is invalid.
     * @throws DateOutOfRangeException If the year is outside the supported range.
     *
     * @override
     */
    public function daysInMonth(int $year, int $month): int
    {
        $this->assertValidMonth($year, $month);

        if ($month <= 6) {
            return 31;
        }
        if ($month <= 11) {
            return 30;
        }
        return $this->isLeapYear($year) ? 30 : 29;
    }

    /**
     * Returns the number of days in a Jalali year.
     *
     * @param int $year Jalali year.
     *
     * @return int Number of days in the year.
     *
     * @throws InvalidArgumentException If the year is zero.
     * @throws DateOutOfRangeException If the year is outside the supported range.
     *
     * @override
     */
    public function daysInYear(int $year): int
    {
        $this->assertValidYear($year);

        $nextYear = YearNumbering::offsetYear($year, 1);
        return self::nowruzJDN($nextYear) - self::nowruzJDN($year);
    }

    /**
     * Converts Jalali date components to a day of year value.
     *
     * @param int $year Jalali year.
     * @param int $month Jalali month number.
     * @param int $day Jalali day of month.
     *
     * @return int Day of year, where 1 is the first day of the year.
     *
     * @throws InvalidArgumentException If the year is zero or date components are invalid.
     * @throws DateOutOfRangeException If the year is outside the supported range.
     *
     * @override
     */
    public function toDayOfYear(int $year, int $month, int $day): int
    {
        $this->assertValidDay($year, $month, $day);

        return ($month <= 7 ? ($month - 1) * 31 : 186 + (($month - 7) * 30)) + $day;
    }

    /**
     * Converts a day of year value to Jalali month and day components.
     *
     * @param int $year Jalali year.
     * @param int $dayOfYear Day of year, where 1 is the first day of the year.
     *
     * @return array{0:int,1:int} Jalali [month, day] components.
     *
     * @throws InvalidArgumentException If the year or day of year is invalid.
     *
     * @override
     */
    public function toMonthDay(int $year, int $dayOfYear): array
    {
        $this->assertValidYear($year);

        if ($dayOfYear <= 0) {
            throw new InvalidArgumentException('Day of year cannot be zero or negative.');
        }
        if ($dayOfYear > $this->daysInYear($year)) {
            throw new InvalidArgumentException("Day of year value {$dayOfYear} is out of range for year {$year}, which has {$this->daysInYear($year)} days.");
        }

        if ($dayOfYear <= 187) {
            $m = intdiv($dayOfYear - 1, 31);
            $day = $dayOfYear - ($m * 31);
            return [$m + 1, $day];
        }

        $m = intdiv($dayOfYear - 187, 30);
        $day = $dayOfYear - 186 - ($m * 30);
        return [$m + 7, $day];
    }

    /**
     * Returns the fixed Iran Standard Time timezone used to resolve today's Jalali date.
     *
     * @return DateTimeZone Fixed Iran Standard Time timezone.
     *
     * @override
     */
    public function todayTimeZone(): DateTimeZone
    {
        return new DateTimeZone('+03:30');
    }

    /**
     * Returns the vernal equinox date and time in Iran Standard Time for a Jalali year.
     *
     * @param int $year Jalali year.
     *
     * @return DateTimeImmutable Equinox instant in Iran Standard Time (UTC+3:30).
     *
     * @throws InvalidArgumentException If the year is zero.
     * @throws DateOutOfRangeException If the year is outside the supported range.
     */
    public function vernalEquinox(int $year): DateTimeImmutable
    {
        $this->assertValidYear($year);

        $gregorianYear = YearNumbering::offsetYear($year, self::JALALI_TO_GREGORIAN_OFFSET);
        $jd = VernalEquinoxCalculator::julianDay($gregorianYear);
        $unixTime = (int) round(($jd - self::UNIX_EPOCH_JD) * self::SECONDS_PER_DAY);
        return (new DateTimeImmutable('@' . $unixTime))->setTimezone(new DateTimeZone('+03:30'));
    }

    /**
     * Finds the Jalali year containing a Julian Day Number.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return int Jalali year, or 0 when the JDN is outside the supported range.
     *
     * @override
     */
    protected function findYear(int $jdn): int
    {
        $minJDN = $this->firstDayOfYearJDN(self::MIN_YEAR);
        if ($jdn < $minJDN) {
            return 0;
        }

        $maxJDN = $this->firstDayOfYearJDN(self::MAX_YEAR + 1);
        if ($jdn >= $maxJDN) {
            return 0;
        }

        $astronomicalYear = YearNumbering::toAstronomicalYear(self::MIN_YEAR)
                          + (int) (($jdn - $minJDN) / self::MEAN_JALALI_YEAR_DAYS);

        while ($this->firstDayOfYearJDN(YearNumbering::toCalendarYear($astronomicalYear)) > $jdn) {
            --$astronomicalYear;
        }

        while ($this->firstDayOfYearJDN(YearNumbering::toCalendarYear($astronomicalYear + 1)) <= $jdn) {
            ++$astronomicalYear;
        }

        return YearNumbering::toCalendarYear($astronomicalYear);
    }

    /**
     * Returns the Julian Day Number of the first day of a Jalali year.
     *
     * @param int $year Jalali year.
     *
     * @return int Julian Day Number of the year's first day.
     *
     * @override
     */
    protected function firstDayOfYearJDN(int $year): int
    {
        return $this->firstDayOfYearCache[$year] ??= self::nowruzJDN($year);
    }

    /**
     * Calculates the Julian Day Number of Nowruz for a Jalali year.
     *
     * @param int $year Jalali year.
     *
     * @return int Julian Day Number of the year's first day.
     */
    private static function nowruzJDN(int $year): int
    {
        $gregorianYear = YearNumbering::offsetYear($year, self::JALALI_TO_GREGORIAN_OFFSET);
        $equinoxJD = VernalEquinoxCalculator::julianDay($gregorianYear);
        $equinoxTehranJD = $equinoxJD + (self::IRAN_OFFSET_SECONDS / self::SECONDS_PER_DAY);
        $midnightJD = $equinoxTehranJD + 0.5;
        $civilDay = (int) floor($midnightJD);
        $fractionOfDay = $midnightJD - $civilDay;
        $jdn = $fractionOfDay < 0.5 ? $civilDay : $civilDay + 1;
        return $jdn;
    }
}
