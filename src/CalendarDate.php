<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateOutOfRangeException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\DayOfWeek;
use Kampute\CivilDate\Season;
use Kampute\CivilDate\Calendars\CalendarSystem;
use Kampute\CivilDate\Calendars\GregorianCalendar;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Localization\LocaleRegistry;
use Kampute\CivilDate\Support\DatePattern\PatternCompiler;
use Kampute\CivilDate\Support\YearNumbering;

/**
 * Represents an immutable date in one supported calendar system.
 *
 * Dates can be created from calendar components, Gregorian input, Julian Day
 * Numbers, PHP date-time objects, or localized format patterns. Conversion and
 * comparison use the represented civil day, so dates in different calendars can
 * be compared directly.
 *
 * @property-read Calendar $calendar Calendar identifier.
 * @property-read CalendarSystem $calendarSystem Calendar system for this date.
 * @property-read int $year Calendar year number.
 * @property-read int $month Calendar month number.
 * @property-read int $day Calendar day of month.
 * @property-read int $jdn Julian Day Number of this date.
 * @property-read int $dayOfYear Day of the year of this date, with the first day of the year as day 1.
 * @property-read DayOfWeek $dayOfWeek Day of the week of this date.
 * @property-read int $dayOfWeekInYear Occurrence of this date's day of week within the year.
 * @property-read int $dayOfWeekInMonth Occurrence of this date's day of week within the month.
 * @property-read bool $isLeapYear Whether the year of this date is a leap year for its calendar.
 * @property-read int $monthsInYear Number of months in this date's year.
 * @property-read int $daysInMonth Number of days in this date's month.
 * @property-read int $daysInYear Number of days in this date's year.
 * @property-read int $quarter Quarter of the year for this date.
 * @property-read Season $season Iran/northern-hemisphere season for this date.
 *
 * @phpstan-consistent-constructor
 *
 * @see Calendar
 * @see CalendarSystem
 */
abstract class CalendarDate
{
    // ================================================================
    // Fields
    // ================================================================

    /**
     * Calendar date components.
     *
     * @var array{0:int,1:int,2:int}
     */
    private readonly array $components;

    /**
     * Cached Julian Day Number for this date.
     *
     * @var int|null
     */
    private ?int $jdn = null;

    /**
     * Date returned by `today()` when deterministic current dates are configured.
     *
     * @var self|null
     */
    private static ?self $testToday = null;

    // ================================================================
    // Constructor
    // ================================================================

    /**
     * Creates a date from calendar components.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $day Calendar day of month.
     *
     * @throws InvalidArgumentException When the components are not valid for the concrete calendar.
     */
    public function __construct(int $year, int $month, int $day)
    {
        static::calendarSystem()->assertValidDay($year, $month, $day);

        $this->components = [$year, $month, $day];
    }

    // ================================================================
    // Current date
    // ================================================================

    /**
     * Returns today's date in this calendar.
     *
     * @return static Current date, or the deterministic test date when set.
     *
     * @see CalendarDate::setTestToday()
     * @see CalendarDate::getTestToday()
     */
    final public static function today(): static
    {
        return static::getTestToday()
            ?? static::fromDateTime(new DateTimeImmutable('now', static::calendarSystem()->todayTimeZone()));
    }

    /**
     * Returns yesterday's date in this calendar.
     *
     * @return static Date one day before today.
     */
    final public static function yesterday(): static
    {
        return static::today()->addDays(-1);
    }

    /**
     * Returns tomorrow's date in this calendar.
     *
     * @return static Date one day after today.
     */
    final public static function tomorrow(): static
    {
        return static::today()->addDays(1);
    }

    // ================================================================
    // Factory methods
    // ================================================================

    /**
     * Creates a date from a Julian Day Number.
     *
     * The returned instance uses this class's calendar while representing the
     * same civil day as the supplied Julian Day Number.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return static Calendar date corresponding to the Julian Day Number.
     *
     * @throws DateOutOfRangeException When the Julian Day Number is outside the supported range for this calendar.
     *
     * @see CalendarDate::jdn()
     * @see CalendarSystem::toYearMonthDay()
     */
    final public static function fromJDN(int $jdn): static
    {
        [$year, $month, $day] = static::calendarSystem()->toYearMonthDay($jdn);

        $date = new static($year, $month, $day);
        $date->jdn = $jdn;

        return $date;
    }

    /**
     * Creates a date from a Gregorian calendar date.
     *
     * This is useful when external input is Gregorian but the application works
     * with another calendar.
     *
     * @param int $year Gregorian calendar year.
     * @param int $month Gregorian calendar month number.
     * @param int $day Gregorian calendar day of month.
     *
     * @return static Equivalent date in this calendar.
     *
     * @throws InvalidArgumentException If the Gregorian date is invalid.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     *
     * @see CalendarDate::toCalendar()
     * @see CalendarDate::toIso8601DateString()
     */
    final public static function fromGregorianDate(int $year, int $month, int $day): static
    {
        $jdn = GregorianCalendar::instance()->toJDN($year, $month, $day);
        return static::fromJDN($jdn);
    }

    /**
     * Creates a date from a Gregorian ISO 8601 date string.
     *
     * The accepted shape is `YYYY-MM-DD`, with a signed year allowed when the
     * year has more than four digits.
     *
     * @param string $isoDate Gregorian date in ISO 8601 `YYYY-MM-DD` form.
     *
     * @return static Equivalent date in this calendar.
     *
     * @throws DateParseException If the string does not match the accepted ISO shape.
     * @throws InvalidArgumentException If the Gregorian date is invalid.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range.
     *
     * @see CalendarDate::toIso8601DateString()
     * @see CalendarDate::fromGregorianDate()
     */
    final public static function fromIso8601DateString(string $isoDate): static
    {
        if (preg_match('/^(-?\d{4,})-(\d{2})-(\d{2})$/', $isoDate, $matches) !== 1) {
            throw new DateParseException("Invalid ISO date string: \"{$isoDate}\".");
        }

        return static::fromGregorianDate((int) $matches[1], (int) $matches[2], (int) $matches[3]);
    }

    /**
     * Creates a date from a DateTimeInterface value using its own timezone.
     *
     * Only the civil date part is used. The time of day is ignored after the
     * value has been interpreted in its own timezone.
     *
     * @param DateTimeInterface $dateTime Date and time whose civil date should be converted.
     *
     * @return static Equivalent date in this calendar.
     *
     * @throws DateOutOfRangeException If the resulting date is outside the supported range.
     *
     * @see CalendarDate::fromGregorianDate()
     */
    final public static function fromDateTime(DateTimeInterface $dateTime): static
    {
        [$year, $month, $day] = explode('/', $dateTime->format('Y/m/d'), 3);
        return static::fromGregorianDate((int) $year, (int) $month, (int) $day);
    }

