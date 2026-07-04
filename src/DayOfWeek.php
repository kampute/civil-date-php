<?php

declare(strict_types=1);

namespace Kampute\CivilDate;

use InvalidArgumentException;

/**
 * Represents a civil day of week.
 *
 * Integer values follow PHP's `date('w')` convention: Sunday is `0` and Saturday is `6`.
 */
enum DayOfWeek: int
{
    /**
     * Sunday.
     */
    case Sunday = 0;

    /**
     * Monday.
     */
    case Monday = 1;

    /**
     * Tuesday.
     */
    case Tuesday = 2;

    /**
     * Wednesday.
     */
    case Wednesday = 3;

    /**
     * Thursday.
     */
    case Thursday = 4;

    /**
     * Friday.
     */
    case Friday = 5;

    /**
     * Saturday.
     */
    case Saturday = 6;

    /**
     * Returns the day of week for a Julian Day Number.
     *
     * @param int $jdn Julian Day Number.
     *
     * @return self Day of week for the given Julian Day Number.
     */
    public static function fromJDN(int $jdn): self
    {
        return self::from(self::normalizeValue($jdn + 1));
    }

    /**
     * Counts occurrences of this day of week in an inclusive Julian Day Number range.
     *
     * @param int $startJDN Inclusive start Julian Day Number.
     * @param int $endJDN Inclusive end Julian Day Number.
     *
     * @return int Number of matching days of week in the range.
     *
     * @throws InvalidArgumentException If the range start is after the range end.
     */
    public function countOccurrences(int $startJDN, int $endJDN): int
    {
        if ($endJDN < $startJDN) {
            throw new InvalidArgumentException("Start JDN {$startJDN} must be less than or equal to end JDN {$endJDN}.");
        }

        $daysToFirstOccurrence = self::fromJDN($startJDN)->daysUntil($this);
        $firstOccurrenceJDN = $startJDN + $daysToFirstOccurrence;

        return $firstOccurrenceJDN <= $endJDN
            ? intdiv($endJDN - $firstOccurrenceJDN, 7) + 1
            : 0;
    }

    /**
     * Returns the number of weeks spanned by a day count starting from this day of week.
     *
     * @param int $days The number of days in the span, starting from this day of week.
     * @param self $firstDayOfWeek Day of week considered as the start of the week for counting purposes.
     *
     * @return int Number of weeks spanned by the day count.
     *
     * @throws InvalidArgumentException If the number of days is less than 1.
     */
    public function weeksSpanned(int $days, self $firstDayOfWeek): int
    {
        if ($days <= 0) {
            throw new InvalidArgumentException("The number of days must be at least 1: {$days}.");
        }

        return intdiv($days - 1 + $this->daysSince($firstDayOfWeek), 7) + 1;
    }

    /**
     * Returns the number of days from this day of week to a future occurrence of another day of week.
     *
     * @param self $dayOfWeek Target day of week to look forward to.
     * @param int $occurrence Which occurrence of the target day of week.
     *
     * @return int Number of days to the requested occurrence.
     *
     * @throws InvalidArgumentException If the occurrence is less than 1.
     */
    public function daysUntil(self $dayOfWeek, int $occurrence = 1): int
    {
        if ($occurrence < 1) {
            throw new InvalidArgumentException("Occurrence value must be at least 1: {$occurrence}.");
        }

        return self::normalizeValue($dayOfWeek->value - $this->value) + (($occurrence - 1) * 7);
    }

    /**
     * Returns the number of days from this day of week to a past occurrence of another day of week.
     *
     * @param self $dayOfWeek Target day of week to look back to.
     * @param int $occurrence Which occurrence of the target day of week.
     *
     * @return int Number of days since the requested occurrence.
     *
     * @throws InvalidArgumentException If the occurrence is less than 1.
     */
    public function daysSince(self $dayOfWeek, int $occurrence = 1): int
    {
        return $dayOfWeek->daysUntil($this, $occurrence);
    }

    /**
     * Normalizes an integer into PHP day of week numbering.
     *
     * @param int $value Weekday offset or number.
     *
     * @return int Normalized day of week number in the range 0 through 6.
     */
    private static function normalizeValue(int $value): int
    {
        return (($value % 7) + 7) % 7;
    }
}
