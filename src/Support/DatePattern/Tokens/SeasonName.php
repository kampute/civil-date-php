<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\ParsableTokenRule;

/**
 * Formats and parses localized season-name token values.
 */
class SeasonName implements ParsableTokenRule
{
    /**
     * Returns the semantic calendar-date property represented by this rule.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return 'season';
    }

    /**
     * Returns the regular-expression fragment for capturing this token.
     *
     * @return string Capturing regular-expression fragment.
     */
    public function captureRegex(): string
    {
        return '(.+?)';
    }

    /**
     * Formats the season name.
     *
     * @param CalendarDate $date Date providing the season.
     * @param Locale $locale Locale definition.
     *
     * @return string Localized season name.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        return $locale->seasonName($date->season());
    }

    /**
     * Parses the season name.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed season value.
     *
     * @throws DateParseException If the season name is unrecognized.
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        return $locale->seasonFromName($value)->value
            ?? throw new DateParseException("Unrecognized season name: \"{$value}\".");
    }
}
