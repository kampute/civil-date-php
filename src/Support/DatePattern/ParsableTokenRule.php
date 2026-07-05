<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;

/**
 * Defines parsing behavior for a date-pattern token.
 *
 * @see ParseCapture
 * @see TokenElement::regex()
 */
interface ParsableTokenRule extends TokenRule
{
    /**
     * Returns the semantic calendar-date property represented by this rule.
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
     * @see ParseCapture::parse()
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int;
}
