<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\ParsableTokenRule;

/**
 * Formats and parses localized day-of-week-name token values.
 */
class DayOfWeekName implements ParsableTokenRule
{
    /**
     * Creates a day-of-week-name token rule.
     *
     * @param bool $abbreviated Whether abbreviated names are used.
     */
    public function __construct(
        private readonly bool $abbreviated
    ) {
    }

    /**
     * Returns the semantic calendar-date property represented by this rule.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return 'dayOfWeek';
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
     * Formats the day-of-week name.
     *
     * @param CalendarDate $date Date providing the day of week.
     * @param Locale $locale Locale definition.
     *
     * @return string Localized day-of-week name.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        return $this->abbreviated
            ? $locale->abbreviatedDayOfWeekName($date->dayOfWeek())
            : $locale->dayOfWeekName($date->dayOfWeek());
    }

    /**
     * Parses the day-of-week name.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed day-of-week value.
     *
     * @throws DateParseException If the day-of-week name is unrecognized.
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        $dayOfWeek = $this->abbreviated
            ? $locale->abbreviatedDayOfWeekFromName($value)
            : $locale->dayOfWeekFromName($value);

        return $dayOfWeek->value
            ?? throw new DateParseException("Unrecognized day of week name: \"{$value}\".");
    }
}
