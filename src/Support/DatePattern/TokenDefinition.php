<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents the parsing and formatting behavior of a date-pattern token.
 *
 * @see Token
 * @see TokenRegistry
 */
interface TokenDefinition
{
    /**
     * Returns the semantic calendar-date property represented by this definition.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string;

    /**
     * Returns the regular-expression fragment for capturing this token.
     *
     * @return string Capturing regular-expression fragment.
     */
    public function captureRegex(): string;

    /**
     * Formats the token for a date.
     *
     * @param CalendarDate $date Date providing the token value.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted token value.
     *
     * @see Token::format()
     */
    public function format(CalendarDate $date, Locale $locale): string;

    /**
     * Parses a captured value.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed semantic value.
     *
     * @throws DateParseException If the captured value is invalid.
     *
     * @see Token::parse()
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int;
}
