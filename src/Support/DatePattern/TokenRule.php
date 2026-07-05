<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\Localization\Locale;

/**
 * Defines the formatting behavior of a date-pattern token.
 *
 * @see TokenElement
 * @see TokenRegistry
 */
interface TokenRule
{
    /**
     * Formats the token for a date.
     *
     * @param CalendarDate $date Date providing the token value.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted token value.
     *
     * @see TokenElement::format()
     */
    public function format(CalendarDate $date, Locale $locale): string;
}