    /**
     * Creates a date from a relative day of month.
     *
     * Positive values count from the first day of the month. Negative values
     * count from the last day, so `-1` selects the last day of the month.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $dayOfMonth The day of month, where positive values count from the start of the month and negative values count from the end of the month.
     *
     * @return static Calendar date represented by the day-of-month value.
     *
     * @throws InvalidArgumentException When the year or month is invalid.
     * @throws InvalidArgumentException When the day-of-month value is zero or out of range for the given month.
     */
    final public static function fromDayOfMonth(int $year, int $month, int $dayOfMonth): static
    {
        if ($dayOfMonth === 0) {
            throw new InvalidArgumentException('Day of month value cannot be zero.');
        }

        $daysInMonth = static::calendarSystem()->daysInMonth($year, $month);
        $dayOffset = $dayOfMonth > 0
            ? $dayOfMonth - 1
            : $daysInMonth + $dayOfMonth;

        if ($dayOffset < 0 || $dayOffset >= $daysInMonth) {
            throw new InvalidArgumentException("Day of month value {$dayOfMonth} is out of range for month {$month} of year {$year}, which has {$daysInMonth} days.");
        }

        return new static($year, $month, $dayOffset + 1);
    }

    /**
     * Creates a date from a relative day of year.
     *
     * Positive values count from the first day of the year. Negative values
     * count from the last day, so `-1` selects the last day of the year.
     *
     * @param int $year Calendar year.
     * @param int $dayOfYear The day of year, where positive values count from the start
     *                       of the year and negative values count from the end of the year.
     *
     * @return static Calendar date represented by the day-of-year value.
     *
     * @throws InvalidArgumentException When the year is invalid.
     * @throws InvalidArgumentException When the day-of-year value is zero or out of range for the given year.
     * @throws DateOutOfRangeException When the resulting date is outside the supported range for this calendar.
     */
    final public static function fromDayOfYear(int $year, int $dayOfYear): static
    {
        if ($dayOfYear === 0) {
            throw new InvalidArgumentException('Day of year value cannot be zero.');
        }

        $calendarSystem = static::calendarSystem();

        $daysInYear = $calendarSystem->daysInYear($year);
        $absoluteDayOfYear = $dayOfYear > 0
            ? $dayOfYear
            : $daysInYear + $dayOfYear + 1;

        if ($absoluteDayOfYear < 1 || $absoluteDayOfYear > $daysInYear) {
            throw new InvalidArgumentException("Day of year value {$dayOfYear} is out of range for year {$year}, which has {$daysInYear} days.");
        }

        [$month, $day] = $calendarSystem->toMonthDay($year, $absoluteDayOfYear);
        return new static($year, $month, $day);
    }

    /**
     * Creates a date from an occurrence of a day of week in a calendar year.
     *
     * Positive occurrences count from the start of the year. Negative
     * occurrences count from the end of the year.
     *
     * @param int $year Calendar year.
     * @param int $occurrence The occurrence number of the day of week, where positive values
     *                        count from the start of the year and negative values count from
     *                        the end of the year.
     * @param int|DayOfWeek $dayOfWeek Day of week to select.
     *
     * @return static Date for the requested day of week occurrence inside the requested year.
     *
     * @throws InvalidArgumentException If the year, occurrence, or day of week is invalid.
     * @throws InvalidArgumentException If the requested occurrence does not exist in the given year.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public static function fromNthDayOfWeekInYear(int $year, int $occurrence, int|DayOfWeek $dayOfWeek): static
    {
        if ($occurrence === 0) {
            throw new InvalidArgumentException('Occurrence value cannot be zero.');
        }

        $dayOfWeek = self::toDayOfWeek($dayOfWeek);

        if ($occurrence > 0) {
            $yearStart = static::fromDayOfYear($year, 1);
            $target = $yearStart->addDays($yearStart->dayOfWeek()->daysUntil($dayOfWeek, $occurrence));
        } else {
            $yearEnd = static::fromDayOfYear($year, -1);
            $target = $yearEnd->addDays(-$yearEnd->dayOfWeek()->daysSince($dayOfWeek, -$occurrence));
        }

        return $target->year() === $year
            ? $target
            : throw new InvalidArgumentException("The {$occurrence} occurrence of day of week {$dayOfWeek->name} does not exist in year {$year}.");
    }

    /**
     * Creates a date from an occurrence of a day of week in a calendar month.
     *
     * Positive occurrences count from the start of the month. Negative
     * occurrences count from the end of the month.
     *
     * @param int $year Calendar year.
     * @param int $month Calendar month number.
     * @param int $occurrence The occurrence number of the day of week, where positive values
     *                        count from the start of the month and negative values count from
     *                        the end of the month.
     * @param int|DayOfWeek $dayOfWeek Day of week as enum or compatible integer.
     *
     * @return static Date for the requested day of week occurrence inside the requested month.
     *
     * @throws InvalidArgumentException If the year, month, occurrence, or day of week is invalid.
     * @throws InvalidArgumentException If the requested occurrence does not exist in the given month.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public static function fromNthDayOfWeekInMonth(int $year, int $month, int $occurrence, int|DayOfWeek $dayOfWeek): static
    {
        if ($occurrence === 0) {
            throw new InvalidArgumentException('Occurrence value cannot be zero.');
        }
        if ($occurrence < -6 || $occurrence > 6) {
            throw new InvalidArgumentException("Occurrence value {$occurrence} is out of range: expected 1..6 or -1..-6.");
        }

        $dayOfWeek = self::toDayOfWeek($dayOfWeek);

        if ($occurrence > 0) {
            $monthStart = static::fromDayOfMonth($year, $month, 1);
            $target = $monthStart->addDays($monthStart->dayOfWeek()->daysUntil($dayOfWeek, $occurrence));
        } else {
            $monthEnd = static::fromDayOfMonth($year, $month, -1);
            $target = $monthEnd->addDays(-$monthEnd->dayOfWeek()->daysSince($dayOfWeek, -$occurrence));
        }

        return $target->year() === $year && $target->month() === $month
            ? $target
            : throw new InvalidArgumentException("The {$occurrence} occurrence of day of week {$dayOfWeek->name} does not exist in month {$month} of year {$year}.");
    }

    // ================================================================
    // Conversions
    // ================================================================

    /**
     * Returns this civil day represented in the requested calendar system.
     *
     * @param Calendar $calendar Target calendar system.
     *
     * @return self Equivalent calendar date in the requested calendar system.
     *
     * @throws DateOutOfRangeException When this date is outside the supported range for the requested calendar.
     *
     * @see Calendar::dateClass()
     * @see CalendarDate::fromJDN()
     */
    final public function toCalendar(Calendar $calendar): self
    {
        if (static::calendar() === $calendar) {
            return $this;
        }

        return $calendar->dateClass()::fromJDN($this->jdn());
    }

