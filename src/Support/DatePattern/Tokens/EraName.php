<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\ParsableTokenRule;

/**
 * Formats and parses localized era-name token values.
 */
class EraName implements ParsableTokenRule
{
    /**
     * Creates an era-name token rule.
     *
     * @param bool $abbreviated Whether abbreviated era names are used.
     */
    public function __construct(private readonly bool $abbreviated = false)
    {
    }

    /**
     * Returns the semantic calendar-date property represented by this rule.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return 'calendar';
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
     * Formats the era name.
     *
     * @param CalendarDate $date Date providing the calendar.
     * @param Locale $locale Locale definition.
     *
     * @return string Localized era name.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        return $this->abbreviated
            ? $locale->abbreviatedEraName($date::calendar())
            : $locale->eraName($date::calendar());
    }

    /**
     * Parses the era name.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed calendar value.
     *
     * @throws DateParseException If the era name is unrecognized.
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        $parsed = $this->abbreviated
            ? $locale->abbreviatedEraFromName($value)
            : $locale->eraFromName($value);

        return $parsed->value
            ?? throw new DateParseException("Unrecognized era name: \"{$value}\".");
    }
}
