<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern\Tokens;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateParseException;
use Kampute\CivilDate\Localization\Locale;
use Kampute\CivilDate\Support\DatePattern\ParsableTokenRule;
use LogicException;

/**
 * Formats and parses localized number-digit token values.
 */
class NumberDigit implements ParsableTokenRule
{
    /**
     * Creates a localized number-digit token rule.
     *
     * @param string $property Calendar-date property name.
     * @param int $minimumDigits Minimum formatted digit width.
     * @param bool $signed Whether negative values may be captured.
     */
    public function __construct(
        private readonly string $property,
        private readonly int $minimumDigits = 1,
        private readonly bool $signed = false
    ) {
    }

    /**
     * Returns the semantic calendar-date property represented by this rule.
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
        return $this->signed ? '(-?\\p{Nd}+)' : '(\\p{Nd}+)';
    }

    /**
     * Formats the token as localized number digits.
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
        $value = $date->{$this->property()};
        if (!is_int($value)) {
            throw new LogicException("Calendar-date property {$this->property()} is not an integer.");
        }

        return $locale->numberLocalizer()->formatDigits($value, $this->minimumDigits);
    }

    /**
     * Parses localized number digits.
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
        return $locale->numberLocalizer()->parseDigits($value)
            ?? throw new DateParseException("Invalid {$this->property} value: \"{$value}\".");
    }
}
