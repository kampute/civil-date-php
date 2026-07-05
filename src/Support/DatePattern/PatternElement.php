<?php

declare(strict_types=1);

namespace Kampute\CivilDate\Support\DatePattern;

use Kampute\CivilDate\CalendarDate;
use Kampute\CivilDate\Localization\Locale;

/**
 * Represents an element in a compiled date pattern.
 *
 * @see PatternCompiler::compile()
 * @see TextElement
 * @see TokenElement
 */
interface PatternElement
{
    /**
     * Formats this element for a date.
     *
     * @param CalendarDate $date Date providing the element value.
     * @param Locale $locale Locale definition.
     *
     * @return string Formatted element.
     *
     * @see \Kampute\CivilDate\CalendarDate::format()
     */
    public function format(CalendarDate $date, Locale $locale): string;

}
