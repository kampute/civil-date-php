<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Localization\Numbers\NumberForm;
use Kampute\CivilDate\Support\DatePattern\TokenDefinition;
use LogicException;

/**
 * Formats and parses localized number-word token values.
 */
class NumberWord implements TokenDefinition
{
    /**
     * Creates a number-word token definition.
     *
     * @param string $property Calendar-date property name.
     * @param bool $ordinal Whether ordinal words are formatted and parsed.
     */
    public function __construct(
        private readonly string $property,
        private readonly bool $ordinal
    ) {
    }

    /**
     * Returns the semantic calendar-date property represented by this definition.
     *
     * @return string Calendar-date property name.
     */
    public function property(): string
    {
        return $this->property;
    }

    /**
     * Returns the regular-expression fragment for capturing this token.
     *
     * @return string Capturing regular-expression fragment.
     */
    public function captureRegex(): string
    {
        return '(.+)';
    }

    /**
     * Formats the token as localized number words.
     *
     * @param CalendarDate $date Date providing the token value.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted token value.
     *
     * @throws LogicException If the calendar-date property is not an integer.
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        $value = $date->{$this->property};
        if (!is_int($value)) {
            throw new LogicException("Calendar-date property {$this->property} is not an integer.");
        }

        return $this->ordinal
            ? $locale->numberLocalizer()->formatOrdinal($value)
            : $locale->numberLocalizer()->formatCardinal($value);
    }

    /**
     * Parses localized number words.
     *
     * @param string $value Captured input value.
     * @param Calendar $calendar Calendar associated with the token scope.
     * @param Locale $locale Locale definition.
     *
     * @return int Parsed semantic value.
     *
     * @throws DateParseException If the captured value is invalid.
     */
    public function parse(string $value, Calendar $calendar, Locale $locale): int
    {
        $parsed = $locale->numberLocalizer()->parseWords($value);
        if ($parsed->value() === null) {
            throw new DateParseException("Invalid {$this->property} value: \"{$value}\".");
        }
        if (!$this->ordinal && $parsed->form() === NumberForm::Ordinal) {
            throw new DateParseException("Invalid {$this->property} value: \"{$value}\". Expected cardinal words, but got ordinal words.");
        }

        return $parsed->value();
    }
}