    /**
     * Returns this date as a Gregorian ISO 8601 date string.
     *
     * @return string Gregorian date in ISO 8601 `YYYY-MM-DD` form.
     *
     * @throws DateOutOfRangeException When this date is outside the supported Gregorian range.
     *
     * @see CalendarDate::fromIso8601DateString()
     * @see CalendarDate::toCalendar()
     */
    final public function toIso8601DateString(): string
    {
        [$year, $month, $day] = $this->toCalendar(Calendar::Gregorian)->toArray();
        $fmt = $year < 0 ? '%05d-%02d-%02d' : '%04d-%02d-%02d';
        return sprintf($fmt, $year, $month, $day);
    }

    /**
     * Returns the calendar components of this date as an array in [year, month, day] order.
     *
     * @return array{0:int,1:int,2:int} Array of [year, month, day].
     */
    final public function toArray(): array
    {
        return $this->components;
    }

    // ================================================================
    // Parsing
    // ================================================================

    /**
     * Parses a date string using a format pattern.
     *
     * A pattern is a sequence of tokens and literal text. Tokens read date
     * fields. Literal text must appear in the input at the same position.
     * Single-quoted or double-quoted text is always literal text. A backslash
     * escapes the next character.
     *
     * A calendar scope reads a sub-pattern in another calendar. Scope syntax is
     * `[Calendar:pattern]`, for example `[Gregorian:Y-m-d]`. Scope names
     * match `Calendar` case names case-insensitively. Scopes cannot be nested.
     *
     * Unscoped tokens resolve the date returned by this class. Scoped tokens
     * validate the same civil day in the scoped calendar; they do not change the
     * returned date class.
     *
     * Supported tokens:
     *
     * - `Y`: year number.
     * - `V`: year number, as digits or words.
     * - `y`: two-digit year.
     * - `n`: month number.
     * - `m`: month number.
     * - `F`: name of month.
     * - `M`: abbreviated name of month.
     * - `j`: day of month.
     * - `d`: day of month.
     * - `J`: day of month, as digits or words.
     * - `l`: name of day of week.
     * - `D`: abbreviated name of day of week.
     * - `k`: day of week occurrence in the month, as digits or words.
     * - `K`: day of week occurrence in the year, as digits or words.
     * - `R`: day of year, as digits or words.
     * - `q`: quarter, as digits or words.
     * - `Q`: season name.
     * - `C`: era name.
     * - `E`: abbreviated era name.
     *
     * The primary calendar fields must identify one date. Accepted field sets:
     *
     * - Year, month, and day.
     * - Year and day of year.
     * - Year, day of week occurrence in year, and day of week.
     * - Year, month, day of week occurrence in month, and day of week.
     *
     * Parsing uses the selected locale for digits, number words, names, and text
     * normalization. By default it strips Unicode bidi controls and normalizes
     * whitespace in both input and pattern. Parsed day of week, day of year,
     * ordinal position, quarter, season, era, and scoped calendar fields
     * are checked against the resolved date unless validation is skipped.
     *
     * @param string $input Input text to parse.
     * @param string $pattern Format pattern.
     * @param array{locale?:Locale|string,preserveBidiControls?:bool,preserveWhitespace?:bool,skipValidation?:bool} $options Parsing options:
     *     - locale: Locale or language tag used for localized digits, words, names, and text normalization (default LocaleRegistry::default()).
     *     - preserveBidiControls: Keep Unicode bidi control characters in the input and pattern (default false).
     *     - preserveWhitespace: Match whitespace exactly instead of trimming and collapsing whitespace (default false).
     *     - skipValidation: Skip consistency checks for derived and scoped fields after the date is resolved (default false).
     *
     * @return static Parsed date.
     *
     * @throws DateParseException If the input does not match the pattern or contains invalid date components.
     * @throws InvalidArgumentException If the pattern or options are invalid.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range.
     *
     * @see CalendarDate::format()
     * @see PatternCompiler
     */
    public static function parse(string $input, string $pattern, array $options = []): static
    {
        $locale = self::toLocale($options['locale'] ?? null);

        $stripBidiControls = !($options['preserveBidiControls'] ?? false);
        $normalizeWhitespace = !($options['preserveWhitespace'] ?? false);
        $cleanInput = self::normalizeParseText($input, $stripBidiControls, $normalizeWhitespace);
        $cleanPattern = self::normalizeParseText($pattern, $stripBidiControls, $normalizeWhitespace);

        $compiledPattern = PatternCompiler::shared()->compile($cleanPattern);
        $primaryCalendar = static::calendar();

        $matches = $compiledPattern->match($cleanInput);
        if ($matches === false) {
            throw new DateParseException("Input \"{$input}\" does not match the pattern \"{$pattern}\".");
        }

        /** @var array<int,array<string,int>> $parsedByCalendar */
        $parsedByCalendar = [];
        foreach ($matches as [$capture, $value]) {
            $property = $capture->property();
            $calendar = $capture->calendarScope() ?? $primaryCalendar;
            $parsedValue = $capture->parse($value, $primaryCalendar, $locale);

            $parsedByCalendar[$calendar->value] ??= [];
            $parsedValues = &$parsedByCalendar[$calendar->value];

            if (isset($parsedValues[$property]) && $parsedValues[$property] !== $parsedValue) {
                throw new DateParseException("Inconsistent values parsed for \"{$property}\" in calendar {$calendar->name}: {$parsedValues[$property]} and {$parsedValue}.");
            }

            $parsedValues[$property] = $parsedValue;
        }

        $primaryFields = $parsedByCalendar[$primaryCalendar->value] ?? [];
        try {
            $date = self::dateFromParsedFields($primaryFields);
        } catch (InvalidArgumentException $e) {
            throw new DateParseException("Invalid date components extracted from \"{$input}\": " . $e->getMessage(), previous: $e);
        }

        if (!isset($date)) {
            throw new DateParseException("Failed to extract {$primaryCalendar->name} date components from \"{$input}\" with pattern \"{$pattern}\".");
        }

        if (!($options['skipValidation'] ?? false)) {
            foreach ($parsedByCalendar as $calendarId => $fields) {
                if (!empty($fields)) {
                    $calendar = Calendar::from($calendarId);
                    $date->toCalendar($calendar)->validateParsedFields($fields);
                }
            }
        }

        return $date;
    }

