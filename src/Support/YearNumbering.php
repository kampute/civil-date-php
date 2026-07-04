<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support;

use InvalidArgumentException;

/**
 * Converts between calendar and astronomical year numbering.
 *
 * Calendar years have no year zero. Astronomical years use zero for 1 BCE.
 */
final class YearNumbering
{
    /**
     * Disallows construction.
     */
    private function __construct()
    {
    }

    /**
     * Converts a calendar year to astronomical year numbering.
     *
     * @param int $year Calendar year.
     *
     * @return int Astronomical year.
     *
     * @throws InvalidArgumentException If the input year is zero.
     */
    public static function toAstronomicalYear(int $year): int
    {
        if ($year === 0) {
            throw new InvalidArgumentException('Calendar year zero is not defined.');
        }

        return $year < 0 ? $year + 1 : $year;
    }

    /**
     * Converts an astronomical year to a calendar year.
     *
     * @param int $year Astronomical year.
     *
     * @return int Calendar year.
     */
    public static function toCalendarYear(int $year): int
    {
        return $year <= 0 ? $year - 1 : $year;
    }

    /**
     * Offsets a calendar year by a given number of years, skipping year zero.
     *
     * @param int $year Calendar year to offset.
     * @param int $offset Number of years to offset, positive or negative.
     *
     * @return int Offset calendar year.
     *
     * @throws InvalidArgumentException If the input year is zero.
     */
    public static function offsetYear(int $year, int $offset): int
    {
        return self::toCalendarYear(self::toAstronomicalYear($year) + $offset);
    }

    /**
     * Expands a two-digit year to the nearest matching year around a reference year.
     *
     * @param int $year Two-digit year in the range 0..99.
     * @param int $referenceYear Full year used to choose the nearest matching year.
     *
     * @return int Expanded calendar year.
     *
     * @throws InvalidArgumentException If year is not in the range 0..99.
     * @throws InvalidArgumentException If reference year is zero.
     */
    public static function expandTwoDigitYear(int $year, int $referenceYear): int
    {
        if ($year < 0 || $year > 99) {
            throw new InvalidArgumentException("Two-digit year {$year} is out of range: 0..99.");
        }

        if ($referenceYear === 0) {
            throw new InvalidArgumentException('Referenced year zero is not defined.');
        }

        $absoluteReferenceYear = abs($referenceYear);
        $referenceCentury = intdiv($absoluteReferenceYear, 100) * 100;
        $expandedYear = $absoluteReferenceYear;
        $minimumDistance = PHP_INT_MAX;

        foreach ([$referenceCentury - 100, $referenceCentury, $referenceCentury + 100] as $century) {
            $candidate = $century + $year;
            if ($candidate <= 0) {
                continue;
            }

            $distance = abs($candidate - $absoluteReferenceYear);
            if ($distance < $minimumDistance) {
                $expandedYear = $candidate;
                $minimumDistance = $distance;
            }
        }

        return $referenceYear < 0 ? -$expandedYear : $expandedYear;
    }
}
