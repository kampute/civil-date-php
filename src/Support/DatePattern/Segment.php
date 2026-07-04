<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents a parsed segment of a date pattern.
 *
 * @see PatternParser::parse()
 * @see Literal
 * @see Token
 */
interface Segment
{
    /**
     * Returns the regular-expression fragment for parsing this segment.
     *
     * @return string Regular-expression fragment.
     */
    public function captureRegex(): string;

    /**
     * Formats this segment for a date.
     *
     * @param CalendarDate $date Date providing the segment value.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted segment.
     *
     * @see \Kampute\CivilDate\CalendarDate::format()
     */
    public function format(CalendarDate $date, Locale $locale): string;
}
