<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateOutOfRangeException;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;

/**
 * Associates a token definition with its optional calendar scope.
 *
 * @see TokenDefinition
 * @see TokenRegistry
 */
final class Token implements Segment
{
    /**
     * Creates a date-pattern token.
     *
     * @param Calendar|null $calendar Explicit calendar, or null when unscoped.
     * @param TokenDefinition $definition Token semantics.
     */
    public function __construct(
        private readonly ?Calendar $calendar,
        private readonly TokenDefinition $definition
    ) {
    }

    /**
     * Returns the explicit calendar scope of this token.
     *
     * @return Calendar|null Explicit calendar, or null when unscoped.
     */
    public function calendar(): ?Calendar
    {
        return $this->calendar;
    }

    /**
     * Returns the semantic calendar-date property represented by this token.
     *
     * @return string Calendar-date property name.
     *
     * @see TokenDefinition::property()
     */
    public function property(): string
    {
        return $this->definition->property();
    }

    /**
     * Returns the regular-expression fragment for parsing this token.
     *
     * @return string Regular-expression fragment.
     *
     * @see TokenDefinition::captureRegex()
     */
    public function captureRegex(): string
    {
        return $this->definition->captureRegex();
    }

    /**
     * Formats this token for a date.
     *
     * @param CalendarDate $date Date providing the token value.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted token value.
     *
     * @throws DateOutOfRangeException When scoped calendar conversion is outside the supported range.
     *
     * @see TokenDefinition::format()
     * @see \Kampute\CivilDate\CalendarDate::format()
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        if ($this->calendar !== null) {
            $date = $date->toCalendar($this->calendar);
        }

        return $this->definition->format($date, $locale);
    }

    /**
     * Parses the captured value into a calendar-date field value.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed field value.
     *
     * @throws DateParseException If the captured value is invalid.
     *
     * @see TokenDefinition::parse()
     * @see \Kampute\CivilDate\CalendarDate::parse()
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        return $this->definition->parse($value, $calendar, $locale);
    }
}
