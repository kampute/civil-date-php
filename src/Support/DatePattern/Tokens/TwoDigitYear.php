<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\ParsableTokenRule;
use Kampute\CivilDate\Support\EuclideanDivision;
use Kampute\CivilDate\Support\YearNumbering;

/**
 * Formats and parses two-digit year token values.
 */
class TwoDigitYear implements ParsableTokenRule
{
    /**
     * Returns the semantic calendar-date property represented by this rule.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return 'year';
    }

    /**
     * Returns the regular-expression fragment for capturing this token.
     *
     * @return string Capturing regular-expression fragment.
     */
    public function captureRegex(): string
    {
        return '(\\p{Nd}+)';
    }

    /**
     * Formats the year as two localized digits.
     *
     * @param CalendarDate $date Date providing the year.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted year.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        $year = $date->year();
        $twoDigitYear = $year <= -100
            ? abs($year) % 100
            : EuclideanDivision::remainder($year, 100);

        return $locale->numberLocalizer()->formatDigits($twoDigitYear, 2);
    }

    /**
     * Parses and expands a two-digit year.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Expanded year.
     *
     * @throws DateParseException If the captured value is invalid or outside 00..99.
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        $twoDigitYear = $locale->numberLocalizer()->parseDigits($value);
        if ($twoDigitYear === null) {
            throw new DateParseException("Invalid year value: \"{$value}\".");
        }
        if ($twoDigitYear < 0 || $twoDigitYear > 99) {
            throw new DateParseException("Two-digit year value \"{$value}\" is out of scope (00..99).");
        }

        return YearNumbering::expandTwoDigitYear($twoDigitYear, $calendar->dateClass()::today()->year());
    }
}