    // ================================================================
    // Accessors
    // ================================================================

    /**
     * Returns the calendar system used by this date class.
     *
     * @return CalendarSystem Calendar system.
     *
     * @see Calendar::system()
     * @see CalendarDate::calendar()
     */
    abstract public static function calendarSystem(): CalendarSystem;

    /**
     * Returns the calendar identifier used by this date class.
     *
     * @return Calendar Calendar identifier.
     *
     * @see Calendar::dateClass()
     * @see CalendarDate::calendarSystem()
     */
    final public static function calendar(): Calendar
    {
        return static::calendarSystem()->id();
    }

    /**
     * Returns this date's Julian Day Number.
     *
     * @return int Julian Day Number.
     *
     * @see CalendarDate::fromJDN()
     */
    final public function jdn(): int
    {
        return $this->jdn ??= static::calendarSystem()->toJDN($this->year, $this->month, $this->day);
    }

    /**
     * Returns the calendar year component.
     *
     * @return int Calendar year.
     */
    final public function year(): int
    {
        return $this->components[0];
    }

    /**
     * Returns the calendar month component.
     *
     * @return int Calendar month number.
     */
    final public function month(): int
    {
        return $this->components[1];
    }

    /**
     * Returns the calendar day-of-month component.
     *
     * @return int Calendar day of month.
     */
    final public function day(): int
    {
        return $this->components[2];
    }

    /**
     * Returns this date's quarter of the year.
     *
     * @return int Quarter of the year.
     */
    final public function quarter(): int
    {
        return intdiv(($this->month() - 1) * 4, $this->monthsInYear()) + 1;
    }

    /**
     * Returns this date's Iran/northern-hemisphere season.
     *
     * @return Season Season enum value.
     */
    public function season(): Season
    {
        return self::toCalendar(Calendar::Jalali)->season();
    }

    /**
     * Returns this date's one-based day of year.
     *
     * @return int Day of year.
     */
    final public function dayOfYear(): int
    {
        return static::calendarSystem()->toDayOfYear($this->year(), $this->month(), $this->day());
    }

    /**
     * Returns this date's day of week.
     *
     * @return DayOfWeek Day-of-week enum value.
     */
    final public function dayOfWeek(): DayOfWeek
    {
        return DayOfWeek::fromJDN($this->jdn());
    }

    /**
     * Returns this date's one-based occurrence of its day of week within the calendar year.
     *
     * @return int Positive occurrence number from the start of the year.
     */
    final public function dayOfWeekInYear(): int
    {
        return $this->dayOfWeek()->countOccurrences($this->startOfYearJDN(), $this->jdn());
    }

    /**
     * Returns this date's one-based occurrence of its day of week within the calendar month.
     *
     * @return int Positive occurrence number from the start of the month.
     */
    final public function dayOfWeekInMonth(): int
    {
        return $this->dayOfWeek()->countOccurrences($this->startOfMonthJDN(), $this->jdn());
    }

    /**
     * Returns whether this date's year is a leap year for its calendar.
     *
     * @return bool True when the date falls in a leap year.
     */
    final public function isLeapYear(): bool
    {
        return static::calendarSystem()->isLeapYear($this->year());
    }

    /**
     * Returns the number of months in this date's year.
     *
     * @return int Number of months in the year.
     */
    final public function monthsInYear(): int
    {
        return static::calendarSystem()->monthsInYear($this->year());
    }

    /**
     * Returns the number of days in this date's month.
     *
     * @return int Number of days in the month.
     */
    final public function daysInMonth(): int
    {
        return static::calendarSystem()->daysInMonth($this->year(), $this->month());
    }

    /**
     * Returns the number of days in this date's year.
     *
     * @return int Number of days in the year.
     */
    final public function daysInYear(): int
    {
        return static::calendarSystem()->daysInYear($this->year());
    }

    // ================================================================
    // Arithmetic operations
    // ================================================================

    /**
     * Returns a new date offset by a number of days.
     *
     * @param int $days Number of days to add, possibly negative.
     *
     * @return static Shifted date.
     *
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public function addDays(int $days): static
    {
        if ($days === 0) {
            return $this;
        }

        return static::fromJDN($this->jdn() + $days);
    }

    /**
     * Returns a new date offset by a number of calendar months.
     *
     * If the target month has fewer days, the day of month is clamped to that
     * month end.
     *
     * @param int $months Number of months to add, possibly negative.
     * @return static Shifted date, with day clamped if needed.
     *
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public function addMonths(int $months): static
    {
        if ($months === 0) {
            return $this;
        }

        $calendarSystem = static::calendarSystem();

        $year = $this->year();
        $month = $this->month() + $months;

        if ($months <= 0) {
            while ($month <= 0) {
                $year = YearNumbering::offsetYear($year, -1);
                $month += $calendarSystem->monthsInYear($year);
            }
        } else {
            while ($month > $calendarSystem->monthsInYear($year)) {
                $year = YearNumbering::offsetYear($year, 1);
                $month -= $calendarSystem->monthsInYear($year);
            }
        }

        $day = min($this->day(), $calendarSystem->daysInMonth($year, $month));
        return new static($year, $month, $day);
    }

    /**
     * Returns a new date offset by a number of calendar years.
     *
     * If the target year does not contain the current month or day, the result
     * is clamped to the nearest valid date in that year.
     *
     * @param int $years Number of years to add, possibly negative.
     * @return static Shifted date, with month or day clamped if needed.
     *
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public function addYears(int $years): static
    {
        if ($years === 0) {
            return $this;
        }

        $calendarSystem = static::calendarSystem();
        $newYear = YearNumbering::offsetYear($this->year, $years);
        $newMonth = min($this->month, $calendarSystem->monthsInYear($newYear));
        $newDay = min($this->day, $calendarSystem->daysInMonth($newYear, $newMonth));

        return new static($newYear, $newMonth, $newDay);
    }

    /**
     * Returns the next occurrence of a day of week after this date.
     *
     * @param int|DayOfWeek $dayOfWeek Day of week to select.
     * @param int $occurrence One-based occurrence after this date.
     *
     * @return static Selected future date.
     *
     * @throws InvalidArgumentException If the day of week or occurrence is invalid.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public function nextDayOfWeek(int|DayOfWeek $dayOfWeek, int $occurrence = 1): static
    {
        if ($occurrence < 1) {
            throw new InvalidArgumentException("Occurrence value must be at least 1: {$occurrence}.");
        }

        $dayOfWeek = self::toDayOfWeek($dayOfWeek);

        $currentDayOfWeek = $this->dayOfWeek();
        if ($currentDayOfWeek === $dayOfWeek) {
            ++$occurrence;
        }

        $daysUntil = $currentDayOfWeek->daysUntil($dayOfWeek, $occurrence);
        return $this->addDays($daysUntil);
    }

    /**
     * Returns the previous occurrence of a day of week before this date.
     *
     * @param int|DayOfWeek $dayOfWeek Day of week to select.
     * @param int $occurrence One-based occurrence before this date.
     *
     * @return static Selected past date.
     *
     * @throws InvalidArgumentException If the day of week or occurrence is invalid.
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public function previousDayOfWeek(int|DayOfWeek $dayOfWeek, int $occurrence = 1): static
    {
        if ($occurrence < 1) {
            throw new InvalidArgumentException("Occurrence value must be at least 1: {$occurrence}.");
        }

        $dayOfWeek = self::toDayOfWeek($dayOfWeek);

        $currentDayOfWeek = $this->dayOfWeek();
        if ($currentDayOfWeek === $dayOfWeek) {
            ++$occurrence;
        }

        $daysSince = $currentDayOfWeek->daysSince($dayOfWeek, $occurrence);
        return $this->addDays(-$daysSince);
    }

    // ================================================================
    // Boundary calculations
    // ================================================================

    /**
     * Returns the first day of this date's year.
     *
     * @return static First day of year.
     */
    final public function startOfYear(): static
    {
        return new static($this->year(), 1, 1);
    }

