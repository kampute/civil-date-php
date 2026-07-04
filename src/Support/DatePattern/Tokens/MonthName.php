<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\TokenDefinition;

/**
 * Formats and parses localized month-name token values.
 */
class MonthName implements TokenDefinition
{
    /**
     * Creates a month-name token definition.
     *
     * @param bool $abbreviated Whether abbreviated names are used.
     */
    public function __construct(
        private readonly bool $abbreviated
    ) {
    }

    /**
     * Returns the semantic calendar-date property represented by this definition.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return 'month';
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
     * Formats the month name.
     *
     * @param CalendarDate $date Date providing the month.
     * @param Locale $locale Locale definition.
     *
     * @return string Localized month name.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        return $this->abbreviated
            ? $locale->abbreviatedMonthName($date::calendar(), $date->month())
            : $locale->monthName($date::calendar(), $date->month());
    }

    /**
     * Parses the month name.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed month.
     *
     * @throws DateParseException If the month name is unrecognized.
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        $month = $this->abbreviated
            ? $locale->abbreviatedMonthFromName($calendar, $value)
            : $locale->monthFromName($calendar, $value);

        return $month
            ?? throw new DateParseException("Unrecognized month name: \"{$value}\".");
    }
}
