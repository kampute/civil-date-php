<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\Calendar;
use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\DateOutOfRangeException;
use Kampute\CivilDate\Localization\Locale;

/**
 * Associates a token rule with its pattern symbol and optional calendar scope.
 *
 * @see TokenRule
 * @see TokenRegistry
 */
final class TokenElement implements PatternElement
{
    /**
     * Creates a date-pattern token element.
     *
     * @param string $symbol Pattern token symbol.
     * @param Calendar|null $calendar Explicit calendar, or null when unscoped.
     * @param TokenRule $rule Token behavior.
     */
    public function __construct(
        private readonly string $symbol,
        private readonly ?Calendar $calendar,
        private readonly TokenRule $rule
    ) {
    }

    /**
     * Returns the pattern token symbol.
     *
     * @return string Pattern token symbol.
     */
    public function symbol(): string
    {
        return $this->symbol;
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
     * Returns the token rule.
     *
     * @return TokenRule Token rule.
     */
    public function rule(): TokenRule
    {
        return $this->rule;
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
     * @see TokenRule::format()
     * @see \Kampute\CivilDate\CalendarDate::format()
     */
    public function format(CalendarDate $date, Locale $locale): string
    {
        if ($this->calendar !== null) {
            $date = $date->toCalendar($this->calendar);
        }

        return $this->rule->format($date, $locale);
    }
}