    /**
     * Returns the last day of this date's year.
     *
     * @return static Last day of year.
     */
    final public function endOfYear(): static
    {
        $calendarSystem = static::calendarSystem();
        $month = $calendarSystem->monthsInYear($this->year());
        $day = $calendarSystem->daysInMonth($this->year(), $month);
        return new static($this->year(), $month, $day);
    }

    /**
     * Returns the first day of this date's month.
     *
     * @return static First day of month.
     */
    final public function startOfMonth(): static
    {
        return new static($this->year(), $this->month(), 1);
    }

    /**
     * Returns the last day of this date's month.
     *
     * @return static Last day of month.
     */
    final public function endOfMonth(): static
    {
        return new static($this->year(), $this->month(), $this->daysInMonth());
    }

    /**
     * Returns the first day of this date's week.
     *
     * @param int|DayOfWeek $firstDayOfWeek Day of week considered as the start of the week.
     *
     * @return static Date starting the week.
     *
     * @throws InvalidArgumentException If the week start is invalid.
     */
    final public function startOfWeek(int|DayOfWeek $firstDayOfWeek): static
    {
        $firstDayOfWeek = self::toDayOfWeek($firstDayOfWeek);

        return $this->addDays(-$this->dayOfWeek()->daysSince($firstDayOfWeek));
    }

    /**
     * Returns the final day of this date's week.
     *
     * @param int|DayOfWeek $firstDayOfWeek Day of week considered as the start of the week.
     *
     * @return static Date ending the week.
     *
     * @throws InvalidArgumentException If the week start is invalid.
     */
    final public function endOfWeek(int|DayOfWeek $firstDayOfWeek): static
    {
        $firstDayOfWeek = self::toDayOfWeek($firstDayOfWeek);

        return $this->addDays(6 - $this->dayOfWeek()->daysSince($firstDayOfWeek));
    }

    /**
     * Returns the first day of this date's quarter.
     *
     * @return static First day of quarter.
     */
    final public function startOfQuarter(): static
    {
        $month = intdiv(($this->quarter() - 1) * $this->monthsInYear(), 4) + 1;
        return new static($this->year(), $month, 1);
    }

    /**
     * Returns the last day of this date's quarter.
     *
     * @return static Last day of quarter.
     */
    final public function endOfQuarter(): static
    {
        $month = intdiv($this->quarter() * $this->monthsInYear(), 4);
        $day = static::calendarSystem()->daysInMonth($this->year(), $month);
        return new static($this->year(), $month, $day);
    }

    // ================================================================
    // Wither methods
    // ================================================================

    /**
     * Returns a new date with a different year.
     *
     * @param int $year New calendar year.
     * @return static Date with changed year, with month or day clamped if needed.
     *
     * @throws DateOutOfRangeException If the resulting date is outside the supported range for this calendar.
     */
    final public function withYear(int $year): static
    {
        if ($year === $this->year()) {
            return $this;
        }

        $calendarSystem = static::calendarSystem();
        $month = min($this->month(), $calendarSystem->monthsInYear($year));
        $day = min($this->day(), $calendarSystem->daysInMonth($year, $month));
        return new static($year, $month, $day);
    }

    /**
     * Returns a new date with a different month.
     *
     * @param int $month New calendar month.
     * @return static Date with changed month, with day clamped if needed.
     */
    final public function withMonth(int $month): static
    {
        if ($month === $this->month()) {
            return $this;
        }

        $day = min($this->day(), static::calendarSystem()->daysInMonth($this->year(), $month));
        return new static($this->year(), $month, $day);
    }

    /**
     * Returns a new date with a different day of month.
     *
     * @param int $day New day of month.
     * @return static Date with changed day.
     */
    final public function withDay(int $day): static
    {
        if ($day === $this->day()) {
            return $this;
        }

        return new static($this->year(), $this->month(), $day);
    }

    // ================================================================
    // Comparison
    // ================================================================

    /**
     * Compares this date to another calendar date by chronological order.
     *
     * @param self $other Date to compare.
     * @return int Negative when this date is before `$other`, zero when equal, positive when after.
     */
    final public function compareTo(self $other): int
    {
        return $this->jdn() <=> $other->jdn();
    }

