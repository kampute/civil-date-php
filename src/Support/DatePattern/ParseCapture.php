<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents a token capture in a compiled parse pattern.
 *
 * @see CompiledPattern::match()
 */
final class ParseCapture
{
    /**
     * Creates a parse capture.
     *
     * @param Calendar|null $calendarScope Explicit calendar scope, or null when unscoped.
     * @param ParsableTokenRule $rule Parsable token rule.
     */
    public function __construct(
        private readonly ?Calendar $calendarScope,
        private readonly ParsableTokenRule $rule
    ) {
    }

    /**
     * Returns the regular-expression fragment for capturing this token.
     *
     * @return string Capturing regular-expression fragment.
     *
     */
    public function regex(): string
    {
        return $this->rule->captureRegex();
    }

    /**
     * Returns the explicit calendar scope of this capture.
     *
     * @return Calendar|null Explicit calendar scope, or null when unscoped.
     */
    public function calendarScope(): ?Calendar
    {
        return $this->calendarScope;
    }

    /**
     * Returns the semantic calendar-date property represented by this capture.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return $this->rule->property();
    }

    /**
     * Parses the captured value into a calendar-date field value.
     *
     * @param string $value Captured input value.
     * @param Calendar $defaultCalendar Calendar used when this capture is unscoped.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed field value.
     *
     * @throws DateParseException If the captured value is invalid.
     *
     * @see ParsableTokenRule::parse()
     */
    public function parse(string $value, Calendar $defaultCalendar, Locale $locale): int
    {
        return $this->rule->parse($value, $this->calendarScope ?? $defaultCalendar, $locale);
    }
}