    /**
     * Returns whether this date and another date represent the same civil day.
     *
     * Dates in different calendars are equal when they identify the same day.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when equal.
     */
    final public function equals(self $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    /**
     * Returns whether this date is before another date chronologically.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when before.
     */
    final public function isBefore(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    /**
     * Returns whether this date is after another date chronologically.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when after.
     */
    final public function isAfter(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Returns whether this date is inside an inclusive date range defined by two other dates.
     *
     * @param self $start Inclusive start.
     * @param self $end Inclusive end.
     *
     * @return bool True when inside.
     */
    final public function isBetween(self $start, self $end): bool
    {
        return !($this->isBefore($start) || $this->isAfter($end));
    }

    /**
     * Returns whether this date and another date are in the same calendar day.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when same day.
     */
    final public function isSameDay(self $other): bool
    {
        return $this->isSameMonth($other)
            && $this->day() === $other->day();
    }

    /**
     * Returns whether this date and another date are in the same calendar week.
     *
     * @param self $other Date to compare.
     * @param int|DayOfWeek $firstDayOfWeek Day of week considered as the start of the week.
     *
     * @return bool True when same week.
     *
     * @throws InvalidArgumentException If the week start is invalid.
     */
    final public function isSameWeek(self $other, int|DayOfWeek $firstDayOfWeek): bool
    {
        $firstDayOfWeek = self::toDayOfWeek($firstDayOfWeek);

        return static::calendar() === $other::calendar()
            && $this->startOfWeek($firstDayOfWeek)->equals($other->startOfWeek($firstDayOfWeek));
    }

    /**
     * Returns whether this date and another date are in the same calendar month.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when same month.
     */
    final public function isSameMonth(self $other): bool
    {
        return $this->isSameYear($other)
            && $this->month() === $other->month();
    }

    /**
     * Returns whether this date and another date are in the same calendar quarter.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when same quarter.
     */
    final public function isSameQuarter(self $other): bool
    {
        return $this->isSameYear($other)
            && $this->quarter() === $other->quarter();
    }

    /**
     * Returns whether this date and another date are in the same calendar season.
     *
     * Seasons use the Iran/northern-hemisphere convention of spring starting at the
     * March equinox, summer at the June solstice, autumn at the September equinox, and
     * winter at the December solstice.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when same season.
     */
    final public function isSameSeason(self $other): bool
    {
        return $this->isSameYear($other)
            && $this->season() === $other->season();
    }

    /**
     * Returns whether this date and another date are in the same calendar year.
     *
     * @param self $other Date to compare.
     *
     * @return bool True when same year.
     */
    final public function isSameYear(self $other): bool
    {
        return static::calendar() === $other::calendar()
            && $this->year() === $other->year();
    }

    // ================================================================
    // Interval calculations
    // ================================================================

    /**
     * Returns the signed day difference from this date to another date.
     *
     * @param self $other Target date.
     *
     * @return int Number of days.
     */
    final public function differenceInDays(self $other): int
    {
        return $other->jdn() - $this->jdn();
    }

    /**
     * Returns the signed month difference from this date to another date, ignoring any day-of-month differences.
     *
     * @param self $other Target date.
     *
     * @return int Number of months.
     */
    final public function differenceInMonths(self $other): int
    {
        $calendarSystem = static::calendarSystem();

        $monthDiff = 0;
        $y = $this->year();
        while ($y < $other->year()) {
            $monthDiff += $calendarSystem->monthsInYear($y);
            $y = YearNumbering::offsetYear($y, 1);
        }
        while ($y > $other->year()) {
            $y = YearNumbering::offsetYear($y, -1);
            $monthDiff -= $calendarSystem->monthsInYear($y);
        }
        $monthDiff += $other->month() - $this->month();

        if ($this->day() > $other->day()) {
            --$monthDiff;
        }

        return $monthDiff;
    }

    /**
     * Returns the signed year difference from this date to another date, ignoring any month or day differences.
     *
     * @param self $other Target date.
     *
     * @return int Number of years.
     */
    final public function differenceInYears(self $other): int
    {
        $thisAstronomical = YearNumbering::toAstronomicalYear($this->year());
        $otherAstronomical = YearNumbering::toAstronomicalYear($other->year());
        $yearDiff = $otherAstronomical - $thisAstronomical;

        if ($this->month() > $other->month() || ($this->month() === $other->month() && $this->day() > $other->day())) {
            --$yearDiff;
        }

        return $yearDiff;
    }

    /**
     * Returns the number of times a day of week occurs within this date's year.
     *
     * @param int|DayOfWeek $dayOfWeek Day of week to count.
     *
     * @return int Count of matching days of week in the year.
     *
     * @throws InvalidArgumentException If the day of week is invalid.
     */
    final public function daysOfWeekInYear(int|DayOfWeek $dayOfWeek): int
    {
        $dayOfWeek = self::toDayOfWeek($dayOfWeek);

        $yearStartJDN = $this->jdn() - $this->dayOfYear() + 1;
        $yearEndJDN = $yearStartJDN + $this->daysInYear() - 1;
        return $dayOfWeek->countOccurrences($yearStartJDN, $yearEndJDN);
    }

    /**
     * Returns the number of times a day of week occurs within this date's month.
     *
     * @param int|DayOfWeek $dayOfWeek Day of week to count.
     *
     * @return int Count of matching days of week in the month.
     *
     * @throws InvalidArgumentException If the day of week is invalid.
     */
    final public function daysOfWeekInMonth(int|DayOfWeek $dayOfWeek): int
    {
        $dayOfWeek = self::toDayOfWeek($dayOfWeek);

        $monthStartJDN = $this->jdn() - $this->day() + 1;
        $monthEndJDN = $monthStartJDN + $this->daysInMonth() - 1;
        return $dayOfWeek->countOccurrences($monthStartJDN, $monthEndJDN);
    }

    /**
     * Returns the number of weeks spanned by this date's year.
     *
     * @param int|DayOfWeek $firstDayOfWeek Day of week considered as the start of the week.
     *
     * @return int Number of weeks in the year.
     *
     * @throws InvalidArgumentException If the week start is invalid.
     */
    final public function weeksInYear(int|DayOfWeek $firstDayOfWeek): int
    {
        return DayOfWeek::fromJDN($this->startOfYearJDN())->weeksSpanned(
            $this->daysInYear(),
            self::toDayOfWeek($firstDayOfWeek)
        );
    }

    /**
     * Returns the number of weeks spanned by this date's month.
     *
     * @param int|DayOfWeek $firstDayOfWeek Day of week considered as the start of the week.
     *
     * @return int Number of weeks in the month.
     *
     * @throws InvalidArgumentException If the week start is invalid.
     */
    final public function weeksInMonth(int|DayOfWeek $firstDayOfWeek): int
    {
        return DayOfWeek::fromJDN($this->startOfMonthJDN())->weeksSpanned(
            $this->daysInMonth(),
            self::toDayOfWeek($firstDayOfWeek)
        );
    }

    // ================================================================
    // Formatting
    // ================================================================

    /**
     * Formats this date with a calendar format pattern.
     *
     * A pattern is a sequence of tokens and literal text. Tokens write date
     * fields. Literal text is copied to the output. Single-quoted or
     * double-quoted text is always literal text. A backslash escapes the next
     * character.
     *
     * A calendar scope writes a sub-pattern in another calendar. Scope syntax
     * is `[Calendar:pattern]`, for example `[Gregorian:Y-m-d]`. Scope names
     * match `Calendar` case names case-insensitively. Scopes cannot be nested.
     *
     * Unscoped tokens format fields from this date. Scoped tokens first convert
     * the same civil day to the scoped calendar, then format that scoped date.
     *
     * Supported tokens:
     *
     * - `Y`: year number with minimum 4 digits, padded with leading zeros.
     * - `V`: year in cardinal words.
     * - `y`: two-digit year.
     * - `n`: month number.
     * - `m`: month number with minimum 2 digits, padded with leading zeros.
     * - `F`: name of month.
     * - `M`: abbreviated name of month.
     * - `j`: day of month.
     * - `d`: day of month with minimum 2 digits, padded with leading zeros.
     * - `J`: day of month in ordinal words.
     * - `l`: name of day of week.
     * - `D`: abbreviated name of day of week.
     * - `k`: day of week occurrence in the month, as ordinal words.
     * - `K`: day of week occurrence in the year, as ordinal words.
     * - `R`: day of year in ordinal words.
     * - `q`: quarter in ordinal words.
     * - `Q`: season name.
     * - `C`: era name.
     * - `E`: abbreviated era name.
     *
     * Formatting uses the selected locale for numbers, words, names, and text
     * direction. When text-direction protection is enabled, the result is
     * wrapped in Unicode isolate controls for the locale direction.
     *
     * @param string $pattern Format pattern.
     * @param array{locale?:Locale|string,protectTextDirection?:bool} $options Formatting options:
     *     - locale: Locale or language tag used for numbers, words, names, and text direction (default LocaleRegistry::default()).
     *     - protectTextDirection: Wrap the result in Unicode isolate controls for the locale direction (default false).
     *
     * @return string Formatted date.
     *
     * @throws InvalidArgumentException If the pattern or options are invalid.
     * @throws DateOutOfRangeException When a scoped calendar conversion is outside the supported range.
     *
     * @see CalendarDate::parse()
     * @see PatternCompiler
     */
    public function format(string $pattern, array $options = []): string
    {
        $locale = self::toLocale($options['locale'] ?? null);
        $result = PatternCompiler::shared()->compile($pattern)->format($this, $locale);

        if (!empty($options['protectTextDirection'])) {
            $isolate = $locale->isRightToLeft() ? "\u{2067}" : "\u{2066}";
            $result = "{$isolate}{$result}\u{2069}";
        }

        return $result;
    }

    // ================================================================
    // Magic properties and methods
    // ================================================================

    /**
     * Returns the date as a string.
     *
     * @return string String representation of the date.
     */
    public function __toString(): string
    {
        [$year, $month, $day] = $this->components;
        $fmt = $year < 0 ? '%05d/%02d/%02d' : '%04d/%02d/%02d';
        return sprintf($fmt, $year, $month, $day);
    }

    /**
     * Returns the PHP serialization representation of this date.
     *
     * The Julian Day Number is used as the serialized state because it uniquely
     * identifies the represented day independently of any calendar system.
     *
     * @return array{jdn:int} Serializable date state.
     */
    final public function __serialize(): array
    {
        return ['jdn' => $this->jdn()];
    }

    /**
     * Restores this date from its PHP serialization representation.
     *
     * The concrete date class determines which calendar components are rebuilt
     * from the serialized Julian Day Number.
     *
     * @param array<string,mixed> $data Serialized date state.
     *
     * @return void
     *
     * @throws InvalidArgumentException When the serialized state does not contain an integer Julian Day Number.
     * @throws DateOutOfRangeException When the Julian Day Number is outside the supported range for this calendar.
     */
    final public function __unserialize(array $data): void
    {
        if (!isset($data['jdn']) || !is_int($data['jdn'])) {
            throw new InvalidArgumentException('Serialized calendar date must contain an integer JDN.');
        }

        $this->components = static::calendarSystem()->toYearMonthDay($data['jdn']);
        $this->jdn = $data['jdn'];
    }

    /**
     * Returns the value of a read-only calendar property.
     *
     * @param string $name Property name.
     *
     * @return mixed Property value.
     *
     * @throws InvalidArgumentException If the property is unknown.
     */
    public function __get(string $name)
    {
        return match ($name) {
            'year' => $this->year(),
            'month' => $this->month(),
            'day' => $this->day(),
            'jdn' => $this->jdn(),
            'dayOfWeek' => $this->dayOfWeek(),
            'dayOfYear' => $this->dayOfYear(),
            'quarter' => $this->quarter(),
            'season' => $this->season(),
            'isLeapYear' => $this->isLeapYear(),
            'monthsInYear' => $this->monthsInYear(),
            'daysInMonth' => $this->daysInMonth(),
            'daysInYear' => $this->daysInYear(),
            'dayOfWeekInYear' => $this->dayOfWeekInYear(),
            'dayOfWeekInMonth' => $this->dayOfWeekInMonth(),
            'calendar' => static::calendar(),
            'calendarSystem' => static::calendarSystem(),
            default => throw new InvalidArgumentException("Unknown property: {$name}"),
        };
    }

    // ================================================================
    // Parsing helpers
    // ================================================================

    /**
     * Validates parsed property values against this date.
     *
     * @param array<string,int> $parsedProperties Parsed property values to validate against this date.
     *
     * @return void
     *
     * @throws DateParseException If any parsed property value does not match the corresponding value of this date.
     */
    protected function validateParsedFields(array $parsedProperties): void
    {
        foreach ($parsedProperties as $propertyName => $expectedValue) {
            // Retrieve the actual property value from this date as an integer
            $propertyValue = match ($propertyName) {
                'year' => $this->year(),
                'month' => $this->month(),
                'day' => $this->day(),
                'dayOfWeek' => $this->dayOfWeek()->value,
                'dayOfWeekInYear' => $this->dayOfWeekInYear(),
                'dayOfWeekInMonth' => $this->dayOfWeekInMonth(),
                'dayOfYear' => $this->dayOfYear(),
                'quarter' => $this->quarter(),
                'season' => $this->season()->value,
                'calendar' => static::calendar()->value,
                'isLeapYear' => $this->isLeapYear() ? 1 : 0,
                default => null,
            };

            if ($propertyValue === null) {
                continue; // Non-comparable property, skip validation
            }

            // Adjust negative values to be relative to the end of the range
            if ($expectedValue < 0) {
                $expectedValue = match ($propertyName) {
                    'dayOfYear' => $this->daysInYear() + $expectedValue + 1,
                    'dayOfWeekInYear' => $this->daysOfWeekInYear($this->dayOfWeek()) + $expectedValue + 1,
                    'dayOfWeekInMonth' => $this->daysOfWeekInMonth($this->dayOfWeek()) + $expectedValue + 1,
                    'month' => $this->monthsInYear() + $expectedValue + 1,
                    'day' => $this->daysInMonth() + $expectedValue + 1,
                    'quarter' => 4 + $expectedValue + 1,
                    default => $expectedValue,
                };
            }

            if ($propertyValue !== $expectedValue) {
                $calendarLabel = static::calendar()->name;
                $parsedDateString = $this->__toString();
                throw new DateParseException("{$calendarLabel} {$propertyName} \"{$expectedValue}\" does not match the date {$parsedDateString} (expected \"{$propertyValue}\").");
            }
        }
    }

    /**
     * Creates a date from parsed field values for the called calendar.
     *
     * @param array<string,int|null> $fields Parsed field values.
     *
     * @return static|null Date from the parsed fields, or null if the fields are insufficient to determine a date.
     *
     * @throws InvalidArgumentException If parsed date components are invalid.
     */
    protected static function dateFromParsedFields(array $fields): ?static
    {
        if (isset($fields['year'], $fields['month'], $fields['day'])) {
            return static::fromDayOfMonth($fields['year'], $fields['month'], $fields['day']);
        }

        if (isset($fields['year'], $fields['dayOfYear'])) {
            return static::fromDayOfYear($fields['year'], $fields['dayOfYear']);
        }

        if (isset($fields['year'], $fields['dayOfWeekInYear'], $fields['dayOfWeek'])) {
            return static::fromNthDayOfWeekInYear($fields['year'], $fields['dayOfWeekInYear'], $fields['dayOfWeek']);
        }

        if (isset($fields['year'], $fields['month'], $fields['dayOfWeekInMonth'], $fields['dayOfWeek'])) {
            return static::fromNthDayOfWeekInMonth($fields['year'], $fields['month'], $fields['dayOfWeekInMonth'], $fields['dayOfWeek']);
        }

        return null;
    }

    /**
     * Normalizes parse input and pattern text.
     *
     * @param string $text Text to normalize.
     * @param bool $stripBidiControls Whether to strip Unicode bidi controls.
     * @param bool $normalizeWhitespace Whether to trim and collapse whitespace.
     *
     * @return string Normalized text.
     */
    protected static function normalizeParseText(string $text, bool $stripBidiControls, bool $normalizeWhitespace): string
    {
        if ($stripBidiControls) {
            $text = str_replace([
                "\u{061C}", // Arabic Letter Mark
                "\u{200E}", // Left-to-Right Mark
                "\u{200F}", // Right-to-Left Mark
                "\u{202A}", // Left-to-Right Embedding
                "\u{202B}", // Right-to-Left Embedding
                "\u{202C}", // Pop Directional Formatting
                "\u{202D}", // Left-to-Right Override
                "\u{202E}", // Right-to-Left Override
                "\u{2066}", // Left-to-Right Isolate
                "\u{2067}", // Right-to-Left Isolate
                "\u{2068}", // First Strong Isolate
                "\u{2069}", // Pop Directional Isolate
            ], '', $text);
        }

        if ($normalizeWhitespace) {
            $text = preg_replace('/\s+/u', ' ', trim($text)) ?? $text;
        }

        return $text;
    }

    // ================================================================
    // Internal helpers
    // ================================================================

    /**
     * Returns the Julian Day Number of the first day of this date's month.
     *
     * @return int JDN of the first day of the month.
     */
    final protected function startOfMonthJDN(): int
    {
        return $this->jdn() - $this->day() + 1;
    }

    /**
     * Returns the Julian Day Number of the first day of this date's year.
     *
     * @return int JDN of the first day of the year.
     */
    final protected function startOfYearJDN(): int
    {
        return $this->jdn() - $this->dayOfYear() + 1;
    }

    /**
     * Validates a day of week value and returns a DayOfWeek instance.
     *
     * @param int|DayOfWeek $dayOfWeek Day of week value as an integer (0..6) or DayOfWeek instance.
     *
     * @return DayOfWeek Validated DayOfWeek instance.
     *
     * @throws InvalidArgumentException If the value is not a valid day of week.
     */
    final protected static function toDayOfWeek(int|DayOfWeek $dayOfWeek): DayOfWeek
    {
        if ($dayOfWeek instanceof DayOfWeek) {
            return $dayOfWeek;
        }

        return DayOfWeek::tryFrom($dayOfWeek)
            ?? throw new InvalidArgumentException("Day of week {$dayOfWeek} is out of range: expected 0 through 6.");
    }

    /**
     * Validates a locale value and returns a Locale instance.
     *
     * @param Locale|string|null $locale Locale value as a language tag or Locale instance.
     *
     * @return Locale Validated Locale instance.
     *
     * @throws InvalidArgumentException If the language tag is not registered.
     */
    final protected static function toLocale(Locale|string|null $locale): Locale
    {
        if ($locale === null) {
            return LocaleRegistry::default();
        }

        if ($locale instanceof Locale) {
            return $locale;
        }

        return LocaleRegistry::find($locale)
            ?? throw new InvalidArgumentException("Locale language tag \"{$locale}\" is not registered.");
    }

    // ================================================================
    // Test utilities
    // ================================================================

    /**
     * Sets the date returned by today().
     *
     * @param self|null $testToday Date to return, or null to restore real time.
     *
     * @return void
     *
     * @see CalendarDate::today()
     * @see CalendarDate::getTestToday()
     */
    final public static function setTestToday(?self $testToday): void
    {
        self::$testToday = $testToday;
    }

    /**
     * Returns the configured date for today() in this calendar.
     *
     * @return static|null Configured date, or null when today() uses real time.
     *
     * @see CalendarDate::today()
     * @see CalendarDate::setTestToday()
     */
    final public static function getTestToday(): ?static
    {
        /** @var static|null $testableToday */
        $testableToday = self::$testToday?->toCalendar(static::calendar());
        return $testableToday;
    }
}
